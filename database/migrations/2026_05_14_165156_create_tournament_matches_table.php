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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->string('round_name')->nullable()->comment('e.g., Round of 16, Quarter-final');
            $table->foreignId('player1_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('player2_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->tinyInteger('status')->default(0)->comment('0: Scheduled, 1: Ongoing, 2: Completed');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
