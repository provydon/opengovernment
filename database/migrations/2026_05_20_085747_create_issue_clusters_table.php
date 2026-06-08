<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An issue cluster groups citizen issues that the AI similarity service
        // judges to be about the same underlying problem. The first issue posted
        // becomes the canonical "leader" of the cluster and accumulates votes from
        // duplicates that get folded into it.
        Schema::create('issue_clusters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_government_id')->constrained()->cascadeOnDelete();
            $table->string('signature', 64)->nullable()->index(); // simhash / embedding bucket id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_clusters');
    }
};
