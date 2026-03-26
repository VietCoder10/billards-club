<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('event_type');
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->tinyInteger('desktop_notification_flag')->nullable()->default(0);
            $table->text('note')->nullable();
            $table->string('color', 20)->nullable();
            $table->tinyInteger('type')->nullable()->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
