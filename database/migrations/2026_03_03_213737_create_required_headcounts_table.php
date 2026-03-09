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
        Schema::create('required_headcounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique()->comment('0=日〜6=土');
            $table->integer('required_count')->comment('必要人数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_headcounts');
    }
};
