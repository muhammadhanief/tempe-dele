<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\admin\PresensiController;
use App\Http\Controllers\admin\TimController;
use App\Http\Controllers\admin\AnggotaTimController;
use App\Http\Controllers\admin\PenggunaController;
use App\Http\Controllers\admin\RateController;
use App\Http\Controllers\admin\DokumenController;
use App\Http\Controllers\admin\PresensiUploadController;

use App\Http\Controllers\LemburController;
use App\Http\Controllers\admin\DokumenGenerateController;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\LemburController as AdminLemburController;
use App\Http\Controllers\pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\admin\PengajuanController as AdminPengajuanController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\pimpinan\PengajuanController as PimpinanPengajuanController;
use App\Http\Controllers\ketuatim\KabagUmumPengajuanController;

// ─── Public ───────────────────────────────────────────────
Route::get('/', fn() => view('welcome'));

Route::get('/debug-session', function () {
    dd(session('user'));
})->middleware('checksession');

// ─── Dev Test Login (Memudahkan Pengujian Peran Tanpa Password SSO) ───
Route::get('/dev-login/{nip}', function ($nip) {
    $pegawai = \DB::table('m_pegawai')->where('nip', $nip)->orWhere('id_pegawai', $nip)->orWhere('nip_lama', $nip)->first();
    if (!$pegawai) {
        return response("Pegawai dengan NIP/ID {$nip} tidak ditemukan di database m_pegawai.", 404);
    }
    session()->put('user', [
        'nip'       => $pegawai->nip,
        'nip_lama'  => $pegawai->nip_lama,
        'nama'      => $pegawai->nama,
        'email'     => $pegawai->email,
        'role'      => $pegawai->role,
        'satker'    => $pegawai->satker,
        'kd_satker' => $pegawai->kd_satker,
    ]);
    session()->put('logged_in', true);
    session()->put('id_pegawai', $pegawai->id_pegawai);
    session()->put('role', $pegawai->role);

    if ($pegawai->role === 'superadmin' || $pegawai->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($pegawai->role === 'ketua_tim') {
        return redirect()->route('ketua-tim.dashboard');
    } elseif ($pegawai->role === 'pimpinan') {
        return redirect()->route('pimpinan.dashboard');
    } else {
        return redirect()->route('pegawai.dashboard');
    }
})->name('dev.login');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
Route::post('/logout', function () {session()->flush(); return redirect()->route('login'); })->name('logout');


Route::get('/lembur', [LemburController::class, 'index'])->name('lembur')->middleware('checksession');
Route::post('/lembur', [LemburController::class, 'store'])->name('lembur.store')->middleware('checksession');
Route::post('/lembur/{id}/approve', [LemburController::class, 'approve'])->name('lembur.approve')->middleware('checksession');
Route::get('/lembur/tim', [LemburController::class, 'timPegawai'])->name('lembur.tim')->middleware('checksession');

// ─── Authenticated (semua role) ───────────────────────────
Route::middleware('checksession')->group(function () {

    Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');
    Route::get('/akumulasi',     fn() => view('akumulasi'))->name('akumulasi');
    Route::get('/rekapitulasi', [\App\Http\Controllers\pegawai\RekapitulasiController::class, 'index'])->name('rekapitulasi')->middleware('checksession');
    Route::get('/profile', function () { $nip = session('user')['nip']; $user = \DB::table('m_pegawai')->where('nip', $nip)->first(); return view('profile', compact('user'));})->name('profile')->middleware('checksession');

    // ─── Pegawai ──────────────────────────────────────────
    Route::prefix('pegawai')->name('pegawai.')->group(function () {
        Route::post('/lembur/{id_transaksi}/dokumentasi', [LemburController::class, 'storeDoc'])->name('lembur.storeDoc');
        Route::delete('/lembur/{id_transaksi}/dokumentasi', [LemburController::class, 'destroyDoc'])->name('lembur.destroyDoc');
        Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');
});

    // ─── Ketua Tim ────────────────────────────────────────
    Route::prefix('ketua-tim')->name('ketua-tim.')->middleware('checksession')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\ketuatim\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/pending', [\App\Http\Controllers\ketuatim\DashboardController::class, 'getPending'])->name('dashboard.pending');
        Route::post('/transaksi/{id}/approve', [\App\Http\Controllers\ketuatim\DashboardController::class, 'approve'])->name('transaksi.approve');
        Route::get('/pengajuan', [\App\Http\Controllers\ketuatim\PengajuanController::class, 'index'])->name('pengajuan');
        Route::post('/pengajuan/{id}/approve', [\App\Http\Controllers\ketuatim\PengajuanController::class, 'approve'])->name('pengajuan.approve');
        Route::get('/pengajuan/{id}/presensi', [\App\Http\Controllers\ketuatim\PengajuanController::class, 'presensi'])->name('pengajuan.presensi');
        Route::get('/pengajuan/anggota', [\App\Http\Controllers\ketuatim\PengajuanController::class, 'anggotaTim'])->name('pengajuan.anggota');
        Route::get('/lembur', [\App\Http\Controllers\LemburController::class, 'index'])->name('lembur');
        Route::post('/lembur', [\App\Http\Controllers\LemburController::class, 'store'])->name('lembur.store');
    });

    // ─── Kabag Umum (Persetujuan Tim Lain) ───────────────
    Route::prefix('kabag-umum')->name('kabag-umum.')->middleware('checksession')->group(function () {
        Route::get('/pengajuan', [KabagUmumPengajuanController::class, 'index'])->name('pengajuan');
        Route::post('/pengajuan/{id}/approve', [KabagUmumPengajuanController::class, 'approve'])->name('pengajuan.approve');
        Route::get('/pengajuan/{id}/presensi', [KabagUmumPengajuanController::class, 'presensi'])->name('pengajuan.presensi');
        Route::get('/pengajuan/pegawai', [KabagUmumPengajuanController::class, 'semuaPegawai'])->name('pengajuan.pegawai');
    });

    // ─── Admin ────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/transaksi/pending', [DashboardController::class, 'getPending'])->name('dashboard.pending');
        Route::post('/transaksi/{id}/approve', [AdminLemburController::class, 'quickApprove'])->name('dashboard.approve');
        Route::get('/pegawai/all', [PresensiController::class, 'getAllPegawai'])->name('pegawai.all')->middleware('checksession');

        //Superadmin
        // Route::get('/master-pegawai', [\App\Http\Controllers\admin\MasterPegawaiController::class, 'index'])->name('master_pegawai');

        //Rekapitulasi
        Route::get('/spkl', [\App\Http\Controllers\admin\RekapitulasiController::class, 'index'])->name('spkl');
        Route::get('/rekapitulasi', [\App\Http\Controllers\admin\RekapitulasiController::class, 'index'])->name('rekapitulasi');
        Route::get('/rekapitulasi', [\App\Http\Controllers\pegawai\RekapitulasiController::class, 'index'])->name('rekapitulasi')->middleware('checksession');
        Route::get('/dokumen/download/{type}', [\App\Http\Controllers\admin\DokumenGenerateController::class, 'download'])->name('dokumen.download')->middleware('checksession');
        Route::get('/rekapitulasi/export', [\App\Http\Controllers\admin\RekapitulasiController::class, 'downloadExcel'])->name('rekapitulasi.export');
        Route::get('admin/rekapitulasi', [\App\Http\Controllers\admin\RekapitulasiController::class, 'index']);
        Route::get('/rekapitulasi/export-status', [\App\Http\Controllers\admin\RekapitulasiController::class, 'exportStatus'])->name('rekapitulasi.export.status');

        //Laporan
        Route::get('/laporan', [\App\Http\Controllers\admin\LaporanController::class, 'index'])->name('laporan');
        Route::get('/dokumen/download-excel/laporan/{jenis}', [DokumenGenerateController::class, 'downloadExcel'])->name('dokumen.download.excel')->middleware('checksession');

        //Akumulasi
        Route::get('/akumulasi', [\App\Http\Controllers\admin\AkumulasiController::class, 'index'])->name('akumulasi');
        Route::get('/akumulasi/download', [\App\Http\Controllers\admin\AkumulasiController::class, 'download'])->name('akumulasi.download')->middleware('checksession');

        //Pengajuan Lembur
        Route::get('/lembur', [AdminLemburController::class, 'index'])->name('lembur');
        Route::post('/lembur', [AdminLemburController::class, 'store'])->name('lembur.store');
        Route::get('/lembur/tim', [AdminLemburController::class, 'timPegawai'])->name('lembur.tim');
        Route::get('/admin/lembur/pegawai', [AdminLemburController::class, 'allPegawai'])->name('admin.lembur.pegawai');
        Route::get('/admin/lembur/export', [AdminLemburController::class, 'exportExcel'])->name('admin.lembur.export');
        Route::get('/lembur/export', [AdminLemburController::class, 'exportExcel'])->name('lembur.export');
        Route::post('/lembur/{id_transaksi}/dokumentasi', [AdminLemburController::class, 'storeDoc'])->name('lembur.storeDoc');
        Route::delete('/lembur/{id_transaksi}/dokumentasi', [AdminLemburController::class, 'destroyDoc'])->name('lembur.destroyDoc');
        Route::post('/lembur/{id}/uraian', [AdminLemburController::class, 'updateUraian'])->name('lembur.updateUraian');

        // approve lembur
        Route::get('/pengajuan', [AdminPengajuanController::class, 'index'])->name('pengajuan');
        Route::post('/pengajuan/{id}/approve', [AdminLemburController::class, 'approve'])->name('pengajuan.approve');
        Route::get('/pengajuan/{id}/presensi', [AdminPengajuanController::class, 'presensi'])->name('pengajuan.presensi');
        Route::get('/pengajuan/pegawai', [AdminPengajuanController::class, 'semuaPegawai'])->name('pengajuan.pegawai');

        //Daftar Hadir
        Route::get('/daftar-hadir', [\App\Http\Controllers\admin\DaftarHadirController::class, 'index'])->name('daftar_hadir');
        Route::get('/daftar-hadir/download', [\App\Http\Controllers\admin\DaftarHadirController::class, 'download'])->name('daftar_hadir.download')->middleware('checksession');

        // Presensi
        Route::get('/presensi',           [PresensiUploadController::class, 'index'])->name('presensi');
        Route::post('/presensi/upload',   [PresensiUploadController::class, 'upload'])->name('presensi.upload');
        Route::get('/presensi/riwayat',   [PresensiUploadController::class, 'riwayat'])->name('presensi.riwayat');
        Route::get('/presensi/kalender',  [PresensiUploadController::class, 'getKalender'])->name('presensi.kalender');
        Route::get('/presensi/tim',       [PresensiController::class, 'getTim'])->name('presensi.tim');
        Route::get('/presensi/pegawai',   [PresensiController::class, 'getPegawai'])->name('presensi.pegawai');
        Route::get('/riwayat_presensi',   [PresensiUploadController::class, 'riwayat'])->name('riwayat_presensi');
        Route::delete('hari-libur/{id}', [PresensiUploadController::class, 'hariLiburDestroy'])->name('hari-libur.destroy');
        Route::post('hari-libur', [PresensiUploadController::class, 'hariLiburStore'])->name('hari-libur.store');

        // Tim
        Route::get('/tim', [\App\Http\Controllers\admin\TimController::class, 'index'])->name('tim');
        Route::get('/tim',                [TimController::class, 'index'])->name('tim');
        Route::post('/tim',               [TimController::class, 'store'])->name('tim.store');
        Route::put('/tim/{kode_tim}',     [TimController::class, 'update'])->name('tim.update');
        Route::delete('/tim/{kode_tim}',  [TimController::class, 'destroy'])->name('tim.destroy');
        Route::post('/tim/sync',          [TimController::class, 'sync'])->name('tim.sync');

        // Anggota Tim
        Route::get('/tim/{kode_tim}/anggota',           [AnggotaTimController::class, 'index'])->name('tim.anggota');
        Route::post('/tim/{kode_tim}/anggota',          [AnggotaTimController::class, 'store'])->name('tim.anggota.store');
        Route::delete('/tim/{kode_tim}/anggota',        [AnggotaTimController::class, 'destroy'])->name('tim.anggota.destroy');
        Route::get('/tim/{kode_tim}/pegawai-tersedia',  [AnggotaTimController::class, 'getPegawaiTersedia'])->name('tim.pegawai.tersedia');

        // Pengguna
        Route::get('/pengguna',                    [PenggunaController::class, 'index'])->name('pengguna');
        Route::get('/pengguna/all',                [PenggunaController::class, 'getAll'])->name('pengguna.all');
        Route::post('/pengguna',                   [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{id}',               [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::put('/pengguna/{id}/password',      [PenggunaController::class, 'updatePassword'])->name('pengguna.password');
        Route::delete('/pengguna/{id}',            [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
        Route::post('/pengguna/sinkron',           [PenggunaController::class, 'sinkronPegawai'])->name('pengguna.sinkron');
        Route::get('/pengguna',                    [PenggunaController::class, 'index'])->name('pengguna');
        Route::get('/pengguna/all',                [PenggunaController::class, 'getAll'])->name('pengguna.all');
        Route::post('/pengguna/sinkron',           [PenggunaController::class, 'sinkronPegawai'])->name('pengguna.sinkron'); 
        Route::post('/pengguna',                   [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{id}',               [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::put('/pengguna/{id}/password',      [PenggunaController::class, 'updatePassword'])->name('pengguna.password');
        Route::delete('/pengguna/{id}',            [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

        // Tarif
        Route::get('/tarif',               [RateController::class, 'index'])->name('tarif');
        Route::put('/tarif/{id_rate}',     [RateController::class, 'update'])->name('tarif.update');

        // Dokumen
        Route::get('/dokumen', [\App\Http\Controllers\admin\DokumenViewController::class, 'index'])->name('dokumen');
        Route::get('/dokumen/view/{id}', [\App\Http\Controllers\admin\DokumenViewController::class, 'view'])->name('dokumen.view')->middleware('checksession');
        Route::delete('/dokumen/hapus', [\App\Http\Controllers\admin\DokumenViewController::class, 'hapus'])->name('dokumen.hapus')->middleware('checksession');
        Route::delete('/dokumen/hapus/{id}', [\App\Http\Controllers\admin\DokumenViewController::class, 'hapusSatu'])->name('dokumen.hapus-satu')->middleware('checksession');
        Route::get('/dokumen/generate/spkl', [\App\Http\Controllers\admin\DokumenGenerateController::class, 'spkl'])->name('dokumen.generate.spkl')->middleware('checksession');
        Route::get('/dokumen/generate/laporan/{jenis}', [\App\Http\Controllers\admin\DokumenGenerateController::class, 'laporan'])->name('dokumen.generate.laporan')->middleware('checksession');

        //Pejabat
        Route::get('/pejabat',           [\App\Http\Controllers\admin\PejabatController::class, 'index'])->name('pejabat');
        Route::post('/pejabat',          [\App\Http\Controllers\admin\PejabatController::class, 'store'])->name('pejabat.store');
        Route::get('/pejabat/{id}/data', [\App\Http\Controllers\admin\PejabatController::class, 'getData'])->name('pejabat.data');
        Route::put('/pejabat/{id}',      [\App\Http\Controllers\admin\PejabatController::class, 'update'])->name('pejabat.update');
        Route::delete('/pejabat/{id}', [\App\Http\Controllers\admin\PejabatController::class, 'destroy'])->name('pejabat.destroy');
    });

        Route::prefix('pimpinan')->name('pimpinan.')->middleware('checksession')->group(function () {
        Route::get('/dashboard', [PimpinanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pengajuan', [PimpinanPengajuanController::class, 'index'])->name('pengajuan');
        Route::get('/pengajuan/pegawai', [PimpinanPengajuanController::class, 'semuaPegawai'])->name('pengajuan.pegawai');
        Route::get('/pengajuan/{id}/presensi', [PimpinanPengajuanController::class, 'presensi'])->name('pengajuan.presensi');
    });
});