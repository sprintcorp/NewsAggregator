<?php

namespace App\Http\Repositories\Eloquent;

use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Models\Preference;

class PreferenceRepository implements PreferenceRepositoryInterface
{
    /**
     * Create or update the user's preferences.
     *
     * @param int $userId
     * @param array $data
     * @return Preference
     */
    public function createOrUpdate(int $userId, array $data): Preference
    {
        $preference = Preference::firstOrNew(['user_id' => $userId]);
        $preference->fill($data);
        $preference->save();

        return $preference;
    }

    /**
     * Find preferences by user ID.
     *
     * @param int $userId
     * @return Preference|null
     */
    public function findByUserId(int $userId): ?Preference
    {
        return Preference::where('user_id', $userId)->first();
    }
}
