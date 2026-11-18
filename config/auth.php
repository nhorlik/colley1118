<?php

/*
 * A hitelesítés a `felhasznalo` táblát és az App\Models\Felhasznalo modellt használja
 * (nem a Laravel alap `users` tábláját/modelljét). A 'web' guard neve és a 'login'
 * útvonalnév megmarad a keretrendszer konvenciói miatt (az Authenticate middleware
 * ezt a nevet keresi), de az útvonal URL-je és a felület magyar marad.
 */

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'felhasznalok',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'felhasznalok',
        ],
    ],

    'providers' => [
        'felhasznalok' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\Felhasznalo::class),
        ],
    ],

    'passwords' => [
        'felhasznalok' => [
            'provider' => 'felhasznalok',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
