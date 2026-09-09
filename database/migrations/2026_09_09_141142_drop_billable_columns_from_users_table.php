<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The billable columns being removed from the users table.
     *
     * @var list<string>
     */
    private const COLUMNS = ['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexesToDrop = collect(Schema::getIndexes('users'))
            ->filter(fn (array $index) => ! empty(array_intersect($index['columns'], self::COLUMNS)))
            ->pluck('name');

        foreach ($indexesToDrop as $indexName) {
            Schema::table('users', function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(self::COLUMNS);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });
    }
};
