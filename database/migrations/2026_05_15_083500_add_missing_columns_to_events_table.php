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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('name');
            }
            if (!Schema::hasColumn('events', 'target_date')) {
                $table->date('target_date')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('events', 'private_flag')) {
                $table->tinyInteger('private_flag')->default(0)->after('type');
            }
        });

        Schema::table('user_events', function (Blueprint $table) {
            if (!Schema::hasColumn('user_events', 'is_complete')) {
                $table->tinyInteger('is_complete')->default(0)->after('memo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['location', 'target_date', 'private_flag']);
        });

        Schema::table('user_events', function (Blueprint $table) {
            $table->dropColumn(['is_complete']);
        });
    }
};
