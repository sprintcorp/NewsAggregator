<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\FilterArticlesRequest;
use App\Http\Transformers\ArticleTransformer;
use App\Http\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

class ArticleController extends Controller
{
    protected ArticleService $articleService;
    private ArticleTransformer $articleTransformer;
    public function __construct(ArticleService $articleService, ArticleTransformer $articleTransformer)
    {
        $this->articleService = $articleService;
        $this->articleTransformer = $articleTransformer;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     operationId="getArticles",
     *     tags={"Articles"},
     *     summary="Fetch a paginated list of articles",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of articles per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Articles retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(FilterArticlesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', 10);
        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        return ApiResponse::success($this->articleTransformer->transformPaginated($articles));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     operationId="getArticle",
     *     tags={"Articles"},
     *     summary="Fetch a single article by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Article ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article not found.")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $article = $this->articleService->getArticleById($id);

        return ApiResponse::success($this->articleTransformer->transformForDetail($article));
    }
}
