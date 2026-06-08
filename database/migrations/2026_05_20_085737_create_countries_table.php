<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso2', 2)->unique();          // e.g. NG, KE, GH
            $table->string('iso3', 3)->unique();          // e.g. NGA, KEN, GHA
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('currency_code', 3);           // ISO 4217: NGN, KES, GHS
            $table->string('currency_symbol', 8);

            // Naming tier for the region above the LGA — "State", "Province",
            // "County", "Region" etc. Lets the UI label things correctly per country.
            $table->string('region_label')->default('State');
            $table->string('local_government_label')->default('Local Government');

            // Identity scheme handled by this country (e.g. "ng-nin-bvn", "ke-id",
            // "us-ssn"). The IdentityVerificationProvider implementation registered
            // for this scheme decides what fields are needed.
            $table->string('identity_scheme')->default('generic');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
