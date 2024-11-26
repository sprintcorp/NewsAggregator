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

    private ArticleTransformer $articleTransformer;

    public function __construct(PreferenceService $preferenceService, ArticleTransformer $articleTransformer)
    {
        $this->preferenceService = $preferenceService;
        $this->articleTransformer = $articleTransformer;
    }


     /**
     * @OA\Get(
     *     path="/api/v1/personalized-feed",
     *     operationId="getPersonalized feed",
     *     tags={"Preferences"},
     *     summary="Fetch user preferences",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Preferences retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Preferences retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No preferences found.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);

        $articles = $this->preferenceService->getArticlesByPreferences($user, $perPage);
        if(!$articles){
            return ApiResponse::error(null, 'No personalized feed.', 404);
        }
        return ApiResponse::success($this->articleTransformer->transformPaginated($articles), 'Articles retrieved successfully.', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/preferences",
     *     operationId="storePreferences",
     *     tags={"Preferences"},
     *     summary="Save user preferences",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="category",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Technology", "Australia news", "Real Estate", "US news"}
     *             ),
     *             @OA\Property(
     *                 property="author",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Caitlin Cassidy", "Graeme Wearden", "By Angela Serratore"}
     *             ),
     *             @OA\Property(
     *                 property="source",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"The Guardian", "The New York Times"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Preferences saved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Preferences saved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input data.")
     *         )
     *     )
     * )
     */
    public function store(StorePreferenceRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $preference = $this->preferenceService->storePreferences($user, $data);
        return ApiResponse::success($preference, 'Preferences saved successfully.', 201);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/preferences",
     *     operationId="getPreferences",
     *     tags={"Preferences"},
     *     summary="Fetch user preferences",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Preferences retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Preferences retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No preferences found.")
     *         )
     *     )
     * )
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
