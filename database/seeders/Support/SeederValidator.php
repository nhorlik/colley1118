<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Seeder-szintű előzetes validálás: MINDEN tábla MINDEN sorát ellenőrzi beszúrás előtt
 * (ismeretlen oszlop, VARCHAR-hossz túllépés, kötelező mező hiánya), és az összes hibát
 * egyszerre jelenti. Így egy hibás sor nem hagyja félbe a seedelést észrevétlenül.
 */
final class SeederValidator
{
    /** @var array<string, array<string, array{hossz: ?int, kotelezo: bool}>> */
    private array $semaCache = [];

    /** @var list<string> */
    private array $hibak = [];

    public function ellenoriz(string $tabla, array $sorok): self
    {
        $sema = $this->sema($tabla);

        foreach ($sorok as $i => $sor) {
            $hely = "{$tabla}[#{$i}]";

            foreach (array_diff(array_keys($sor), array_keys($sema)) as $ismeretlen) {
                $this->hibak[] = "{$hely}: ismeretlen oszlop '{$ismeretlen}'";
            }

            foreach ($sema as $oszlop => ['hossz' => $hossz, 'kotelezo' => $kotelezo]) {
                $ertek = $sor[$oszlop] ?? null;

                if ($kotelezo && $ertek === null) {
                    $this->hibak[] = "{$hely}: '{$oszlop}' kötelező, de hiányzik";
                }
                if ($hossz !== null && is_string($ertek) && mb_strlen($ertek) > $hossz) {
                    $this->hibak[] = sprintf("%s: '%s' túl hosszú (%d > %d): %s",
                        $hely, $oszlop, mb_strlen($ertek), $hossz, mb_substr($ertek, 0, 40) . '…');
                }
            }
        }

        return $this;
    }

    /** Ha bármelyik ellenőrzés hibát talált, egyetlen kivételben jelenti mindet. */
    public function dobjHaHibas(): void
    {
        if ($this->hibak !== []) {
            throw new RuntimeException(
                "A seed-adatok validálása sikertelen (" . count($this->hibak) . " hiba), semmi nem lett beszúrva:\n - "
                . implode("\n - ", $this->hibak)
            );
        }
    }

    /** @return array<string, array{hossz: ?int, kotelezo: bool}> */
    private function sema(string $tabla): array
    {
        if (isset($this->semaCache[$tabla])) {
            return $this->semaCache[$tabla];
        }

        $eredmeny = [];
        foreach (Schema::getColumns($tabla) as $o) {
            // 'type' pl. "varchar(40)" MySQL-en; SQLite-on hossz nélkül jöhet → ilyenkor nincs hossz-ellenőrzés
            $hossz = preg_match('/^(?:var)?char\((\d+)\)/i', $o['type'] ?? '', $m) ? (int) $m[1] : null;
            $eredmeny[$o['name']] = [
                'hossz' => $hossz,
                'kotelezo' => ! $o['nullable'] && $o['default'] === null && ! ($o['auto_increment'] ?? false),
            ];
        }

        return $this->semaCache[$tabla] = $eredmeny;
    }
}
