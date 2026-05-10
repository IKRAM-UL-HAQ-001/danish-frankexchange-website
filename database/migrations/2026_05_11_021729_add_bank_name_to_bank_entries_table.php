<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_entries', function (Blueprint $table) {
            // Add bank_name column if it doesn't exist
            if (!Schema::hasColumn('bank_entries', 'bank_name')) {
                $table->string('bank_name')->after('id')->nullable();
            }
            // Make bank_id nullable if it exists (live server may not have it)
            if (Schema::hasColumn('bank_entries', 'bank_id')) {
                $table->unsignedBigInteger('bank_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_entries', function (Blueprint $table) {
            if (Schema::hasColumn('bank_entries', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
        });
    }
};
