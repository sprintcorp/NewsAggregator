<?php

namespace App\Http\Services;

use App\Models\User;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->createUser($data);
    }

    public function login(string $email, string $password): ?string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user->createToken('API Token')->plainTextToken;
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    public function resetPassword(array $data): void
    {
        $user = $this->userRepository->findByEmail($data['email']);
        $otp = Str::random(6);

        $user->update([
            'otp' => $otp,
            'otp_expiration' => Carbon::now()->addMinutes(15),
        ]);

        $user->notify(new SendOtpNotification($user->otp));
    }

    public function updatePassword(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user) {
            return $this->response(false, 'User not found.');
        }

        if ($user->otp !== $data['otp']) {
            return $this->response(false, 'Invalid OTP.');
        }

        if (Carbon::now()->gt($user->otp_expiration)) {
            return $this->response(false, 'OTP has expired.');
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'otp' => null,
            'otp_expiration' => null,
        ]);

        return $this->response(true, 'Password updated successfully.');
    }

    private function response(bool $success, string $message): array
    {
        return [
            'status' => $success,
            'message' => $message,
        ];
    }
}
