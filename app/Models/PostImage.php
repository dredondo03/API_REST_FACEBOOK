<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'image_path'];

    // Este atributo se agrega solo a cada imagen cuando se convierte a JSON
    protected $appends = ['image_url'];

    // Accessor: crea el atributo image_url a partir de image_path
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}