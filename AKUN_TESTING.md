# 🔑 Daftar Akun Testing Cepat - Sistem Lembur BPS

Gunakan link di bawah ini untuk langsung masuk (*auto-login*) ke sistem tanpa perlu memasukkan password SSO. Cukup **klik link** pada masing-masing akun.

> [!NOTE]
> Pastikan server lokal sudah menyala (`php artisan serve` di port 8000 dan `npm run dev`).

---

## 🌐 1. Akun Tim SID (Sistem Informasi dan Diseminasi)

### A. Ketua Tim SID
* **Nama**: **Sumbodo Aji Cahyono S.Si., M.A**
* **NIP**: `197703081999011001`
* **Role**: `ketua_tim`
* **Wewenang**: Menyetujui lembur anggota tim SID (Status akan berubah menjadi `Menunggu Kabag`) atau menolak (`Ditolak`).
* 🔗 **Link Login**: [http://127.0.0.1:8000/dev-login/197703081999011001](http://127.0.0.1:8000/dev-login/197703081999011001)

### B. Pegawai Tim SID (3 Akun)
| No | Nama Pegawai | NIP | Role | Link Login Langsung |
|:---:|:---|:---|:---:|:---|
| 1 | **Pristiana Diah Ariyantika SST, M.M.** | `198907112010122003` | Pegawai | [👉 Login Pristiana](http://127.0.0.1:8000/dev-login/198907112010122003) |
| 2 | **Indah Purnamasari S.E** | `198501252006042001` | Pegawai | [👉 Login Indah](http://127.0.0.1:8000/dev-login/198501252006042001) |
| 3 | **Herry Kusmaiwanto S.Si** | `197205242006041002` | Pegawai | [👉 Login Herry](http://127.0.0.1:8000/dev-login/197205242006041002) |

---

## 🏛️ 2. Akun Tim Bagian Umum & Kabag Umum

### A. Kabag Umum / Ketua Tim Bagian Umum
* **Nama**: **Joko Suwarjo S.Si, M.Si**
* **NIP**: `197106131993121001`
* **Role**: `ketua_tim` (Ketua Tim Bagian Umum) & Pejabat `Kepala Bagian Umum`
* **Wewenang**: 
  - Menu **Persetujuan Kabag Umum**: Menu tunggal terpadu untuk menyetujui lembur seluruh tim (pengajuan Tim Bagian Umum langsung masuk ke antrean ini, bersanding dengan pengajuan tim lain yang sudah di-ACC Ketua Tim masing-masing).
* 🔗 **Link Login**: [http://127.0.0.1:8000/dev-login/197106131993121001](http://127.0.0.1:8000/dev-login/197106131993121001)

### B. Pegawai Tim Bagian Umum (3 Akun)
| No | Nama Pegawai | NIP | Role | Link Login Langsung |
|:---:|:---|:---|:---:|:---|
| 1 | **Ika Budi Ambaryanti SE** | `198110052006042035` | Pegawai | [👉 Login Ika](http://127.0.0.1:8000/dev-login/198110052006042035) |
| 2 | **Istiqomah SST** | `197509231998032001` | Pegawai | [👉 Login Istiqomah](http://127.0.0.1:8000/dev-login/197509231998032001) |
| 3 | **Aning Widiyatmi SE** | `197412171998032004` | Pegawai | [👉 Login Aning](http://127.0.0.1:8000/dev-login/197412171998032004) |

---

## 🧪 3. Skenario Pengujian Alur Baru (Step-by-Step)

### Skenario 1: Uji Alur Bertingkat (Tim SID)
1. **Ajukan Lembur:**
   - Klik link login **[Pristiana](http://127.0.0.1:8000/dev-login/198907112010122003)** (Pegawai SID).
   - Masuk ke menu **Lembur**, klik **Ajukan Lembur**, isi formulir, dan simpan. Status awal: `Menunggu Ketua`.
2. **Persetujuan Ketua Tim:**
   - Klik link login **[Sumbodo](http://127.0.0.1:8000/dev-login/197703081999011001)** (Ketua Tim SID).
   - Masuk ke menu **Pengajuan Lembur**, cari pengajuan Pristiana.
   - Klik tombol edit (pensil), pilih **Setujui** dan **Simpan**.
   - Perhatikan: Status pengajuan berubah menjadi **`Menunggu Kabag`** (Badge Biru)!
3. **Persetujuan Akhir Kabag Umum:**
   - Klik link login **[Joko Suwarjo](http://127.0.0.1:8000/dev-login/197106131993121001)** (Kabag Umum).
   - Di sidebar, buka menu: **`Persetujuan Kabag Umum`**.
   - Pengajuan Pristiana akan muncul di tabel ini.
   - Klik tombol **Proses**, pilih **Setujui Final** (atau **Tolak** dengan alasan pada kolom Catatan Kabag Umum).
   - Klik **Simpan Keputusan**. Status resmi menjadi **`Disetujui Final`** (atau `Ditolak`).

---

### Skenario 2: Uji Alur Tim Bagian Umum (Langsung ke Antrean Kabag Umum)
1. **Ajukan Lembur:**
   - Klik link login **[Ika Budi](http://127.0.0.1:8000/dev-login/198110052006042035)** (Pegawai Bagian Umum).
   - Masuk ke menu **Lembur**, ajukan lembur dan simpan.
   - Perhatikan: Status pengajuan **langsung `Menunggu Kabag`** (tanpa menunggu tahap ketua tim, karena Ketua Timnya adalah Kabag Umum sendiri).
2. **Persetujuan oleh Kabag Umum:**
   - Klik link login **[Joko Suwarjo](http://127.0.0.1:8000/dev-login/197106131993121001)**.
   - Di sidebar, buka menu **`Persetujuan Kabag Umum`** (tab *Menunggu Kabag*).
   - Cari pengajuan Ika Budi, klik **Proses** $\rightarrow$ **Setujui**.
   - Status pengajuan resmi menjadi **`Disetujui Final`**!
