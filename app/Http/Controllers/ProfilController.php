<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            [
                'title' => 'Profil',
                'href' => route('profil.index')
            ],
        ];

        $user = Auth::user();
        $user = User::with(['pegawai', 'roles'])->where('id', $user->id)->firstOrFail();

        $user->role = $user->roles->first()?->name;

        return Inertia::render('auth/Profil', [
            'breadcrumbs' => $breadcrumbs,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|max:255',
            'nohp' => 'nullable|string|max:20',
            'foto_pegawai' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $user = User::with('pegawai')->findOrFail($user->id);

            // Update data User
            $user->nama = $validated['nama'];
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            // Update atau buat data Pegawai
            if (!$user->pegawai) {
                $pegawai = new \App\Models\Pegawai();
                $pegawai->id_pegawai = $this->getNewIdPegawai();
                $pegawai->nama = $validated['nama'];
                $pegawai->email = $validated['email'] ?? null;
                $pegawai->nohp = $validated['nohp'] ?? null;
                $pegawai->save();

                $user->pegawai_id = $pegawai->id_pegawai;
                $user->save();
            } else {
                $pegawai = $user->pegawai;
                $pegawai->nama = $validated['nama'];
                $pegawai->email = $validated['email'] ?? $pegawai->email;
                $pegawai->nohp = $validated['nohp'] ?? $pegawai->nohp;
                $pegawai->save();
            }

            if ($request->hasFile('foto_pegawai')) {
                $pegawai->clearMediaCollection('foto_pegawai');
                $pegawai->addMediaFromRequest('foto_pegawai')->toMediaCollection('foto_pegawai');
            }

            DB::commit();
            return back()->with('toast', [
                'type' => 'success',
                'message' => 'User berhasil diperbarui!',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('toast', [
                'type' => 'error',
                'message' => "User gagal diperbarui! : " . $e->getMessage(),
            ]);
        }
    }

    private function getNewIdPegawai()
    {
        $existingIdPegawai = Pegawai::withTrashed()
        ->pluck('id_pegawai')->map(function ($item) {
            return (int) substr($item, 1); // Ambil angka setelah 'P'
        })->toArray();

        $newIdPegawai = 1;
        while (in_array($newIdPegawai, $existingIdPegawai)) {
            $newIdPegawai++;
        }

        return 'P' . str_pad($newIdPegawai, 4, '0', STR_PAD_LEFT);
    }
}
