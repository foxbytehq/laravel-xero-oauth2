<?php

namespace Webfox\Xero\Oauth2CredentialManagers;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Webfox\Xero\Exceptions\XeroCredentialsNotFound;
use Webfox\Xero\OauthCredentialManager;
use Webfox\Xero\Xero;

class ModelStore extends BaseCredentialManager implements OauthCredentialManager
{
    public Model $model;

    public function __construct()
    {
        if ($model = Xero::getModelStorage()) {
            $this->model = $model;
        }

        parent::__construct();
    }

    public function exists(): bool
    {
        return isset($this->model) && $this->model->exists && is_array($this->model->{$this->getModelKey()});
    }

    public function store(AccessTokenInterface $token, ?array $tenants = null): void
    {
        $this->model->update([
            $this->getModelKey() => [
                'token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'id_token' => $token->getValues()['id_token'],
                'expires' => $token->getExpires(),
                'tenants' => $tenants ?? $this->getTenants(),
            ],
        ]);
    }

    protected function data(?string $key = null)
    {
        if (! $this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        $data = $this->model->{$this->getModelKey()};

        return $key === null ? $data : $data[$key] ?? null;
    }

    private function getModelKey(): string
    {
        return Xero::getModelAttribute();
    }
}
