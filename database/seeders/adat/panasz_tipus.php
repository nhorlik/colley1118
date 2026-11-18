<?php

// Generálva a colley SQL-dumpból (tools nélkül ne szerkeszd kézzel, ha lehet).

return [
    ['panasz_tipus_id' => 1, 'kod' => 'PORCKOPAS', 'nev' => 'Porckopás (gerinc)', 'terulet' => 'gerinc', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 2, 'kod' => 'PORCKORONG', 'nev' => 'Porckorongsérv (gerincsérv)', 'terulet' => 'gerinc', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 3, 'kod' => 'GERINCFERDULES', 'nev' => 'Gerincferdülés (szkoliózis)', 'terulet' => 'gerinc', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 4, 'kod' => 'ISIASZ', 'nev' => 'Isiász (lábba sugárzó fájdalom)', 'terulet' => 'gerinc', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 5, 'kod' => 'NYAKI', 'nev' => 'Nyaki gerincpanasz', 'terulet' => 'gerinc', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 6, 'kod' => 'TERD', 'nev' => 'Térdpanasz', 'terulet' => 'izulet', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 7, 'kod' => 'VALL', 'nev' => 'Vállpanasz', 'terulet' => 'izulet', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 8, 'kod' => 'CSUKLO', 'nev' => 'Csukló- vagy könyökpanasz', 'terulet' => 'izulet', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 9, 'kod' => 'BOKA', 'nev' => 'Boka- vagy lábfejpanasz', 'terulet' => 'izulet', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 10, 'kod' => 'CSIPO', 'nev' => 'Csípőpanasz', 'terulet' => 'izulet', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 11, 'kod' => 'MAGAS_VERNYOMAS', 'nev' => 'Magas vérnyomás', 'terulet' => 'altalanos', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 12, 'kod' => 'DIASZTAZIS', 'nev' => 'Rectus diasztázis (szétnyílt hasizom)', 'terulet' => 'altalanos', 'eredet' => 'diagnozis'],
    ['panasz_tipus_id' => 13, 'kod' => 'HAJLITAS_ERZEKENY', 'nev' => 'Hajlításra érzékeny derék (előrehajlás, ülés, emelés)', 'terulet' => 'gerinc', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 14, 'kod' => 'EXTENZIO_ERZEKENY', 'nev' => 'Hátrahajlításra érzékeny derék (extenzió, hosszabb állás)', 'terulet' => 'gerinc', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 15, 'kod' => 'TERHELES_ERZEKENY', 'nev' => 'Axiális terhelésre érzékeny gerinc (cipekedés, ugrás)', 'terulet' => 'gerinc', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 16, 'kod' => 'VALL_FEJFOLOTT_KERULENDO', 'nev' => 'Fej fölötti vállmozgás kerülendő', 'terulet' => 'izulet', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 17, 'kod' => 'TERD_UTKOZES_KERULENDO', 'nev' => 'Ütközésterhelés kerülendő (ugrálás, szökdelés)', 'terulet' => 'izulet', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 18, 'kod' => 'NYAK_TERHELES_KERULENDO', 'nev' => 'Nyaki terhelés kerülendő (fej fölötti, nyakat terhelő)', 'terulet' => 'gerinc', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 19, 'kod' => 'CSIPO_MELY_KERULENDO', 'nev' => 'Mély csípőhajlítás kerülendő (mély guggolás)', 'terulet' => 'izulet', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 20, 'kod' => 'BOKA_UTKOZES_KERULENDO', 'nev' => 'Bokát érő ütközés kerülendő (futás, ugrás)', 'terulet' => 'izulet', 'eredet' => 'terhelesi_jelzo'],
    ['panasz_tipus_id' => 21, 'kod' => 'SZIVRITMUSZAVAR', 'nev' => 'Szívritmuszavar', 'terulet' => 'altalanos', 'eredet' => 'orvosi_engedely'],
    ['panasz_tipus_id' => 22, 'kod' => 'TERHELESES_LEGSZOMJ', 'nev' => 'Terhelésre jelentkező légszomj', 'terulet' => 'altalanos', 'eredet' => 'orvosi_engedely'],
    ['panasz_tipus_id' => 23, 'kod' => 'TERHELESES_MELLKASI_FAJDALOM', 'nev' => 'Terhelésre jelentkező mellkasi fájdalom', 'terulet' => 'altalanos', 'eredet' => 'orvosi_engedely'],
    ['panasz_tipus_id' => 24, 'kod' => 'CUKORBETEGSEG', 'nev' => 'Cukorbetegség', 'terulet' => 'altalanos', 'eredet' => 'orvosi_engedely'],
];
