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
        if (! Schema::hasColumn('users', 'biodata')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('biodata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'biodata')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->text('biodata')->nullable()->after('email');
        });
    }
};
