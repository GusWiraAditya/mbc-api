<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * Menampilkan daftar semua alamat milik pengguna yang sedang login.
     * Diurutkan dari yang terbaru, dengan alamat utama selalu di atas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()
            ->orderBy('is_primary', 'desc') // Alamat utama selalu di paling atas
            ->latest()
            ->get();

        return response()->json($addresses);
    }

    /**
     * Menyimpan alamat baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
   public function store(Request $request)
    {
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'province_name' => 'required|string|max:255',
            'city_id' => 'required|integer',
            'city_name' => 'required|string|max:255',
            'district_id' => 'required|integer',
            'district_name' => 'required|string|max:255',
            'subdistrict_id' => 'required|integer',
            'subdistrict_name' => 'required|string|max:255',
            'address_detail' => 'required|string|max:1000',
            'postal_code' => 'nullable|string|max:10',
            'is_primary' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        
        // 1. Deklarasikan variabel di luar untuk menampung hasil

        // 2. Lakukan transaksi untuk menjaga konsistensi data
        DB::transaction(function () use ($user, $request, $validatedData, &$newAddress) {
            // Jika is_primary=true, maka set semua alamat lain menjadi false terlebih dahulu.
            if ($request->boolean('is_primary')) {
                $user->addresses()->update(['is_primary' => false]);
            }

            // Buat alamat baru dan simpan ke dalam variabel $newAddress
            $newAddress = $user->addresses()->create($validatedData);

            // Jika ini adalah alamat pertama yang dibuat, otomatis jadikan sebagai alamat utama.
            if ($user->addresses()->count() === 1) {
                $newAddress->update(['is_primary' => true]);
            }
        });

        // 3. Kembalikan respons JSON di luar blok transaksi, menggunakan variabel yang sudah diisi.
        // Kita gunakan ->fresh() untuk memastikan kita mendapatkan data terbaru setelah update.
        return response()->json($newAddress->fresh(), 201); // 201 = Created
    }
    /**
     * Menampilkan detail satu alamat spesifik.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Address $address)
    {
        // Pastikan pengguna hanya bisa melihat alamatnya sendiri
        if ($address->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($address);
    }

    /**
     * Memperbarui alamat yang sudah ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Address $address)
    {
        // Pastikan pengguna hanya bisa mengupdate alamatnya sendiri
        if ($address->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => ['required', 'string', 'min:10', 'max:15', 'regex:/^((\+62)|0|62)8[1-9][0-9]{6,11}$/'],
            'province_id' => 'required|integer',
            'province_name' => 'required|string|max:255',
            'city_id' => 'required|integer',
            'city_name' => 'required|string|max:255',
            'district_id' => 'required|integer',
            'district_name' => 'required|string|max:255',
            'subdistrict_id' => 'required|integer',
            'subdistrict_name' => 'required|string|max:255',
            'address_detail' => 'required|string|max:1000',
            'postal_code' => 'nullable|string|max:10',
            'is_primary' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $address, $validatedData) {
            if ($request->boolean('is_primary')) {
                // Set semua alamat lain milik user ini menjadi tidak utama
                $address->user->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
            }
    
            $address->update($validatedData);
        });

        return response()->json($address->fresh());
    }

    /**
     * Menghapus sebuah alamat.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Address $address)
    {
        // Pastikan pengguna hanya bisa menghapus alamatnya sendiri
        if ($address->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($address) {
            $wasPrimary = $address->is_primary;
            $address->delete();

            // Jika alamat yang dihapus adalah alamat utama,
            // dan masih ada alamat lain, jadikan alamat terbaru sebagai utama.
            if ($wasPrimary && $address->user->addresses()->count() > 0) {
                $address->user->addresses()->latest()->first()->update(['is_primary' => true]);
            }
        });

        return response()->json(null, 204); // 204 = No Content
    }

    /**
     * Mengatur sebuah alamat sebagai alamat utama.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPrimary(Address $address)
    {
        // Pastikan pengguna hanya bisa mengatur alamatnya sendiri
        if ($address->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Gunakan transaksi untuk memastikan operasi berjalan dengan aman
        DB::transaction(function () use ($address) {
            // 1. Set semua alamat lain milik user ini menjadi TIDAK utama
            $address->user->addresses()->update(['is_primary' => false]);
            
            // 2. Set alamat yang dipilih ini menjadi utama
            $address->update(['is_primary' => true]);
        });

        // Kembalikan semua alamat agar UI bisa di-refresh
        return $this->index();
    }
}
