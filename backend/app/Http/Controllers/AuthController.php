<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {
    /**
     * Display login view.
     */
    public function login() {
        return view('auth/login');
    }

    /**
     * Display register view.
     */
    public function register() {
        return view('auth/register');
    }

    /**
     * Authenticate user.
     */
    public function authenticate(Request $request): JsonResponse {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('device_name')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(): RedirectResponse {
        Auth::logout();
        return redirect()->intended('');
    }
}
