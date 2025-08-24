<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendHttpRequest
{
    /**
     * Send HTTP Request
     *
     * @param string $method (GET, POST, PUT, DELETE)
     * @param string $url
     * @param array $headers
     * @param array $body
     * @return array [success => bool, data => mixed, error => string|null]
     */
    public function sendRequest(
        string $method,
        string $url,
        array  $headers = [],
        array  $body = []
    ): array
    {
        try {
            $response = Http::withHeaders($headers)
                ->send($method, $url, ['json' => $body]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('HTTP Request failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
