<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Pegawai;
use App\Models\Produk;
use App\Models\Salary;
use App\Models\Satuan;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DataController extends Controller
{
    public function index()
    {
        return Inertia::render('data/Index');
    }

    // SALARY
    public function salary()
    {
        $breadcrumbs = [
            ['title' => 'Data', 'href' => route('data.index')],
        ];
        $salaries = Salary::with(['produk', 'user'])->get();
        $produks = Produk::all();

        return Inertia::render('data/salary/Index', [
            'salaries' => $salaries,
            'produks' => $produks,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function storeSalary(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produk,id_produk',
            'divisi'    => 'required|string|max:255',
            'salary'    => 'required|numeric|min:0',
            'user_id'   => 'nullable|exists:users,id',
            'user_nama' => 'nullable|string|max:255',
        ]);

        Salary::create($validated);

        dd($validated);

        return redirect()->route('data.salary')->with('success', 'Salary berhasil ditambahkan!');
    }

    // PRODUK
    public function produk()
    {
        $breadcrumbs = [
            ['title' => 'Data', 'href' => route('data.index')],
            ['title' => 'Daftar Produk', 'href' => route('data.produk')],
        ];
        $produks = Produk::whereNull('deleted_at')->get();
        return Inertia::render('data/produk/Index', [
            'produks' => $produks,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function showProduk($id_produk)
    {
        // Ambil produk lengkap dengan relasi kategori dan satuan
        $produk = Produk::with(['kategori', 'satuan'])
            ->where('id_produk', $id_produk)
            ->firstOrFail();

        // Ambil media cover dan galeri dengan method bawaan model
        $coverFoto = $produk->getCoverFoto();
        $galeriFoto = $produk->getGaleriFoto();

        return Inertia::render('data/produk/Show', [
            'produk' => [
                'id_produk' => $produk->id_produk,
                'nama' => $produk->nama,
                'deskripsi' => $produk->deskripsi,
                'harga' => $produk->harga,
                'stok' => $produk->stok,
                'kategori' => optional($produk->kategori)->nama,
                'satuan' => optional($produk->satuan)->nama,
                'cover_foto_url' => $coverFoto ? $coverFoto->getUrl('thumb') : null,
                'galeri_foto_urls' => $galeriFoto->map(fn($media) => $media->getUrl('thumb')),
                'created_at' => $produk->created_at->format('d M Y H:i'),
                'updated_at' => $produk->updated_at->format('d M Y H:i'),
            ],
        ]);
    }

    public function createProduk()
    {
        $kategoriProdukParent = Kategori::where('nama', 'Kategori Produk')->first();
        return Inertia::render('data/produk/Create', [
            'id_produk' => $this->getNewIdProduk(),
            'satuans' => Satuan::select('id', 'nama')->get(),
            'kategoris' => Kategori::select('id', 'nama')
                            ->where('parent_id', $kategoriProdukParent->id)
                            ->get(),
            'produks' => Produk::select('id_produk', 'nama')->get(),
        ]);
    }

    public function storeProduk(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|string|unique:produk,id_produk',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'kategori_id' => 'nullable|exists:kategori,id',
            'satuan_id' => 'nullable|exists:satuan,id',
            'parent_id' => 'nullable|exists:produk,id_produk',
            'foto_produk.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_cover' => 'nullable|integer|min:0',
        ]);

        $produk = Produk::create($validated);

        if ($request->hasFile('foto_produk')) {
            $files = $request->file('foto_produk');
            $coverIndex = $request->input('is_cover');

            foreach ($files as $index => $file) {
                $media = $produk
                    ->addMedia($file)
                    ->toMediaCollection('foto_produk');

                if ($coverIndex !== null && (int)$coverIndex === $index) {
                    $produk->setCoverFoto($media);
                }
            }
        }

        return redirect()->route('data.produk')->with('toast', [
            'type' => 'success',
            'message' => 'Produk berhasil disimpan!',
        ]);
    }

    public function editProduk($id_produk)
    {
        $produk = Produk::where('id_produk', $id_produk)->firstOrFail();
        $kategoriProdukParent = Kategori::where('nama', 'Kategori Produk')->first();
        return Inertia::render('data/produk/Edit', [
            'produk' => $produk,
            'satuans' => Satuan::select('id', 'nama')->get(),
            'kategoris' => Kategori::select('id', 'nama')
                            ->where('parent_id', $kategoriProdukParent->id)
                            ->get(),
            'produks' => Produk::select('id_produk', 'nama')
                ->where('id_produk', '!=', $id_produk)
                ->get(),
        ]);
    }

    public function updateProduk(Request $request, $id_produk)
    {
        $produk = Produk::where('id_produk', $id_produk)->firstOrFail();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'kategori_id' => 'nullable|exists:kategori,id',
            'satuan_id' => 'nullable|exists:satuan,id',
            'parent_id' => 'nullable|exists:produk,id_produk',
            'foto_produk.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_cover' => 'nullable|integer|min:0',
        ]);

        $produk->update($validated);

        if ($request->hasFile('foto_produk')) {
            $files = $request->file('foto_produk');
            $coverIndex = $request->input('is_cover');

            foreach ($files as $index => $file) {
                $media = $produk
                    ->addMedia($file)
                    ->toMediaCollection('foto_produk');

                if ($coverIndex !== null && (int)$coverIndex === $index) {
                    $produk->setCoverFoto($media);
                }
            }
        }

        return redirect()->route('data.produk')->with('toast', [
            'type' => 'success',
            'message' => 'Produk berhasil diperbarui!',
        ]);
    }

    public function destroyProduk(Produk $produk){
        if ($produk->hasMedia()) {
            $produk->clearMediaCollection();
        }
        $produk->delete();
        return redirect()->route('data.produk');
    }

    private function getNewIdProduk()
    {
        $existingIdProduk = Produk::withTrashed()
        ->pluck('id_produk')->map(function ($item) {
            return (int) substr($item, 3); // Ambil angka setelah 'PRD'
        })->toArray();

        $newIdProduk = 1;
        while (in_array($newIdProduk, $existingIdProduk)) {
            $newIdProduk++;
        }

        return 'PRD' . str_pad($newIdProduk, 5, '0', STR_PAD_LEFT);
    }

    // USER
    public function user()
    {
        $breadcrumbs = [
            ['title' => 'Data', 'href' => route('data.index')],
            ['title' => 'Daftar User', 'href' => route('data.user')],
        ];
        $users = User::whereNull('deleted_at')->get();
        return Inertia::render('data/user/Index', [
            'users' => $users,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function createUser()
    {
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('data/user/Create', [
            'roles' => $roles,
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:255',
            'password'     => 'required|string|min:6',
            'email'        => 'nullable|email|max:255',
            'nohp'         => 'nullable|string|max:20',
            'role_id'      => 'required|exists:roles,id',
            'jabatan'      => 'nullable|string|max:255',
            'foto_pegawai' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $idPegawai = $this->getNewIdPegawai();

            $pegawai = Pegawai::create([
                'id_pegawai' => $idPegawai,
                'nama'       => $request->nama,
                'email'      => $request->email,
                'nohp'       => $request->nohp,
                'jabatan'    => $request->jabatan,
            ]);

            $user = User::create([
                'nama'       => $request->nama,
                'pegawai_id' => $idPegawai,
                'password'   => Hash::make($request->password),
            ]);

            $role = Role::findById($request->role_id);
            $user->assignRole($role);

            if ($request->hasFile('foto_pegawai')) {
                $pegawai->addMediaFromRequest('foto_pegawai')
                        ->toMediaCollection('foto_pegawai');
            }

            DB::commit();

            return redirect()->route('data.user')->with('toast', [
                'type' => 'success',
                'message' => 'User berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('toast', [
                'type' => 'error',
                'message' => "User gagal disimpan! : " . $e->getMessage(),
            ]);
        }
    }

    public function editUser($id)
    {
        $user = User::with(['pegawai', 'roles'])->where('id', $id)->firstOrFail();

        $user->role_id = $user->roles->first()?->id;

        $roles = Role::select('id', 'name')->get();

        return Inertia::render('data/user/Edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|max:255',
            'nohp' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'foto_pegawai' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = User::with('pegawai')->findOrFail($id);

            // Update role
            $role = Role::findById($request->role_id);
            $user->syncRoles([$role]);

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
                $pegawai->jabatan = $validated['jabatan'] ?? null;
                $pegawai->save();

                $user->pegawai_id = $pegawai->id_pegawai;
                $user->save();
            } else {
                $pegawai = $user->pegawai;
                $pegawai->nama = $validated['nama'];
                $pegawai->email = $validated['email'] ?? $pegawai->email;
                $pegawai->nohp = $validated['nohp'] ?? $pegawai->nohp;
                $pegawai->jabatan = $validated['jabatan'] ?? $pegawai->jabatan;
                $pegawai->save();
            }

            if ($request->hasFile('foto_pegawai')) {
                $pegawai->clearMediaCollection('foto_pegawai');
                $pegawai->addMediaFromRequest('foto_pegawai')->toMediaCollection('foto_pegawai');
            }

            DB::commit();
            return redirect()->route('data.user')->with('toast', [
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

    public function destroyUser($id)
    {
        $user = User::with('pegawai')->where('id', $id)->firstOrFail();
        DB::beginTransaction();

        try {
            if ($user->pegawai) {
                if ($user->pegawai->hasMedia('foto_pegawai')) {
                    $user->pegawai->clearMediaCollection('foto_pegawai');
                }
                $user->pegawai->delete();
            }

            $user->syncRoles([]);
            $user->syncPermissions([]);
            $user->delete();
            DB::commit();
            return redirect()->route('data.user');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('data.user');
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

    // SATUAN
    public function satuan()
    {
        $satuans = Satuan::whereNull('deleted_at')->get();
        return Inertia::render('data/satuan/Index', [
            'satuans' => $satuans,
        ]);
    }

    public function storeSatuan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Satuan::create($request->all());

        return redirect()->route('data.satuan');
    }

    public function updateSatuan(Request $request, Satuan $satuan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $satuan->update($request->all());

        return redirect()->route('data.satuan');
    }

    public function destroySatuan(Satuan $satuan)
    {
        $satuan->delete();
        return redirect()->route('data.satuan');
    }

    // KATEGORI
    public function kategori()
    {
        $kategoris = Kategori::whereNull('deleted_at')->with('parent')->get();
        return Inertia::render('data/kategori/Index', [
            'kategoris' => $kategoris,
        ]);
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:kategori,id',
        ]);

        Kategori::create($request->all());

        return redirect()->route('data.kategori');
    }

    public function updateKategori(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:kategori,id',
        ]);

        $kategori->update($request->all());

        return redirect()->route('data.kategori');
    }

    public function destroyKategori(Kategori $kategori)
    {
        $kategori->delete();
        return redirect()->route('data.kategori');
    }
}
