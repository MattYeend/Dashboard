<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new question.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Question
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Question {
                $questionData = $this->dataPreparation->prepareForCreation($data);

                $newQuestion = Question::create($questionData);

                $newQuestion->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_QUESTION,
                    $actor,
                    $newQuestion,
                    ['after' => $this->auditLogService->snapshot($newQuestion)],
                );

                return $newQuestion;
            });
    }
}
