<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
        $table->unsignedInteger('diskon_persen')->nullable()->default(0); // diskon dalam %
        $table->boolean('ppn_aktif')->default(false); // true = ya, false = tidak
        $table->unsignedInteger('ppn_persen')->nullable()->default(11); // hanya dipakai jika ppn_aktif = true
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('pakets', function (Blueprint $table) {
        $table->dropColumn(['diskon_persen', 'ppn_aktif', 'ppn_persen']);
    });
    }
};
