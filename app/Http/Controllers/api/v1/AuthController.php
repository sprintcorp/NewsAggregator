<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Services\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        return ApiResponse::success($user, 'User registered successfully.', 201);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return ApiResponse::success($user, 200);
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authService->login($request->email, $request->password);

        if (!$token) {
            return ApiResponse::error('Invalid credentials.', 401);
        }

        return ApiResponse::success(['token' => $token], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return ApiResponse::success([], 'Logged out successfully.');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->validated());
        return ApiResponse::success([], 'Password reset successfully.');
    }
}
