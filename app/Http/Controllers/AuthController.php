<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
/** @group Autenticación */
class AuthController extends Controller
{/** Registrar usuario. Devuelve el usuario y su token. */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }
    /** Iniciar sesión. Devuelve un token nuevo. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }
    /** 
     * Ver mi perfil. 
     * 
     * @authenticated
     */
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }
    /** Cerrar sesión. Invalida el token actual.
     * 
     *  @authenticated
     */
    public function logout(Request $request)
    {
        // Borra solo el token con el que se hizo esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada'], 200);
    }
}