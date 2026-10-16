<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LemburController extends Controller
{
    public function index(Request $request)
    {
        $nipUser  = session('user')['nip'];
        $namaUser = session('user')['nama'];
        $role = DB::table('m_pegawai')->where('nip', $nipUser)->value('role');

        $perPage = in_array((int) $request->get('perPage', 10), [10, 25, 50, 100])
            ? (int) $request->get('perPage', 10)
            : 10;

        // Filter bulan & tahun murni dari URL (bukan session) agar "Semua Bulan" / "Semua Tahun" bisa membersihkannya
        $bulanParam = $request->query('bulan');
        $tahunParam = $request->query('tahun');

        if ($bulanParam && preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $bulanParam, $matches)) {
            // Dukung format lama YYYY-MM jika ada (misal di halaman ketua_tim atau bookmark)
            $tahun = $tahunParam ?: $matches[1];
            $bulan = ($role === 'ketua_tim') ? $bulanParam : $matches[2];
            $tahunFilter = $matches[1];
            $bulanFilter = $matches[2];
        } else {
            $tahunFilter = ($tahunParam && preg_match('/^\d{4}$/', $tahunParam)) ? $tahunParam : null;
            $bulanFilter = ($bulanParam && is_numeric($bulanParam) && (int)$bulanParam >= 1 && (int)$bulanParam <= 12)
                ? str_pad((string)(int)$bulanParam, 2, '0', STR_PAD_LEFT)
                : null;
            $bulan = $bulanFilter;
            $tahun = $tahunFilter;
        }

        // Koreksi otomatis berdasarkan presensi setiap kali halaman dibuka
        $this->koreksiDariPresensi($nipUser);

        $query = DB::table('t_transaksi as t')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->leftJoin('m_dokumentasi as md', 't.dokumentasi_id_dokumentasi', '=', 'md.id_dokumentasi')
            ->where('t.submitted_by_NIP', $nipUser)
            ->select('t.*', 'mt.nama_tim', 'mt.nama_ketua', 'md.file_path as file_dokumentasi');

        if ($tahunFilter && $bulanFilter) {
            $periode = Carbon::createFromDate((int) $tahunFilter, (int) $bulanFilter, 1);
            $query->whereBetween('t.date', [
                $periode->copy()->startOfMonth()->toDateString(),
                $periode->copy()->endOfMonth()->toDateString(),
            ]);
        } elseif ($tahunFilter) {
            $query->whereYear('t.date', (int) $tahunFilter);
        } elseif ($bulanFilter) {
            $query->whereMonth('t.date', (int) $bulanFilter);
        }

        $query->orderBy('t.date', 'desc')
              ->orderBy('t.id_transaksi', 'desc');

        $transaksi = $query->paginate($perPage)->withQueryString();

        $idPegawai = session('id_pegawai');

        $ketuaTim = DB::table('t_anggota_tim as at')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('at.pegawai_id_pegawai', $idPegawai)
            ->where('mt.status', 'aktif')
            ->whereNotNull('mt.nipbaru_ketua')
            ->where('mt.nipbaru_ketua', '!=', $nipUser)
            ->select(
                'mt.nipbaru_ketua as nip',
                'mt.nama_ketua as nama',
                'mt.nama_tim as tim',
                'mt.kode_tim'
            )
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

           $timSendiri = DB::table('m_tim')
                ->where('nipbaru_ketua', $nipUser)
                ->where('status', 'aktif')
                ->select('kode_tim', 'nama_tim')
                ->first();

            if ($timSendiri) {
                $ketuaTim[] = [
                    'nip'      => $nipUser,
                    'nama'     => $namaUser,
                    'tim'      => $timSendiri->nama_tim,
                    'kode_tim' => $timSendiri->kode_tim,
                ];
            }

        $hariLibur = DB::table('m_hari_libur')->pluck('tanggal')->toArray();

        // Daftar tanggal pengajuan aktif pegawai (untuk proteksi lock anti-dobel di formulir)
        $existingActiveDates = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nipUser)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->select('date', 'status')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = match($item->status) {
                    'pending' => 'Menunggu Ketua',
                    'menunggu_kabag' => 'Menunggu Kabag',
                    'approved' => 'Disetujui',
                    default => ucfirst($item->status),
                };
                return [$item->date => $label];
            })
            ->toArray();

        // Daftar tahun untuk filter tahun
        $daftarTahun = DB::table('t_transaksi')
            ->whereNotNull('date')
            ->whereYear('date', '>=', 2020)
            ->selectRaw('DISTINCT YEAR(date) as th')
            ->orderBy('th', 'desc')
            ->pluck('th')
            ->toArray();

        $currentYear = (int) now()->year;
        if (!in_array($currentYear, $daftarTahun)) {
            $daftarTahun[] = $currentYear;
            rsort($daftarTahun);
        }

        return view('lembur', compact('ketuaTim', 'transaksi', 'hariLibur', 'bulan', 'tahun', 'daftarTahun', 'perPage', 'existingActiveDates'));
    }

    private function koreksiDariPresensi(string $nipUser): void
    {
        $transaksis = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.submitted_by_NIP', $nipUser)
            ->where('t.status', 'approved')
            ->whereNull('t.eligible')
            ->select('t.*', 'p.nip_lama', 'p.nip')
            ->get();

        foreach ($transaksis as $transaksi) {

            $presensi = DB::table('t_presensi')
                ->whereDate('tanggal', $transaksi->date)
                ->where(function ($q) use ($transaksi) {
                    $q->where('niplama', $transaksi->nip_lama);
                    if (!empty($transaksi->nip)) {
                        $q->orWhere('niplama', $transaksi->nip);
                    }
                })
                ->first();

            if (!$presensi || !$presensi->jam_selesai) {
                continue;
            }

            $tanggalCarbon     = Carbon::parse($transaksi->date);
            $isWeekend = $tanggalCarbon->isWeekend();
            $isLiburNasional = DB::table('m_hari_libur')
                ->whereDate('tanggal', $tanggalCarbon->toDateString())
                ->exists();
            $maxJam = ($isWeekend || $isLiburNasional) ? 6 : 4;
            $jamMulaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
            $batasMaksimal     = $jamMulaiPengajuan->copy()->addHours($maxJam);

            $jamSelesaiPresensi = Carbon::parse($presensi->jam_selesai);
            if ($jamSelesaiPresensi->lessThan($jamMulaiPengajuan)) {
                $jamSelesaiPresensi->addDay();
            }

            // Jam lembur disetujui tidak boleh melebihi jam yang diajukan
            $jamSelesaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_selesai);
            if ($jamSelesaiPengajuan->lessThan($jamMulaiPengajuan)) {
                $jamSelesaiPengajuan->addDay();
            }

            $batasAtas = $jamSelesaiPengajuan->lessThan($batasMaksimal)
                ? $jamSelesaiPengajuan
                : $batasMaksimal;

            $jamSelesaiFinal = $jamSelesaiPresensi->lessThan($batasAtas)
                ? $jamSelesaiPresensi
                : $batasAtas;

            $durasi = $jamMulaiPengajuan->diffInHours($jamSelesaiFinal);
            if ($durasi < 2) {
                DB::table('t_transaksi')
                    ->where('id_transaksi', $transaksi->id_transaksi)
                    ->update([
                        'status'                => 'rejected',
                        'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                        'note'                  => 'Durasi lembur kurang dari 2 jam berdasarkan data presensi.',
                        'eligible'              => null,
                    ]);
                continue;
            }

            // Cek pelanggaran
            $pelanggaranList = [];

            $statusPresensi = strtolower(trim($presensi->status));
            if (!in_array($statusPresensi, ['wfo', 'wfol'])) {
                $pelanggaranList[] = 'status bekerja tidak sesuai kebijakan';
            }

            $jamMasuk    = Carbon::parse($presensi->jam_mulai);
            $batasLambat = Carbon::parse($transaksi->date . ' 07:31:00');
            if (!($isWeekend || $isLiburNasional) && !$jamMasuk->lessThan($batasLambat)) {
                $pelanggaranList[] = 'presensi kantor terlambat';
            }

            if (!empty($pelanggaranList)) {
                $catatan   = implode(' dan ', $pelanggaranList);
                $noteFinal = "Lembur disetujui namun tidak masuk proses bisnis karena: {$catatan}.";
                $eligible  = 0;
            } else {
                $noteFinal = $transaksi->note ?? null;
                $eligible  = 1;
            }

            DB::table('t_transaksi')
                ->where('id_transaksi', $transaksi->id_transaksi)
                ->update([
                    'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                    'note'                  => $noteFinal,
                    'eligible'              => $eligible,
                ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'approver_id' => 'required|string',
            'kode_tim'    => 'required|string',
            'tanggal'     => 'required|date_format:Y-m-d',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'nullable',
            'uraian'      => 'required|string|max:2000',
            'signature'   => 'required|string',
        ], [
            'uraian.required' => 'Uraian kegiatan wajib diisi.',
            'uraian.max'      => 'Uraian kegiatan maksimal 2000 karakter.',
            'tanggal.date_format' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
        ]);

        $nip     = session('user')['nip'];
        $tanggal = Carbon::parse($validated['tanggal']);

        // Cegah pengajuan ganda di tanggal yang sama (1 pengajuan aktif per hari per pegawai)
        $transaksiAda = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nip)
            ->where('date', $tanggal->toDateString())
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->first();

        if ($transaksiAda) {
            $statusLabel = match($transaksiAda->status) {
                'pending' => 'Menunggu Ketua Tim',
                'menunggu_kabag' => 'Menunggu Kabag Umum',
                'approved' => 'Sudah Disetujui',
                default => $transaksiAda->status,
            };
            return back()->withErrors([
                'tanggal' => "Anda sudah memiliki pengajuan lembur pada tanggal ini (Status: {$statusLabel}). Satu pegawai hanya dapat mengajukan 1 kali lembur per hari sesuai ketentuan BPS."
            ])->withInput();
        }

        $tahun             = $tanggal->year;
        $isWeekend       = $tanggal->isWeekend();
        $isLiburNasional = DB::table('m_hari_libur')  // ganti t_ → m_
            ->whereDate('tanggal', $tanggal->toDateString())
            ->exists();
        $isHariLibur = $isWeekend || $isLiburNasional;
        $hari        = $isHariLibur ? 1 : 0;

        $jamMulai   = Carbon::parse($validated['jam_mulai']);
        $jamSelesai = !empty($validated['jam_selesai'])
            ? Carbon::parse($validated['jam_selesai'])
            : null;

        $status = 'pending';
        $note   = null;

        if ($jamSelesai) {
            if ($jamSelesai->lessThan($jamMulai)) {
                $jamSelesai->addDay();
            }

            $durasi = $jamMulai->diffInHours($jamSelesai);

            if ($durasi < 2) {
                return back()->withErrors([
                    'jam_selesai' => 'Durasi lembur minimal 2 jam sesuai ketentuan PMK/BPS.'
                ])->withInput();
            }

            if ($durasi > 12) {
                return back()->withErrors([
                    'jam_selesai' => 'Durasi lembur melebihi batas wajar harian (maksimal 12 jam).'
                ])->withInput();
            }
        }

        // Jika merupakan Tim Bagian Umum atau approver adalah Kabag Umum,
        // pengajuan langsung masuk ke antrean persetujuan Kabag Umum (status: menunggu_kabag)
        $isTimBagianUmum = false;
            if (!empty($validated['kode_tim'])) {
                $tim = DB::table('m_tim')->where('kode_tim', $validated['kode_tim'])->first();
                if ($tim && (str_contains(strtolower($tim->nama_tim), 'bagian umum') || $tim->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                    $isTimBagianUmum = true;
                }
            }

            $isApproverKabag = DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->where('status', 'aktif')
                ->where(function ($q) use ($validated) {
                    $q->where('nip', $validated['approver_id'])
                      ->orWhere('nip_lama', $validated['approver_id']);
                })->exists();

            if ($isTimBagianUmum || $isApproverKabag) {
                $status = 'menunggu_kabag';
            }

        // Cegah tanda tangan kosong / kanvas polos digital
        if ($this->isBlankSignature($validated['signature'])) {
            return back()->withErrors([
                'signature' => 'Tanda tangan digital tidak boleh kosong atau kanvas polos. Silakan goreskan tanda tangan Anda.'
            ])->withInput();
        }

        $idTransaksi = DB::table('t_transaksi')->insertGetId([
            'submitted_by_NIP'      => $nip,
            'date'                  => $tanggal->toDateString(),
            'jam_mulai'             => $jamMulai->format('H:i:s'),
            'jam_selesai'           => $jamSelesai?->format('H:i:s'),
            'jam_mulai_disetujui'   => null,
            'jam_selesai_disetujui' => null,
            'uraian'                => $validated['uraian'],
            'approver_employee_id'  => $validated['approver_id'],
            'tim_kode_tim'          => $validated['kode_tim'],
            'status'                => $status,
            'note'                  => $note,
            'submitted_at'          => now(),
            'hari'                  => $hari,
        ]);

        $signatureRaw = str_replace('data:image/png;base64,', '', $validated['signature']);
        $signatureRaw = str_replace(' ', '+', $signatureRaw);
        $fileName     = 'signatures/' . $idTransaksi . '_' . $nip . '.png';

        Storage::disk('public')->put($fileName, base64_decode($signatureRaw));

        DB::table('t_transaksi')
            ->where('id_transaksi', $idTransaksi)
            ->update(['signature_path' => $fileName]);

        $perPage = in_array((int) $request->get('perPage', 10), [10, 25, 50, 100])
            ? (int) $request->get('perPage', 10)
            : 10;

        $bulanAktif = $request->get('bulan');
        if ($bulanAktif && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanAktif)) {
            $bulanAktif = null;
        }

        // Hitung halaman tempat record baru berada agar langsung terlihat setelah submit
        $queryBaru = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nip)
            ->where(function ($q) use ($tanggal, $idTransaksi) {
                $q->where('date', '>', $tanggal->toDateString())
                  ->orWhere(function ($q2) use ($tanggal, $idTransaksi) {
                      $q2->where('date', '=', $tanggal->toDateString())
                         ->where('id_transaksi', '>', $idTransaksi);
                  });
            });

        if ($bulanAktif) {
            $periodeAktif = Carbon::parse($bulanAktif . '-01');
            $queryBaru->whereBetween('date', [
                $periodeAktif->copy()->startOfMonth()->toDateString(),
                $periodeAktif->copy()->endOfMonth()->toDateString(),
            ]);
        }

        $newerCount = $queryBaru->count();
        $page       = (int) floor($newerCount / $perPage) + 1;

        $params = ['page' => $page];
        if ($bulanAktif) {
            $params['bulan'] = $bulanAktif;
        }
        if ($perPage !== 10) {
            $params['perPage'] = $perPage;
        }

        $message = 'Pengajuan lembur berhasil dikirim.';

        $targetRoute = (request()->routeIs('ketua-tim.*') || (session('user')['role'] ?? '') === 'ketua_tim')
            ? 'ketua-tim.lembur'
            : 'lembur';

        return redirect()->route($targetRoute, $params)
            ->with('success', $message);
    }

    public function timPegawai()
    {
        $idPegawai = session('id_pegawai');

        $tim = DB::table('t_anggota_tim as at')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('at.pegawai_id_pegawai', $idPegawai)
            ->where('mt.status', 'aktif')
            ->select('mt.kode_tim', 'mt.nama_tim')
            ->distinct()
            ->get();

        return response()->json($tim);
    }

    public function storeDoc(Request $request, $id_transaksi)
    {
        $filePath = trim((string) $request->input('file_path'));

        if (str_starts_with($filePath, 'b64:')) {
            $decoded = base64_decode(substr($filePath, 4), true);
            if ($decoded !== false) {
                $filePath = trim($decoded);
            }
        } elseif (str_starts_with($filePath, 'enc:')) {
            $decoded = base64_decode(substr($filePath, 4), true);
            if ($decoded !== false) {
                $filePath = trim($decoded);
            }
        } elseif (!str_starts_with($filePath, 'http://') && !str_starts_with($filePath, 'https://')) {
            $decoded = base64_decode($filePath, true);
            if ($decoded !== false && (str_starts_with($decoded, 'http://') || str_starts_with($decoded, 'https://'))) {
                $filePath = trim($decoded);
            }
        }

        if (!preg_match('~^https?://~i', $filePath) && preg_match('~^[a-z0-9\-\.]+\.[a-z]{2,}~i', $filePath)) {
            $filePath = 'https://' . $filePath;
        }

        $request->merge(['file_path' => $filePath]);

        $request->validate([
            'file_path' => 'required|url|max:255',
        ]);

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', session('user')['nip'])
            ->where('status', 'approved')
            ->firstOrFail();

        $idDok = DB::table('m_dokumentasi')->insertGetId([
            'transaksi_id' => $id_transaksi,
            'date'         => $transaksi->date,
            'file_path'    => $filePath,
        ]);

        DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->update(['dokumentasi_id_dokumentasi' => $idDok]);

        return back()->with('success', 'Dokumentasi berhasil disimpan.');
    }

    public function destroyDoc($id_transaksi)
    {
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', session('user')['nip'])
            ->firstOrFail();

        if ($transaksi->dokumentasi_id_dokumentasi) {
            DB::table('m_dokumentasi')
                ->where('id_dokumentasi', $transaksi->dokumentasi_id_dokumentasi)
                ->delete();

            DB::table('t_transaksi')
                ->where('id_transaksi', $id_transaksi)
                ->update(['dokumentasi_id_dokumentasi' => null]);
        }

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    public function update(Request $request, $id_transaksi)
    {
        $nipUser = session('user')['nip'];

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', $nipUser)
            ->first();

        if (!$transaksi) {
            return back()->with('error', 'Data pengajuan lembur tidak ditemukan.');
        }

        // Kunci edit: boleh diubah jika:
        // 1. Status masih 'pending' (belum pernah diproses Ketua Tim)
        // 2. Status 'menunggu_kabag' KHUSUS untuk Tim Bagian Umum / Approver Kabag yang belum diproses Kabag Umum
        // (karena pengajuan Tim Bagian Umum otomatis langsung 'menunggu_kabag' tanpa melalui Ketua Tim terpisah)
        $isTimBagianUmum = false;
        if (!empty($transaksi->tim_kode_tim)) {
            $tim = DB::table('m_tim')->where('kode_tim', $transaksi->tim_kode_tim)->first();
            if ($tim && (str_contains(strtolower($tim->nama_tim), 'bagian umum') || $tim->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                $isTimBagianUmum = true;
            }
        }

        $isApproverKabag = false;
        if (!empty($transaksi->approver_employee_id)) {
            $isApproverKabag = DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->where('status', 'aktif')
                ->where(function ($q) use ($transaksi) {
                    $q->where('nip', $transaksi->approver_employee_id)
                      ->orWhere('nip_lama', $transaksi->approver_employee_id);
                })->exists();
        }

        $isBolehEditTimUmum = ($transaksi->status === 'menunggu_kabag')
            && ($isTimBagianUmum || $isApproverKabag)
            && empty($transaksi->approved_kabag_at);

        if ($transaksi->status !== 'pending' && !$isBolehEditTimUmum) {
            $statusLabel = match($transaksi->status) {
                'menunggu_kabag' => 'Menunggu Kabag Umum (sudah disetujui Ketua Tim)',
                'approved'       => 'Sudah Disetujui Final',
                'rejected'       => 'Ditolak',
                'cancelled'      => 'Dibatalkan',
                default          => $transaksi->status,
            };
            return back()->with('error', "Pengajuan lembur tidak dapat diubah karena sudah diproses atau disetujui (Status: {$statusLabel}).");
        }

        $validated = $request->validate([
            'approver_id' => 'nullable|string',
            'kode_tim'    => 'nullable|string',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'nullable',
            'uraian'      => 'required|string|max:2000',
        ], [
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'uraian.required'    => 'Uraian kegiatan wajib diisi.',
            'uraian.max'         => 'Uraian kegiatan maksimal 2000 karakter.',
        ]);

        $jamMulai   = Carbon::parse($validated['jam_mulai']);
        $jamSelesai = !empty($validated['jam_selesai'])
            ? Carbon::parse($validated['jam_selesai'])
            : null;

        if ($jamSelesai) {
            if ($jamSelesai->lessThan($jamMulai)) {
                $jamSelesai->addDay();
            }

            $durasi = $jamMulai->diffInHours($jamSelesai);
            if ($durasi < 2) {
                return back()->with('error', 'Durasi lembur minimal 2 jam sesuai ketentuan PMK/BPS.');
            }
            if ($durasi > 12) {
                return back()->with('error', 'Durasi lembur melebihi batas wajar harian (maksimal 12 jam).');
            }
        }

        $updateData = [
            'jam_mulai'      => $jamMulai->format('H:i:s'),
            'jam_selesai'    => $jamSelesai?->format('H:i:s'),
            'uraian'         => $validated['uraian'],
            'user_edited'    => session('user')['nama'] ?? $nipUser,
            'tanggal_edited' => now(),
        ];

        // Jika ada perubahan Ketua Tim / Tim
        if (!empty($validated['approver_id']) && !empty($validated['kode_tim'])) {
            $updateData['approver_employee_id'] = $validated['approver_id'];
            $updateData['tim_kode_tim']         = $validated['kode_tim'];

            // Evaluasi status jika tujuan merupakan Tim Bagian Umum / approver adalah Kabag Umum
            $isTargetTimBagianUmum = false;
            $timTarget = DB::table('m_tim')->where('kode_tim', $validated['kode_tim'])->first();
            if ($timTarget && (str_contains(strtolower($timTarget->nama_tim), 'bagian umum') || $timTarget->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                $isTargetTimBagianUmum = true;
            }

            $isTargetApproverKabag = DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->where('status', 'aktif')
                ->where(function ($q) use ($validated) {
                    $q->where('nip', $validated['approver_id'])
                      ->orWhere('nip_lama', $validated['approver_id']);
                })->exists();

            if ($isTargetTimBagianUmum || $isTargetApproverKabag) {
                $updateData['status'] = 'menunggu_kabag';
            } else {
                $updateData['status'] = 'pending';
            }
        }

        DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->update($updateData);

        return back()->with('success', 'Pengajuan lembur berhasil diperbarui.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'required',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.id_transaksi', $id)
            ->select('t.*', 'p.nip_lama')
            ->first();

        $noteKetua = trim($request->note ?? '');
        $noteFinal = null;
        $eligible  = null;

        $jamMulaiDisetujui = Carbon::parse($transaksi->date . ' ' . $request->jam_mulai_disetujui);

        $jamSelesaiDisetujui = $request->jam_selesai_disetujui
            ? Carbon::parse($transaksi->date . ' ' . $request->jam_selesai_disetujui)
            : null;

        if ($jamSelesaiDisetujui && $jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        if ($request->status === 'approved') {

            $tanggalCarbon     = Carbon::parse($transaksi->date);
            $isWeekend = $tanggalCarbon->isWeekend();
            $isLiburNasional = DB::table('m_hari_libur')
                ->whereDate('tanggal', $tanggalCarbon->toDateString())
                ->exists();
            $maxJam = ($isWeekend || $isLiburNasional) ? 6 : 4;
            $jamMulaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
            $batasMaksimal     = $jamMulaiPengajuan->copy()->addHours($maxJam);

            $presensi = DB::table('t_presensi')
                ->whereDate('tanggal', $transaksi->date)
                ->where('niplama', $transaksi->nip_lama)
                ->first();

            if ($presensi && $presensi->jam_selesai) {

                $jamSelesaiPresensi = Carbon::parse($presensi->jam_selesai);
                if ($jamSelesaiPresensi->lessThan($jamMulaiPengajuan)) {
                    $jamSelesaiPresensi->addDay();
                }

                $jamSelesaiDisetujui = $jamSelesaiPresensi->lessThan($batasMaksimal)
                    ? $jamSelesaiPresensi
                    : $batasMaksimal;

                $durasi = $jamMulaiPengajuan->diffInHours($jamSelesaiDisetujui);
                if ($durasi < 2) {
                    DB::table('t_transaksi')->where('id_transaksi', $id)->update([
                        'status'                => 'rejected',
                        'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                        'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
                        'note'                  => 'Durasi lembur kurang dari 2 jam berdasarkan data presensi.',
                        'eligible'              => null,
                        'approved_at'           => now()->toDateString(),
                    ]);
                    return response()->json(['success' => true]);
                }

                // Cek pelanggaran
                $pelanggaranList = [];

                $statusPresensi = strtolower(trim($presensi->status));
                if (!in_array($statusPresensi, ['wfo', 'wfol'])) {
                    $pelanggaranList[] = 'status bekerja tidak sesuai kebijakan';
                }

                $jamMasuk    = Carbon::parse($presensi->jam_mulai);
                $batasLambat = Carbon::parse($transaksi->date . ' 07:31:00');
                if (!($isWeekend || $isLiburNasional) && !$jamMasuk->lessThan($batasLambat)) {
                    $pelanggaranList[] = 'presensi kantor terlambat';
                }

                if (!empty($pelanggaranList)) {
                    $catatan   = implode(' dan ', $pelanggaranList);
                    $noteFinal = "Lembur disetujui namun tidak masuk proses bisnis karena: {$catatan}.";
                    $eligible  = 0;
                } else {
                    $noteFinal = $noteKetua !== '' ? $noteKetua : null;
                    $eligible  = 1;
                }

            } else {
                // Presensi belum ada, koreksi nanti via koreksiDariPresensi()
                $noteFinal = $noteKetua !== '' ? $noteKetua : null;
                $eligible  = null;
            }

        } else {
            $noteFinal = $noteKetua !== '' ? $noteKetua : null;
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update([
            'status'                => $request->status,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui?->format('H:i:s'),
            'note'                  => $noteFinal,
            'eligible'              => $eligible,
            'approved_at'           => now()->toDateString(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Memverifikasi apakah kanvas tanda tangan benar-benar digoreskan atau hanya gambar polos/kosong.
     */
    private function isBlankSignature(?string $base64Data): bool
    {
        if (empty($base64Data)) {
            return true;
        }

        $raw = str_replace(['data:image/png;base64,', ' '], ['', '+'], $base64Data);
        $binary = base64_decode($raw);
        if (!$binary || strlen($binary) < 500) {
            return true;
        }

        if (extension_loaded('gd')) {
            $img = @imagecreatefromstring($binary);
            if (!$img) {
                return true;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $drawnPixels = 0;

            // Sampling per 2 pixel untuk performa tinggi (eksekusi < 2 milidetik)
            for ($x = 0; $x < $w; $x += 2) {
                for ($y = 0; $y < $h; $y += 2) {
                    $rgba = imagecolorat($img, $x, $y);
                    $alpha = ($rgba & 0x7F000000) >> 24;
                    // Pada GD, alpha 127 = 100% transparan
                    if ($alpha < 120) {
                        $r = ($rgba >> 16) & 0xFF;
                        $g = ($rgba >> 8) & 0xFF;
                        $b = $rgba & 0xFF;
                        // Bukan warna putih/latar belakang polos
                        if ($r < 240 || $g < 240 || $b < 240) {
                            $drawnPixels++;
                            if ($drawnPixels > 35) {
                                imagedestroy($img);
                                return false; // Ditemukan goresan tanda tangan asli
                            }
                        }
                    }
                }
            }

            imagedestroy($img);
            return $drawnPixels <= 35;
        }

        return false;
    }
}
