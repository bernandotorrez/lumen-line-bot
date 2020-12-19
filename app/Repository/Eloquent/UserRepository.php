<?php

namespace App\Repository\Eloquent;

use App\Models\User;

class UserRepository
{
    public function getUser(string $userId)
    {
        $user = User::where('user_id', $userId)->first();
     
        if ($user) {
            return (array) $user;
        }
     
         return null;
    }

    public function saveUser(string $userId, string $displayName)
    {
        User::create([
            'user_id' => $userId,
            'display_name' => $displayName
        ]);
    }
}