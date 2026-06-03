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
        Schema::create('tournament_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->cascadeOnDelete();
            $table->integer('round_number')->nullable(); // 1, 2, 3...
            $table->string('name')->nullable(); // Vòng 1, Tứ kết, Bán kết, Chung kết
            $table->timestamps();
        });

        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('round_id')->nullable()->constrained('tournament_rounds')->nullOnDelete();
            $table->foreignId('player1_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->foreignId('player2_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->integer('player1_score')->nullable()->default(0);
            $table->integer('player2_score')->nullable()->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->tinyInteger('status')->nullable()->default(0); // 0: chưa đấu, 1: đang đấu, 2: hoàn thành
            $table->dateTime('match_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
        Schema::dropIfExists('tournament_rounds');
    }
};
