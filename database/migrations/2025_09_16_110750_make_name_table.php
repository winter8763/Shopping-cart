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
        Schema::table('order_items', function (Blueprint $table) {
            // 如果你要允許空值，改成 ->nullable()
            $table->string('name')->after('product_id')->default('')->comment('商品名稱');
            // 若想要 price 也確認存在，則可以在這裡檢查或新增
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
