<?php

namespace App\Http\Controllers;

use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly SearchService $searchService
    ) {}

    /**
     * Search across users, companies, contacts, orders and deals.
     *
     * Validates the search term before passing it to the SearchService.
     *
     * Returns empty result sets when the search term is shorter than
     * two characters to avoid unnecessary database queries.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'users' => [],
                'companies' => [],
                'contacts' => [],
                'orders' => [],
                'deals' => [],
            ]);
        }

        return response()->json(
            $this->searchService->search($request->user(), $term)
        );
    }
}
