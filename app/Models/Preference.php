<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category', 'author', 'source',
    ];

    protected $casts = [
        'category' => 'array',
        'author' => 'array',
        'source' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
