<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signup(SignupRequest $request)
    {
        $validated_data = $request->validated();
        $data = $this->authService->createUserAccount($validated_data);

        return $this->created([
            'token' => $data['token'],
            'user' => $data['user'],
        ], 'User account created');

    }

    public function signin(SigninRequest $request)
    {
        $data = $this->authService->login($request->validated());
        return $this->success($data);
    }

    public function refreshToken()
    {
        try {
            $newToken = $this->authService->refreshToken();
            return $this->success(['access_token' => $newToken], "Token refreshed");
        } catch (\Throwable $th) {
            return $this->badRequest($th->getMessage());
        }
    }

}
