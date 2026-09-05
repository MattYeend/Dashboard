<?php

namespace App\Services\Reports;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;
use InvalidArgumentException;

class DataPreparationService
{
    /**
     * Inject the required services into the data preparation service.
     */
    public function __construct(
        private readonly PolicyAuthorisationService $authorisationService,
    ) {}

    /**
     * Prepare report data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function prepareForCreation(array $data): array
    {
        return $this->prepareCommon($data);
    }

    /**
     * Prepare report data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function prepareForUpdate(array $data): array
    {
        return $this->prepareCommon($data);
    }

    /**
     * Resolve the next scheduled run date/time from a frequency and a
     * time-of-day, rolling forward to the next occurrence if the
     * computed time has already passed today.
     */
    public function resolveNextRunAt(string $frequency, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        $next = CarbonImmutable::today()->setTime($hour, $minute);

        if ($next->isPast()) {
            $next = match ($frequency) {
                'daily' => $next->addDay(),
                'weekly' => $next->addWeek(),
                'monthly' => $next->addMonth(),
                default => $next->addDay(),
            };
        }

        return $next;
    }

    /**
     * Apply shared preparation logic to creation and update payloads,
     * cleaning rich text and gating scheduling on the 'schedule reports'
     * permission.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private function prepareCommon(array $data): array
    {
        if (array_key_exists('description', $data)) {
            $data['description'] = $data['description'] !== null
                ? Purifier::clean($data['description'])
                : null;
        }

        if (($data['is_scheduled'] ?? false) === true) {
            $actor = Auth::user();

            if (! $actor instanceof User || ! $this->authorisationService->canSchedule($actor)) {
                throw new InvalidArgumentException('You do not have permission to schedule reports.');
            }

            $data['next_run_at'] = $this->resolveNextRunAt(
                $data['schedule_frequency'],
                $data['schedule_time']
            );
        } elseif (array_key_exists('is_scheduled', $data)) {
            $data['schedule_frequency'] = null;
            $data['schedule_time'] = null;
            $data['next_run_at'] = null;
        }

        return $data;
    }
}
