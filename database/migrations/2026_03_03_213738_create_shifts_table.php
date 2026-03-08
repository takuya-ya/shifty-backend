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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id'); // スタッフプロフィールID
            $table->dateTime('start_at'); // 勤務開始時刻
            $table->dateTime('end_at');   // 勤務終了時刻
            $table->dateTime('break_start_at')->nullable(); // 休憩開始時刻
            $table->dateTime('break_end_at')->nullable();   // 休憩終了時刻
            $table->unsignedBigInteger('position_id')->nullable(); // ポジションID
            $table->enum('attendance_type', ['working'])->default('working'); // 出勤タイプ
            $table->enum('shift_state', ['draft', 'confirmed'])->default('draft'); // シフト状態
            $table->integer('version')->default(1); // 楽観的ロック用バージョン
            $table->text('memo')->nullable(); // メモ
            $table->timestamps();
            $table->softDeletes(); // ソフトデリート

            // 制約
            $table->unique(['staff_id', 'start_at']);
            $table->foreign('staff_id')->references('id')->on('staff_profiles')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
