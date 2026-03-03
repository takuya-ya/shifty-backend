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
        Schema::create('weekday_closures', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->primary()->comment('0=日〜6=土');
            $table->boolean('is_closed')->default(false)->comment('店休日フラグ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekday_closures');
    }
};
