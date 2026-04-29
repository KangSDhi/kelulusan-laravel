<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Graduation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required',
            'password' => 'required'
        ], [
            'email.required' => 'Email Kosong!',
            'password.required' => 'Password Kosong!'
        ]);

        if($validator->fails()){
            return response()->json([
                'http_code' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'http_code' => 401,
                    'errors' => 'Pengguna Tidak Ditemukan!'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'http_code' => 500,
                'errors' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'http_code' => 200,
            'message' => 'Berhasil Login',
            'data' => [
                'user' => Auth::user(),
                'token' => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]
        ]);
    }

    public function loginGraduation(Request $request) {
        $credentials = $request->only('nisn', 'tanggal_lahir');

        $validator = Validator::make($credentials, [
            'nisn' => 'required',
            'tanggal_lahir' => 'required|date_format:Y-m-d',
        ], [
            'nisn.required' => 'NISN Kosong!',
            'tanggal_lahir.required' => 'Tanggal Lahir Kosong!',
            'tanggal_lahir.date_format' => 'Format Tanggal Lahir Tidak Valid!'
        ]);

        if($validator->fails()){
            return response()->json([
                'http_code' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $graduation = Graduation::where('nisn', $request->nisn)->first();

//        dd($graduation->birth_date, $request->tanggal_lahir);

        if (!$graduation || $graduation->birth_date != $request->tanggal_lahir) {
            return response()->json([
                'http_code' => 401,
                'message' => 'NISN atau Tanggal Lahir Tidak Valid!'
            ], 401);
        }

        try {
            // fromUser bisa menerima Model apapun selama implements JWTSubject
            if (!$token = JWTAuth::fromUser($graduation)) {
                return response()->json(['error' => 'Could not create token'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'http_code' => 200,
            'message' => 'Login Berhasil',
            'data' => $graduation,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function checkUser()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'http_code' => 404,
                    'errors' => 'Pengguna Tidak Ditemukan!'
                ], 404);
            }
            return response()->json([
                'http_code' => 200,
                'message' => 'Berhasil Menemukan Pengguna!',
                'data' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'http_code' => 500,
                'errors' => 'Failed to fetch user profile'
            ], 500);
        }
    }
}
