<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_government_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issue_cluster_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('category')->nullable(); // roads, water, electricity, security, health...

            // Denormalised vote counters updated by the IssueVote model.
            $table->unsignedInteger('upvotes')->default(0);
            $table->unsignedInteger('downvotes')->default(0);
            $table->integer('score')->default(0);

            $table->enum('status', ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['local_government_id', 'status']);
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
