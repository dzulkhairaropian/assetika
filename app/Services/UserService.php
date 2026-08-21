<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ];

        $user = User::create($userData);

        if (!empty($data['roles'])) {
            $roles = collect($data['roles'])->map(fn($val) => is_numeric($val) ? (int)$val : $val)->toArray();
            $user->assignRole($roles);
        }

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : false,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        if (isset($data['roles'])) {
            $roles = collect($data['roles'])->map(fn($val) => is_numeric($val) ? (int)$val : $val)->toArray();
            $user->syncRoles($roles);
        }

        return $user;
    }
}
