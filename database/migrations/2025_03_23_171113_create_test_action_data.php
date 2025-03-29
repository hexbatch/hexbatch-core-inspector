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
        Schema::create('test_action_data', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_user_id')
                ->nullable()
                ->default(null)
                ->comment("The owner")
                ->index()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('parent_action_id')
                ->nullable()->default(null)
                ->comment("If has a parent")
                ->index()
                ->constrained('test_action_data')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->integer('test_action_priority')->default(0)->nullable(false)->comment("Priority default 0");
            $table->integer('test_action_start_offset_seconds')->default(0)->nullable(false)->comment("if delayed start");
            $table->integer('test_action_invalid_offset_seconds')->default(0)->nullable(false)->comment("if invalid after");
            $table->integer('test_action_data_row_limit')->default(0)->nullable(false)->comment("if invalid after");
            $table->boolean('test_action_async')->default(false)->nullable(false)->comment("if true then this is asyncronous");

            $table->jsonb('test_action_content')->default(null)->nullable()->comment("holds the data for the action");
            $table->jsonb('test_action_constant')->default(null)->nullable()->comment("holds the constants for the action");
            $table->jsonb('test_action_tags')->default(null)->nullable()->comment("holds array of string tags");
        });

        DB::statement("CREATE TYPE type_of_test_action_status AS ENUM (
            'action_pending',
            'action_success',
            'action_fail',
            'action_error'
            );");

        DB::statement("ALTER TABLE test_action_data Add COLUMN action_status type_of_test_action_status NOT NULL default 'action_pending';");

        Schema::table('test_action_data', function (Blueprint $table) {


            $table->string('test_action_color',10)
                ->default(null)->nullable()
                ->comment("css color if used");

            $table->string('test_action_type',20)
                ->index()
                ->default(null)->nullable()
                ->comment("what kind of action is this");

            $table->string('test_action_name',20)
                ->unique()
                ->default(null)->nullable()
                ->comment("give this action a unique name for better tracing");


            $table->string('parent_key',20)
                ->default(null)->nullable()
                ->comment("optional key for parent grouping");

            $table->index(['parent_action_id','parent_key'],'idx_parent_key_id');


            $table->string('test_action_run_class')->nullable()->default(null)
                ->comment('If set, this is the class that holds the run function');

            $table->string('test_action_run_function')->nullable()->default(null)
                ->comment('If set, this is the function to call to run the action, takes one param, the model for this table');

        });

        DB::statement("ALTER TABLE test_action_data ALTER COLUMN created_at SET DEFAULT NOW();");

        DB::statement("
            CREATE TRIGGER update_modified_time BEFORE UPDATE ON test_action_data FOR EACH ROW EXECUTE PROCEDURE update_modified_column();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_action_data');
        DB::statement("DROP TYPE type_of_test_action_status;");
    }
};
