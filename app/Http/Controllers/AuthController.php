<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nisn_nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nisn_nip', $request->nisn_nip)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'error_message' => ['NISN/NIP atau password salah.'],
            ]);
        }

        if (! $user->aktif) {
            return response()->json([
                'error_message' => 'Akun tidak aktif. Hubungi admin/waka.',
            ], 403);
        }

        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => $user->load(['siswa.kelas', 'guru']),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $request->user()->load(['siswa.kelas', 'guru'])
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:4',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password user berhasil direset.']);
    }
}
