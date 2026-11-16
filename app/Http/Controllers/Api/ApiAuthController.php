<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    /**
     * Login via REST API untuk Flutter Mobile App (Sanctum Token).
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi email dan kata sandi tidak valid.',
            ], 401);
        }

        // Batasi akses: Super Admin & Setting Data Master hanya dapat diakses melalui Web SIIS
        if ($user->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Super Admin & Setting Data Master hanya dapat diakses melalui Aplikasi Web SIIS.',
            ], 403);
        }

        $deviceName = $request->device_name ?? 'Flutter Mobile App';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'role' => $user->role,
                    'nomor_identitas' => $user->nomor_identitas,
                    'rfid_uid' => $user->rfid_uid,
                    'kelas_atau_jabatan' => $user->kelas_atau_jabatan,
                ],
            ],
        ], 200);
    }

    /**
     * Dapatkan data profil user yang sedang terotentikasi.
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
                'nomor_identitas' => $user->nomor_identitas,
                'rfid_uid' => $user->rfid_uid,
                'kelas_atau_jabatan' => $user->kelas_atau_jabatan,
            ],
        ]);
    }

    /**
     * Logout dan hapus token Sanctum mobile aktif.
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dari aplikasi mobile.',
        ]);
    }
}

