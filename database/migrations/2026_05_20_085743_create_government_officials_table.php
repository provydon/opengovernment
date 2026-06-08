<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_officials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_government_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('official_title')->nullable();
            $table->string('password');

            // Government accounts must be approved by a platform admin before publishing.
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('government_officials');
    }
};
