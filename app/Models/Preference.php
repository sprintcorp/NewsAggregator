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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
