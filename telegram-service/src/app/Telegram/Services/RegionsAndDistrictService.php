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
        return $this->fetchAndCache(
            key: 'regions',
            endpoint: '/regions',
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
        $cached = Cache::connection('bot')->get($key);
        if (!empty($cached)) {
            $decoded = json_decode($cached, true);
            return is_array($decoded) ? $decoded : [];
        }

        Log::info("Fetching from $endpoint");

        $response = $this->forwarder->forward(
            'GET',
            $this->baseUrl,
            $endpoint,
            []
        );

        if (!$response->successful()) {
            logger()->error('API fetch error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $data = $response->json($responsePath) ?? [];

        if (is_array($data) && !empty($data)) {
            Cache::connection('bot')->put($key, json_encode($data), now()->addHours(12));
        }

        return is_array($data) ? $data : [];
    }
}