<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;

return [
    'questionable_types' => [
        'course' => Course::class,
        'lesson' => Lesson::class,
    ],
];
