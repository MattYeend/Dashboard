<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Testing\TestResponse;

/**
 * @property Organisation $testOrganisation
 */
trait CreatesQuizzes
{
    /**
     * Create a published course in the test organisation.
     */
    protected function courseFor(User $user, array $attributes = []): Course
    {
        return Course::factory()->create(array_merge([
            'organisation_id' => $this->testOrganisation->id,
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    /**
     * Create a lesson for the given course.
     */
    protected function lessonFor(Course $course, array $attributes = []): Lesson
    {
        return Lesson::factory()->create(array_merge([
            'organisation_id' => $course->organisation_id,
            'course_id' => $course->id,
        ], $attributes));
    }

    /**
     * Create a question with answers in the given organisation.
     */
    protected function questionIn(int $organisationId, bool $withCorrect = true, array $attributes = []): Question
    {
        $question = Question::factory()->create(array_merge([
            'organisation_id' => $organisationId,
            'points' => 1,
        ], $attributes));

        Answer::factory()->create([
            'organisation_id' => $organisationId,
            'question_id' => $question->id,
            'is_correct' => $withCorrect,
            'position' => 1,
        ]);

        Answer::factory()->create([
            'organisation_id' => $organisationId,
            'question_id' => $question->id,
            'is_correct' => false,
            'position' => 2,
        ]);

        return $question;
    }

    /**
     * Attach a question to a quiz, setting the pivot organisation.
     */
    protected function attachQuestion(Quiz $quiz, Question $question, int $position, ?int $points = null): void
    {
        $quiz->questions()->attach($question->id, [
            'organisation_id' => $quiz->organisation_id,
            'position' => $position,
            'points' => $points,
        ]);
    }

    /**
     * Create a quiz in the test organisation with attached questions.
     */
    protected function quizFor(User $user, array $attributes = [], int $questionCount = 0): Quiz
    {
        $course = $attributes['course'] ?? $this->courseFor($user);
        unset($attributes['course']);

        $quiz = Quiz::factory()->create(array_merge([
            'organisation_id' => $this->testOrganisation->id,
            'course_id' => $course->id,
            'published_at' => now()->subDay(),
        ], $attributes));

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = $this->questionIn($this->testOrganisation->id, true, [
                'explanation' => 'Secret explanation '.$i,
            ]);

            $this->attachQuestion($quiz, $question, $i);
        }

        return $quiz->fresh();
    }

    /**
     * Build a valid request payload.
     *
     * @param  array<int, array{question_id: int, points: int|null}>  $questions
     */
    protected function quizPayload(Course $course, array $questions, array $overrides = []): array
    {
        return array_merge([
            'course_id' => $course->id,
            'lesson_id' => null,
            'title' => 'Laravel Basics Quiz',
            'description' => 'Checks the fundamentals.',
            'pass_mark_percentage' => 70,
            'time_limit_minutes' => 30,
            'max_attempts' => 3,
            'shuffle_questions' => false,
            'show_correct_answers' => true,
            'published_at' => null,
            'questions' => $questions,
        ], $overrides);
    }

    /**
     * Create an organisation other than the test organisation.
     */
    protected function foreignOrganisation(): Organisation
    {
        return Organisation::factory()->create();
    }

    /**
     * Create an organisation id other than the test organisation's.
     */
    protected function otherOrganisationId(): int
    {
        return $this->foreignOrganisation()->id;
    }

    /**
     * Run a callback with another organisation as the current tenant, then
     * restore the test organisation. BelongsToOrganisation refuses to create
     * records for an organisation that is not the current tenant.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function runInTenant(Organisation $organisation, callable $callback): mixed
    {
        $result = $organisation->execute($callback);

        $this->testOrganisation->makeCurrent();
        setPermissionsTeamId($this->testOrganisation->id);

        return $result;
    }

    /**
     * Create a published course in another organisation.
     */
    protected function foreignCourse(): Course
    {
        $organisation = $this->foreignOrganisation();

        return $this->runInTenant($organisation, fn (): Course => Course::factory()->create([
            'organisation_id' => $organisation->id,
            'published_at' => now()->subDay(),
        ]));
    }

    /**
     * Create a lesson (inside a course) in another organisation.
     */
    protected function foreignLesson(): Lesson
    {
        $organisation = $this->foreignOrganisation();

        return $this->runInTenant($organisation, function () use ($organisation): Lesson {
            $course = Course::factory()->create([
                'organisation_id' => $organisation->id,
                'published_at' => now()->subDay(),
            ]);

            return Lesson::factory()->create([
                'organisation_id' => $organisation->id,
                'course_id' => $course->id,
            ]);
        });
    }

    /**
     * Create a published quiz (inside a course) in another organisation.
     */
    protected function foreignQuiz(array $attributes = []): Quiz
    {
        $organisation = $this->foreignOrganisation();

        return $this->runInTenant($organisation, function () use ($organisation, $attributes): Quiz {
            $course = Course::factory()->create([
                'organisation_id' => $organisation->id,
                'published_at' => now()->subDay(),
            ]);

            return Quiz::factory()->create(array_merge([
                'organisation_id' => $organisation->id,
                'course_id' => $course->id,
                'published_at' => now()->subDay(),
            ], $attributes));
        });
    }

    /**
     * Create a question with answers in another organisation.
     */
    protected function foreignQuestion(bool $withCorrect = true): Question
    {
        $organisation = $this->foreignOrganisation();

        return $this->runInTenant(
            $organisation,
            fn (): Question => $this->questionIn($organisation->id, $withCorrect),
        );
    }

    /**
     * Decode the Inertia page props from a standard HTML response.
     *
     * @return array<string, mixed>
     */
    protected function inertiaProps(TestResponse $response): array
    {
        return $response->viewData('page')['props'];
    }
}
