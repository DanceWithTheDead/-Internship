<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'ssn' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'ssn' => $request->ssn,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = [
            'Token' => $token,
            'User' => [
                'Id' => $user->id,
                'First_Name' => $user->first_name,
                'Last_Name' => $user->last_name,
                'Phone' => $user->phone,
                'Email' => $user->email,
                'SSN' => $user->ssn,
            ]
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password,$user->password )){
            return response()->json([
                'error' => "Invalid phone or password",
            ], 401);
        }
        $token = JWTAuth::fromUser($user);

        return response()->json([
           'Token' => $token,
            'User' => [
                'Id' => $user->id,
                'First_Name' => $user->first_name,
                'Last_Name' => $user->last_name,
                'Phone' => $user->phone,
                'Email' => $user->email,
                'SSN' => $user->ssn,
            ]
        ]);
    }
}
