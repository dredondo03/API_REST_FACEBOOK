<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;
/** @group Comentarios */
class CommentController extends Controller
{ /**
     * Listar comentarios de una publicación
     *
     * @urlParam id integer required ID de la publicación. Example: 2
     */
    public function index($postId)
    {
        $post = Post::findOrFail($postId);
        return response()->json($post->comments()->latest()->get(), 200);
    }
   /**
     * Comentar una publicación
     *
     * El autor del comentario es el usuario autenticado. Si el post es de otra
     * persona, se le envía un correo de aviso.
     *
     * @authenticated
     * @urlParam id integer required ID de la publicación. Example: 2
     */
   public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $post = Post::findOrFail($postId);

        $comment = $post->comments()->create([
            'author'  => $request->user()->name,
            'content' => $request->content,
        ]);

        // Avisar por correo al autor del post, si tiene autor y no es quien comenta
        if ($post->user && (int) $post->user_id !== $request->user()->id) {
            $post->user->notify(new NewCommentNotification($comment));
        }

        return response()->json($comment, 201);
    }
}