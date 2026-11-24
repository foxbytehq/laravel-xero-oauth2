# A Laravel integration for Xero using the Oauth 2.0 spec

This package integrates the new recommended package of [xeroapi/xero-php-oauth2](https://github.com/XeroAPI/xero-php-oauth2) using the Oauth 2.0 spec with
Laravel.

### Requirements

* PHP 8.2 or higher
* [Laravel](https://laravel.com/) 10.x or higher
* [PHPUnit](https://github.com/sebastianbergmann/phpunit) 10.x or higher

## Installation

You can install this package via composer using the following command:  

```
composer require webfox/laravel-xero-oauth2
```

The package will automatically register itself.

You should add your Xero keys to your `.env` file using the following keys:

```
XERO_CLIENT_ID=
XERO_CLIENT_SECRET=
```

(on [Xero developer portal](https://developer.xero.com/app/manage)): ***IMPORTANT*** When setting up the application in Xero ensure your redirect url is:

```
https://{your-domain}/xero/auth/callback
```

*(The flow is xero/auth/callback performs the oAuth handshake and stores your token, then redirects you over to your success callback)*

### Protecting the Routes

By default, the `xero/auth/authorize` and `xero/auth/callback` routes only use the `web` middleware. With a credential store shared by the whole app, such as the default `FileStore` or `CacheStore`, anyone who can visit these routes can connect their own Xero organisation and replace your stored credentials.

To restrict who can connect, publish the configuration file and add your authentication middleware to `routes.middleware`:

```php
'routes' => [
    'middleware' => ['web', 'auth'],
],
```

### Error Handling

In the event that a user denies access on the Xero Authorisation page, the package will throw a `OAuthException` from the [AuthorizationCallbackController](src/Controllers/AuthorizationCallbackController.php). This can be caught and acted upon however you prefer.

#### Laravel 11

To do this in Laravel 11, bind a custom exception renderer in `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle when the user clicks cancel on the Xero authorization screen
        $exceptions->render(function (OAuthException $e, Request $request) {
            return redirect('/my/xero/connect/page')->with('errorMessage', $e->getMessage());
        });
    })->create();
```

#### Laravel 8-10

Use the `renderable` method in the `App\Exceptions\Handler` class:

```php
    public function register()
    {
        $this->renderable(function (OAuthException $e) {
            // Handle when the user clicks cancel on the Xero authorization screen
            return redirect('/my/xero/connect/page')->with('errorMessage', $e->getMessage());
        });
    }
```

You can publish the configuration file with:

```
php artisan vendor:publish --provider="Webfox\Xero\XeroServiceProvider" --tag="config"
```
# Useful Documentation 

* [Using the package](using-the-package.md)
* [Examples](examples.md)
* [Credential Storage](credential-storage.md)
* [Webhooks](webhooks.md)