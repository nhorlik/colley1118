<?php

namespace App\Services\Terv;

final readonly class NapSablon
{
    /** @param list<Slot> $slotok */
    public function __construct(
        public string $kod,
        public string $nev,
        public array $slotok,
    ) {
    }

    /** Kardió-blokk a nap végére — ha még nincs benne. */
    public function kardioVeggel(): self
    {
        foreach ($this->slotok as $s) {
            if ($s->blokk === Blokk::KARDIO) {
                return $this;
            }
        }

        return new self($this->kod, $this->nev, [...$this->slotok, new Slot(['KARDIO'], Blokk::KARDIO)]);
    }
}
