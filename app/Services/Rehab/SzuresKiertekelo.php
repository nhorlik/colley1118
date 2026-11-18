<?php

namespace App\Services\Rehab;

use App\Enums\SzuresEredmeny;

/**
 * Egy régiós szűrés válaszaiból: eredmény + beállítandó terhelési jelzők + felhasználói összegzés.
 * Piros zászló → orvoshoz (felülír mindent); panaszhoz kötött válasz → terhelési jelző; különben rendben.
 */
final class SzuresKiertekelo
{
    /** @param iterable<array{vorosjelzes: bool|int, panasz_tipus_id: ?int}> $valaszok */
    public function ertekel(string $teruletNev, iterable $valaszok): SzuresKiertekeles
    {
        $voros = false;
        $panaszok = [];

        foreach ($valaszok as $v) {
            $voros = $voros || (bool) $v['vorosjelzes'];
            if ($v['panasz_tipus_id'] !== null) {
                $panaszok[(int) $v['panasz_tipus_id']] = true;
            }
        }

        $terulet = mb_strtolower($teruletNev);
        $panaszok = array_keys($panaszok);
        sort($panaszok);

        if ($voros) {
            return new SzuresKiertekeles(SzuresEredmeny::ORVOSHOZ, $panaszok,
                "A válaszaid között van olyan tünet ({$terulet}), amit szakembernek kell megvizsgálnia. "
                . 'Orvosi tisztázásig nem állítunk össze edzéstervet.');
        }

        if ($panaszok !== []) {
            return new SzuresKiertekeles(SzuresEredmeny::TERHELESI_JELZO, $panaszok,
                "A(z) {$terulet} területére vonatkozó terhelési korlátozásokat beállítottuk. "
                . 'A következő terved ezeket figyelembe veszi.');
        }

        return new SzuresKiertekeles(SzuresEredmeny::RENDBEN, [],
            "A(z) {$terulet} területén nem találtunk korlátozó tényezőt.");
    }
}
