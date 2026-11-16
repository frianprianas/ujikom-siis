<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Tampilkan daftar user (Siswa, Guru, Staff) untuk pengelolaan RFID Card.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_identitas', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('role')->orderBy('nama')->paginate(10)->withQueryString();

        return view('sarpras.users.index', compact('users', 'search', 'role'));
    }

    /**
     * Perbarui data RFID Card atau identitas peminjam.
     */
    public function updateRfid(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['nullable', 'string', 'max:50', Rule::unique('users', 'rfid_uid')->ignore($user->id)],
            'nomor_identitas' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nomor_identitas')->ignore($user->id)],
            'kelas_atau_jabatan' => ['nullable', 'string', 'max:100'],
        ], [
            'rfid_uid.unique' => 'Kartu RFID UID ini sudah digunakan oleh akun lain.',
            'nomor_identitas.unique' => 'Nomor Identitas (NIS/NIP) ini sudah terdaftar.',
        ]);

        if (! empty($validated['rfid_uid'])) {
            $validated['rfid_uid'] = strtoupper(trim($validated['rfid_uid']));
        }

        $user->update($validated);

        return back()->with('success', "Data kartu RFID untuk '{$user->nama}' berhasil diperbarui.");
    }
}
