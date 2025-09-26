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
        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'name')) {
                $table->string('name')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('cart_items', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'price']);
        });
    }
};
