<?php

namespace App\Services\Kapu;

use App\Enums\PanaszEredet;
use App\Models\Felhasznalo;
use DateTimeImmutable;

/** A felhasználó adataiból összeállítja az OrvosiSzuro bemenetét. */
final class EdzesKapu
{
    public function __construct(private readonly OrvosiSzuro $szuro = new OrvosiSzuro())
    {
    }

    public function ellenoriz(Felhasznalo $f): KapuDontes
    {
        $orvosi = $f->panaszok()
            ->where('eredet', PanaszEredet::ORVOSI_ENGEDELY->value)
            ->orderBy('panasz_tipus.panasz_tipus_id')
            ->pluck('nev')
            ->all();

        $szuresek = $f->szuresek()->with('testterulet')->get()
            ->map(fn ($s) => new SzuresAllapot(
                $s->testterulet->nev,
                $s->eredmeny,
                DateTimeImmutable::createFromInterface($s->kitoltve),
            ))->all();

        return $this->szuro->dont(
            $orvosi,
            $szuresek,
            $f->orvosi_engedely ? DateTimeImmutable::createFromInterface($f->orvosi_engedely) : null,
            (bool) $f->rehab_mod,
        );
    }
}
