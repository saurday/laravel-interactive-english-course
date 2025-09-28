<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/users?role=dosen|mahasiswa|admin
    public function index(Request $request)
    {
        $q = User::query();

        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }

        return response()->json(
            $q->orderBy('name')->get(),
            200
        );
    }

    // GET /api/users/{id}
    public function show($id)
    {
        $user = User::findOrFail($id);
        // Kembalikan objek user langsung (tanpa wrapper)
        return response()->json($user, 200);
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        $auth = $request->user();              // user yang login
        $user = User::findOrFail($id);

        // Hanya pemilik akun atau admin yang boleh update
        if (!$auth || ($auth->id !== (int)$user->id && $auth->role !== 'admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Aturan dasar
        $rules = [
            'name'  => ['sometimes','string','max:255'],
            'email' => ['sometimes','email', Rule::unique('users','email')->ignore($user->id)],
        ];

        // Role hanya boleh diubah admin (opsional)
        if ($auth->role === 'admin' && $request->has('role')) {
            $rules['role'] = ['in:admin,dosen,mahasiswa'];
        }

        // Jika ingin ganti password -> wajib current_password & konfirmasi
        if ($request->filled('password') || $request->filled('current_password')) {
            $rules['current_password'] = ['required'];
            $rules['password'] = ['required','string','min:6','confirmed'];
        }

        $validated = $request->validate($rules);

        // Update field dasar
        if (array_key_exists('name', $validated))  $user->name  = $validated['name'];
        if (array_key_exists('email', $validated)) $user->email = $validated['email'];
        if (array_key_exists('role', $validated))  $user->role  = $validated['role']; // hanya admin

        // Ganti password (jika diminta)
        if (array_key_exists('password', $validated)) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Kembalikan objek user terbaru
        return response()->json($user, 200);
    }

    // DELETE /api/users/{id}
    public function destroy(Request $request, $id)
    {
        $auth = $request->user();
        if (!$auth || $auth->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
