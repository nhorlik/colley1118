# Colley v2 — 1. fázis: adatbázis-réteg és szakmai mag

Mestermunka 2027 · Drajkó Gergő, Czinege Márton, Horlik Nimród
Stack: PHP 8.2+ · Laravel 12 · MySQL 8 / MariaDB 10.6+ (XAMPP) · Three.js r128 (2. fázistól)

Ez a csomag egy **friss Laravel 12 projektre másolandó réteg** (a `vendor/` mappát a `composer` tölti le).
Az 1. fázis UI nélkül is teljes: adatbázis, törzsadatok, edzésterv-generátor, orvosi/rehab kapu, tesztek, QA-eszközök.

---

## Telepítés (XAMPP, Windows)

```bash
cd C:\xampp\htdocs
composer create-project laravel/laravel colley "^12.0"
# a ZIP tartalmát másold a colley\ mappába, felülírással
cd colley
```

`.env` módosítások (új adatbázis, hogy a régi `colley` ne sérüljön):

```ini
DB_CONNECTION=mysql          # MariaDB esetén: mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=colley_v2
DB_USERNAME=root
DB_PASSWORD=
COLLEY_DIAG=true
```

phpMyAdmin-ban hozd létre a `colley_v2` adatbázist `utf8mb4_unicode_ci` illesztéssel, majd:

```bash
php artisan migrate --seed          # séma + nézetek + CHECK-ek + törzsadatok + 3 tesztfiók
php artisan test                    # egység- és feature-tesztek (SQLite memóriában)
php tools/fk_ellenorzo.php          # FK típusegyezés-ellenőrzés
php artisan colley:terv teszt.bulk@colley.hu --fokusz=1 --fokusz=5 --mag=42
php artisan serve                   # → http://127.0.0.1:8000/diag
```

Újraépítéskor: `php artisan migrate:fresh --seed --drop-views` (a nézetek miatt kell a `--drop-views`).

**Tesztfiókok** (jelszó: `colley123`): `teszt.bulk@colley.hu` (haladó, terem, heti 4), `teszt.rehab@colley.hu`
(rehab, otthon, porckorongsérv + isiász), `teszt.otthon@colley.hu` (zsírégetés, kezdő, otthon).

---

## Mi van a csomagban

| Terület | Fájlok |
|---|---|
| Migrációk (10) | `database/migrations/` — idempotensek (`Schema::hasTable` + `information_schema`), minden PK `increments()`, minden FK `unsignedInteger()` |
| Nézetek | `v_gyakorlat`, `v_valaszthato_gyakorlat` — szabványos SQL, MySQL-en és SQLite-on is fut |
| CHECK-kényszerek | `2026_11_18_000009_add_check_kenyszerek.php` — értéklisták az `App\Enums` osztályokból |
| Törzsadatok | `database/seeders/adat/*.php` (a dumpból generálva) + `TorzsadatSeeder` (upsert, tranzakció) |
| Seeder-validálás | `SeederValidator` — minden sort ellenőriz beszúrás előtt, minden hibát egyszerre jelez |
| Modellek | `app/Models/` (15 db), `Felhasznalo` = hitelesítendő modell (`jelszo_hash`) |
| Generátor | `app/Services/Terv/` — `TervGenerator` (DB) + tiszta, DB-független mag: `Valaszto`, `Pontozo`, `ParameterSzamito`, `Sablonok`, `TervValidator` |
| Orvosi/rehab kapu | `app/Services/Kapu/` — `EdzesKapu` (DB) + `OrvosiSzuro` (tiszta) → `KapuDontes` (TILTVA / REHAB / SZABAD, indokokkal) |
| Szűrés-kiértékelő | `app/Services/Rehab/SzuresKiertekelo` — régiós válaszok → eredmény + terhelési jelzők + üzenet |
| Artisan | `php artisan colley:terv {email}` — generálás UI nélkül |
| Diagnosztika | `GET /diag` — verzió, migrációk, seed-darabszámok, nézetek, kulcsfájlok hash-e |
| QA | `tools/fk_ellenorzo.php`, `tests/Unit` (5 osztály), `tests/Feature` (2 osztály) |

---

## A generátor működése

1. **EdzesKapu** → `TILTVA`: `EdzesTiltvaException` indokokkal és teendőkkel, semmi nem mentődik ·
   `REHAB`: rehab-sablonok, max. 2-es nehézség · `SZABAD`: normál sablonok
2. **Napsablonok** a heti edzésszám szerint: 1–3 → teljes test A/B/C · 4 → felső/alsó A/B · 5 → + kondíció ·
   6 → + teljes test C · rehab: mobilizáció–stabilizáció / kíméletes erősítés / aktív pihenő (max. 3 nap).
   Zsírégetés és állóképesség célnál minden nap végére kardió kerül.
   Minden slot mozgásminták prioritási listája (pl. függőleges húzás → ha nincs, vízszintes).
3. **Jelöltlista** a `v_valaszthato_gyakorlat` nézetből (szint, otthoni eszköz, ellenjavallat szűrve).
4. **Pontozás**: fő fókuszizom +3 · egyéb kijelölt izom +2 · rehab-ajánlott +4 · héten belüli ismétlés −4 ·
   nem-rehab felhasználónál terápiás −1,5 · erő-blokkban nem erő jellegű −1.
   Egyenlő pontnál a szinthez illő nehézség nyer (másodlagos rendezés), azon belül mag szerinti véletlen.
5. **Paraméterezés** cél és szint szerint (pl. erőfejlesztésnél összetett gyakorlat 4–6 ism., 150 mp pihenő;
   kezdőnél −1 szett; időalapú tétel szint szerint skálázva; kardió max. 45 perc).
6. **TervValidator** független utóellenőrzés → hiba esetén `TervGeneralasiHiba`, semmi nem mentődik.
7. **Mentés** egy tranzakcióban; a korábbi aktív terv `aktiv = 0`.

---

## Eltérések a régi dumphoz képest (döntések)

- **`panasz_tipus` 13–16 javítva**: a kód mozgás-érzékenységet jelölt, a név viszont orvosi állapotot
  ('Szívritmuszavar', 'Cukorbetegség' …), miközben a gyakorlat-tiltások a kód szerinti jelentésre épültek.
  A 13–16 visszakapta a mozgásjelző nevet (`eredet = terhelesi_jelzo`); a négy orvosi állapot új sorokba került
  (21–24, `eredet = orvosi_engedely`).
- **Kihagyva**: `bolt_targy`, `felhasznalo_targy` (öltöztetés), `fajdalom_naplo` és `felhasznalo_szures.fajdalom`
  (numerikus fájdalomskála). A rehabilitáció a régiós szűrésre épül. A token-rendszer gyűjt, költés egyelőre nincs.
- `fajdalom_hozzajarulas` → **`egeszseg_hozzajarulas`** (GDPR 9. cikk szerinti hozzájárulás az egészségügyi adathoz).
- **`v_gyakorlat` duplikáció javítva**: a több elsődleges izmú gyakorlatok (pl. tolódzkodás, felhúzás) a régi
  nézetben 2–3 sorban jelentek meg; most gyakorlatonként pontosan 1 sor van (69).
- **Új**: `edzesterv.rehab` oszlop; `naplo_szett` egyedi kulcs (`updateOrCreate` alapja); CHECK-kényszerek
  (a phpMyAdmin-dump elhagyta őket).
- **Enumok**: `szint` = KEZDO / HALADO / PROFI (a nézet `ELSE 5` ágához); `cel` = BULK / ERO / FOGYAS /
  ALLOKEPESSEG / REHABILITACIO.
- **`rehab_mod`**: diagnózis vagy terhelési jelző típusú panasz, illetve rehabilitációs cél kapcsolja be;
  az orvosi engedélyhez kötött állapot önmagában nem (azt a kapu kezeli).
- **Piros zászlós szűrés**: a teljes generálást blokkolja (TILTVA), amíg a szűrés *utáni* orvosi engedély nincs rögzítve.
  A régi rendszer ilyenkor csak az adott régiót zárta ki.
- **Seed**: csak a három tesztfiók; a dumpban lévő valós személyek adatai és az előzmények nem kerülnek át.

---

## Fázisterv

| Fázis | Tartalom |
|---|---|
| **1 – kész** | adatbázis, törzsadatok, generátor, kapu, szűrés-kiértékelő, tesztek, FK-ellenőrző, /diag |
| 2 | hitelesítés (`config/auth.php` → `Felhasznalo`), egylépéses regisztrációs kérdőív (orvosi állapotok + explicit egészségügyi hozzájárulás), Blade-layout, navigáció, Tervem / Új terv, 3D avatar (`data-mesh` ↔ `izomcsoport.mesh_nev`), gyakorlatböngésző |
| 3 | edzés-végrehajtás (időzítő, `POST /api/naplo` `updateOrCreate`-tel, `[hidden]`-szabály), token-szolgáltatás (`JutalomEredmeny`, napi fokozatos korlát, fejléc-kijelző) |
| 4 | rehabilitáció (régiós kártyák, szűrés mentése a régi jelzők törlésével, idővonal), naptár (havi rács, beidőzítés, push-értesítés, drift-korrigált pollozás) |
| 5 | Sanctum REST API az asztali klienshez (ugyanazok a szolgáltatásosztályok), végső QA |
