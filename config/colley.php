<?php

return [
    // Kódverzió — a /diag végpont mutatja; minden kiadásnál növeld.
    'verzio' => '2.0.0-fazis1',

    // A /diag végpont csak akkor él, ha ez true (fejlesztéskor: COLLEY_DIAG=true a .env-ben).
    'diag' => (bool) env('COLLEY_DIAG', false),

    // A /diag ezeknek a fájloknak a meglétét, módosítási idejét és rövid hash-ét mutatja,
    // hogy egy pillantással látszódjon: tényleg a legfrissebb fájlok vannak-e a helyükön.
    'diag_fajlok' => [
        'app/Services/Terv/TervGenerator.php',
        'app/Services/Terv/Valaszto.php',
        'app/Services/Terv/Pontozo.php',
        'app/Services/Terv/ParameterSzamito.php',
        'app/Services/Kapu/OrvosiSzuro.php',
        'app/Services/Kapu/EdzesKapu.php',
        'database/migrations/2026_11_18_000008_create_nezetek.php',
        'database/seeders/TorzsadatSeeder.php',
    ],
];
