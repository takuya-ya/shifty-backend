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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->nullable();
            $table->string('name')->comment('スタッフ名');
            $table->date('date_of_birth')->nullable()->comment('年少者チェック用');
            $table->boolean('is_student')->nullable()->comment('学生フラグ');
            $table->decimal('hourly_wage', 8, 2)->nullable()->comment('時給');
            $table->string('memo')->nullable()->comment('備考');
            $table->integer('max_consecutive_days')->nullable()->comment('最大連勤日数 (Phase3)');
            $table->integer('max_hours_per_week')->nullable()->comment('週最大労働時間 (Phase3)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
