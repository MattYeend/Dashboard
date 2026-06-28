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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'UPDATE logs SET data = NULL WHERE data IS NOT NULL AND JSON_VALID(data) = 0'
            );
        } elseif ($driver === 'pgsql') {
            DB::statement('
                DO $$
                DECLARE
                    r RECORD;
                BEGIN
                    FOR r IN SELECT id, data FROM logs WHERE data IS NOT NULL LOOP
                        BEGIN
                            PERFORM r.data::jsonb;
                        EXCEPTION WHEN others THEN
                            UPDATE logs SET data = NULL WHERE id = r.id;
                        END;
                    END LOOP;
                END;
                $$;
            ');
        }

        Schema::table('logs', function (Blueprint $table) use ($driver) {
            $table->unsignedBigInteger('action_id')
                ->change();

            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE logs ALTER COLUMN data TYPE json USING data::json');
                DB::statement('ALTER TABLE logs ALTER COLUMN data DROP NOT NULL');
            } else {
                $table->json('data')
                    ->nullable()
                    ->change();
            }

            $table->foreign('logged_in_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('related_to_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('action_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        Schema::table('logs', function (Blueprint $table) use ($driver) {
            $table->dropForeign(['logged_in_user_id']);
            $table->dropForeign(['related_to_user_id']);
            $table->dropIndex(['action_id']);

            $table->bigInteger('action_id')->change();

            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE logs ALTER COLUMN data TYPE text USING data::text');
                DB::statement('ALTER TABLE logs ALTER COLUMN data DROP NOT NULL');
            } else {
                $table->text('data')->nullable()->change();
            }
        });
    }
};
