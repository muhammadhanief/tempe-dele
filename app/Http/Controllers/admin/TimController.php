<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimController extends Controller
{
    public function index(Request $request)
    {
        $query = Tim::query();

        if ($request->filled('search')) {
            $query->where('nama_tim', 'like', '%' . $request->search . '%');
        }

        $tim = $query->orderBy('nama_tim')->paginate(10)->withPath(route('admin.tim'));

        if ($request->expectsJson()) {
            return response()->json($tim);
        }

        return view('admin.tim', compact('tim'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_tim'            => 'required|string|max:50|unique:m_tim,kode_tim',
            'nama_tim'            => 'required|string|max:255',
            'nama_ketua'          => 'nullable|string|max:255',
            'niplama_ketua'       => 'nullable|string|max:30',
            'nipbaru_ketua'       => 'nullable|string|max:30',
            'is_penugasan_khusus' => 'nullable|integer',
            'status'              => 'nullable|string|max:45',
            'tanggal_non_aktif'   => 'nullable|date',
        ]);

        Tim::create($validated);

        return response()->json(['message' => 'Tim berhasil ditambahkan']);
    }

    public function update(Request $request, string $kode_tim)
    {
        $tim = Tim::findOrFail($kode_tim);

        $validated = $request->validate([
            'nama_tim'            => 'sometimes|string|max:255',
            'nama_ketua'          => 'nullable|string|max:255',
            'niplama_ketua'       => 'nullable|string|max:30',
            'nipbaru_ketua'       => 'nullable|string|max:30',
            'is_penugasan_khusus' => 'nullable|integer',
            'status'              => 'nullable|string|max:45',
            'tanggal_non_aktif'   => 'nullable|date',
        ]);

        $tim->update($validated);

        return response()->json(['message' => 'Tim berhasil diupdate']);
    }

    public function destroy(string $kode_tim)
    {
        $tim = Tim::findOrFail($kode_tim);
        $tim->delete();

        return response()->json(['message' => 'Tim berhasil dihapus']);
    }

    public function sync(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . config('services.kipapp.token'),
                'Origin'        => 'https://jateng.web.bps.go.id',
            ])->post('https://kipapp.bps.go.id/api/v3/timkerja', [
                'tahun' => (string) $tahun,
                'type'  => '1',
            ]);

            if (!$response->successful()) {
                Log::warning('Gagal sync timkerja', [
                    'tahun'  => $tahun,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'message' => "Gagal mengambil data tim tahun $tahun dari API.",
                ], 502);
            }

            $body    = $response->json();
            $timData = $body['data'] ?? [];

            if (empty($timData)) {
                return response()->json([
                    'message' => "Tidak ada data tim tahun $tahun dari API. Data tim existing tidak diubah.",
                ], 200);
            }

            // ---------- KUMPULKAN KODE_TIM YANG ADA DI API TAHUN INI ----------
            $kodeTimApi = collect($timData)
                ->pluck('kode_tim')
                ->filter()
                ->values()
                ->toArray();

            $jumlahTimUpsert     = 0;
            $jumlahAnggotaTambah = 0;
            $jumlahAnggotaHapus  = 0;
            $jumlahTimNonaktif   = 0;

            DB::beginTransaction();

            $jumlahTimNonaktif = DB::table('m_tim')
                ->where('status', 'aktif')
                ->where('sumber', 'api')
                ->whereNotIn('kode_tim', $kodeTimApi)
                ->update(['status' => 'nonaktif']);

            foreach ($timData as $tim) {
                $kodeTim = $tim['kode_tim'] ?? null;
                if (!$kodeTim) continue;

                // ---------- UPSERT TIM ----------
                DB::table('m_tim')->upsert(
                    [
                        [
                            'kode_tim'            => $kodeTim,
                            'nama_tim'            => $tim['nama_tim'] ?? null,
                            'nama_ketua'          => $tim['nama_ketua'] ?? null,
                            'niplama_ketua'       => $tim['niplama_ketua'] ?? null,
                            'nipbaru_ketua'       => $tim['nipbaru_ketua'] ?? null,
                            'is_penugasan_khusus' => $tim['is_penugasan_khusus'] ?? 0,
                            'status'              => $this->mapStatus($tim['status'] ?? null),
                            'sumber'              => 'api',
                        ],
                    ],
                    ['kode_tim'],
                    [
                        'nama_tim',
                        'nama_ketua',
                        'niplama_ketua',
                        'nipbaru_ketua',
                        'is_penugasan_khusus',
                        'status',
                        'sumber',
                    ]
                );

                $jumlahTimUpsert++;

                // ---------- SYNC ANGGOTA (diff-based, jenis=1 only) ----------
                $anggotaApiList = $tim['anggota_tim'] ?? [];
                $idPegawaiApi   = [];

                foreach ($anggotaApiList as $anggota) {
                    $nipBaru = $anggota['nipbaru'] ?? null;
                    $nipLama = $anggota['niplama'] ?? null;

                    $pegawai = DB::table('m_pegawai')
                        ->where('nip', $nipBaru)
                        ->orWhere('nip_lama', $nipLama)
                        ->first();

                    if (!$pegawai) continue; // pegawai belum ada di m_pegawai, skip

                    $idPegawaiApi[] = $pegawai->id_pegawai;

                    $sudahAda = DB::table('t_anggota_tim')
                        ->where('tim_kode_tim', $kodeTim)
                        ->where('pegawai_id_pegawai', $pegawai->id_pegawai)
                        ->first();

                    if (!$sudahAda) {
                        DB::table('t_anggota_tim')->insert([
                            'tim_kode_tim'       => $kodeTim,
                            'pegawai_id_pegawai' => $pegawai->id_pegawai,
                            'nip'                => $nipBaru,
                            'nip_lama'           => $nipLama,
                            'jenis'              => 1,
                        ]);
                        $jumlahAnggotaTambah++;
                    } else {
                        DB::table('t_anggota_tim')
                            ->where('tim_kode_tim', $kodeTim)
                            ->where('pegawai_id_pegawai', $pegawai->id_pegawai)
                            ->update([
                                'nip'      => $nipBaru,
                                'nip_lama' => $nipLama,
                                'jenis'    => 1,
                            ]);
                    }
                }

                // Hapus anggota jenis=1 yang tidak ada lagi di response API tahun ini
                $deleted = DB::table('t_anggota_tim')
                    ->where('tim_kode_tim', $kodeTim)
                    ->where('jenis', 1)
                    ->when(!empty($idPegawaiApi), function ($q) use ($idPegawaiApi) {
                        $q->whereNotIn('pegawai_id_pegawai', $idPegawaiApi);
                    })
                    ->delete();

                $jumlahAnggotaHapus += $deleted;

                // ---------- UPDATE JUMLAH ANGGOTA ----------
                $jumlah = DB::table('t_anggota_tim')
                    ->where('tim_kode_tim', $kodeTim)
                    ->count();

                DB::table('m_tim')
                    ->where('kode_tim', $kodeTim)
                    ->update(['jumlah_anggota' => $jumlah]);
            }

            DB::commit();

            return response()->json([
                'message' => "Sinkronisasi tim tahun $tahun berhasil.",
                'detail'  => [
                    'tim_diupsert'      => $jumlahTimUpsert,
                    'tim_dinonaktifkan' => $jumlahTimNonaktif,
                    'anggota_ditambah'  => $jumlahAnggotaTambah,
                    'anggota_dihapus'   => $jumlahAnggotaHapus,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exception saat sync tim', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function mapStatus(?string $statusApi): string
    {
        return strtolower(trim($statusApi ?? '')) === 'aktif' ? 'aktif' : 'nonaktif';
    }
}