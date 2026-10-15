<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
/**
 * @group Publicaciones
 *
 * Crear, listar, ver y eliminar publicaciones, y dar me gusta.
 */
class PostController extends Controller
{/**
     * Listar publicaciones
     *
     * Devuelve las publicaciones paginadas, de la más reciente a la más antigua,
     * con sus imágenes, comentarios y autor.
     *
     * @queryParam search string Filtra por texto en el título. Example: viaje
     * @queryParam page integer Número de página. Example: 1
     * @queryParam per_page integer Publicaciones por página, de 1 a 50. Example: 10
     */
    public function index(Request $request)
    {
        $perPage = max(1, min($request->integer('per_page', 10), 50));

        $query = Post::with(['images', 'comments', 'user:id,name'])->latest();

        // Si viene ?search=..., filtra los posts cuyo título contiene ese texto
         if ($request->filled('search')) {
            $search = mb_strtolower($request->input('search'));
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%']);
        }

        // withQueryString(): los enlaces de página conservan ?search
        $posts = $query->paginate($perPage)->withQueryString();

        return response()->json($posts, 200);
    }
    /**
     * Ver una publicación
     *
     * @urlParam id integer required ID de la publicación. Example: 2
     */
    public function show($id)
    {
        $post = Post::with(['images', 'comments', 'user:id,name'])->findOrFail($id);

        return response()->json($post, 200);
    }
    /**
     * Crear publicación
     *
     * Crea una publicación del usuario autenticado. Se envía como form-data
     * y acepta varias imágenes en el campo images[].
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id, // el autor es el dueño del token
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('posts', 'public');
                PostImage::create([
                    'post_id'    => $post->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json($post->load(['images', 'user:id,name']), 201);
    }
     /**
     * Dar o quitar me gusta
     *
     * Si el usuario no había dado like, lo agrega; si ya lo había dado, lo quita.
     *
     * @authenticated
     * @urlParam id integer required ID de la publicación. Example: 2
     * @response 200 {"message": "Me gusta añadido", "liked": true, "likes_count": 1}
     */
    public function like(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // toggle: si el usuario no tenía like, lo agrega; si ya lo tenía, lo quita
        $result = $post->likedBy()->toggle($request->user()->id);
        $liked  = count($result['attached']) > 0;

        // likes_count se recalcula contando las filas reales de la tabla likes
        $post->update(['likes_count' => $post->likedBy()->count()]);

        return response()->json([
            'message'     => $liked ? 'Me gusta añadido' : 'Me gusta retirado',
            'liked'       => $liked,
            'likes_count' => $post->likes_count,
        ], 200);
    }
   /**
     * Eliminar publicación
     *
     * Solo el autor puede eliminarla. Borra también sus imágenes y comentarios.
     *
     * @authenticated
     * @urlParam id integer required ID de la publicación. Example: 2
     * @response 204
     * @response 403 {"message": "No puedes eliminar una publicación que no es tuya"}
     */
   public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Solo el autor puede eliminar su publicación
        if ((int) $post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No puedes eliminar una publicación que no es tuya',
            ], 403);
        }

        foreach ($post->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $post->delete();

        return response()->json(null, 204);
    }

}