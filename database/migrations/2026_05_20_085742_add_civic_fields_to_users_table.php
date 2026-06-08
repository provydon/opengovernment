<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained()->nullOnDelete();
            $table->foreignId('local_government_id')->nullable()->after('state_id')->constrained()->nullOnDelete();

            // Identity is stored hashed — raw national IDs are never persisted.
            // The country's identity_scheme decides what these correspond to
            // (e.g. NG: NIN / BVN; KE: National ID; etc.).
            $table->string('primary_id_hash', 64)->nullable()->unique()->after('local_government_id');
            $table->string('secondary_id_hash', 64)->nullable()->unique()->after('primary_id_hash');
            $table->string('verification_provider')->nullable()->after('secondary_id_hash');
            $table->string('verification_reference')->nullable()->after('verification_provider');
            $table->timestamp('identity_verified_at')->nullable()->after('verification_reference');

            $table->string('phone')->nullable()->after('identity_verified_at');
            $table->boolean('is_banned')->default(false)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropConstrainedForeignId('state_id');
            $table->dropConstrainedForeignId('local_government_id');
            $table->dropColumn([
                'primary_id_hash',
                'secondary_id_hash',
                'verification_provider',
                'verification_reference',
                'identity_verified_at',
                'phone',
                'is_banned',
            ]);
        });
    }
};
