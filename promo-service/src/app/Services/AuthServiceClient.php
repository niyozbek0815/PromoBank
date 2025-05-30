<?php

namespace App\Services;

class AuthServiceClient
{
    protected string $baseUrl;
    protected \GuzzleHttp\Client $http;

    public function __construct()
    {
        $this->baseUrl = config('services.auth_service.url'); // .env dan olinadi
        $this->http = new \GuzzleHttp\Client(['base_uri' => $this->baseUrl]);
    }

    public function findUserByPhone(string $phone): ?array
    {
        try {
            $response = $this->http->get("/api/users/by-phone/{$phone}");
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $data['user'] ?? null;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
        return null;
    }

    public function createUser(array $userData): array
    {
        $response = $this->http->post('/api/users', [
            'json' => $userData,
        ]);
        return json_decode($response->getBody(), true);
    }
}
