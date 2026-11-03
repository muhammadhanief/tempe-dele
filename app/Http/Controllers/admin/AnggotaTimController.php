<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnggotaTimController extends Controller
{
    public function index(Request $request, string $kode_tim)
    {
        $tim = Tim::findOrFail($kode_tim);

        $query = DB::table('t_anggota_tim')
            ->join('m_pegawai', 't_anggota_tim.pegawai_id_pegawai', '=', 'm_pegawai.id_pegawai')
            ->where('tim_kode_tim', $kode_tim)
            ->select(
                'm_pegawai.id_pegawai',
                'm_pegawai.nama',
                'm_pegawai.nip',
                'm_pegawai.nip_lama',
                'm_pegawai.golongan',
                't_anggota_tim.jenis',
            );

        if ($request->filled('search')) {
            $query->where('m_pegawai.nama', 'like', '%' . $request->search . '%');
        }

        $anggota = $query->orderBy('m_pegawai.nama')->paginate(15)->withPath(route('admin.tim.anggota', $kode_tim));

        if ($request->expectsJson()) {
            return response()->json($anggota);
        }

        // Daftar anggota hanya ditampilkan lewat modal di halaman Tim (AJAX).
        // View 'admin.master.tim.anggota' tidak ada, jadi akses langsung diarahkan kembali.
        return redirect()->route('admin.tim');
    }

    public function destroy(Request $request, string $kode_tim)
    {
        $pegawai_id = $request->pegawai_id;

        DB::table('t_anggota_tim')
            ->where('tim_kode_tim', $kode_tim)
            ->where('pegawai_id_pegawai', $pegawai_id)
            ->delete();

        // Update jumlah anggota di m_tim
        $jumlah = DB::table('t_anggota_tim')->where('tim_kode_tim', $kode_tim)->count();
        Tim::where('kode_tim', $kode_tim)->update(['jumlah_anggota' => $jumlah]);

        return response()->json(['message' => 'Anggota berhasil dihapus']);
    }

    public function store(Request $request, string $kode_tim)
    {
        $request->validate([
            'pegawai_id' => 'required|integer',
        ]);

        // Cek sudah jadi anggota belum
        $exists = DB::table('t_anggota_tim')
            ->where('tim_kode_tim', $kode_tim)
            ->where('pegawai_id_pegawai', $request->pegawai_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Pegawai sudah menjadi anggota tim ini.'], 422);
        }

        DB::table('t_anggota_tim')->insert([
            'tim_kode_tim'       => $kode_tim,
            'pegawai_id_pegawai' => $request->pegawai_id,
            'jenis'              => 1,
        ]);

        // Update jumlah anggota
        $jumlah = DB::table('t_anggota_tim')->where('tim_kode_tim', $kode_tim)->count();
        Tim::where('kode_tim', $kode_tim)->update(['jumlah_anggota' => $jumlah]);

        return response()->json(['message' => 'Anggota berhasil ditambahkan']);
    }

    public function getPegawaiTersedia(Request $request, string $kode_tim)
    {
        $search = $request->search;

        $anggotaIds = DB::table('t_anggota_tim')
            ->where('tim_kode_tim', $kode_tim)
            ->pluck('pegawai_id_pegawai');

        $pegawai = DB::table('m_pegawai')
            ->whereNotIn('id_pegawai', $anggotaIds)
            ->when($search, fn($q) => $q->where('nama', 'like', "%$search%"))
            ->select('id_pegawai', 'nama', 'nip')
            ->orderBy('nama')
            ->paginate(10);

        return response()->json($pegawai);
    }
}
