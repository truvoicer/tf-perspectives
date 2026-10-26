<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Auth view components
    |--------------------------------------------------------------------------
    |
    | These are the Inertia component names that the package's auth routes
    | render. Override any of them in the host app's config to substitute
    | a custom component without touching the routes themselves.
    |
    | Example:
    |   'auth' => [
    |       'login' => 'auth/my-custom-login',
    |   ],
    |
    */

    'auth_views' => [
        'login'           => '@tf-perspectives::auth',
        'register'        => '@tf-perspectives::auth',
        'forgot_password' => '@tf-perspectives::auth/forgot-password',
        'reset_password'  => '@tf-perspectives::auth/reset-password',
        'verify_email'    => '@tf-perspectives::auth/verify-email',
    ],
    'load_web_routes' => true,
    'load_api_routes' => true,
];
