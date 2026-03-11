<?php

namespace Webfox\Xero\Oauth2CredentialManagers;

use Illuminate\Support\Facades\Auth;
use Webfox\Xero\Exceptions\XeroCredentialsNotFound;
use Webfox\Xero\Exceptions\XeroUserNotAuthenticated;
use Webfox\Xero\Xero;

class AuthenticatedUserStore extends ModelStore
{
    public function __construct()
    {
        $auth = Auth::guard(Xero::getDefaultAuthGuard());

        parent::__construct();

        if ($auth->check()) {
            $this->model = $auth->user();
        }
    }

    /**
     * @throws XeroUserNotAuthenticated
     * @throws XeroCredentialsNotFound
     */
    public function data(?string $key = null)
    {
        if (! Auth::guard(Xero::getDefaultAuthGuard())->check()) {
            throw XeroUserNotAuthenticated::make();
        }

        return parent::data($key);
    }
}
