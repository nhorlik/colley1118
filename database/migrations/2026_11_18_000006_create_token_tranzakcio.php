<?php

/*
 * Append-only token-mozgásnapló. NINCS tárolt egyenleg-oszlop: egyenleg = SUM(mennyiseg).
 * uq_token_forras: egy forrásért (pl. egy edzes_session) csak egyszer lehet jutalmat kapni.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('token_tranzakcio')) {
            Schema::create('token_tranzakcio', function (Blueprint $table) {
                $table->increments('token_tranzakcio_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->integer('mennyiseg');
                $table->string('tipus', 24);
                $table->string('leiras', 160);
                $table->string('forras_tipus', 24)->nullable();
                $table->unsignedInteger('forras_id')->nullable();
                $table->dateTime('letrehozva')->useCurrent();

                $table->unique(['felhasznalo_id', 'forras_tipus', 'forras_id'], 'uq_token_forras');
                $table->index(['felhasznalo_id', 'letrehozva'], 'ix_token_felh');
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('token_tranzakcio');
    }
};
