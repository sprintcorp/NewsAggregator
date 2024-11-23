<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id','title', 'source', 'category', 'body', 'published_date',
    ];

    protected $casts = [
        'published_date' => 'datetime',
    ];
}
