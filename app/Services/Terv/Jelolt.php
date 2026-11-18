<?php

namespace App\Services\Terv;

/** Egy választható gyakorlat a generátor szemszögéből (DB-független érték-objektum). */
final readonly class Jelolt
{
    /**
     * @param array<int, bool> $izmok             izomcsoport_id => elsődleges-e
     * @param list<int>        $ajanlottPanaszok  panasz_tipus_id-k, amelyeknél ajánlott
     */
    public function __construct(
        public int $id,
        public string $kod,
        public string $nev,
        public string $tipus,
        public string $mozgasminta,
        public int $nehezseg,
        public bool $terapias,
        public bool $otthonVegezheto,
        public array $izmok,
        public array $ajanlottPanaszok,
        public int $alapSzett,
        public ?int $alapIsmMin,
        public ?int $alapIsmMax,
        public ?int $alapIdoMp,
        public int $alapPihenoMp,
    ) {
    }
}
