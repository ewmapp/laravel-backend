<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->all(['email', 'password']);

        $token = auth('api')->attempt($credentials);

        if ($token) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'description' => 'Login success',
                    'data' => [
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth('api')->factory()->getTTL() * 60
                    ]
                ]
            ], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => ['description' => '403 Forbidden', 'data' => []]], 403);
        }
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['status' => 'success', 'message' => ['description' => 'Logout success', 'data' => []]], 200);
    }

    public function refresh()
    {
        $token = auth('api')->refresh();
        return response()->json([
            'status' => 'success',
            'message' => [
                'description' => 'Refresh token success',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ]
        ], 200);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }
}
