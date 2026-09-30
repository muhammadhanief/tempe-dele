<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait KoreksiLembur
{
    public function koreksiUntukTransaksi(int $idTransaksi): void
    {
        $transaksi = DB::table('t_transaksi')->where('id_transaksi', $idTransaksi)->first();
        if ($transaksi && $transaksi->date) {
            $this->koreksiUntukTanggal([$transaksi->date]);
        }
    }

    public function koreksiUntukBulan(int $tahun, int $bulan): void
    {
        $tanggalList = DB::table('t_transaksi')
            ->where('status', 'approved')
            ->whereNull('eligible')
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->distinct()
            ->pluck('date')
            ->toArray();

        if (!empty($tanggalList)) {
            $this->koreksiUntukTanggal($tanggalList);
        }
    }

    public function koreksiUntukTanggal(array $tanggalList): void
    {
        if (empty($tanggalList)) return;

        $transaksis = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->whereIn('t.date', $tanggalList)
            ->where('t.status', 'approved')
            ->whereNull('t.eligible')
            ->select('t.*', 'p.nip_lama')
            ->get();

        foreach ($transaksis as $transaksi) {
            $presensi = DB::table('t_presensi')
                ->whereDate('tanggal', $transaksi->date)
                ->where('niplama', $transaksi->nip_lama)
                ->first();

            // Presensi belum ada → skip, akan dihitung saat upload presensi
            if (!$presensi || !$presensi->jam_selesai) continue;

            $tanggalCarbon   = Carbon::parse($transaksi->date);
            $isWeekend       = $tanggalCarbon->isWeekend();
            $isLiburNasional = DB::table('m_hari_libur')
                ->whereDate('tanggal', $tanggalCarbon->toDateString())
                ->exists();

            $isHariLibur = $isWeekend || $isLiburNasional;
            $maxJam      = $isHariLibur ? 6 : 4;

            $jamMulaiStr        = $transaksi->jam_mulai_disetujui ?: $transaksi->jam_mulai;
            $jamMulai           = Carbon::parse($transaksi->date . ' ' . $jamMulaiStr);
            $batasMaksimal      = $jamMulai->copy()->addHours($maxJam);
            $jamSelesaiPresensi = Carbon::parse($presensi->jam_selesai);

            if ($jamSelesaiPresensi->lessThan($jamMulai)) $jamSelesaiPresensi->addDay();

            // Jam lembur disetujui tidak boleh melebihi jam yang disetujui/diajukan
            $jamSelesaiAcuan     = $transaksi->jam_selesai_disetujui ?: $transaksi->jam_selesai;
            $jamSelesaiPengajuan = Carbon::parse($transaksi->date . ' ' . $jamSelesaiAcuan);
            if ($jamSelesaiPengajuan->lessThan($jamMulai)) $jamSelesaiPengajuan->addDay();

            $batasAtas = $jamSelesaiPengajuan->lessThan($batasMaksimal)
                ? $jamSelesaiPengajuan : $batasMaksimal;

            $jamSelesaiFinal = $jamSelesaiPresensi->lessThan($batasAtas)
                ? $jamSelesaiPresensi : $batasAtas;

            $durasi = $jamMulai->diffInHours($jamSelesaiFinal);

            if ($durasi < 2) {
                DB::table('t_transaksi')->where('id_transaksi', $transaksi->id_transaksi)->update([
                    'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                    'eligible'              => 0,
                    'note'                  => ($transaksi->note ? $transaksi->note . ' — ' : '') . 'Durasi lembur presensi kurang dari 2 jam.',
                ]);
                continue;
            }

            $pelanggaranList = [];
            $statusPresensi  = strtolower(trim($presensi->status ?? ''));

            if (!in_array($statusPresensi, ['wfo', 'wfol'])) {
                $pelanggaranList[] = 'status bekerja tidak sesuai kebijakan';
            }

            if (!$isHariLibur) {
                $jamMasuk    = Carbon::parse($presensi->jam_mulai);
                $batasLambat = Carbon::parse($transaksi->date . ' 07:31:00');
                if (!$jamMasuk->lessThan($batasLambat)) {
                    $pelanggaranList[] = 'presensi kantor terlambat';
                }
            }

            $eligible  = empty($pelanggaranList) ? 1 : 0;
            $noteFinal = empty($pelanggaranList)
                ? ($transaksi->note ?? null)
                : 'Lembur disetujui namun tidak masuk proses bisnis karena: ' . implode(' dan ', $pelanggaranList) . '.';

            DB::table('t_transaksi')->where('id_transaksi', $transaksi->id_transaksi)->update([
                'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                'note'                  => $noteFinal,
                'eligible'              => $eligible,
            ]);
        }
    }
}
