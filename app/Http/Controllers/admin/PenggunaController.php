<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('nip', 'like', '%' . $request->search . '%');
        }

        $pegawai = $query->orderBy('nama')->paginate(10)->withPath(route('admin.pengguna'));

        if ($request->expectsJson()) {
            return response()->json($pegawai);
        }

        return view('admin.pengguna', compact('pegawai'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'nip'      => 'nullable|string|max:30',
            'nip_lama' => 'nullable|string|max:30',
            'email'    => 'required|email|unique:m_pegawai,email',
            'golongan' => 'nullable|string|max:10',
            'role'     => 'required|in:user,admin,ketua_tim',
        ]);

        Pegawai::create($validated);
        return response()->json(['message' => 'Pegawai berhasil ditambahkan']);
    }

    public function update(Request $request, int $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $nip = session('user')['nip'];
        $roleSaya = DB::table('m_pegawai')->where('nip', $nip)->value('role');

        $validated = $request->validate([
            'nama'     => 'sometimes|string|max:255',
            'nip'      => 'nullable|string|max:30',
            'nip_lama' => 'nullable|string|max:30',
            'email'    => 'sometimes|email|unique:m_pegawai,email,' . $id . ',id_pegawai',
            'golongan' => 'nullable|string|max:10',
            'role'     => 'sometimes|in:user,admin,ketua_tim',
        ]);

        if ($roleSaya !== 'superadmin' || $pegawai->role === 'superadmin') {
            unset($validated['role']);
        }

        $pegawai->update($validated);
        return response()->json(['message' => 'Pegawai berhasil diupdate']);
    }

    public function updatePassword(Request $request, int $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $pegawai->update(['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function destroy(int $id)
    {
        Pegawai::findOrFail($id)->delete();
        return response()->json(['message' => 'Pegawai berhasil dihapus']);
    }

    public function getAll()
    {
        $pegawai = Pegawai::orderBy('nama')->get(['id_pegawai', 'nama', 'nip']);
        return response()->json($pegawai);
    }

    public function sinkronPegawai()
    {
        try {
            $tahun = date('Y');

            $getTimKerja = function ($tahun) {
                return Http::withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.kipapp.token'),
                    'Origin'        => 'https://jateng.web.bps.go.id',
                ])->post('https://kipapp.bps.go.id/api/v3/timkerja', [
                    'tahun' => (string) $tahun,
                    'type'  => '1',
                ]);
            };

            // Ambil data timkerja tahun ini, fallback tahun lalu
            $timData = null;

            $responseTim = $getTimKerja($tahun);
            if ($responseTim->successful() && !empty($responseTim->json()['data'])) {
                $timData = $responseTim->json()['data'];
            } else {
                $responseTim = $getTimKerja($tahun - 1);
                if ($responseTim->successful() && !empty($responseTim->json()['data'])) {
                    $timData = $responseTim->json()['data'];
                }
            }

            if (empty($timData)) {
                Log::error('Gagal ambil data timkerja dari kipapp');
                return response()->json(['message' => 'Gagal mengambil data dari kipapp'], 500);
            }

            $pegawaiMap = [];

            // Pass 1: masukkan semua anggota dulu sebagai user
            foreach ($timData as $tim) {
                foreach ($tim['anggota_tim'] ?? [] as $anggota) {
                    $nipAnggota     = $anggota['nipbaru'] ?? null;
                    $nipLamaAnggota = $anggota['niplama'] ?? null;
                    $key = $nipAnggota ?: $nipLamaAnggota;
                    if (!$key) continue;

                    if (!isset($pegawaiMap[$key])) {
                        $pegawaiMap[$key] = [
                            'nip'      => $nipAnggota,
                            'nip_lama' => $nipLamaAnggota,
                            'nama'     => $anggota['nama'] ?? null,
                            'role'     => 'user',
                        ];
                    }
                }
            }

            // Pass 2: overwrite ketua — ketua selalu menang
            foreach ($timData as $tim) {
                $nipKetua     = $tim['nipbaru_ketua'] ?? null;
                $nipLamaKetua = $tim['niplama_ketua'] ?? null;
                $key = $nipKetua ?: $nipLamaKetua;
                if (!$key) continue;

                // Selalu overwrite jadi ketua_tim
                $pegawaiMap[$key] = [
                    'nip'      => $nipKetua,
                    'nip_lama' => $nipLamaKetua,
                    'nama'     => $tim['nama_ketua'] ?? null,
                    'role'     => 'ketua_tim',
                ];
            }

            // Insert / update ke m_pegawai
            $inserted = 0;
            $updated  = 0;

            foreach ($pegawaiMap as $data) {
                // Cari existing by nip dulu, fallback by nip_lama
                $existing = null;

                if ($data['nip']) {
                    $existing = DB::table('m_pegawai')->where('nip', $data['nip'])->first();
                }

                if (!$existing && $data['nip_lama']) {
                    $existing = DB::table('m_pegawai')->where('nip_lama', $data['nip_lama'])->first();
                }

                if ($existing) {
                    $updateData = [
                        'nip_lama' => $data['nip_lama'],
                    ];

                    // Update nip kalau sebelumnya kosong
                    if (!$existing->nip && $data['nip']) {
                        $updateData['nip'] = $data['nip'];
                    }

                    // Jangan overwrite role admin/superadmin
                    if (!in_array($existing->role, ['admin', 'superadmin'])) {
                        $updateData['role'] = $data['role'];
                    }

                    DB::table('m_pegawai')
                        ->where('id_pegawai', $existing->id_pegawai)
                        ->update($updateData);
                    $updated++;
                } else {
                    DB::table('m_pegawai')->insert([
                        'nip'       => $data['nip'],
                        'nip_lama'  => $data['nip_lama'],
                        'nama'      => $data['nama'],
                        'role'      => $data['role'],
                        'satker'    => null,
                        'kd_satker' => '3300',
                    ]);
                    $inserted++;
                }
            }

            Log::info('Sinkron pegawai selesai', compact('inserted', 'updated'));

            return response()->json([
                'message'  => 'Sinkronisasi berhasil',
                'inserted' => $inserted,
                'updated'  => $updated,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception sinkron pegawai', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
