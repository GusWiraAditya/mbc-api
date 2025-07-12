<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi semua data yang masuk
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => ['nullable', 'string', 'min:10', 'max:15', 'regex:/^((\+62)|0|62)8[1-9][0-9]{6,11}$/'],
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            // Validasi untuk gambar profil
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10048', // Maks 10MB
        ]);

        // 2. Logika untuk menangani unggahan file gambar profil
        if ($request->hasFile('profile_picture')) {
            // Hapus gambar profil lama jika ada
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Simpan gambar baru dan dapatkan path-nya
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            
            // Tambahkan path gambar baru ke data yang akan di-update
            $validatedData['profile_picture'] = $path;
        }

        // 3. Update data pengguna di database
        $user->update($validatedData);

        // 4. Kembalikan data pengguna yang sudah diperbarui
        // Menggunakan ->fresh() untuk memastikan kita mendapatkan data terbaru
        // setelah update, termasuk Accessor 'profile_picture_url'.
        return response()->json($user->fresh());
    }
}
