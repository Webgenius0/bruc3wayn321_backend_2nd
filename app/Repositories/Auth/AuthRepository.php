<?php

namespace App\Repositories\Auth;

use App\Models\User;

class AuthRepository
{
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['email_visibility'])) {
            $data['email_visibility'] = $data['email_visibility'] === 'true';
        }

        if (isset($data['phone_visibility'])) {
            $data['phone_visibility'] = $data['phone_visibility'] === 'true';
        }

        $user->update($data);

        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function uploadAvatar(array $data): array
    {
        if (
            isset($data['avatar']) &&
            $data['avatar']
        ) {
            $file = $data['avatar'];

            $fileName = time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'uploads/avatar',
                $fileName,
                'public'
            );

            $data['avatar'] = 'storage/' . $path;
        }

        return $data;
    }
}
