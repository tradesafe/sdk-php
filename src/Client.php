<?php

namespace TradeSafe;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use TradeSafe\Traits\Allocations;
use TradeSafe\Traits\ApiClient;
use TradeSafe\Traits\Tokens;
use TradeSafe\Traits\Transactions;

class Client
{
    use ApiClient, Tokens, Transactions, Allocations;

    /**
     * OAuth2 Access Token
     * @var AccessToken|string|null
     */
    private AccessToken|string|null $accessToken;
    private string $authEndpoint;
    private string $authMethod = 'bearer';
    private string $apiEndpoint;

    const TRADESAFE_CLIENT_VERSION = 'v0.0.0-alpha.1';

    public function __construct(
        private readonly string  $clientId,
        private readonly string  $clientSecret,
        private readonly ?string $cachedToken = null,
        private readonly ?string $environment = null,
    )
    {
        preg_match('/^(?<environ>[a-z]+)_/', $this->clientSecret, $matches);

        $environ = $matches['environ'] ?? null;

        if (empty($environ)) {
            $this->authMethod = 'oauth2';
        }

        if (!empty($this->environment)) {
            $environ = $this->environment;
        }

        $this->apiEndpoint = match ($environ) {
            'live' => 'https://api.tradesafe.co.za',
            'test' => 'https://api.tradesafe.test',
            default => 'https://api.tradesafe.dev',
        };

        $this->authEndpoint = match ($environ) {
            'test' => 'https://auth.tradesafe.test',
            default => 'https://auth.tradesafe.co.za',
        };

        if (!empty($cachedToken)) {
            $this->accessToken = $cachedToken;
        }
    }

    public function generateAccessToken(): void
    {
        if (!empty($this->accessToken)) {
            return;
        }

        $this->accessToken = sprintf('%s|%s', $this->clientId, $this->clientSecret);

        if ($this->authMethod === 'oauth2') {
            $httpClient = new \GuzzleHttp\Client(
                array(
                    'headers' => array(
                        'Accept-Encoding' => 'gzip',
                        'User-Agent' => 'TradeSafe PHP SDK ' . self::TRADESAFE_CLIENT_VERSION,
                    ),
                )
            );

            $provider = new GenericProvider([
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'urlAuthorize' => $this->authEndpoint . '/oauth/authorize',
                'urlAccessToken' => $this->authEndpoint . '/oauth/token',
                'urlResourceOwnerDetails' => $this->authEndpoint . '/oauth/resource',
            ], [
                'httpClient' => $httpClient,
            ]);

            try {
                $this->accessToken = $provider->getAccessToken('client_credentials');
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                exit($e->getMessage());
            }
        }
    }

    public function getAccessToken(): array
    {
        if (empty($this->accessToken)) {
            $this->generateAccessToken();
        }

        if ($this->authMethod === 'oauth2') {
            return [
                'access_token' => $this->accessToken->getToken(),
                'expires_in' => $this->accessToken->getExpires(),
            ];
        }

        return [
            'access_token' => $this->accessToken,
            'expires_in' => 0,
        ];
    }

    public function executeQuery($query, $variables = [], $operation = null)
    {
        $this->generateAccessToken();

        $gqlRequest = [
            'query' => $query,
            'variables' => $variables,
            'operationName' => $operation
        ];

        $accessToken = $this->accessToken instanceof AccessToken ? $this->accessToken->getToken() : $this->accessToken;

        $httpClient = new \GuzzleHttp\Client([
            'headers' => [
                'Accept-Encoding' => 'gzip',
                'User-Agent' => 'TradeSafe PHP SDK ' . self::TRADESAFE_CLIENT_VERSION,
                'Authorization' => 'Bearer ' . $accessToken,
            ]
        ]);

        $response = $httpClient->post($this->apiEndpoint . '/graphql', [
            'json' => $gqlRequest,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
