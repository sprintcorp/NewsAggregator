<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StorePreferenceRequest;
use App\Http\Services\PreferenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Transformers\ArticleTransformer;

class PreferenceController extends Controller
{
    protected PreferenceService $preferenceService;

    public function __construct(PreferenceService $preferenceService)
    {
        $this->preferenceService = $preferenceService;
    }

    /**
     * Display a preferred article.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);

        $articles = $this->preferenceService->getArticlesByPreferences($user, $perPage);

        return ApiResponse::success(ArticleTransformer::transformPaginated($articles), 'Articles retrieved successfully.', 200);
    }

    /**
     * Store preferences.
     */
    public function store(StorePreferenceRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $preference = $this->preferenceService->storePreferences($user, $data);
        return ApiResponse::success($preference, 'Preferences saved successfully.', 201);
    }

    /**
     * Fetch the user preferences.
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $preference = $this->preferenceService->getPreferences($user);

        if (!$preference) {
            return ApiResponse::error($preference, 'No preferences found.', 404);
        }

        return ApiResponse::success($preference, 'Preferences retrieved successfully.', 200);
    }

}
