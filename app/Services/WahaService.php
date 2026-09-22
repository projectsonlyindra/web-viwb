<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    public function sendText(string $noWa, string $pesan): array
    {
        if (config('services.waha.dummy_mode')) {
            Log::info("[WAHA DUMMY] → {$noWa}: ".substr($pesan, 0, 80));

            return ['status' => 'dummy', 'no_wa' => $noWa];
        }

        $response = Http::post(config('services.waha.base_url').'/api/sendText', [
            'session' => config('services.waha.session'),
            'chatId' => "{$noWa}@c.us",
            'text' => $pesan,
        ]);

        return $response->json();
    }
}
