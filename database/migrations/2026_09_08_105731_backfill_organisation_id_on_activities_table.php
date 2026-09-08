<?php

use App\Models\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The tenant-scoped tables backfilled onto the Default organisation.
     *
     * @var list<string>
     */
    protected array $tables = [
        'activities',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $organisation = Organisation::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'slug' => 'default']
        );

        foreach ($this->tables as $table) {
            DB::table($table)
                ->whereNull('organisation_id')
                ->update(['organisation_id' => $organisation->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::table($table)->update(['organisation_id' => null]);
        }
    }
};
