<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'source', 'category', 'content', 'published_date', 'url',
    ];

    protected $casts = [
        'published_date' => 'datetime',
    ];
}
