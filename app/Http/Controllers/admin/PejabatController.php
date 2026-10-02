<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PejabatController extends Controller
{
    public function index()
    {
        $pejabat = DB::table('m_pejabat')->orderBy('tahun', 'desc')->get();
        $pejabatPerTahun = $pejabat->groupBy('tahun');
        $pejabatList = DB::table('m_pejabat')
            ->select('nama', 'jabatan', 'nip_lama', 'nip')
            ->distinct()
            ->get()
            ->groupBy('jabatan');

        return view('admin.pejabat', compact('pejabatPerTahun', 'pejabatList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun'   => 'required|integer',
            'jabatan' => 'required|string',
            'nama'    => 'required|string',
            'status'  => 'required|string',
        ]);

        if ($request->status === 'aktif' && in_array($request->jabatan, ['Kepala Bagian Umum', 'PPK', 'Kepala BPS'])) {
            DB::table('m_pejabat')->where('jabatan', $request->jabatan)->update(['status' => 'nonaktif']);
        }

        DB::table('m_pejabat')->insert([
            'nama'     => $request->nama,
            'jabatan'  => $request->jabatan,
            'nip_lama' => $request->nip_lama,
            'nip'      => $request->nip,
            'status'   => $request->status,
            'tahun'    => $request->tahun,
        ]);

        return back()->with('success', 'Pejabat berhasil ditambahkan.');
    }

    public function getData($id)
    {
        $pejabat = DB::table('m_pejabat')->where('id_pejabat', $id)->first();
        return response()->json($pejabat);
    }

    public function update(Request $request, $id)
    {
        $pejabat = DB::table('m_pejabat')->where('id_pejabat', $id)->first();
        if ($request->status === 'aktif' && $pejabat && in_array($pejabat->jabatan, ['Kepala Bagian Umum', 'PPK', 'Kepala BPS'])) {
            DB::table('m_pejabat')->where('jabatan', $pejabat->jabatan)->where('id_pejabat', '!=', $id)->update(['status' => 'nonaktif']);
        }

        DB::table('m_pejabat')->where('id_pejabat', $id)->update([
            'nama'   => $request->nama,
            'status' => $request->status,
            'nip_lama' => $request->nip_lama
        ]);
        return response()->json(['message' => 'Berhasil diupdate']);
    }

    public function destroy($id)
    {
        DB::table('m_pejabat')->where('id_pejabat', $id)->delete();
        return response()->json(['message' => 'Berhasil dihapus']);
    }
}
