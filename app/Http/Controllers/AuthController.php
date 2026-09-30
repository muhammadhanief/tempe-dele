<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    private function getSsoBpsToken(): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withBasicAuth(
                    config('services.bps_sso.client_id'),
                    config('services.bps_sso.client_secret')
                )
                ->asForm()
                ->post('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::warning('Gagal ambil token SSO BPS saat login', ['body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::warning('Exception ambil token SSO BPS', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function getGolonganFromSso(string $nip): ?string
    {
        try {
            $token = $this->getSsoBpsToken();
            if (!$token) return null;

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ])
                ->get('https://sso.bps.go.id/auth/realms/pegawai-bps/api-pegawai/nipbaru/' . $nip);

            if ($response->successful()) {
                $data = $response->json();
                $pegawai = $data[0] ?? null;
                if ($pegawai) {
                    return $pegawai['attributes']['attribute-golongan'][0] ?? null;
                }
            }

            Log::warning('Gagal ambil golongan dari SSO BPS', [
                'nip'  => $nip,
                'body' => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::warning('Exception ambil golongan SSO BPS', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function login(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key'    => env('KIPAPP_API_KEY'),
                'Origin'       => 'https://jateng.web.bps.go.id',
            ])->post('https://jateng.web.bps.go.id/apiconnect/login', [
                'username' => $request->username,
                'password' => $request->password,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['data'])) {
                $data = $body['data'];

                // Validasi satker
                if (($data['kd_satker'] ?? null) !== '3300') {
                    return back()->withErrors([
                        'login' => 'Akses ditolak. Hanya pegawai BPS Jawa Tengah yang dapat login.'
                    ]);
                }

                $tahunSekarang = date('Y');
                $nipUser       = $data['nip'];
                $nipLamaUser   = $data['nip_lama'] ?? null;

                // Ambil golongan dari SSO BPS (tidak blocking — kalau gagal lanjut saja)
                $golongan = $this->getGolonganFromSso($nipUser);

                Log::info('Golongan dari SSO BPS', [
                    'nip'      => $nipUser,
                    'golongan' => $golongan,
                ]);

                // Fungsi ambil timkerja
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

                $responseNow  = $getTimKerja($tahunSekarang);
                $responsePrev = $getTimKerja($tahunSekarang - 1);

                // Fungsi cari role user — cek by nipbaru DAN niplama
                $cekJenisUser = function ($timData, $nipUser, $nipLamaUser) {
                    foreach ($timData as $tim) {

                        // Cek ketua tim by nipbaru ATAU niplama
                        $isKetua = (
                            (($tim['nipbaru_ketua'] ?? null) == $nipUser) ||
                            (!empty($nipLamaUser) && ($tim['niplama_ketua'] ?? null) == $nipLamaUser)
                        );

                        if ($isKetua && (($tim['status_ketua'] ?? 'aktif') === 'aktif')) {
                            return 'ketua_tim';
                        }

                        // Cek anggota by nipbaru ATAU niplama
                        if (!empty($tim['anggota_tim'])) {
                            foreach ($tim['anggota_tim'] as $anggota) {
                                $isAnggota = (
                                    (($anggota['nipbaru'] ?? null) == $nipUser) ||
                                    (!empty($nipLamaUser) && ($anggota['niplama'] ?? null) == $nipLamaUser)
                                );
                                if ($isAnggota) {
                                    return 'anggota';
                                }
                            }
                        }
                    }

                    return null;
                };

                // Fungsi sync anggota tim ke database
                $syncAnggotaTim = function ($timData) {
                    foreach ($timData as $tim) {
                        $kodeTim = $tim['kode_tim'] ?? null;
                        if (!$kodeTim) continue;

                        $timExist = DB::table('m_tim')->where('kode_tim', $kodeTim)->first();
                        if (!$timExist) continue;

                        if (empty($tim['anggota_tim'])) continue;

                        foreach ($tim['anggota_tim'] as $anggota) {
                            $pegawaiAnggota = DB::table('m_pegawai')
                                ->where('nip', $anggota['nipbaru'] ?? null)
                                ->orWhere('nip_lama', $anggota['niplama'] ?? null)
                                ->first();

                            if (!$pegawaiAnggota) continue;

                            $sudahAda = DB::table('t_anggota_tim')
                                ->where('tim_kode_tim', $kodeTim)
                                ->where('pegawai_id_pegawai', $pegawaiAnggota->id_pegawai)
                                ->exists();

                            if (!$sudahAda) {
                                DB::table('t_anggota_tim')->insert([
                                    'tim_kode_tim'       => $kodeTim,
                                    'pegawai_id_pegawai' => $pegawaiAnggota->id_pegawai,
                                    'nip'                => $anggota['nipbaru'] ?? null,
                                    'nip_lama'           => $anggota['niplama'] ?? null,
                                    'jenis'              => 1,
                                ]);
                            }
                        }
                    }
                };

                // Cek timkerja tahun sekarang
                $responseTim = $getTimKerja($tahunSekarang);

                Log::info('Timkerja tahun berjalan', [
                    'tahun'  => $tahunSekarang,
                    'status' => $responseTim->status(),
                    'body'   => $responseTim->json(),
                ]);

                $jenisUser = null;

                if ($responseTim->successful()) {
                    $timBody = $responseTim->json();
                    if (isset($timBody['data'])) {
                        // Cek jenis user dulu
                        $jenisUser = $cekJenisUser($timBody['data'], $nipUser, $nipLamaUser);

                        // Sekalian sync anggota tim ke database
                        $syncAnggotaTim($timBody['data']);
                    }
                }

                Log::info('Hasil cek jenis user tahun berjalan', [
                    'nip'        => $nipUser,
                    'jenis_user' => $jenisUser,
                ]);

                // Fallback tahun sebelumnya
                if ($jenisUser === null) {
                    $responseTim = $getTimKerja($tahunSekarang - 1);

                    Log::info('Timkerja tahun sebelumnya (fallback)', [
                        'tahun'  => $tahunSekarang - 1,
                        'status' => $responseTim->status(),
                        'body'   => $responseTim->json(),
                    ]);

                    if ($responseTim->successful()) {
                        $timBody = $responseTim->json();
                        if (isset($timBody['data'])) {
                            $jenisUser = $cekJenisUser($timBody['data'], $nipUser, $nipLamaUser);
                        }
                    }

                    Log::info('Hasil cek jenis user tahun sebelumnya', [
                        'nip'        => $nipUser,
                        'jenis_user' => $jenisUser,
                    ]);
                }

                Session::put('debug_timkerja', [
                    'tahun_sekarang' => [
                        'tahun' => $tahunSekarang,
                        'data'  => $responseNow->json() ?? null,
                    ],
                    'tahun_sebelumnya' => [
                        'tahun' => $tahunSekarang - 1,
                        'data'  => $responsePrev->json() ?? null,
                    ],
                ]);

                // Tentukan role dari hasil cek timkerja
                $existing = DB::table('m_pegawai')->where('nip', $nipUser)->first();

                $roleFromApi = match($jenisUser) {
                    'ketua_tim' => 'ketua_tim',
                    'anggota'   => 'user',
                    default     => 'user',
                };

                if ($existing) {
                    $updateData = [
                        'nama'      => $data['nama'] ?? null,
                        'email'     => $data['email'] ?? null,
                        'nip_lama'  => $data['nip_lama'] ?? null,
                        'foto_url'  => $data['foto_url'] ?? null,
                        'satker'    => $data['satker'] ?? null,
                        'kd_satker' => $data['kd_satker'] ?? null,
                    ];

                    if ($golongan) {
                        $updateData['golongan'] = $golongan;
                    }

                    // Jangan overwrite role admin/superadmin
                    if (!in_array($existing->role, ['admin', 'superadmin', 'ketua_tim', 'pimpinan'])) {
                        $updateData['role'] = $roleFromApi;
                    }

                    DB::table('m_pegawai')->where('nip', $nipUser)->update($updateData);

                } else {
                    DB::table('m_pegawai')->insert([
                        'nama'      => $data['nama'] ?? null,
                        'email'     => $data['email'] ?? null,
                        'nip'       => $nipUser,
                        'nip_lama'  => $data['nip_lama'] ?? null,
                        'foto_url'  => $data['foto_url'] ?? null,
                        'satker'    => $data['satker'] ?? null,
                        'kd_satker' => $data['kd_satker'] ?? null,
                        'golongan'  => $golongan,
                        'role'      => $roleFromApi,
                    ]);
                }

                $pegawaiData = DB::table('m_pegawai')->where('nip', $nipUser)->first();

                Session::put('user', $data);
                Session::put('logged_in', true);
                Session::put('id_pegawai', $pegawaiData->id_pegawai ?? null);
                Session::put('role', $pegawaiData->role ?? 'user');

                $role = $pegawaiData->role ?? 'user';

                if ($role === 'superadmin' || $role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($role === 'ketua_tim') {
                    return redirect()->route('ketua-tim.dashboard');
                } elseif ($role === 'pimpinan') {
                    return redirect()->route('pimpinan.dashboard');
                } else {
                    return redirect()->route('pegawai.dashboard');
                }
            }

            Log::error('Login gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if (isset($body['detail']) && str_contains(strtolower($body['detail']), 'incorrect username or password')) {
                return back()->withErrors(['login' => 'Username atau password salah.']);
            }

            if ($response->serverError()) {
                return back()->withErrors(['login' => 'Server API sedang bermasalah, coba lagi nanti.']);
            }

            if ($response->clientError()) {
                return back()->withErrors(['login' => 'Username atau password salah.']);
            }

            return back()->withErrors([
                'login' => '[Error ' . $response->status() . '] ' . ($body['detail'] ?? $body['message'] ?? $response->body())
            ]);

        } catch (ConnectionException $e) {
            Log::error('Connection error saat login', ['message' => $e->getMessage()]);
            return back()->withErrors(['login' => 'Tidak dapat terhubung ke server.']);

        } catch (\Exception $e) {
            Log::error('Exception login', ['message' => $e->getMessage()]);
            return back()->withErrors(['login' => '[Error tidak terduga] ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Session::flush();
        return redirect()->route('login');
    }
}