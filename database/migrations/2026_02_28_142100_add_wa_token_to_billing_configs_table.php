<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::table('billing_configs', function (Blueprint $table) {
            $table->string('wa_token')->nullable()->after('due_days_after_period');
        });
    }

    public function down()
    {
        Schema::table('billing_configs', function (Blueprint $table) {
            $table->dropColumn('wa_token');
        });
    }
};
