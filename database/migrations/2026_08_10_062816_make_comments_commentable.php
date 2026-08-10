<?php

use App\Models\Post;
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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('commentable_type')->nullable()->after('id');
            $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
        });

        // Backfill: every existing comment belongs to a Post.
        DB::table('comments')->update([
            'commentable_type' => Post::class,
            'commentable_id' => DB::raw('post_id'),
        ]);

        Schema::table('comments', function (Blueprint $table) {
            $table->string('commentable_type')->nullable(false)->change();
            $table->unsignedBigInteger('commentable_id')->nullable(false)->change();

            $table->index(['commentable_type', 'commentable_id']);

            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::table('comments')
            ->where('commentable_type', Post::class)
            ->update(['post_id' => DB::raw('commentable_id')]);

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable(false)->change();

            $table->dropIndex(['commentable_type', 'commentable_id']);
            $table->dropColumn(['commentable_type', 'commentable_id']);
        });
    }
};
