<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // HasApiTokens: permite crear y revisar tokens de Sanctum
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    // Nunca se envían en las respuestas JSON
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    // Posts a los que este usuario les dio like
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'likes')->withTimestamps();
    }
}