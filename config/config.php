<?php

use Webfox\Xero\Oauth2CredentialManagers\FileStore;

return [

    'api_host' => 'https://api.xero.com/api.xro/2.0',

    /************************************************************************
     * Class used to store credentials.
     * Must implement OauthCredentialManager Interface
     ************************************************************************/
    'credential_store' => FileStore::class,

    /************************************************************************
     * Disk used to store credentials.
     ************************************************************************/
    'credential_disk' => env('XERO_CREDENTIAL_DISK'),

    /************************************************************************
     * Middleware applied to the authorize and callback routes.
     * With an application-wide credential store, anyone who can visit these
     * routes can connect their own Xero organisation and replace the stored
     * credentials, so add your authentication middleware here.
     ************************************************************************/
    'routes' => [
        'middleware' => ['web'],
    ],

    'oauth' => [
        /************************************************************************
         * Client ID provided by Xero when registering your application
         ************************************************************************/
        'client_id' => env('XERO_CLIENT_ID'),

        /************************************************************************
         * Client Secret provided by Xero when registering your application
         ************************************************************************/
        'client_secret' => env('XERO_CLIENT_SECRET'),

        /************************************************************************
         * Webhook signing key provided by Xero when registering webhooks
         ************************************************************************/
        'webhook_signing_key' => env('XERO_WEBHOOK_KEY', ''),

        /************************************************************************
         * Then scopes you wish to request access to on your token
         * https://developer.xero.com/documentation/oauth2/scopes
         ************************************************************************/
        'scopes' => [
            'openid',
            'email',
            'profile',
            'offline_access',
            'accounting.settings',
        ],

        /************************************************************************
         * Url to redirect to upon success
         ************************************************************************/
        'redirect_on_success' => 'xero.auth.success',

        /************************************************************************
         * Url for Xero to redirect to upon granting access
         * Unless you wish to change the default behaviour you should not need to
         * change this
         ************************************************************************/
        'redirect_uri' => 'xero.auth.callback',

        /************************************************************************
         * If the 'redirect_uri' is not a route name, but rather a full url set
         * this to true and we won't wrap it in `route()`
         ************************************************************************/
        'redirect_full_url' => false,

        /************************************************************************
         * Urls for Xero's Oauth integration, you shouldn't need to change these
         ************************************************************************/
        'url_authorize' => 'https://login.xero.com/identity/connect/authorize',
        'url_access_token' => 'https://identity.xero.com/connect/token',
        'url_resource_owner_details' => 'https://api.xero.com/api.xro/2.0/Organisation',
    ],

];
