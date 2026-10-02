<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    public function sendText(string $noWa, string $pesan): array
    {
        $targetWa = $this->normalizePhone($noWa);

        if (config('services.waha.dummy_mode')) {
            Log::info("[WAHA DUMMY] → {$targetWa}: ".substr($pesan, 0, 80));

            return ['status' => 'dummy', 'no_wa' => $targetWa];
        }

        $baseUrl = (string) config('services.waha.base_url');
        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Konfigurasi WAHA base URL tidak valid.');
        }

        $response = Http::post(rtrim($baseUrl, '/').'/api/sendText', [
            'session' => config('services.waha.session'),
            'chatId' => "{$targetWa}@c.us",
            'text' => $pesan,
        ])->throw();

        return $response->json();
    }

    public function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            return '62'.substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62'.$clean;
        }

        return $clean;
    }
}
