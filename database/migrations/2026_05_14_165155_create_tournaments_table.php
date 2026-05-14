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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('registration_deadline');
            $table->integer('max_participants')->default(0); // 0 means unlimited
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->string('prize_pool')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Draft, 1: Open, 2: Ongoing, 3: Completed, 4: Cancelled');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
