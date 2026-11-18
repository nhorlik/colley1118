<?php

namespace App\Services\Terv;

final readonly class TervNap
{
    /** @param list<TervTetel> $tetelek */
    public function __construct(
        public int $sorszam,
        public string $nev,
        public array $tetelek,
    ) {
    }
}
