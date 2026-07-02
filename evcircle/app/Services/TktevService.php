<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TktevService
{
    private string $baseUrl;
    private string $aesKey;

    private string $tenantId;
    private string $lang;
    private string $timezone;

    public function __construct()
    {
        $this->baseUrl  = rtrim((string) config('services.tktev.base_url'), '/');
        $this->aesKey   = (string) config('services.tktev.aes_key');

        $this->tenantId = (string) (config('services.tktev.tenant_id') ?? '1');
        $this->lang     = (string) (config('services.tktev.lang') ?? 'zh-CN');
        $this->timezone = (string) (config('services.tktev.timezone') ?? 'Asia/Kolkata');
    }

    private function defaultHeaders(): array
    {
        return [
            'x-tenant-id' => $this->tenantId,
            'lang'        => $this->lang,
            'timezone'    => $this->timezone,
            'accept'      => 'application/json',
        ];
    }

    /**
     * Login and return accessToken
     */
    public function login(): string
    {
        $account  = (string) config('services.tktev.username');
        $password = (string) config('services.tktev.password');

        if ($account === '' || $password === '' || $this->aesKey === '') {
            throw new \RuntimeException('TKTEV config missing (username/password/aes_key).');
        }

        // AES-128-ECB encryption (raw bytes -> base64)
        $encryptedPassword = openssl_encrypt(
            $password,
            'AES-128-ECB',
            $this->aesKey,
            OPENSSL_RAW_DATA
        );

        if ($encryptedPassword === false) {
            throw new \RuntimeException('TKTEV password encryption failed.');
        }

        $payload = [
            'account'     => $account,
            'password'    => base64_encode($encryptedPassword),
            'rememberMe'  => false,
            'endType'     => 1,
            'loginType'   => 1,
            'clientType'  => 'PC',
            'encrypted'   => true,
            'region'      => '1',
        ];

        $response = Http::acceptJson()
            ->timeout(20)
            ->post($this->baseUrl . '/op/v1/auth/login', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('TKTEV login failed: ' . $response->body());
        }

        $token = $response->json('result.accessToken');

        if (!$token) {
            throw new \RuntimeException('TKTEV token not found: ' . $response->body());
        }

        return (string) $token;
    }

    /**
     * Manual add pre-save
     * Endpoint exists in doc (preSave returns preSaveKey, then commit uses it). :contentReference[oaicite:3]{index=3}
     */
    public function manualAddPreSave(array $payload, string $token)
    {
        return Http::withToken($token)
            ->withHeaders($this->defaultHeaders())
            ->acceptJson()
            ->timeout(25)
            ->post($this->baseUrl . '/op/v1/business/chargePile/manual/add/pre/save', $payload);
    }

    /**
     * Commit the pre-saved charger(s).
     * Doc: POST /op/v1/business/chargePile/manual/add/commit { preSaveKey } :contentReference[oaicite:4]{index=4}
     */
    public function manualAddCommit(string $preSaveKey, string $token)
    {
        return Http::withToken($token)
            ->withHeaders($this->defaultHeaders())
            ->acceptJson()
            ->timeout(25)
            ->post($this->baseUrl . '/op/v1/business/chargePile/manual/add/commit', [
                'preSaveKey' => $preSaveKey,
            ]);
    }

    /**
     * Optional: Batch add device numbers (NOT your custom station fields).
     * Endpoint exists: /op/v1/business/chargePile/manual/batch/add :contentReference[oaicite:5]{index=5}
     */
    public function manualBatchAdd(array $payload, string $token)
    {
        return Http::withToken($token)
            ->withHeaders($this->defaultHeaders())
            ->acceptJson()
            ->timeout(25)
            ->post($this->baseUrl . '/op/v1/business/chargePile/manual/batch/add', $payload);
    }
}
