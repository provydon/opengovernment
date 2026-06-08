<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value'); // 1 or -1
            $table->timestamps();

            $table->unique(['issue_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_votes');
    }
};
