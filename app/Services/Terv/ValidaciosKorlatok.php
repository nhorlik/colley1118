<?php

namespace App\Services\Terv;

final readonly class ValidaciosKorlatok
{
    /** @param list<int> $tiltottGyakorlatok a felhasználó panaszai miatt tiltott gyakorlat_id-k */
    public function __construct(
        public array $tiltottGyakorlatok,
        public bool $otthonEdz,
        public int $maxNehezseg,
    ) {
    }
}
