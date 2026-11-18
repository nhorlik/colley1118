<?php

namespace App\Services\Terv;

use App\Enums\Cel;

/**
 * Napok kiosztása a heti edzésszám és a rehab-mód alapján.
 *  - normál: 1–3 nap teljes test, 4 nap felső/alsó bontás, 5–6 nap + kondíció/teljes test C
 *  - rehab:  legfeljebb 3 nap (mobilizáció–stabilizáció, kíméletes erősítés, aktív pihenő)
 *  - zsírégetés / állóképesség célnál minden erőnap végére kardió-blokk kerül
 */
final class Sablonok
{
    /** @return list<NapSablon> */
    public static function normal(int $heti, Cel $cel): array
    {
        $s = self::osszes();
        $napok = match (true) {
            $heti <= 1 => [$s['TELJES_A']],
            $heti === 2 => [$s['TELJES_A'], $s['TELJES_B']],
            $heti === 3 => [$s['TELJES_A'], $s['TELJES_B'], $s['TELJES_C']],
            $heti === 4 => [$s['FELSO_A'], $s['ALSO_A'], $s['FELSO_B'], $s['ALSO_B']],
            $heti === 5 => [$s['FELSO_A'], $s['ALSO_A'], $s['FELSO_B'], $s['ALSO_B'], $s['KONDI']],
            default => [$s['FELSO_A'], $s['ALSO_A'], $s['FELSO_B'], $s['ALSO_B'], $s['TELJES_C'], $s['KONDI']],
        };

        if (in_array($cel, [Cel::FOGYAS, Cel::ALLOKEPESSEG], true)) {
            $napok = array_map(fn (NapSablon $n) => $n->kardioVeggel(), $napok);
        }

        return $napok;
    }

    /** @return list<NapSablon> */
    public static function rehab(int $heti): array
    {
        $s = self::osszes();
        $sor = [$s['REHAB_MOBIL'], $s['REHAB_ERO'], $s['REHAB_AKTIV']];

        return array_slice($sor, 0, max(1, min(3, $heti)));
    }

    /** @return array<string, NapSablon> */
    public static function osszes(): array
    {
        $e = fn (string ...$m) => new Slot($m, Blokk::ERO);
        $core = new Slot(['CORE'], Blokk::CORE);
        $mobil = new Slot(['MOBILIZACIO'], Blokk::MOBIL);
        $rehab = new Slot(['TERAPIAS'], Blokk::REHAB);
        $kardio = new Slot(['KARDIO'], Blokk::KARDIO);

        return [
            'FELSO_A' => new NapSablon('FELSO_A', 'Felsőtest A', [
                $e('NYOMAS_VIZ'), $e('NYOMAS_VIZ'), $e('HUZAS_VIZ'), $e('HUZAS_VIZ'), $e('IZOLACIO')]),
            'ALSO_A' => new NapSablon('ALSO_A', 'Alsótest A', [
                $e('TERD_DOM'), $e('TERD_DOM', 'EGYOLDALU_LAB'), $e('CSIPO_DOM'), $core]),
            'FELSO_B' => new NapSablon('FELSO_B', 'Felsőtest B', [
                $e('NYOMAS_FUGG', 'NYOMAS_VIZ'), $e('NYOMAS_FUGG', 'NYOMAS_VIZ', 'IZOLACIO'), $e('HUZAS_FUGG', 'HUZAS_VIZ'),
                $e('HUZAS_FUGG', 'HUZAS_VIZ'), $e('IZOLACIO')]),
            'ALSO_B' => new NapSablon('ALSO_B', 'Alsótest B', [
                $e('CSIPO_DOM'), $e('CSIPO_DOM', 'TERD_DOM'), $e('EGYOLDALU_LAB', 'TERD_DOM'), $e('IZOLACIO')]),
            'TELJES_A' => new NapSablon('TELJES_A', 'Teljes test A', [
                $e('TERD_DOM'), $e('NYOMAS_VIZ'), $e('HUZAS_VIZ', 'HUZAS_FUGG'), $e('CSIPO_DOM'), $core]),
            'TELJES_B' => new NapSablon('TELJES_B', 'Teljes test B', [
                $e('CSIPO_DOM'), $e('NYOMAS_FUGG', 'NYOMAS_VIZ'), $e('HUZAS_FUGG', 'HUZAS_VIZ'), $e('EGYOLDALU_LAB', 'TERD_DOM'), $core]),
            'TELJES_C' => new NapSablon('TELJES_C', 'Teljes test C', [
                $e('TERD_DOM', 'EGYOLDALU_LAB'), $e('NYOMAS_VIZ', 'NYOMAS_FUGG'), $e('HUZAS_VIZ', 'HUZAS_FUGG'), $e('IZOLACIO'), $kardio]),
            'KONDI' => new NapSablon('KONDI', 'Kondíció és core', [$kardio, $core, $core, $mobil]),
            'REHAB_MOBIL' => new NapSablon('REHAB_MOBIL', 'Mobilizáció és stabilizáció', [$mobil, $rehab, $rehab, $rehab, $core]),
            'REHAB_ERO' => new NapSablon('REHAB_ERO', 'Kíméletes erősítés', [
                $rehab, $rehab, new Slot(['CSIPO_DOM', 'TERAPIAS'], Blokk::REHAB), $mobil]),
            'REHAB_AKTIV' => new NapSablon('REHAB_AKTIV', 'Aktív pihenő', [$kardio, $mobil, $mobil]),
        ];
    }
}
