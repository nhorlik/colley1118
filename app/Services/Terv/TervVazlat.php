<?php

namespace App\Services\Terv;

/** A generátor DB-független kimenete; a TervGenerator ezt menti el. */
final readonly class TervVazlat
{
    /** @param list<TervNap> $napok */
    public function __construct(
        public array $napok,
        public bool $rehab,
    ) {
    }

    /** @return list<TervTetel> */
    public function osszesTetel(): array
    {
        return array_merge([], ...array_map(fn (TervNap $n) => $n->tetelek, $this->napok));
    }
}
