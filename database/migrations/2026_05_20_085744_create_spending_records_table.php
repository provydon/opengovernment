<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_government_id')->constrained()->cascadeOnDelete();
            $table->foreignId('published_by')->constrained('government_officials')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // e.g. infrastructure, health, education
            $table->text('description');

            // Store amount as integer minor units (kobo, cents, pesewas, etc.)
            // and pair it with an ISO 4217 currency code so a single deployment
            // can in theory hold records from multiple jurisdictions.
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 3);
            $table->string('vendor')->nullable();
            $table->date('spent_on');
            $table->string('source_document_url')->nullable(); // link to original PDF / scan

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['local_government_id', 'spent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_records');
    }
};
