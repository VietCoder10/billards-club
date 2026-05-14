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
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->integer('match_number')->nullable()->after('tournament_id')->comment('Match sequence number');
            $table->integer('round_number')->nullable()->after('match_number')->comment('Round number (1, 2, 3...)');
            $table->tinyInteger('bracket_type')->default(0)->after('round_number')->comment('0: Winner Bracket, 1: Loser Bracket');
            $table->foreignId('next_match_id')->nullable()->after('bracket_type')->constrained('tournament_matches')->onDelete('set null');
            $table->foreignId('next_loser_match_id')->nullable()->after('next_match_id')->constrained('tournament_matches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            //
        });
    }
};
