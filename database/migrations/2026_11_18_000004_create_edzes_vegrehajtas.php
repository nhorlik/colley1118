<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('edzes_session')) {
            Schema::create('edzes_session', function (Blueprint $table) {
                $table->increments('edzes_session_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->unsignedInteger('edzesnap_id');
                $table->date('datum');
                $table->dateTime('kezdes')->nullable();
                $table->dateTime('befejezes')->nullable();
                $table->smallInteger('tervezett_szett')->default(0);
                $table->smallInteger('teljesitett_szett')->default(0);
                $table->string('statusz', 16)->default('folyamatban');
                $table->tinyInteger('kozerzet')->nullable();
                $table->string('megjegyzes', 255)->nullable();

                $table->unique(['felhasznalo_id', 'edzesnap_id', 'datum'], 'uq_session_nap_nap');
                $table->index(['felhasznalo_id', 'statusz'], 'ix_session_felh');
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
                $table->foreign('edzesnap_id')->references('edzesnap_id')->on('edzesnap')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('naplo_szett')) {
            Schema::create('naplo_szett', function (Blueprint $table) {
                $table->increments('naplo_szett_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->unsignedInteger('edzes_session_id')->nullable();
                $table->unsignedInteger('edzesnap_gyakorlat_id')->nullable();
                $table->unsignedInteger('gyakorlat_id');
                $table->date('datum');
                $table->tinyInteger('szett_sorszam');
                $table->smallInteger('ismetles')->nullable();
                $table->decimal('suly_kg', 6, 2)->nullable();
                $table->smallInteger('ido_mp')->nullable();
                $table->dateTime('rogzitve')->useCurrent();

                // updateOrCreate kulcsa: ugyanaz a szett többszöri beküldése nem duplikál
                $table->unique(['edzes_session_id', 'edzesnap_gyakorlat_id', 'szett_sorszam'], 'uq_naplo_szett');
                $table->index(['felhasznalo_id', 'datum'], 'ix_naplo_felh_datum');
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
                $table->foreign('edzes_session_id')->references('edzes_session_id')->on('edzes_session')->nullOnDelete();
                $table->foreign('edzesnap_gyakorlat_id')->references('edzesnap_gyakorlat_id')->on('edzesnap_gyakorlat')->nullOnDelete();
                $table->foreign('gyakorlat_id')->references('gyakorlat_id')->on('gyakorlat');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('naplo_szett');
        Schema::dropIfExists('edzes_session');
    }
};
