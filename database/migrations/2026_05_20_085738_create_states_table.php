<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Despite the table name, this is the "region above LGA" tier regardless
        // of what each country calls it (state, province, county, etc.). The
        // country's `region_label` controls the public-facing label.
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('capital')->nullable();
            $table->string('zone')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'slug']);
            $table->unique(['country_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
