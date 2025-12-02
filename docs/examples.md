*app\Http\Controllers\XeroController.php*

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Webfox\Xero\OauthCredentialManager;

class XeroController extends Controller
{

    public function index(Request $request, OauthCredentialManager $xeroCredentials)
    {
        try {
            // Check if we've got any stored credentials
            if ($xeroCredentials->exists()) {
                /* 
                 * We have stored credentials so we can resolve the AccountingApi, 
                 * If we were sure we already had some stored credentials then we could just resolve this through the controller
                 * But since we use this route for the initial authentication we cannot be sure!
                 */
                $xero             = resolve(\XeroAPI\XeroPHP\Api\AccountingApi::class);
                $organisationName = $xero->getOrganisations($xeroCredentials->getTenantId())->getOrganisations()[0]->getName();
                $user             = $xeroCredentials->getUser();
                $username         = "{$user['given_name']} {$user['family_name']} ({$user['username']})";
            }
        } catch (\throwable $e) {
            // This can happen if the credentials have been revoked or there is an error with the organisation (e.g. it's expired)
            $error = $e->getMessage();
        }

        return view('xero', [
            'connected'        => $xeroCredentials->exists(),
            'error'            => $error ?? null,
            'organisationName' => $organisationName ?? null,
            'username'         => $username ?? null
        ]);
    }

}
```

*resources\views\xero.blade.php*

```injectablephp
    @extends('_layouts.main')
    
    @section('content')        
    @if($error)
        <h1>Your connection to Xero failed</h1>
        <p>`{{ $error }}`</p>
        <a href="`{{ route('xero.auth.authorize') }}`" class="btn btn-primary btn-large mt-4">
            Reconnect to Xero
        </a>
    @elseif($connected)
        <h1>You are connected to Xero</h1>
        <p>`{{ $organisationName }}` via `{{ $username }}`</p>
        <a href="`{{ route('xero.auth.authorize') }}`" class="btn btn-primary btn-large mt-4">
            Reconnect to Xero
        </a>
    @else
        <h1>You are not connected to Xero</h1>
        <a href="`{{ route('xero.auth.authorize') }}`" class="btn btn-primary btn-large mt-4">
            Connect to Xero
        </a>
    @endif
    @endsection
```

*routes/web.php*

```php
/* 
 * We name this route xero.auth.success as by default the config looks for a route with this name to redirect back to
 * after authentication has succeeded. The name of this route can be changed in the config file.
 */
Route::get('/manage/xero', [\App\Http\Controllers\XeroController::class, 'index'])->name('xero.auth.success');
```