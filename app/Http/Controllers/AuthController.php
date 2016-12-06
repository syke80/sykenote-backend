<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        if (User::isRegistered($email)) {
            $responseData = [
                'msg' => 'User already exists.',
            ];

            return response()->json($responseData, 400);
        }

        $userData = [
            'email' => $email,
            'password' => bcrypt($password)
        ];

        $user = new User($userData);

        if ($user->save()) {
            $responseData = [
                'msg' => 'User has been created.',
                'user' => $user
            ];

            return response()->json($responseData, 201);
        }

        $responseData = [
            'msg' => 'An error occurred.',
        ];

        return response()->json($responseData, 404);
    }

    public function signIn(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if ($token = JWTAuth::attempt($credentials)) {
                $responseData = [
                    'msg' => 'Successfully logged in.',
                    'token' => $token
                ];
                return response()->json($responseData, 200);
            } else {
                $responseData = [
                    'msg' => 'Invalid credentials.'
                ];
                return response()->json($responseData, 401);
            }
        } catch(JWTException $exception) {
            $responseData = [
                'msg' => 'Some error occurred.'
            ];
            return response()->json($responseData, 500);
        }
    }

    public function getUserInfo(Request $request)
    {
        $user = JWTAuth::authenticate();

        $responseData = [
            'email' => $user->email
        ];

        return response()->json($responseData, 200);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        JWTAuth::invalidate($request->input('token'));
    }
}
