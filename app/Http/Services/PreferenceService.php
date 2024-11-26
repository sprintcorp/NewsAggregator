<?php

namespace App\Http\Services;

use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Models\User;

class PreferenceService
{
    protected PreferenceRepositoryInterface $preferenceRepository;
    protected ArticleRepositoryInterface $articleRepository;

    public function __construct(
        PreferenceRepositoryInterface $preferenceRepository,
        ArticleRepositoryInterface $articleRepository
    ) {
        $this->preferenceRepository = $preferenceRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Store or update the user's preferences.
     *
     * @param User $user
     * @param array $data
     * @return mixed
     */
    public function storePreferences(User $user, array $data)
    {
        return $this->preferenceRepository->createOrUpdate($user->id, $data);
    }

    /**
     * Fetch the user's preferences.
     *
     * @param User $user
     * @return mixed
     */
    public function getPreferences(User $user)
    {
        return $this->preferenceRepository->findByUserId($user->id);
    }

    /**
     * Get articles based on user preferences.
     *
     * @param User $user
     * @param int $perPage
     * @return mixed
     */
    public function getArticlesByPreferences(User $user, int $perPage)
    {
        $preferences = $this->preferenceRepository->findByUserId($user->id);

        if (!$preferences) {
            return;
        }

        return $this->articleRepository->getArticlesByPreferences($preferences, $perPage);
    }
}
