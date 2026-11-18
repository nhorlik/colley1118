<?php

namespace App\Services\Terv;

use App\Enums\Cel;
use App\Enums\Szint;

final readonly class ValasztoBemenet
{
    /**
     * @param list<NapSablon>                $napok
     * @param array<string, list<Jelolt>>    $jeloltekMintankent  mozgásminta => jelöltek
     * @param list<int>                      $fokuszok
     * @param list<int>                      $panaszok
     */
    public function __construct(
        public array $napok,
        public array $jeloltekMintankent,
        public ?int $foFokusz,
        public array $fokuszok,
        public array $panaszok,
        public bool $rehabFelhasznalo,
        public bool $rehabTerv,
        public Cel $cel,
        public Szint $szint,
        public ?int $mag = null,
    ) {
    }
}
