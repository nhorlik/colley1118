<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('edzesterv') && ! Schema::hasColumn('edzesterv', 'rehab')) {
            Schema::table('edzesterv', function (Blueprint $table) {
                $table->boolean('rehab')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('edzesterv') && Schema::hasColumn('edzesterv', 'rehab')) {
            Schema::table('edzesterv', function (Blueprint $table) {
                $table->dropColumn('rehab');
            });
        }
    }
};
