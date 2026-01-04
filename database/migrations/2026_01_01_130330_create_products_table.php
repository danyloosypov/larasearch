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
        Schema::create('products_test', function (Blueprint $table) {
            $table->char('uniq_id', 32)->primary();

            $table->text('crawl_timestamp')->nullable();
            $table->text('product_url')->nullable();
            $table->text('product_name')->nullable();
            $table->text('product_category_tree')->nullable();
            $table->text('pid')->nullable();

            $table->text('retail_price')->nullable();
            $table->text('discounted_price')->nullable();

            $table->text('image')->nullable();
            $table->text('is_FK_Advantage_product')->nullable();

            $table->longText('description')->nullable();
            $table->text('product_rating')->nullable();
            $table->text('overall_rating')->nullable();

            $table->string('brand', 255)->nullable();
            $table->longText('product_specifications')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_test');
    }
};
