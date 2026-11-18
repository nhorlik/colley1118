<?php

namespace App\Services\Terv;

final readonly class Parameterek
{
    public function __construct(
        public int $szett,
        public ?int $ismMin,
        public ?int $ismMax,
        public ?int $idoMp,
        public int $pihenoMp,
    ) {
    }

    public function idoAlapu(): bool
    {
        return $this->idoMp !== null;
    }
}
