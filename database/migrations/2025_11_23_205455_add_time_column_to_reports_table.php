<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Tambahkan kolom 'time' bertipe timestamp, bisa nullable (opsional)
            // Saya menempatkannya setelah kolom 'text'
            $table->timestamp('time')->nullable()->after('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Ketika di rollback, kolom 'time' akan dihapus
            $table->dropColumn('time');
        });
    }
};
