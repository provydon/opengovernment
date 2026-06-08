<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spending_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index('spending_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_comments');
    }
};
