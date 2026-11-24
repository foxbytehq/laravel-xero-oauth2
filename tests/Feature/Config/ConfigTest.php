<?php

namespace Tests\Webfox\Xero\Feature\Config;

use Illuminate\Support\Facades\Config;
use Tests\Webfox\Xero\TestCase;

class ConfigTest extends TestCase
{
    public function test_that_all_default_configs_are_set_correctly()
    {
        $this->assertEquals([
            'api_host' => 'https://api.xero.com/api.xro/2.0',
            'credential_store' => 'Webfox\Xero\Oauth2CredentialManagers\FileStore',
            'credential_disk' => 'local',
            'routes' => [
                'middleware' => ['web'],
            ],
            'oauth' => [
                'client_id' => 'demo-client-id',
                'client_secret' => 'demo-client-secret',
                'webhook_signing_key' => 'webhook-key',
                'scopes' => [
                    'openid', 'email', 'profile', 'offline_access', 'accounting.settings',
                ],
                'redirect_on_success' => 'xero.auth.success',
                'redirect_uri' => 'xero.auth.callback',
                'redirect_full_url' => false,
                'url_authorize' => 'https://login.xero.com/identity/connect/authorize',
                'url_access_token' => 'https://identity.xero.com/connect/token',
                'url_resource_owner_details' => 'https://api.xero.com/api.xro/2.0/Organisation',
            ],
        ], Config::get('xero'));
    }
}
