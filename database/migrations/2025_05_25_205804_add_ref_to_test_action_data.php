<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_action_data', function (Blueprint $table) {
            $table->uuid('ref_uuid')
                ->unique()
                ->default(DB::raw('uuid_generate_v4()'))
                ->nullable(false)
                ->comment("used for display and id outside the code");
        });


        Schema::table('users', function (Blueprint $table) {
            $table->uuid('ref_uuid')
                ->unique()
                ->default(DB::raw('uuid_generate_v4()'))
                ->nullable(false)
                ->comment("used for display and id outside the code");
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_action_data', function (Blueprint $table) {
            $table->dropColumn('ref_uuid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ref_uuid');
        });
    }
};
