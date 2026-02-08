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
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            $table->decimal('line_cost_total', 12, 2)->default(0)->after('line_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['cost_price', 'line_cost_total']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('cost_price');
        });
    }
};

