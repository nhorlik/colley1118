<?php

namespace App\Models;

use App\Enums\Cel;
use App\Enums\PanaszEredet;
use App\Enums\Szint;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Fiók + regisztrációs kérdőív. A hitelesítés ezt a modellt használja (config/auth.php → 2. fázis).
 */
class Felhasznalo extends Authenticatable
{
    use Notifiable;

    protected $table = 'felhasznalo';
    protected $primaryKey = 'felhasznalo_id';
    public $timestamps = false;

    protected $fillable = [
        'email', 'jelszo_hash', 'nev', 'cel', 'szint', 'testalkat', 'nem', 'szuletesi_ev',
        'magassag_cm', 'testsuly_kg', 'heti_edzesszam', 'otthon_edz', 'rehab_mod',
        'orvosi_engedely', 'egeszseg_hozzajarulas', 'gdpr_hozzajarulas',
    ];

    protected $hidden = ['jelszo_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'cel' => Cel::class,
            'szint' => Szint::class,
            'otthon_edz' => 'boolean',
            'rehab_mod' => 'boolean',
            'heti_edzesszam' => 'integer',
            'testsuly_kg' => 'decimal:2',
            'orvosi_engedely' => 'datetime',
            'egeszseg_hozzajarulas' => 'datetime',
            'gdpr_hozzajarulas' => 'datetime',
            'regisztralt' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->jelszo_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'jelszo_hash';
    }

    public function panaszok(): BelongsToMany
    {
        return $this->belongsToMany(PanaszTipus::class, 'felhasznalo_panasz', 'felhasznalo_id', 'panasz_tipus_id')
            ->withPivot('megjegyzes', 'rogzitve');
    }

    public function edzestervek(): HasMany
    {
        return $this->hasMany(Edzesterv::class, 'felhasznalo_id');
    }

    public function aktivTerv(): HasOne
    {
        return $this->hasOne(Edzesterv::class, 'felhasznalo_id')->where('aktiv', true)->latestOfMany('edzesterv_id');
    }

    public function szuresek(): HasMany
    {
        return $this->hasMany(FelhasznaloSzures::class, 'felhasznalo_id');
    }

    public function sessionok(): HasMany
    {
        return $this->hasMany(EdzesSession::class, 'felhasznalo_id');
    }

    public function tokenTranzakciok(): HasMany
    {
        return $this->hasMany(TokenTranzakcio::class, 'felhasznalo_id');
    }

    public function edzesIdopontok(): HasMany
    {
        return $this->hasMany(EdzesIdopont::class, 'felhasznalo_id');
    }

    /**
     * rehab_mod szabálya: bekapcsol, ha a cél rehabilitáció, vagy van diagnózis / terhelési jelző
     * típusú panasz. Az orvosi engedélyhez kötött állapotok (pl. cukorbetegség) önmagukban NEM
     * kapcsolnak rehab módot — azokat az EdzesKapu kezeli (TILTVA, amíg nincs orvosi engedély).
     */
    public function rehabModSzamol(): bool
    {
        if ($this->cel === Cel::REHABILITACIO) {
            return true;
        }

        return $this->panaszok()
            ->whereIn('eredet', [PanaszEredet::DIAGNOZIS->value, PanaszEredet::TERHELESI_JELZO->value])
            ->exists();
    }

    public function rehabModFrissit(): void
    {
        $uj = $this->rehabModSzamol();
        if ($this->rehab_mod !== $uj) {
            $this->forceFill(['rehab_mod' => $uj])->save();
        }
    }
}
