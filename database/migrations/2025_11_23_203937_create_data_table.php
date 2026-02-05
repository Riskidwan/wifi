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
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->text('text'); // Kolom yang dibutuhkan Model/View
            // $table->timestamp('time')->nullable()->after('text');
            $table->timestamp('time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('data', function (Blueprint $table) {
        // Ketika di rollback, kolom 'time' akan dihapus
        $table->dropColumn('time');
    });
    }
};
