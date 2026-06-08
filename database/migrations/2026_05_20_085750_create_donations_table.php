<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('donor_name')->nullable(); // for anonymous-but-named donors
            $table->string('donor_email')->nullable();
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 3);
            $table->string('provider')->default('paystack');
            $table->string('provider_reference')->unique();
            $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
            $table->text('message')->nullable();
            $table->boolean('display_publicly')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
