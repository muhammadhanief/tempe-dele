<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Traits\KoreksiLembur;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\RiwayatPresensi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PresensiUploadController extends Controller
{
    use KoreksiLembur;

    public function index()
    {
        $hariLibur = DB::table('m_hari_libur')
        ->orderBy('tanggal', 'asc')
        ->get();

        return view('admin.presensi', compact('hariLibur'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            // Ambil tahun dari input request jika ada, atau ekstrak 4 digit dari sel header A5
            $reqTahun = (int) $request->input('tahun');
            if ($reqTahun >= 2020 && $reqTahun <= 2099) {
                $tahun = $reqTahun;
            } elseif (preg_match('/\b(20\d{2})\b/', $rows[4][0] ?? '', $m)) {
                $tahun = (int) $m[1];
            } else {
                $tahun = (int) date('Y');
            }

            $bulan = $this->parseBulan($rows[5][0] ?? '');
            $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

            $isValidJam = fn($j) => preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $j);
            $inserted   = 0;

            // ── LOOP INSERT PRESENSI ──
            for ($i = 9; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nip = trim($row[0] ?? '');
                if (empty($nip)) continue;

                for ($col = 2; $col <= 32; $col++) {
                    $tanggal = $col - 1;
                    if ($tanggal > $daysInMonth) {
                        break;
                    }

                    $cell = trim($row[$col] ?? '');
                    if (empty($cell)) continue;

                    // Normalisasi CRLF Windows menjadi \n tunggal
                    $cell = str_replace(["\r\n", "\r"], "\n", $cell);
                    $parts      = explode("\n", $cell);
                    $jamMulai   = trim($parts[0] ?? '');
                    $jamSelesai = trim($parts[1] ?? '');
                    $status     = trim($parts[2] ?? '');

                    if (!$isValidJam($jamMulai)) {
                        $status     = $jamMulai;
                        $jamMulai   = null;
                        $jamSelesai = null;
                    }

                    if (empty($status)) continue;

                    $tanggalStr   = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggal);
                    $jamMulaiDt   = ($jamMulai && $isValidJam($jamMulai))     ? $tanggalStr . ' ' . $jamMulai   : null;
                    $jamSelesaiDt = ($jamSelesai && $isValidJam($jamSelesai)) ? $tanggalStr . ' ' . $jamSelesai : null;

                    Presensi::updateOrCreate(
                        ['niplama' => $nip, 'tanggal' => $tanggalStr],
                        [
                            'jam_mulai'   => $jamMulaiDt,
                            'jam_selesai' => $jamSelesaiDt,
                            'status'      => $status,
                        ]
                    );

                    $inserted++;
                }
            }
            // ── END LOOP INSERT ──

            // ── KOREKSI LEMBUR: dijalankan SETELAH semua presensi selesai diinsert ──
            $tanggalBulanIni = [];
            $cur = Carbon::create($tahun, $bulan, 1);
            $end = $cur->copy()->endOfMonth();
            while ($cur->lte($end)) {
                $tanggalBulanIni[] = $cur->toDateString();
                $cur->addDay();
            }

            DB::table('t_transaksi')
                ->whereIn('date', $tanggalBulanIni)
                ->where('status', 'approved')
                ->update(['eligible' => null]);

            $this->koreksiUntukTanggal($tanggalBulanIni);
            // ── END KOREKSI ──

            $adminNama = session('nama') ?? session('user.nama') ?? session('pegawai.nama') ?? 'Admin';
            RiwayatPresensi::create([
                'periode'     => sprintf('%04d-%02d', $tahun, $bulan),
                'nama_file'   => $file->getClientOriginalName(),
                'uploaded_by' => $adminNama,
                'uploaded_at' => now(),
            ]);

            $namaPeriode = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');
            return response()->json([
                'message' => "Upload berhasil! $inserted data presensi periode $namaPeriode disimpan.",
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses file: ' . $e->getMessage()], 500);
        }
    }

    private function parseBulan(string $str): int
    {
        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        foreach ($bulanMap as $namaBulan => $num) {
            if (stripos($str, $namaBulan) !== false) {
                return $num;
            }
        }
        return (int) date('m');
    }

    public function getKalender(Request $request)
    {
        $niplama = $request->niplama;
        $periode = $request->periode;

        if (!$niplama || !$periode) {
            return response()->json([]);
        }

        [$tahun, $bulan] = explode('-', $periode);

        $data = Presensi::where('niplama', $niplama)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get(['tanggal', 'jam_mulai', 'jam_selesai', 'status'])
            ->keyBy(fn($p) => \Carbon\Carbon::parse($p->tanggal)
                ->setTimezone(config('app.timezone'))
                ->format('Y-m-d'));

        return response()->json($data);
    }

    public function riwayat(Request $request)
    {
        $query = RiwayatPresensi::orderBy('uploaded_at', 'desc');

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->expectsJson()) {
            $riwayat = $query->paginate(10)->withPath(route('admin.riwayat_presensi'));
            return response()->json($riwayat);
        }

        $daftarHadirQuery = DB::table('t_riwayat_presensi')->orderBy('uploaded_at', 'desc');
        if ($request->filled('periode')) {
            $daftarHadirQuery->where('periode', $request->periode);
        }
        $daftarHadir = $daftarHadirQuery->get();

        return view('admin.riwayat_presensi', compact('daftarHadir'));
    }

    public function hariLiburIndex()
    {
        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('admin.hari-libur', compact('hariLibur'));
    }

    public function hariLiburStore(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:100',
        ]);

        $grupId  = \Illuminate\Support\Str::uuid()->toString();
        $start   = Carbon::parse($request->tanggal_mulai);
        $end     = Carbon::parse($request->tanggal_selesai);
        $current = $start->copy();

        while ($current->lte($end)) {
            DB::table('m_hari_libur')->updateOrInsert(
                ['tanggal'    => $current->toDateString()],
                ['keterangan' => $request->keterangan, 'grup_id' => $grupId]
            );
            $current->addDay();
        }

        // ── TRIGGER RECALCULATE ──
        $tanggalRange = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $tanggalRange[] = $cur->toDateString();
            $cur->addDay();
        }

        // Update kolom hari → 1 untuk semua transaksi di range ini
        DB::table('t_transaksi')
            ->whereIn('date', $tanggalRange)
            ->update(['hari' => 1]);

        // Reset eligible supaya koreksiDariPresensi() hitung ulang
        DB::table('t_transaksi')
            ->whereIn('date', $tanggalRange)
            ->where('status', 'approved')
            ->whereNotNull('eligible')
            ->update(['eligible' => null]);
            $this->koreksiUntukTanggal($tanggalRange);

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function hariLiburDestroy($id)
    {
        $row = DB::table('m_hari_libur')->where('id', $id)->first();

        if (!$row) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        // Kumpulkan semua tanggal dalam grup sebelum dihapus
        $tanggalDihapus = $row->grup_id
            ? DB::table('m_hari_libur')
                ->where('grup_id', $row->grup_id)
                ->pluck('tanggal')
                ->toArray()
            : [$row->tanggal];

        if ($row->grup_id) {
            DB::table('m_hari_libur')->where('grup_id', $row->grup_id)->delete();
        } else {
            DB::table('m_hari_libur')->where('id', $id)->delete();
        }

        // ── TRIGGER RECALCULATE ──
        foreach ($tanggalDihapus as $tgl) {
            $isWeekend = Carbon::parse($tgl)->isWeekend();

            // Kalau bukan weekend, kembalikan hari → 0
            if (!$isWeekend) {
                DB::table('t_transaksi')
                    ->whereDate('date', $tgl)
                    ->update(['hari' => 0]);
            }

            // Reset eligible supaya koreksiDariPresensi() hitung ulang
            DB::table('t_transaksi')
                ->whereDate('date', $tgl)
                ->where('status', 'approved')
                ->whereNotNull('eligible')
                ->update(['eligible' => null]);
        }

        $this->koreksiUntukTanggal($tanggalDihapus);

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
