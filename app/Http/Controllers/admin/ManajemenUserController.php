<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ManajemenUserController extends Controller
{
    /**
     * Memastikan user saat ini memiliki role superadmin.
    /**
     * Ambil NIP user dari session secara aman (bisa array atau object)
     */
    private function getSessionNip(): ?string
    {
        $user = session('user');
        if (is_array($user)) {
            return $user['nip'] ?? null;
        }
        if (is_object($user)) {
            return $user->nip ?? null;
        }
        return null;
    }

    /**
     * Memastikan hanya superadmin yang dapat mengakses fungsionalitas ini
     */
    private function checkSuperadmin(): void
    {
        $nip = $this->getSessionNip();
        $roleSaya = $nip ? DB::table('m_pegawai')->where('nip', $nip)->value('role') : null;

        if ($roleSaya !== 'superadmin') {
            abort(403, 'Akses Terbatas: Hanya Super Administrator yang berhak mengakses menu ini.');
        }
    }

    /**
     * Tampilan utama Manajemen User (Superadmin Only)
     */
    public function index(Request $request)
    {
        $this->checkSuperadmin();

        // 1. Pejabat Kepala Bagian Umum aktif saat ini
        $kabagAktif = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->where('status', 'aktif')
            ->orderByDesc('tahun')
            ->first();

        // Data pegawai dari pejabat kabag aktif (untuk foto/email/detail tambahan)
        $detailKabagAktif = null;
        if ($kabagAktif) {
            $detailKabagAktif = DB::table('m_pegawai')
                ->where(function ($q) use ($kabagAktif) {
                    if ($kabagAktif->nip) $q->where('nip', $kabagAktif->nip);
                    if ($kabagAktif->nip_lama) $q->orWhere('nip_lama', $kabagAktif->nip_lama);
                })
                ->first();
        }

        // Riwayat Kepala Bagian Umum sebelumnya
        $riwayatKabag = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->orderByDesc('tahun')
            ->orderByDesc('id_pejabat')
            ->get();

        // 2. Pejabat Pembuat Komitmen (PPK) aktif saat ini
        $ppkAktif = DB::table('m_pejabat')
            ->where('jabatan', 'PPK')
            ->where('status', 'aktif')
            ->orderByDesc('tahun')
            ->first();

        // Data pegawai dari PPK aktif
        $detailPpkAktif = null;
        if ($ppkAktif) {
            $detailPpkAktif = DB::table('m_pegawai')
                ->where(function ($q) use ($ppkAktif) {
                    if ($ppkAktif->nip) $q->where('nip', $ppkAktif->nip);
                    if ($ppkAktif->nip_lama) $q->orWhere('nip_lama', $ppkAktif->nip_lama);
                })
                ->first();
        }

        // Riwayat PPK sebelumnya
        $riwayatPpk = DB::table('m_pejabat')
            ->where('jabatan', 'PPK')
            ->orderByDesc('tahun')
            ->orderByDesc('id_pejabat')
            ->get();

        // 3. Daftar Admin Lembur saat ini
        $admins = DB::table('m_pegawai')
            ->where('role', 'admin')
            ->orderBy('nama')
            ->get();

        // 4. Daftar Super Administrator saat ini
        $superadmins = DB::table('m_pegawai')
            ->where('role', 'superadmin')
            ->orderBy('nama')
            ->get();

        // 5. Daftar seluruh pegawai untuk modal picker (agar cepat dipilih)
        $semuaPegawai = DB::table('m_pegawai')
            ->select('id_pegawai', 'nama', 'nip', 'nip_lama', 'role', 'satker')
            ->orderBy('nama')
            ->get();

        return view('admin.manajemen_user', compact(
            'kabagAktif',
            'detailKabagAktif',
            'riwayatKabag',
            'ppkAktif',
            'detailPpkAktif',
            'riwayatPpk',
            'admins',
            'superadmins',
            'semuaPegawai'
        ));
    }

    /**
     * Proses pergantian Kepala Bagian Umum
     */
    public function gantiKabag(Request $request)
    {
        $this->checkSuperadmin();

        $request->validate([
            'id_pegawai' => 'required|exists:m_pegawai,id_pegawai',
            'tahun'      => 'required|integer|min:2020|max:2099',
        ], [
            'id_pegawai.required' => 'Pilih pegawai yang akan ditunjuk sebagai Kepala Bagian Umum baru.',
            'tahun.required'      => 'Tahun periode jabatan wajib diisi.',
        ]);

        $pegawaiBaru = DB::table('m_pegawai')->where('id_pegawai', $request->id_pegawai)->first();
        if (!$pegawaiBaru) {
            return back()->with('error', 'Pegawai yang dipilih tidak valid.');
        }

        DB::beginTransaction();
        try {
            // Ambil data Kabag aktif lama
            $kabagLamaList = DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->where('status', 'aktif')
                ->get();

            // 1. Nonaktifkan SEMUA pejabat Kabag lama di m_pejabat (Kabag cuma boleh 1 aktif)
            DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->update(['status' => 'nonaktif']);

            // 2. Daftarkan pejabat Kabag baru di m_pejabat
            DB::table('m_pejabat')->insert([
                'nama'     => $pegawaiBaru->nama,
                'jabatan'  => 'Kepala Bagian Umum',
                'nip_lama' => $pegawaiBaru->nip_lama,
                'nip'      => $pegawaiBaru->nip,
                'status'   => 'aktif',
                'tahun'    => $request->tahun,
            ]);

            // 3. Update ketua pada Tim Kerja Bagian Umum di m_tim
            DB::table('m_tim')
                ->where(function ($q) {
                    $q->where('nama_tim', 'like', '%Bagian Umum%')
                      ->orWhere('kode_tim', 'QrBzgE3O3lEqVPjy')
                      ->orWhere('kode_tim', 'g2YxkEolkZ7qwrm6');
                })
                ->update([
                    'nama_ketua'    => $pegawaiBaru->nama,
                    'nipbaru_ketua' => $pegawaiBaru->nip,
                    'niplama_ketua' => $pegawaiBaru->nip_lama,
                ]);

            // 4. Sesuaikan role pegawai baru jika masih 'user' biasa
            if ($pegawaiBaru->role === 'user') {
                DB::table('m_pegawai')
                    ->where('id_pegawai', $pegawaiBaru->id_pegawai)
                    ->update(['role' => 'ketua_tim']);
            }

            // 5. Normalisasi role pejabat lama jika tidak memimpin tim lain
            foreach ($kabagLamaList as $kl) {
                $pegawaiLama = DB::table('m_pegawai')
                    ->where(function ($q) use ($kl) {
                        if ($kl->nip) $q->where('nip', $kl->nip);
                        if ($kl->nip_lama) $q->orWhere('nip_lama', $kl->nip_lama);
                    })
                    ->first();

                if ($pegawaiLama && $pegawaiLama->role === 'ketua_tim' && $pegawaiLama->id_pegawai !== $pegawaiBaru->id_pegawai) {
                    $masihPimpinTim = DB::table('m_tim')
                        ->where(function ($q) use ($pegawaiLama) {
                            $q->where('nipbaru_ketua', $pegawaiLama->nip)
                              ->orWhere('niplama_ketua', $pegawaiLama->nip_lama);
                        })
                        ->where('status', 'aktif')
                        ->where('nama_tim', 'not like', '%Bagian Umum%')
                        ->exists();

                    if (!$masihPimpinTim) {
                        DB::table('m_pegawai')
                            ->where('id_pegawai', $pegawaiLama->id_pegawai)
                            ->update(['role' => 'user']);
                    }
                }
            }

            DB::commit();

            Log::info("Pergantian Kepala Bagian Umum berhasil oleh Superadmin: {$pegawaiBaru->nama} ({$pegawaiBaru->nip})");

            return back()->with('success', "Kepala Bagian Umum berhasil diperbarui menjadi {$pegawaiBaru->nama}. Seluruh hak persetujuan dan data tim telah disinkronkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui Kepala Bagian Umum', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui Kepala Bagian Umum: ' . $e->getMessage());
        }
    }

    /**
     * Proses pergantian Pejabat Pembuat Komitmen (PPK)
     */
    public function gantiPpk(Request $request)
    {
        $this->checkSuperadmin();

        $request->validate([
            'id_pegawai' => 'required|exists:m_pegawai,id_pegawai',
            'tahun'      => 'required|integer|min:2020|max:2099',
        ], [
            'id_pegawai.required' => 'Pilih pegawai yang akan ditunjuk sebagai Pejabat Pembuat Komitmen (PPK) baru.',
            'tahun.required'      => 'Tahun periode jabatan wajib diisi.',
        ]);

        $pegawaiBaru = DB::table('m_pegawai')->where('id_pegawai', $request->id_pegawai)->first();
        if (!$pegawaiBaru) {
            return back()->with('error', 'Pegawai yang dipilih tidak valid.');
        }

        DB::beginTransaction();
        try {
            // 1. Nonaktifkan SEMUA pejabat PPK sebelumnya (PPK hanya boleh 1 orang yang aktif)
            DB::table('m_pejabat')
                ->where('jabatan', 'PPK')
                ->update(['status' => 'nonaktif']);

            // 2. Daftarkan pejabat PPK baru dengan status aktif
            DB::table('m_pejabat')->insert([
                'nama'     => $pegawaiBaru->nama,
                'jabatan'  => 'PPK',
                'nip_lama' => $pegawaiBaru->nip_lama,
                'nip'      => $pegawaiBaru->nip,
                'status'   => 'aktif',
                'tahun'    => $request->tahun,
            ]);

            DB::commit();

            Log::info("Pergantian PPK berhasil oleh Superadmin: {$pegawaiBaru->nama} ({$pegawaiBaru->nip})");

            return back()->with('success', "Pejabat Pembuat Komitmen (PPK) berhasil diperbarui menjadi {$pegawaiBaru->nama}. Seluruh dokumen kedinasan (SPKL dll) akan otomatis menggunakan data pejabat ini.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui PPK', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui PPK: ' . $e->getMessage());
        }
    }

    /**
     * Penambahan Admin Baru
     */
    public function tambahAdmin(Request $request)
    {
        $this->checkSuperadmin();

        $request->validate([
            'id_pegawai' => 'required|exists:m_pegawai,id_pegawai',
        ], [
            'id_pegawai.required' => 'Pilih pegawai yang akan ditambahkan sebagai Admin Lembur.',
        ]);

        $pegawai = DB::table('m_pegawai')->where('id_pegawai', $request->id_pegawai)->first();

        if ($pegawai->role === 'superadmin') {
            return back()->with('error', "{$pegawai->nama} sudah memiliki hak akses Super Administrator.");
        }

        if ($pegawai->role === 'admin') {
            return back()->with('error', "{$pegawai->nama} sudah berstatus Admin.");
        }

        DB::table('m_pegawai')
            ->where('id_pegawai', $pegawai->id_pegawai)
            ->update(['role' => 'admin']);

        Log::info("Penambahan Admin oleh Superadmin: {$pegawai->nama} ({$pegawai->nip})");

        return back()->with('success', "Pegawai {$pegawai->nama} berhasil diangkat sebagai Admin Lembur.");
    }

    /**
     * Penghapusan / Pencabutan Akses Admin
     */
    public function hapusAdmin(Request $request, $id)
    {
        $this->checkSuperadmin();

        $pegawai = DB::table('m_pegawai')->where('id_pegawai', $id)->firstOrFail();

        if ($pegawai->role !== 'admin') {
            return back()->with('error', "Pegawai {$pegawai->nama} bukan berstatus Admin Lembur.");
        }

        $sessionNip = $this->getSessionNip();
        if ($pegawai->nip === $sessionNip) {
            return back()->with('error', "Anda tidak dapat mencabut hak akses akun Anda sendiri.");
        }

        // Kembalikan role ke 'user'
        DB::table('m_pegawai')
            ->where('id_pegawai', $id)
            ->update(['role' => 'user']);

        Log::info("Pencabutan Admin oleh Superadmin: {$pegawai->nama} ({$pegawai->nip})");

        return back()->with('success', "Akses Admin untuk {$pegawai->nama} berhasil dicabut dan dikembalikan ke peran Pengguna biasa.");
    }

    /**
     * Penambahan Superadmin Baru (Permanen, disertai konfirmasi ganda)
     */
    public function tambahSuperadmin(Request $request)
    {
        $this->checkSuperadmin();

        $request->validate([
            'id_pegawai' => 'required|exists:m_pegawai,id_pegawai',
            'konfirmasi' => 'required|accepted',
        ], [
            'id_pegawai.required' => 'Pilih pegawai yang akan diangkat sebagai Super Administrator.',
            'konfirmasi.accepted' => 'Anda wajib mencentang persetujuan bahwa penambahan Super Administrator bersifat permanen dan tidak dapat dicabut.',
        ]);

        $pegawai = DB::table('m_pegawai')->where('id_pegawai', $request->id_pegawai)->first();

        if ($pegawai->role === 'superadmin') {
            return back()->with('error', "{$pegawai->nama} sudah berstatus Super Administrator.");
        }

        DB::table('m_pegawai')
            ->where('id_pegawai', $pegawai->id_pegawai)
            ->update(['role' => 'superadmin']);

        Log::warning("PROMOSI SUPERADMIN: {$pegawai->nama} ({$pegawai->nip}) telah diangkat menjadi Superadmin oleh " . ($this->getSessionNip() ?? 'unknown'));

        return back()->with('success', "Pegawai {$pegawai->nama} berhasil diangkat menjadi Super Administrator.");
    }
}
