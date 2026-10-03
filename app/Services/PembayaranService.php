<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTagihan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PembayaranService
{
    public function __construct(
        private DendaService $dendaService,
        private WahaService $wahaService,
    ) {}

    public function getList(User $user, array $filters = [], ?int $perPage = null): BaseCollection|LengthAwarePaginator
    {
        $query = Pembayaran::query()->with(['warga', 'item.tagihan']);

        if ($user->role === Role::WARGA) {
            $query->where('warga_id', $user->warga_id);
        }

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        if ($perPage !== null) {
            return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * @param  int[]  $tagihanIds
     */
    public function create(int $wargaId, array $tagihanIds, ?UploadedFile $bukti, ?string $catatan): Pembayaran
    {
        return DB::transaction(function () use ($wargaId, $tagihanIds, $bukti, $catatan) {
            $tagihan = Tagihan::query()
                ->whereKey($tagihanIds)
                ->where('warga_id', $wargaId)
                ->whereIn('status', [StatusTagihan::BELUM_BAYAR, StatusTagihan::SEBAGIAN])
                ->get();

            if ($tagihan->isEmpty()) {
                throw ValidationException::withMessages([
                    'tagihan_ids' => 'Tidak ada tagihan valid yang belum lunas untuk dibayarkan.',
                ]);
            }

            $totalDibayar = 0;
            $items = [];

            foreach ($tagihan as $t) {
                $denda = $this->dendaService->hitungDenda($t->tanggal_jatuh_tempo, $t->denda_harian, $t->denda_maksimal);
                $totalDibayar += $t->nominal + $denda;
                $items[] = [
                    'tagihan_id' => $t->id,
                    'nominal' => $t->nominal,
                    'denda_dibayar' => $denda,
                ];
            }

            $buktiUrl = $bukti?->store('bukti-pembayaran/'.$wargaId, 'public');

            $pembayaran = Pembayaran::query()->create([
                'warga_id' => $wargaId,
                'total_dibayar' => $totalDibayar,
                'bukti_url' => $buktiUrl,
                'catatan' => $catatan,
                'status' => StatusPembayaran::MENUNGGU_KONFIRMASI,
            ]);

            $pembayaran->item()->createMany($items);

            return $pembayaran->load('item.tagihan');
        });
    }

    public function confirm(Pembayaran $pembayaran, User $confirmedBy): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $confirmedBy) {
            $pembayaran->update([
                'status' => StatusPembayaran::DIKONFIRMASI,
                'dikonfirmasi_oleh_id' => $confirmedBy->id,
                'dikonfirmasi_at' => now(),
            ]);

            $pembayaran->load('item.tagihan', 'warga');

            foreach ($pembayaran->item as $item) {
                $item->tagihan->update(['status' => StatusTagihan::LUNAS]);
            }

            Log::info("Pembayaran ID: {$pembayaran->id} warga ID: {$pembayaran->warga_id} total Rp{$pembayaran->total_dibayar} dikonfirmasi oleh User ID: {$confirmedBy->id} ({$confirmedBy->name}).");

            $this->wahaService->sendText(
                $pembayaran->warga->no_wa,
                "Pembayaran Anda sebesar Rp{$pembayaran->total_dibayar} telah dikonfirmasi. Terima kasih."
            );

            return $pembayaran;
        });
    }

    public function reject(Pembayaran $pembayaran, string $catatan): Pembayaran
    {
        $pembayaran->update([
            'status' => StatusPembayaran::DITOLAK,
            'catatan_review' => $catatan,
        ]);

        $pembayaran->load('warga');

        Log::warning("Pembayaran ID: {$pembayaran->id} warga ID: {$pembayaran->warga_id} ditolak. Alasan: {$catatan}");

        $this->wahaService->sendText(
            $pembayaran->warga->no_wa,
            "Pembayaran Anda ditolak. Alasan: {$catatan}"
        );

        return $pembayaran;
    }
}
