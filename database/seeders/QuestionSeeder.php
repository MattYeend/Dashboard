<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $actorId = User::query()->orderBy('id')->value('id');

        $data = [
            'course' => [
                'Laravel Fundamentals' => [
                    [
                        'question' => 'Which Artisan command creates a model together with a migration, factory, seeder, policy and resource controller?',
                        'type' => QuestionType::SingleChoice,
                        'points' => 1,
                        'explanation' => 'The -a (all) flag generates every related class.',
                    ],
                    [
                        'question' => 'Which of the following are valid Eloquent relationship methods?',
                        'type' => QuestionType::MultipleChoice,
                        'points' => 2,
                        'explanation' => 'hasMany, morphTo and belongsToMany are all built in.',
                    ],
                    [
                        'question' => 'Route model binding resolves a model instance from a route parameter automatically.',
                        'type' => QuestionType::TrueFalse,
                        'points' => 1,
                        'explanation' => 'Implicit binding matches the type-hinted model to the route segment.',
                    ],
                    [
                        'question' => 'Which Eloquent trait enables soft deleting?',
                        'type' => QuestionType::ShortText,
                        'points' => 1,
                        'explanation' => 'The SoftDeletes trait adds the deleted_at handling.',
                    ],
                ],
            ],
        ];

        foreach ($data as $alias => $parents) {
            $class = config("questions.questionable_types.{$alias}");

            foreach ($parents as $title => $questions) {
                $parent = $class::query()->where('title', $title)->first();

                if ($parent === null) {
                    continue;
                }

                foreach ($questions as $index => $question) {
                    $record = Question::query()->updateOrCreate(
                        [
                            'questionable_type' => $parent->getMorphClass(),
                            'questionable_id' => $parent->getKey(),
                            'question' => $question['question'],
                        ],
                        [
                            'type' => $question['type'],
                            'points' => $question['points'],
                            'explanation' => $question['explanation'],
                            'position' => $index + 1,
                            'is_required' => true,
                        ],
                    );

                    if ($record->wasRecentlyCreated) {
                        $record->forceFill(['created_by' => $actorId])->save();
                    }
                }
            }
        }
    }
}
