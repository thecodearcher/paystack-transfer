<?php

namespace App\Services;

use App\Exceptions\ApiError;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function createUserAccount(array $userData)
    {
        $userData['password'] = Hash::make($userData["password"]);
        $user = User::create($userData);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            throw new ApiError("Invalid credentials entered!", $credentials);
        }

        $data['user'] = auth()->user();
        $data['token'] = $token;
        return $data;
    }

    public function refreshToken()
    {
        return JWTAuth::refresh(JWTAuth::getToken());
    }
}
