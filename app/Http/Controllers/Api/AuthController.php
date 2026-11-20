<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Înregistrează un utilizator nou și emite un token Sanctum.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = $this->userService->registerUser($validated);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Contul a fost creat cu succes.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * Autentifică utilizatorul și emite un token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = $this->userService->authenticateUser($validated['email'], $validated['password']);
        if (!$user) {
            return response()->json(['message' => 'Credențiale invalide.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Autentificare reușită.',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    /**
     * Revocă token-ul curent al utilizatorului autentificat.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Deconectare reușită.',
        ], 200);
    }

    /**
     * Returnează profilul utilizatorului autentificat.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user(), 200);
    }
}
