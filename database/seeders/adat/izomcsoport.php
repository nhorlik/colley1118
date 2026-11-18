<?php

// Generálva a colley SQL-dumpból (tools nélkül ne szerkeszd kézzel, ha lehet).

return [
    ['izomcsoport_id' => 1, 'kod' => 'MELL', 'nev' => 'Mellizom', 'testoldal' => 'elulso', 'mesh_nev' => 'mesh_mell', 'testterulet_id' => null, 'sorrend' => 10],
    ['izomcsoport_id' => 2, 'kod' => 'HAT', 'nev' => 'Hát (széles hátizom)', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_hat', 'testterulet_id' => null, 'sorrend' => 20],
    ['izomcsoport_id' => 3, 'kod' => 'TRAPEZ', 'nev' => 'Csuklyásizom / felső hát', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_trapez', 'testterulet_id' => null, 'sorrend' => 25],
    ['izomcsoport_id' => 4, 'kod' => 'VALL', 'nev' => 'Váll', 'testoldal' => 'mindketto', 'mesh_nev' => 'mesh_vall', 'testterulet_id' => 2, 'sorrend' => 30],
    ['izomcsoport_id' => 5, 'kod' => 'BICEPSZ', 'nev' => 'Bicepsz', 'testoldal' => 'elulso', 'mesh_nev' => 'mesh_bicepsz', 'testterulet_id' => null, 'sorrend' => 40],
    ['izomcsoport_id' => 6, 'kod' => 'TRICEPSZ', 'nev' => 'Tricepsz', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_tricepsz', 'testterulet_id' => null, 'sorrend' => 45],
    ['izomcsoport_id' => 7, 'kod' => 'ALKAR', 'nev' => 'Alkar', 'testoldal' => 'mindketto', 'mesh_nev' => 'mesh_alkar', 'testterulet_id' => null, 'sorrend' => 48],
    ['izomcsoport_id' => 8, 'kod' => 'HAS', 'nev' => 'Hasizom', 'testoldal' => 'elulso', 'mesh_nev' => 'mesh_has', 'testterulet_id' => 1, 'sorrend' => 50],
    ['izomcsoport_id' => 9, 'kod' => 'FERDE_HAS', 'nev' => 'Ferde hasizom', 'testoldal' => 'elulso', 'mesh_nev' => 'mesh_ferde_has', 'testterulet_id' => 1, 'sorrend' => 55],
    ['izomcsoport_id' => 10, 'kod' => 'GERINC', 'nev' => 'Gerincfeszítő / alsó hát', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_gerinc', 'testterulet_id' => 1, 'sorrend' => 58],
    ['izomcsoport_id' => 11, 'kod' => 'FARIZOM', 'nev' => 'Farizom', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_farizom', 'testterulet_id' => 5, 'sorrend' => 60],
    ['izomcsoport_id' => 12, 'kod' => 'COMB_ELUL', 'nev' => 'Combizom (elöl)', 'testoldal' => 'elulso', 'mesh_nev' => 'mesh_comb_elul', 'testterulet_id' => 3, 'sorrend' => 65],
    ['izomcsoport_id' => 13, 'kod' => 'COMB_HATSO', 'nev' => 'Combhajlító', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_comb_hatso', 'testterulet_id' => 3, 'sorrend' => 70],
    ['izomcsoport_id' => 14, 'kod' => 'COMB_OLDAL', 'nev' => 'Comb belső / külső', 'testoldal' => 'mindketto', 'mesh_nev' => 'mesh_comb_oldal', 'testterulet_id' => 3, 'sorrend' => 75],
    ['izomcsoport_id' => 15, 'kod' => 'VADLI', 'nev' => 'Vádli', 'testoldal' => 'hatso', 'mesh_nev' => 'mesh_vadli', 'testterulet_id' => 6, 'sorrend' => 80],
    ['izomcsoport_id' => 16, 'kod' => 'NYAK', 'nev' => 'Nyak', 'testoldal' => 'mindketto', 'mesh_nev' => 'mesh_nyak', 'testterulet_id' => 4, 'sorrend' => 90],
];
