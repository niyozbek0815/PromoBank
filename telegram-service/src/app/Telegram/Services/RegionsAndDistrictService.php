<?php
namespace App\Telegram\Services;

use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegionsAndDistrictService
{
    protected $baseUrl;
    public function __construct(protected FromServiceRequest $forwarder)
    {
        $this->baseUrl = config('services.urls.auth_service');
    }
    public function handle(): array
    {
        Cache::store('bot')->forget('regionslang');
        return $this->fetchAndCache(
            key: 'regionslang',
            endpoint: '/regionslang',
            responsePath: 'data.regions'
        );
    }

    public function district(int $regionId): array
    {
        return $this->fetchAndCache(
            key: "districts:$regionId",
            endpoint: "/regions/$regionId/districts",
            responsePath: 'data.districts'
        );
    }
    protected function fetchAndCache(string $key, string $endpoint, string $responsePath): array
    {
        return Cache::store('bot')->remember($key, now()->addDay(), function () use ($endpoint, $responsePath) {
            $response = $this->forwarder->forward('GET', $this->baseUrl, $endpoint, []);
            if (!$response->successful()) {
                Log::error('API fetch error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }
            $data = data_get($response->json(), $responsePath, []);
            return is_array($data) ? $data : [];
        });
    }
}
