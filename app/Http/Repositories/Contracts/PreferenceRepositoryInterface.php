<?php

namespace App\Http\Repositories\Contracts;

interface PreferenceRepositoryInterface
{
    public function createOrUpdate(int $userId, array $data);
    public function findByUserId(int $userId);
}
