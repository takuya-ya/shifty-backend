<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropUnique(['staff_id', 'start_at']);
        });

        DB::statement("
            ALTER TABLE shifts
            ADD COLUMN unique_delete_key DATETIME
            GENERATED ALWAYS AS (COALESCE(deleted_at, '9999-12-31 23:59:59')) STORED
        ");

        Schema::table('shifts', function (Blueprint $table) {
            $table->unique(['staff_id', 'start_at', 'unique_delete_key']);
            $table->foreign('staff_id')->references('id')->on('staff_profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropUnique(['staff_id', 'start_at', 'unique_delete_key']);
        });

        DB::statement('ALTER TABLE shifts DROP COLUMN unique_delete_key');

        Schema::table('shifts', function (Blueprint $table) {
            $table->unique(['staff_id', 'start_at']);
            $table->foreign('staff_id')->references('id')->on('staff_profiles')->onDelete('cascade');
        });
    }
};
