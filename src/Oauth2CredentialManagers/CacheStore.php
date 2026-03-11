<?php

namespace Webfox\Xero\Oauth2CredentialManagers;

use Illuminate\Cache\Repository;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Webfox\Xero\Exceptions\XeroCredentialsNotFound;
use Webfox\Xero\OauthCredentialManager;

class CacheStore extends BaseCredentialManager implements OauthCredentialManager
{
    protected string $cacheKey = 'xero_oauth';

    protected Repository $cache;

    public function __construct()
    {
        $this->cache = app(Repository::class);

        parent::__construct();
    }

    public function exists(): bool
    {
        return $this->cache->has($this->cacheKey);
    }

    public function store(AccessTokenInterface $token, ?array $tenants = null): void
    {
        $this->cache->forever($this->cacheKey, [
            'token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'id_token' => $token->getValues()['id_token'],
            'expires' => $token->getExpires(),
            'tenants' => $tenants ?? $this->getTenants(),
        ]);
    }

    /**
     * @throws XeroCredentialsNotFound
     */
    protected function data(?string $key = null)
    {
        if (! $this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        $cacheData = $this->cache->get($this->cacheKey);

        return empty($key) ? $cacheData : ($cacheData[$key] ?? null);
    }
}
