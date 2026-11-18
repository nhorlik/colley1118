<?php

namespace App\Services\Terv;

final readonly class TervTetel
{
    /** @param array<string, float> $pontReszletek */
    public function __construct(
        public int $sorrend,
        public Jelolt $jelolt,
        public Parameterek $parameterek,
        public float $pont,
        public array $pontReszletek,
    ) {
    }
}
