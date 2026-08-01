<?php

namespace App\Http\Controllers;

use App\Services\Notifications\QueryService;
use App\Services\Notifications\UpdaterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        protected readonly QueryService $queryService,
        protected readonly UpdaterService $updaterService,
    ) {}

    /**
     * Display a paginated list of the authenticated user's notifications.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $this->queryService->paginated($request->user()),
        ]);
    }

    /**
     * Return the authenticated user's unread notifications.
     */
    public function unread(Request $request): Response
    {
        return Inertia::render('Notifications/Unread', [
            'notifications' => $this->queryService->unread($request->user()),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $this->updaterService->markAsRead($request->user(), $id);

        return back();
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->updaterService->markAllAsRead($request->user());

        return back();
    }
}
