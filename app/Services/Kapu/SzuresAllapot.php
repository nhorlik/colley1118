<?php

namespace App\Services\Kapu;

use App\Enums\SzuresEredmeny;
use DateTimeImmutable;

final readonly class SzuresAllapot
{
    public function __construct(
        public string $teruletNev,
        public SzuresEredmeny $eredmeny,
        public DateTimeImmutable $kitoltve,
    ) {
    }
}
