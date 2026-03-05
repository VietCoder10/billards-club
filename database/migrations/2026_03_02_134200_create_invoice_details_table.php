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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('product_name'); // snapshot tại thời điểm thanh toán
            $table->integer('quantity');

            $table->decimal('price', 12, 2);
            $table->decimal('sub_total', 12, 2);

            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('final_line_amount', 12, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
