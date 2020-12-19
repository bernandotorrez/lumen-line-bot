<?php

namespace App\Repository\Eloquent;

use App\Models\LineUser;

class LineUserRepository
{
    public function getUser(string $userId)
    {
        $user = LineUser::where('user_id', $userId)->first();
     
        if ($user) {
            return $user;
        }
     
         return null;
    }

    public function saveUser(string $userId, string $displayName)
    {
        return LineUser::create([
            'user_id' => $userId,
            'display_name' => $displayName,
        ]);
    }
}