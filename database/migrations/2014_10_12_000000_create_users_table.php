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
        Schema::create('users', function (Blueprint $table) {
            // barber and client together
            $table->id();
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->enum('role', ['barber', 'client']);
            $table->enum('gender', ['male', 'female']);

            // just for client
            $table->string('address')->nullable();

            // just for barber
            // $table->string('work_time')->nullable();
            $table->string('work_experience')->nullable();
            $table->foreignId('barber_shop_id')->nullable()->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
