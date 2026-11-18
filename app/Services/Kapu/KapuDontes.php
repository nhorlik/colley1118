<?php

namespace App\Services\Kapu;

use App\Enums\KapuAllapot;

/** Az EdzesKapu okolt döntése — soha nem „csendes” igen/nem. */
final readonly class KapuDontes
{
    /**
     * @param list<string> $indokok  miért ez az állapot
     * @param list<string> $teendok  mi hiányzik a továbblépéshez (TILTVA esetén)
     */
    public function __construct(
        public KapuAllapot $allapot,
        public array $indokok = [],
        public array $teendok = [],
    ) {
    }

    public function tiltva(): bool
    {
        return $this->allapot === KapuAllapot::TILTVA;
    }

    public function csakRehab(): bool
    {
        return $this->allapot === KapuAllapot::REHAB;
    }

    public function uzenet(): string
    {
        return match ($this->allapot) {
            KapuAllapot::TILTVA => 'Most nem állítunk össze edzéstervet: ' . implode(' ', $this->indokok),
            KapuAllapot::REHAB => 'Rehabilitációs terv készül: ' . implode(' ', $this->indokok),
            KapuAllapot::SZABAD => 'Normál edzésterv készülhet.',
        };
    }
}
