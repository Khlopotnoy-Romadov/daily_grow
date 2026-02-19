<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return response()->json([
                'user' => [
                    'id' => Auth::user()->id,
                    'login' => Auth::user()->login,
                    'name' => Auth::user()->name,
                ]
            ]);
        }

        return response()->json(['message' => 'Неверный логин или пароль'], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'id' => $request->user()->id,
            'login' => $request->user()->login,
            'name' => $request->user()->name,
        ]);
    }
}