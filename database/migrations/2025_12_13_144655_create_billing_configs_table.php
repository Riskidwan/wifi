<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_configs', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('ISP Anda');
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->integer('billing_start_day')->default(1);
            $table->integer('due_days_after_period')->default(5);
            $table->timestamps();
        });

        // Insert data default
        DB::table('billing_configs')->insert([
            'company_name' => 'ISP Anda',
            'billing_start_day' => 1,
            'due_days_after_period' => 5,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('billing_configs');
    }
};