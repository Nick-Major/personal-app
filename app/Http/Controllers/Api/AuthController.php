<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        // Удаляем существующие токены пользователя (опционально)
        // $user->tokens()->delete();

        // Создаем новый токен
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $this->getUserData($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        // Возвращаем пользователя с полными данными для ЛК
        return response()->json($this->getUserData($request->user()));
    }

    /**
     * Форматируем данные пользователя для фронтенда
     */
    private function getUserData(User $user)
    {
        $user->load('roles.permissions', 'specialties');
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'patronymic' => $user->patronymic,
            'email' => $user->email,
            'phone' => $user->phone,
            'full_name' => $user->full_name,
            'roles' => $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')
                ];
            }),
            'specialties' => $user->specialties,
            'executor_role' => $user->getExecutorRole(),
            'executor_role_display' => $user->getExecutorRoleDisplay(),
            'is_always_brigadier' => $user->is_always_brigadier,
        ];
    }
}
