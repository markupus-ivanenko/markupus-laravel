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
        Schema::create('payment_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('electricity_day')->nullable();
            $table->decimal('electricity_night')->nullable();
            $table->decimal('gas')->nullable();
            $table->decimal('gas_delivery')->nullable();
            $table->decimal('water')->nullable();
            $table->decimal('heating')->nullable();
            $table->dateTime('rate_date')->nullable();
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
        Schema::dropIfExists('payment_rates');
    }
};
