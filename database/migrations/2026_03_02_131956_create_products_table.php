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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('avatar')->nullable();
            $table->tinyInteger('category')->default(1)->comment('1: Đồ ăn, 2: Đồ uống, 3: Thuê Gậy');
            $table->string('sku')->unique(); // Mã sản phẩm
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete();
            $table->decimal('cost_price', 10, 2)->default(0);  // Giá nhập
            $table->decimal('sale_price', 10, 2)->default(0);  // Giá bán            
            $table->integer('quantity')->default(0);
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('description')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
