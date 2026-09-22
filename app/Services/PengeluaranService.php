<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\StatusPengeluaran;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class PengeluaranService
{
    public function __construct(private WahaService $wahaService) {}

    public function getList(array $filters = []): Collection
    {
        return Pengeluaran::query()
            ->with(['dibuatOleh', 'disetujuiOleh'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * SUPERADMIN entri langsung APPROVED (bypass approval).
     * BENDAHARA entri sebagai DRAFT, perlu diajukan terpisah (submitApproval).
     */
    public function create(array $data, User $pembuat, ?UploadedFile $bukti): Pengeluaran
    {
        $isAutoApprove = $pembuat->role === Role::SUPERADMIN;

        return Pengeluaran::query()->create([
            ...$data,
            'bukti_url' => $bukti?->store('bukti-pengeluaran', 'public'),
            'dibuat_oleh_id' => $pembuat->id,
            'status' => $isAutoApprove ? StatusPengeluaran::APPROVED : StatusPengeluaran::DRAFT,
            'disetujui_oleh_id' => $isAutoApprove ? $pembuat->id : null,
            'disetujui_at' => $isAutoApprove ? now() : null,
        ]);
    }

    public function submitApproval(Pengeluaran $pengeluaran): Pengeluaran
    {
        $pengeluaran->update(['status' => StatusPengeluaran::MENUNGGU_APPROVAL]);

        $penerima = User::query()
            ->whereIn('role', [Role::SUPERADMIN, Role::KETUA_RT])
            ->with('warga')
            ->get();

        foreach ($penerima as $user) {
            if ($user->warga?->no_wa) {
                $this->wahaService->sendText(
                    $user->warga->no_wa,
                    "Pengeluaran baru menunggu approval: {$pengeluaran->kategori} - Rp{$pengeluaran->nominal}"
                );
            }
        }

        return $pengeluaran;
    }

    public function approve(Pengeluaran $pengeluaran, User $approver): Pengeluaran
    {
        $pengeluaran->update([
            'status' => StatusPengeluaran::APPROVED,
            'disetujui_oleh_id' => $approver->id,
            'disetujui_at' => now(),
        ]);

        return $pengeluaran;
    }

    public function reject(Pengeluaran $pengeluaran, User $approver, string $catatan): Pengeluaran
    {
        $pengeluaran->update([
            'status' => StatusPengeluaran::REJECTED,
            'disetujui_oleh_id' => $approver->id,
            'disetujui_at' => now(),
            'catatan_review' => $catatan,
        ]);

        return $pengeluaran;
    }
}
