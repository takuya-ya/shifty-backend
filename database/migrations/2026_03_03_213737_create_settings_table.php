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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('period_type')->comment('期間種別 (half_month, month, week等)');
            $table->integer('period_start_day')->comment('シフト開始日 (1, 16等)');
            $table->time('day_start_time')->comment('シフト表の開始時刻 (09:00等)');
            $table->time('day_end_time')->comment('シフト表の終了時刻 (23:00等)');
            $table->integer('initial_view_days')->comment('初期表示日数 (14等)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
