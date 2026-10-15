<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'content', 'likes_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    // Usuarios que le dieron like a este post (a través de la tabla likes)
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }
}