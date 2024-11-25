<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\FilterArticlesRequest;
use App\Http\Transformers\ArticleTransformer;
use App\Http\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(FilterArticlesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', 10);
        $articles = $this->articleService->getFilteredArticles($filters, $perPage);

        return response()->json(ArticleTransformer::transformPaginated($articles));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $article = $this->articleService->getArticleById($id);

        return response()->json([
            'data' => ArticleTransformer::transformForDetail($article),
        ]);
    }
}
