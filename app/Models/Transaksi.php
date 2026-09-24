<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    public $timestamps = false;
    protected $table = 't_transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'submitted_by_NIP',
        'date',
        'jam_mulai',
        'jam_selesai',
        'jam_mulai_disetujui',
        'jam_selesai_disetujui',
        'uraian',
        'approver_employee_id',
        'tim_kode_tim',
        'status',
        'submitted_at',
        'hari',
        'note',
        'note_kabag',
        'approved_at',
        'approved_kabag_at',
    ];

    // Hitung durasi dalam jam (floor ke bawah)
    // Pakai jam_disetujui kalau sudah ada, fallback ke jam pengajuan
    public function getDurasiDiajukanAttribute(): ?int
    {
        if (!$this->jam_mulai || !$this->jam_selesai) return null;
        $mulai = strtotime($this->jam_mulai);
        $selesai = strtotime($this->jam_selesai);
        return (int) floor(($selesai - $mulai) / 3600);
    }

    public function getDurasiDisetujuiAttribute(): ?int
    {
        if (!$this->jam_mulai_disetujui || !$this->jam_selesai_disetujui) return null;
        $mulai = strtotime($this->jam_mulai_disetujui);
        $selesai = strtotime($this->jam_selesai_disetujui);
        return (int) floor(($selesai - $mulai) / 3600);
    }

    // Format jam untuk tampilan: "16:01 - 18:00"
    public function getJamDiajukanLabelAttribute(): string
    {
        if (!$this->jam_mulai || !$this->jam_selesai) return '-';
        return substr($this->jam_mulai, 0, 5) . ' - ' . substr($this->jam_selesai, 0, 5);
    }

    public function getJamDisetujuiLabelAttribute(): string
    {
        if (!$this->jam_mulai_disetujui || !$this->jam_selesai_disetujui) return '-';
        return substr($this->jam_mulai_disetujui, 0, 5) . ' - ' . substr($this->jam_selesai_disetujui, 0, 5);
    }
}
