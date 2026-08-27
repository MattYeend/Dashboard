<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calendar\CalendarEventsRequest;
use App\Services\Tasks\FormatterService as TaskFormatterService;
use App\Services\Tasks\QueryService as TaskQueryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __construct(
        private readonly TaskQueryService $taskQueryService,
        private readonly TaskFormatterService $taskFormatterService,
    ) {}

    public function index(): Response
    {
        $this->authorize('view calendar');

        return Inertia::render('Calendar/Index');
    }

    public function events(CalendarEventsRequest $request): JsonResponse
    {
        $this->authorize('view calendar');

        $tasks = $this->taskQueryService->forDateRange(
            $request->validated('start'),
            $request->validated('end'),
        );

        return response()->json([
            'data' => $tasks->map(fn ($task) => $this->taskFormatterService->formatForCalendar($task)),
        ]);
    }
}
