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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('start_time');
            $table->string('end_time');
            $table->foreignId('service_id')->constrained();
            $table->foreignId('barber_shops_id')->nullable()->constrained();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->nullable()->on('users');
            $table->unsignedBigInteger('barber_id');
            $table->foreign('barber_id')->references('id')->on('users');
            $table->enum('status', ['reserved', 'done'])->default('reserved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
