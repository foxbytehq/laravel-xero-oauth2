<?php

namespace Webfox\Xero;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface OauthCredentialManager
{
    /**
     * Get the current access token.
     */
    public function getAccessToken(): string;

    /**
     * Get the current refresh token.
     */
    public function getRefreshToken(): string;

    /**
     * Get the url to redirect the user to authenticate.
     */
    public function getAuthorizationUrl(): string;

    /**
     * Get all tenants available.
     **/
    public function getTenants(): ?array;

    /**
     * Get the current tenant ID.
     */
    public function getTenantId(int $tenant = 0): string;

    /**
     * Get the time the current access token expires (unix timestamp).
     */
    public function getExpires(): int;

    /**
     * Get the current 'state' used to verify callbacks.
     */
    public function getState(): string;

    /**
     * Check whether we have any credentials stored.
     */
    public function exists(): bool;

    /**
     * Check whether the current access token is expired.
     */
    public function isExpired(): bool;

    /**
     * Refresh and store the new token.
     */
    public function refresh(): void;

    /**
     * Store the details of the access token.
     *
     * Should store array [
     *   'token'         => $token->getToken(),
     *   'refresh_token' => $token->getRefreshToken(),
     *   'id_token'      => $token->getValues()['id_token'],
     *   'expires'       => $token->getExpires(),
     *   'tenants'       => $tenants ?? $this->getTenants(),
     * ]
     *
     * @param  AccessTokenInterface  $token
     * @param  array|null  $tenants
     */
    public function store(AccessTokenInterface $token, ?array $tenants = null): void;

    /**
     * Get the current authenticated users details according to the id token.
     *
     * @return array [
     *               'given_name'  => 'string',
     *               'family_name' => 'string',
     *               'email'       => 'string',
     *               'user_id'     => 'string',
     *               'username'    => 'string',
     *               'session_id'  => 'string',
     *               ]
     */
    public function getUser(): ?array;

    /**
     * Get the current data stored via the store method.
     *
     * @return array [
     *               'token'         => 'string',
     *               'refresh_token' => 'string',
     *               'id_token'      => 'string',
     *               'expires'       => 000000,
     *               'tenants'     => 'array',
     *               ]
     */
    public function getData(): array;
}
