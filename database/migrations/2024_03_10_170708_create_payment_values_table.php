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
        Schema::create('payment_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('electricity_day')->nullable();
            $table->decimal('actual_electricity_day')->nullable();
            $table->decimal('actual_sum_electricity_day')->nullable();
            $table->decimal('electricity_night')->nullable();
            $table->decimal('actual_electricity_night')->nullable();
            $table->decimal('actual_sum_electricity_night')->nullable();
            $table->decimal('gas')->nullable();
            $table->decimal('actual_gas')->nullable();
            $table->decimal('actual_sum_gas')->nullable();
            $table->decimal('gas_delivery')->nullable();
            $table->decimal('water')->nullable();
            $table->decimal('actual_water')->nullable();
            $table->decimal('actual_sum_water')->nullable();
            $table->decimal('heating')->nullable();
            $table->decimal('count_by_rate_no_heating_water_gas_delivery')->nullable();
            $table->decimal('count_by_rate_no_heating')->nullable();
            $table->decimal('count_by_rate')->nullable();
            $table->dateTime('count_date')->nullable();
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('payment_addresses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_values');
    }
};
