<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Create a new API token for the user.
     *
     * @param string $name
     * @return string
     */
    public function createApiToken(string $name = 'API Token'): string
    {
        return $this->createToken($name)->plainTextToken;
    }

    /**
     * Check if the provided password matches the user's password.
     *
     * @param string $password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Reset the user's password.
     *
     * @param string $newPassword
     * @return void
     */
    public function resetPassword(string $newPassword): void
    {
        $this->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Generate a random password reset token.
     *
     * @return string
     */
    public static function generatePasswordResetToken(): string
    {
        return Str::random(60);
    }

    public function preferences()
    {
        return $this->hasOne(Preference::class);
    }
}
