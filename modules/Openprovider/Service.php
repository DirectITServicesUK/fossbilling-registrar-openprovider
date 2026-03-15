<?php

/**
 * OpenProvider DNS Management Service
 * Handles communication with the OpenProvider REST API for DNS zone and record management.
 */

namespace Box\Mod\Openprovider;

use FOSSBilling\InjectionAwareInterface;

class Service implements InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;
    private ?string $token = null;
    private ?array $credentials = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    /**
     * Load OpenProvider credentials from the existing registrar configuration.
     */
    private function getCredentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $registrar = $this->di['db']->findOne('TldRegistrar', 'registrar = :reg', [':reg' => 'OpenProvider']);
        if (!$registrar) {
            throw new \FOSSBilling\InformationException('OpenProvider registrar is not configured. Set it up under System > Domain Registration first.');
        }

        $config = json_decode($registrar->config ?? '', true) ?? [];
        if (empty($config['Username']) || empty($config['Password']) || empty($config['ApiUrl'])) {
            throw new \FOSSBilling\InformationException('OpenProvider registrar credentials are incomplete.');
        }

        $this->credentials = $config;
        return $this->credentials;
    }

    /**
     * Get a bearer token from OpenProvider.
     */
    private function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $creds = $this->getCredentials();
        $apiUrl = rtrim($creds['ApiUrl'], '/') . '/v1beta';

        $ch = curl_init($apiUrl . '/auth/login');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => $creds['Username'],
            'password' => $creds['Password'],
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['data']['token'])) {
            throw new \FOSSBilling\InformationException('Failed to authenticate with OpenProvider API.');
        }

        $this->token = $data['data']['token'];
        return $this->token;
    }

    /**
     * Make an authenticated request to the OpenProvider API.
     */
    private function request(string $method, string $path, array $data = []): array
    {
        $creds = $this->getCredentials();
        $url = rtrim($creds['ApiUrl'], '/') . '/v1beta' . $path;
        $token = $this->getToken();

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method !== 'GET' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($result, true);
        if (!is_array($response)) {
            throw new \FOSSBilling\InformationException('Invalid response from OpenProvider API.');
        }

        if (isset($response['code']) && $response['code'] !== 0) {
            $desc = $response['desc'] ?? 'Unknown error';
            throw new \FOSSBilling\InformationException("OpenProvider API error: {$desc}");
        }

        return $response;
    }

    // --- DNS Zone Methods ---

    public function getZoneList(array $params = []): array
    {
        $query = [
            'limit' => $params['per_page'] ?? 100,
            'offset' => (($params['page'] ?? 1) - 1) * ($params['per_page'] ?? 100),
        ];
        if (!empty($params['name_pattern'])) {
            $query['name_pattern'] = $params['name_pattern'];
        }

        $response = $this->request('GET', '/dns/zones', $query);
        return $response['data'] ?? [];
    }

    public function getZone(string $name, bool $withRecords = true): array
    {
        $query = [];
        if ($withRecords) {
            $query['with_records'] = 'true';
        }
        $query['with_dnskey'] = 'true';

        $response = $this->request('GET', "/dns/zones/{$name}", $query);
        return $response['data'] ?? [];
    }

    public function createZone(string $domainName, string $extension, array $records = [], bool $secured = false, string $provider = ''): array
    {
        $data = [
            'domain' => [
                'name' => $domainName,
                'extension' => $extension,
            ],
            'type' => 'master',
            'secured' => $secured,
        ];

        if (!empty($records)) {
            $data['records'] = $records;
        }

        if (!empty($provider)) {
            $data['provider'] = $provider;
        }

        return $this->request('POST', '/dns/zones', $data);
    }

    public function deleteZone(string $name): array
    {
        return $this->request('DELETE', "/dns/zones/{$name}");
    }

    // --- DNS Record Methods ---

    public function getRecords(string $zoneName, array $params = []): array
    {
        $query = ['limit' => 500];
        if (!empty($params['type'])) {
            $query['type'] = $params['type'];
        }

        $response = $this->request('GET', "/dns/zones/{$zoneName}/records", $query);
        return $response['data'] ?? [];
    }

    public function addRecords(string $zoneName, array $records): array
    {
        return $this->request('PUT', "/dns/zones/{$zoneName}", [
            'records' => ['add' => $records],
        ]);
    }

    public function updateRecord(string $zoneName, array $originalRecord, array $newRecord): array
    {
        return $this->request('PUT', "/dns/zones/{$zoneName}", [
            'records' => [
                'update' => [
                    [
                        'original_record' => $originalRecord,
                        'record' => $newRecord,
                    ],
                ],
            ],
        ]);
    }

    public function removeRecords(string $zoneName, array $records): array
    {
        return $this->request('PUT', "/dns/zones/{$zoneName}", [
            'records' => ['remove' => $records],
        ]);
    }

    // --- DNS Template Methods ---

    public function getTemplateList(): array
    {
        $response = $this->request('GET', '/dns/templates', ['with_records' => 'true']);
        return $response['data'] ?? [];
    }

    public function createTemplate(string $name, array $records): array
    {
        return $this->request('POST', '/dns/templates', [
            'name' => $name,
            'records' => $records,
        ]);
    }

    public function deleteTemplate(int $id): array
    {
        return $this->request('DELETE', "/dns/templates/{$id}");
    }
}
