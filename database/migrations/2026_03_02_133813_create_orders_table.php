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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('table_id')
                ->constrained('tables')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // nhân viên tạo order
            $table->text('note')->nullable();
            $table->integer('status')->default(0); // 0: đang phục vụ, 1: đã thanh toán, 2: đã hủy

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->decimal('price_per_hour', 12, 2);
            $table->integer('total_minutes')->default(0);
            $table->decimal('table_total', 12, 2)->default(0);

            $table->decimal('service_total', 12, 2)->default(0);
            $table->decimal('final_total', 12, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
