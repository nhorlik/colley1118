<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('edzesterv')) {
            Schema::create('edzesterv', function (Blueprint $table) {
                $table->increments('edzesterv_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->string('nev', 120);
                $table->date('kezdo_datum');
                $table->boolean('aktiv')->default(true);          // egyszerre egy aktív terv / felhasználó
                $table->boolean('rehab')->default(false);         // rehabilitációs sablonnal készült-e
                $table->dateTime('letrehozva')->useCurrent();

                $table->index(['felhasznalo_id', 'aktiv'], 'ix_terv_felh');
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('edzesterv_fokusz')) {
            Schema::create('edzesterv_fokusz', function (Blueprint $table) {
                $table->unsignedInteger('edzesterv_id');
                $table->unsignedInteger('izomcsoport_id');
                $table->boolean('fo_fokusz')->default(false);

                $table->primary(['edzesterv_id', 'izomcsoport_id']);
                $table->foreign('edzesterv_id')->references('edzesterv_id')->on('edzesterv')->cascadeOnDelete();
                $table->foreign('izomcsoport_id')->references('izomcsoport_id')->on('izomcsoport')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('edzesnap')) {
            Schema::create('edzesnap', function (Blueprint $table) {
                $table->increments('edzesnap_id');
                $table->unsignedInteger('edzesterv_id');
                $table->tinyInteger('nap_sorszam');
                $table->string('nev', 80);

                $table->unique(['edzesterv_id', 'nap_sorszam'], 'uq_edzesnap');
                $table->foreign('edzesterv_id')->references('edzesterv_id')->on('edzesterv')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('edzesnap_gyakorlat')) {
            Schema::create('edzesnap_gyakorlat', function (Blueprint $table) {
                $table->increments('edzesnap_gyakorlat_id');
                $table->unsignedInteger('edzesnap_id');
                $table->unsignedInteger('gyakorlat_id');
                $table->tinyInteger('sorrend');
                $table->tinyInteger('szett_szam')->default(3);
                $table->smallInteger('ism_min')->nullable();
                $table->smallInteger('ism_max')->nullable();
                $table->smallInteger('ido_mp')->nullable();        // ha kitöltött → visszaszámlálós tétel
                $table->smallInteger('piheno_mp')->default(60);

                $table->unique(['edzesnap_id', 'sorrend'], 'uq_eng_sorrend');
                $table->foreign('edzesnap_id')->references('edzesnap_id')->on('edzesnap')->cascadeOnDelete();
                $table->foreign('gyakorlat_id')->references('gyakorlat_id')->on('gyakorlat');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edzesnap_gyakorlat');
        Schema::dropIfExists('edzesnap');
        Schema::dropIfExists('edzesterv_fokusz');
        Schema::dropIfExists('edzesterv');
    }
};
