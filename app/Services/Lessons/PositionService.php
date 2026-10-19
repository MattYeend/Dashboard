<?php
 
declare(strict_types=1);
 
namespace App\Services\Lessons;
 
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;

class PositionService
{
    /**
     * Get the next free position in a course. Call inside a transaction.
     */
    public function next(int $courseId): int
    {
        $max = Lesson::withTrashed()
            ->where('course_id', $courseId)
            ->lockForUpdate()
            ->max('position');
 
        return ((int) $max) + 1;
    }
 
    /**
     * Rewrite positions to follow the given id order.
     *
     * @param array<int, int> $orderedIds
     */
    public function reorder(
        int $courseId, 
        array $orderedIds, 
        int $actorId
    ): void {
        DB::transaction(function () use ($courseId, $orderedIds, $actorId): void {
            foreach ($orderedIds as $index => $id) {
                Lesson::query()
                    ->where('course_id', $courseId)
                    ->whereKey($id)
                    ->update(['position' => $index + 1, 'updated_by' => $actorId]);
            }
        });
    }
}
