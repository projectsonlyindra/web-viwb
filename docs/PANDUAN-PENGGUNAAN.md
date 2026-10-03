# Panduan Penggunaan VIWB

VIWB adalah aplikasi untuk mengelola administrasi perumahan: data warga, tagihan iuran (keamanan, kebersihan, HIPPAM, paguyuban), pembayaran, pengeluaran kas, dan pengumuman.

> **Catatan:** Aplikasi ini masih berjalan di lingkungan pengembangan (development), diakses lewat **http://localhost** dari komputer tempat aplikasi ini dijalankan. Belum bisa diakses dari HP atau komputer lain di luar mesin ini.

---

## 1. Cara Masuk (Login)

1. Buka browser, ketik alamat **http://localhost**
2. Klik tombol **Log in** di pojok kanan atas
3. Masukkan email dan password akun kamu (lihat daftar akun di bawah)
4. Klik **Log In**

### Akun Bawaan (Default)

Setiap peran (role) sudah punya akun contoh yang bisa langsung dipakai untuk mencoba aplikasi:

| Peran | Email | Password |
|---|---|---|
| **Super Admin** (akses penuh) | `superadmin@viwb.test` | `password` |
| **Ketua RT** | `ketuart@viwb.test` | `password` |
| **Bendahara** | `bendahara@viwb.test` | `password` |
| **Warga** (contoh) | `warga@viwb.test` | `password` |

> Password semua akun contoh di atas sama: **`password`**. Untuk pemakaian sungguhan, segera ganti password lewat menu **Profile** setelah login pertama kali.

---

## 2. Mengenal Peran (Role)

Aplikasi ini punya 5 jenis peran, masing-masing punya akses berbeda:

| Peran | Bisa Apa Saja |
|---|---|
| **Super Admin** | Akses penuh ke semua fitur: kelola data warga, generate tagihan, konfirmasi pembayaran, approve pengeluaran, buat pengumuman |
| **Ketua RT** | Approve/tolak pengeluaran, buat pengumuman, lihat semua laporan (tidak bisa entri pengeluaran sendiri) |
| **Bendahara** | Entri pengeluaran, konfirmasi/tolak pembayaran warga, generate tagihan bulanan (tidak bisa approve pengeluaran buatannya sendiri) |
| **Tim Divisi** | Lihat data sesuai divisinya (Keamanan/Kebersihan/HIPPAM/Paguyuban), menerima notifikasi tunggakan |
| **Warga** | Lihat tagihan & riwayat pembayaran milik sendiri, ajukan pembayaran, lihat pengumuman |

---

## 3. Panduan per Fitur

### 3.1 Data Warga (menu "Warga")

Berisi daftar seluruh warga beserta unit rumahnya (format: 1 huruf + 2 angka, contoh **A01**, **B12**).

- **Melihat daftar warga**: Super Admin, Ketua RT, Bendahara, dan Tim Divisi bisa melihat. Warga biasa tidak bisa melihat daftar warga lain.
- **Menambah/mengubah/menghapus warga**: hanya **Super Admin**.
- Bisa difilter berdasarkan blok (contoh: ketik "A" untuk lihat semua unit blok A) dan status (Aktif/Pindah/Kontrak).

**Cara menambah warga baru** (Super Admin):
1. Buka menu **Warga**
2. Klik **+ Tambah Warga**
3. Isi NIK (16 digit), nama, nomor WhatsApp, unit rumah (contoh: `C05`)
4. Pilih jenis kendaraan dan status warga
5. Centang layanan yang diikuti (HIPPAM / Kebersihan) jika ada
6. Klik **Simpan**

### 3.2 Tagihan (menu "Tagihan")

Tagihan bulanan otomatis dibuat oleh sistem untuk setiap warga aktif:
- **Keamanan** dan **Paguyuban**: wajib untuk semua warga
- **HIPPAM** dan **Kebersihan**: hanya untuk warga yang mengikuti layanan tersebut

Setiap tagihan yang telat dibayar akan otomatis dikenakan **denda harian** (dihitung otomatis, tidak perlu diinput manual) sampai batas denda maksimal.

**Cara generate tagihan bulanan** (Super Admin / Bendahara):
1. Buka menu **Tagihan**
2. Di bagian atas, pilih bulan-tahun yang ingin di-generate
3. Klik **Generate**
4. Sistem akan otomatis membuat tagihan untuk semua warga aktif sesuai layanan yang mereka ikuti

> Aman dijalankan berkali-kali untuk periode yang sama. Data yang sudah ada akan diperbarui, bukan diduplikasi.

**Warga** hanya bisa melihat tagihan miliknya sendiri di menu ini.

### 3.3 Pembayaran (menu "Pembayaran")

**Untuk Warga (mengajukan pembayaran):**
1. Buka menu **Pembayaran**
2. Centang tagihan yang mau dibayar (bisa lebih dari satu sekaligus)
3. (Opsional) unggah foto bukti transfer
4. (Opsional) isi catatan
5. Klik **Ajukan Pembayaran**
6. Status akan menjadi **Menunggu Konfirmasi** sampai diperiksa Bendahara/Super Admin

**Untuk Bendahara / Super Admin (konfirmasi pembayaran):**
1. Buka menu **Pembayaran**
2. Cari pembayaran berstatus **Menunggu Konfirmasi**
3. Klik **Konfirmasi** jika bukti transfer sudah benar → tagihan otomatis berubah jadi **Lunas**
4. Klik **Tolak** jika bukti tidak sesuai, lalu isi alasan penolakan

### 3.4 Pengeluaran (menu "Pengeluaran")

Alur pencatatan kas keluar (misal: bayar listrik, kebersihan, dll) melewati 2 tahap persetujuan:

```
Bendahara entri  →  Draft  →  Ajukan  →  Menunggu Approval  →  Ketua RT/Super Admin approve/tolak  →  Approved / Rejected
```

- **Super Admin** yang entri sendiri → langsung berstatus **Approved** (tidak perlu approval lagi).
- **Ketua RT** tidak bisa entri pengeluaran, hanya bisa approve/tolak punya orang lain.
- Pengguna **tidak bisa approve pengeluaran buatannya sendiri**, harus disetujui oleh pengurus lain.

**Cara mencatat pengeluaran** (Bendahara):
1. Buka menu **Pengeluaran**
2. Klik **+ Tambah**
3. Isi kategori (contoh: "Listrik"), nominal, tanggal, dan keterangan
4. (Opsional) unggah bukti/nota
5. Klik **Simpan** → status awal **Draft**
6. Klik **Ajukan** pada baris tersebut untuk mengirim ke tahap approval

**Cara approve pengeluaran** (Ketua RT / Super Admin):
1. Buka menu **Pengeluaran**
2. Cari pengeluaran berstatus **Menunggu Approval** (yang bukan buatan sendiri)
3. Klik **Approve** untuk menyetujui, atau **Tolak** untuk menolak (isi alasan)

### 3.5 Pengumuman (menu "Pengumuman")

**Untuk Ketua RT / Super Admin (membuat pengumuman):**
1. Buka menu **Pengumuman**
2. Klik **+ Buat Pengumuman**
3. Isi judul dan isi pengumuman
4. (Opsional) isi **Target Blok** dengan 1 huruf (contoh: `A`) untuk kirim khusus ke blok tersebut (kosongkan untuk kirim ke **semua warga**)
5. Klik **Kirim & Antrekan Broadcast**

Pesannya akan otomatis dikirim ke nomor WhatsApp warga target secara bertahap oleh sistem di latar belakang (tidak perlu klik apa pun lagi setelah pengumuman dibuat).

> **Catatan:** Selama masa pengembangan, pengiriman WhatsApp masih dalam **mode simulasi (dummy)**. Pesan tercatat di sistem tapi belum benar-benar terkirim ke WhatsApp asli. Ini akan diaktifkan setelah WAHA (penyedia layanan WhatsApp) disiapkan.

Semua warga (termasuk peran Warga) bisa melihat daftar pengumuman.

### 3.6 Dashboard

Halaman pertama setelah login. Menampilkan ringkasan bulan berjalan:
- Jumlah warga aktif
- Jumlah & total nominal tagihan bulan ini
- Total yang sudah dibayar bulan ini
- Jumlah pengeluaran yang masih menunggu approval
- **Alert Tunggakan**: daftar warga yang menunggak lebih dari batas tertentu per jenis layanan

### 3.7 Laporan

Menu **Laporan** menampilkan rekap tagihan dan pengeluaran untuk bulan yang dipilih.

- Pilih bulan di bagian atas, klik **Tampilkan**
- Klik **Cetak** untuk mencetak langsung dari browser (tampilan otomatis dirapikan untuk kertas)
- Klik **Export CSV** untuk mengunduh data dalam format spreadsheet (bisa dibuka di Excel/Google Sheets)

---

## 4. Istilah-Istilah

| Istilah | Artinya |
|---|---|
| **Unit** | Kode rumah warga, format 1 huruf + 2 angka (contoh: A01) |
| **Periode** | Bulan tagihan, format Tahun-Bulan (contoh: 2026-09 = September 2026) |
| **Denda** | Biaya tambahan otomatis karena telat bayar tagihan, dihitung per hari sampai batas maksimal |
| **Status Tagihan** | Belum Bayar / Sebagian / Lunas |
| **Status Pembayaran** | Menunggu Konfirmasi / Dikonfirmasi / Ditolak |
| **Status Pengeluaran** | Draft / Menunggu Approval / Approved / Rejected |
| **Broadcast** | Pengiriman pesan WhatsApp ke banyak warga sekaligus |
| **HIPPAM** | Himpunan Pemakai Air Minum (layanan air bersih warga) |

---

## 5. Pertanyaan Umum

**Q: Saya lupa password, bagaimana?**
A: Untuk sementara ini belum ada fitur "lupa password" yang aktif (butuh konfigurasi email terlebih dahulu). Hubungi Super Admin untuk reset manual lewat sistem.

**Q: Kenapa tombol tertentu tidak muncul / tidak bisa saya klik?**
A: Setiap peran punya akses berbeda (lihat bagian [2. Mengenal Peran](#2-mengenal-peran-role)). Jika suatu tombol tidak muncul, kemungkinan besar peran kamu memang tidak diizinkan melakukan aksi tersebut.

**Q: Apakah data yang saya buat di sini aman/sungguhan?**
A: Aplikasi saat ini masih di tahap pengembangan (development). Data uji coba bisa saja dihapus/direset saat pengembangan berlanjut. Jangan pakai data pribadi sungguhan dulu sebelum aplikasi dinyatakan siap produksi.

**Q: Bagaimana cara logout?**
A: Klik nama kamu di pojok kanan atas → **Log Out**.
