<?php

namespace Webfox\Xero\Oauth2CredentialManagers;

use Illuminate\Session\Store;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Webfox\Xero\Exceptions\XeroTenantNotFound;
use Webfox\Xero\Oauth2Provider;
use XeroAPI\XeroPHP\JWTClaims;

abstract class BaseCredentialManager
{
    protected Store $session;
    protected Oauth2Provider $oauthProvider;

    public function __construct()
    {
        $this->session = app(Store::class);
        $this->oauthProvider = app(Oauth2Provider::class);
    }

    abstract public function exists(): bool;

    abstract protected function store(AccessTokenInterface $token, ?array $tenants = null): void;

    abstract protected function data(?string $key = null);

    public function getAccessToken(): string
    {
        return $this->data('token');
    }

    public function getRefreshToken(): string
    {
        return $this->data('refresh_token');
    }

    public function getTenants(): ?array
    {
        return $this->data('tenants');
    }

    /**
     * @throws XeroTenantNotFound
     */
    public function getTenantId(int $tenant = 0): string
    {
        if (! isset($this->data('tenants')[$tenant])) {
            throw XeroTenantNotFound::make();
        }

        return $this->data('tenants')[$tenant]['Id'];
    }

    public function getExpires(): int
    {
        return $this->data('expires');
    }

    public function getData(): array
    {
        return $this->data();
    }

    public function getUser(): ?array
    {
        try {
            $jwt = new JWTClaims();
            $jwt->setTokenId($this->data('id_token'));
            $decodedToken = $jwt->decode();

            return [
                'given_name' => $decodedToken->getGivenName(),
                'family_name' => $decodedToken->getFamilyName(),
                'email' => $decodedToken->getEmail(),
                'user_id' => $decodedToken->getXeroUserId(),
                'username' => $decodedToken->getPreferredUsername(),
                'session_id' => $decodedToken->getGlobalSessionId(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isExpired(): bool
    {
        return time() >= $this->data('expires');
    }

    public function refresh(): void
    {
        $newAccessToken = $this->oauthProvider->getAccessToken('refresh_token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
        ]);

        $this->store($newAccessToken);
    }

    public function getState(): string
    {
        return $this->session->get('xero_oauth2_state') ?? '';
    }

    public function getAuthorizationUrl(): string
    {
        $redirectUrl = $this->oauthProvider->getAuthorizationUrl(['scope' => config('xero.oauth.scopes')]);
        $this->session->put('xero_oauth2_state', $this->oauthProvider->getState());

        return $redirectUrl;
    }
}
