<?php

/**
 * Önálló FK-ellenőrző (Laravel nélkül fut): minden migrációból kiolvassa az oszlopdefiníciókat
 * és az idegen kulcsokat, majd keresztbe ellenőrzi:
 *   - létezik-e a cél tábla és oszlop,
 *   - egyezik-e a forrás és a cél oszlop típusa (pl. INT UNSIGNED ↔ INT UNSIGNED) — errno 150 ellen,
 *   - nullOnDelete() mellett nullable-e a forrás oszlop.
 *
 * Használat:  php tools/fk_ellenorzo.php [migrációs_mappa]
 * Kilépési kód: 0 = rendben, 1 = hiba.
 */

$mappa = $argv[1] ?? __DIR__ . '/../database/migrations';
$fajlok = glob(rtrim($mappa, '/') . '/*.php');
sort($fajlok);

$TIPUS = [
    'increments' => 'INT UNSIGNED', 'unsignedInteger' => 'INT UNSIGNED', 'integer' => 'INT',
    'bigIncrements' => 'BIGINT UNSIGNED', 'id' => 'BIGINT UNSIGNED', 'unsignedBigInteger' => 'BIGINT UNSIGNED',
    'foreignId' => 'BIGINT UNSIGNED', 'bigInteger' => 'BIGINT',
    'smallIncrements' => 'SMALLINT UNSIGNED', 'unsignedSmallInteger' => 'SMALLINT UNSIGNED', 'smallInteger' => 'SMALLINT',
    'tinyIncrements' => 'TINYINT UNSIGNED', 'unsignedTinyInteger' => 'TINYINT UNSIGNED', 'tinyInteger' => 'TINYINT',
    'string' => 'VARCHAR', 'char' => 'CHAR',
];

$tablak = [];   // tabla => [oszlop => ['tipus' => , 'nullable' => bool]]
$fkk = [];      // [fajl, tabla, oszlop, celTabla, celOszlop, nullOnDelete]

foreach ($fajlok as $fajl) {
    $kod = file_get_contents($fajl);
    $blokkok = preg_split('/Schema::create\(\s*\'/', $kod);
    array_shift($blokkok);

    foreach ($blokkok as $blokk) {
        if (! preg_match('/^(\w+)\'/', $blokk, $m)) {
            continue;
        }
        $tabla = $m[1];
        $tablak[$tabla] ??= [];

        // oszlopdefiníciók: utasításonként (;-ig) nézzük, hogy a ->nullable() is látsszon
        foreach (preg_split('/;[ \t]*(?:\/\/[^\n]*)?\r?\n/', $blokk) as $utasitas) {
            if (preg_match('/\$table->(\w+)\((?:\'(\w+)\')?/', $utasitas, $o) && isset($TIPUS[$o[1]])) {
                $oszlop = $o[2] ?? ($o[1] === 'id' ? 'id' : null);
                if ($oszlop !== null) {
                    $tablak[$tabla][$oszlop] = [
                        'tipus' => $TIPUS[$o[1]],
                        'nullable' => str_contains($utasitas, '->nullable()'),
                    ];
                }
                if ($o[1] === 'foreignId' && str_contains($utasitas, '->constrained(')) {
                    $fkk[] = [basename($fajl), $tabla, $oszlop, null, 'id', str_contains($utasitas, 'nullOnDelete')];
                }
            }
            if (preg_match('/\$table->foreign\(\'(\w+)\'\)->references\(\'(\w+)\'\)->on\(\'(\w+)\'\)/', $utasitas, $f)) {
                $fkk[] = [basename($fajl), $tabla, $f[1], $f[3], $f[2], str_contains($utasitas, 'nullOnDelete')];
            }
        }
    }
}

$hibak = [];
foreach ($fkk as [$fajl, $tabla, $oszlop, $celTabla, $celOszlop, $nullOnDelete]) {
    $hely = "{$fajl}: {$tabla}.{$oszlop} → " . ($celTabla ?? '?') . ".{$celOszlop}";
    $forras = $tablak[$tabla][$oszlop] ?? null;

    if ($celTabla === null) {
        $hibak[] = "{$hely}: foreignId()->constrained() BIGINT-et hoz létre — használj unsignedInteger + foreign()-t";
        continue;
    }
    if ($forras === null) {
        $hibak[] = "{$hely}: a forrás oszlop nincs definiálva";
        continue;
    }
    if (! isset($tablak[$celTabla])) {
        $hibak[] = "{$hely}: a cél tábla nem létezik a migrációkban";
        continue;
    }
    $cel = $tablak[$celTabla][$celOszlop] ?? null;
    if ($cel === null) {
        $hibak[] = "{$hely}: a cél oszlop nem létezik";
        continue;
    }
    if ($forras['tipus'] !== $cel['tipus']) {
        $hibak[] = "{$hely}: TÍPUSELTÉRÉS ({$forras['tipus']} ≠ {$cel['tipus']}) → errno 150";
    }
    if ($nullOnDelete && ! $forras['nullable']) {
        $hibak[] = "{$hely}: nullOnDelete(), de a forrás oszlop nem nullable";
    }
}

printf("%d migráció, %d tábla, %d idegen kulcs ellenőrizve.\n", count($fajlok), count($tablak), count($fkk));
if ($hibak) {
    echo "HIBÁK:\n - " . implode("\n - ", $hibak) . "\n";
    exit(1);
}
echo "Minden idegen kulcs típusa egyezik.\n";
exit(0);
