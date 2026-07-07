<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlServiceClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ml.base_url'), '/');
        $this->timeout = (int) config('services.ml.timeout', 10);
    }

    public function predictWaitTime(array $payload): ?array
    {
        return $this->post('/predict/wait-time', $payload);
    }

    public function predictRange(array $payload): ?array
    {
        return $this->post('/predict/range', $payload);
    }

    public function recommendStations(array $payload): ?array
    {
        return $this->post('/recommend/stations', $payload);
    }

    public function planRoute(array $payload): ?array
    {
        return $this->post('/route/plan', $payload);
    }

    /**
     * Returns the decoded JSON response, or null if the ML service is
     * unreachable/erroring — callers must handle the null case and degrade
     * gracefully rather than surfacing a 500 to the client.
     */
    private function post(string $path, array $payload): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post("{$this->baseUrl}{$path}", $payload);

            $response->throw();

            return $response->json();
        } catch (\Throwable $e) {
            Log::error("ML service call failed: {$path}", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
