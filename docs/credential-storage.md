# Credential Storage

There are currently five credential storage options available:

1. ** File Store ** (default) - Stores the credentials in a JSON file on the default disk.
2. ** Cache Store ** - Stores the credentials in the cache.
3. ** Model Store ** - Stores the credentials against a predefined model.
4. ** Authenticated User Store ** - Stores the credentials against the authenticated user.
5. ** Array Store ** - Stores the credentials in an array.

## Changing the default storage

If your using a default storage you can change the `xero.credential_store` config key to point to the new implementation.

## File Store
Credentials are stored in a JSON file using the default disk on the Laravel Filesystem, with visibility set to private. This allows credential sharing across multiple servers using a shared disk such as S3, regardless of which server conducted the OAuth flow.

To use a different disk, change the `xero.credential_disk` config item to another disk defined in `config/filesystem.php`.

## Cache Store

Credentials are stored in the cache, this is useful for applications that do not have a shared disk.

## Model Store

Credentials are stored against a predefined model, this is useful for applications that have a settings model.

For the package to know which model you want to use, you will need to call the following method:

```php
use Webfox\Xero\Xero;
use App\Models\Settings;

Xero::useModelStorage(Settings::first());
```

If you need to resolve a model depending on some application state such as the authenticated user, this should be added to [a custom middleware](https://laravel.com/docs/11.x/middleware#defining-middleware) instead of the app service provider, e.g.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Webfox\Xero\Xero;
use Illuminate\Http\Request;

class ConfigureXeroMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            Xero::useModelStorage($request->user()->currentTeam);
        }

        return $next($request);

    }
}
```

By default, the package will use the `xero_credentials` field, Should you need to rename this field, you can do so by calling:

```php
use Webfox\Xero\Xero;

Xero::useAttributeOnModelStore('my_custom_field');
```

## Authenticated User Store

Credentials are stored against the authenticated user. By default, the package will use the `xero_credentials` field on the Users table. 

Should you need to rename this field, you can do so by calling: 

```php
use Webfox\Xero\Xero;

Xero::useAttributeOnModelStore('my_custom_field');
```

Should you need to change the authentication guard, you can do so by calling:
```php
use Webfox\Xero\Xero;

Xero::setDefaultAuthGuard('admin');
```

## Array Store

Credentials are stored in an array, this is useful for testing purposes only. Once the application is restarted, the credentials will be lost.

## Creating your own Storage Manager

You can create your own storage manager by implementing the `OauthCredentialManager` interface, it is suggested that you extend the `BaseCredentialManager` class.

You will need to implement the following methods:

1. `exists()` - Returns a boolean indicating if the credentials exist.
2. `store(AccessTokenInterface $token, array $tenants = null)` - Creates/Updates the credentials.
3. `data(string $key = null)` - Returns the stored credentials (it is recommended you check the credentials exists, if not throw the `XeroCredentialsNotFound` exception).

Once this has been completed, you should point `xero.credential_store` to your new implementation.

## Refreshing from several processes

The credential manager refreshes the token when it is resolved and the access token has expired. If two processes resolve it at the same moment (two queue workers, or a worker and a web request), both send the same refresh token to Xero. Xero rotates the refresh token on every refresh and accepts the previous one for 30 minutes only ([token types](https://developer.xero.com/documentation/guides/oauth2/token-types)), so the stored credentials end up holding whichever result was written last, and a later refresh with a token rotated out more than 30 minutes earlier is rejected (reported as `invalid_grant`, for example in [#48](https://github.com/foxbytehq/laravel-xero-oauth2/issues/48)).

To refresh one process at a time, extend your store and wrap `refresh()` in a cache lock. Use a cache store that supports atomic locks (Redis, Memcached, DynamoDB or database):

```php
<?php

namespace App\Xero;

use Illuminate\Support\Facades\Cache;
use Webfox\Xero\Oauth2CredentialManagers\CacheStore;

class LockedCacheStore extends CacheStore
{
    public function refresh(): void
    {
        Cache::lock('xero-oauth-refresh', 30)->block(10, function () {
            // Another process may have refreshed while this one waited for the lock.
            if (! $this->isExpired()) {
                return;
            }

            parent::refresh();
        });
    }
}
```

Then point `xero.credential_store` to `App\Xero\LockedCacheStore::class`. The same works for `FileStore` and `ModelStore`. If you store credentials per user or per team, include that ID in the lock name.
