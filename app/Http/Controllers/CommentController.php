<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($postId)
    {
        $post = Post::findOrFail($postId);
        return response()->json($post->comments()->latest()->get(), 200);
    }

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