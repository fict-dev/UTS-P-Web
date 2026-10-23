# LabaPro - Sistem Monitoring Finansial UMKM
> **Proyek Ujian Tengah Semester (UTS) Pemrograman Web 1**  
> Aplikasi web manajemen dan pembukuan finansial modern untuk pelaku UMKM, dibangun menggunakan **PHP Native**, **MySQL (PDO)**, **HTML5 Semantic**, **CSS3 Vanilla**, dan **JavaScript Native (ES6+)**.

---

## 📌 Gambaran Umum Proyek

**LabaPro** (Monitoring UMKM) adalah solusi pembukuan digital praktis yang dirancang untuk membantu pemilik usaha mencatat modal, memantau omzet harian, menganalisis laba bersih secara otomatis, serta memproyeksikan performa finansial usaha ke depan tanpa ketergantungan framework berat.

Aplikasi ini mencakup alur lengkap:
1. **Landing Page**: Informasi produk, statistik transaksi, ulasan mitra, dan modal interaktif.
2. **Autentikasi (Login)**: Keamanan berbasis session dengan enkripsi kata sandi `password_hash()` (Bcrypt).
3. **Dashboard Finansial**: Rekapitulasi modal, omzet, margin keuntungan net, status untung/rugi, estimasi pertumbuhan usaha, serta tabel riwayat transaksi.
4. **Pencatatan Transaksi**: Input dinamis omzet per hari (1–30 hari), perhitungan laba/rugi otomatis, dan penyimpanan transaksi ke database via AJAX.
5. **Delete Histori**: Fitur menghapus transaksi per baris maupun membersihkan seluruh histori transaksi sekaligus.
6. **Profil Pengguna**: Informasi identitas pengelola dan rincian bisnis UMKM.

---

## 📁 Struktur Direktori & File

```text
UTS Pemrograman web/
│
├── index.html              # Halaman Landing Page utama (HTML5 Semantic)
├── login.php               # Halaman Login & autentikasi pengguna berbasis session PHP
├── dasboard.php            # Dashboard Finansial, rekap statistik & tabel riwayat transaksi
├── Tambah_Transaksi.php    # Form pencatatan transaksi baru & kalkulator omzet dinamis
├── profil.php              # Halaman profil identitas pengguna & usaha
│
├── db.php                  # Koneksi database MySQL menggunakan PDO
├── init_db.sql             # Skema tabel database (users, transaksi, omzet_hari) & data awal
│
├── dasboard.css            # Desain stylesheet utama Dashboard, Form Transaksi & Profil
├── style.css               # Desain stylesheet khusus Halaman Login
├── script.js               # Logika frontend halaman login (toggle password, interaktivitas)
│
├── css/
│   └── style.css           # Desain stylesheet khusus Landing Page (Flexbox, Grid, Variabel)
├── js/
│   └── script.js           # Script interaktif Landing Page (Hamburger menu, Modal popup)
├── asset/
│  └── Logo.png            # Logo resmi aplikasi LabaPro / Monitoring UMKM
│
├──style.css               # design khusus untuk file .php
│
└── README.md               # Dokumentasi lengkap proyek
```

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x (Native) | Logika server, Session, PDO Transactions, Prepared Statements |
| **Database** | MySQL / MariaDB | Relasi tabel dengan Foreign Key & `ON DELETE CASCADE` |
| **Frontend Layout** | HTML5 Semantic | Struktur halaman ramah SEO & aksesibilitas |
| **Styling** | Vanilla CSS3 | Modern Emerald Palette, Responsive Grid, Flexbox, Glassmorphism |
| **Frontend Logic** | Vanilla JavaScript | Manipulasi DOM, AJAX fetch, validasi, konfirmasi dialog |
| **Typography & Ikon** | Google Fonts & Material Symbols | Font *Plus Jakarta Sans*, *Poppins*, *Inter*, & Google Material Symbols |

---

## ✨ Fitur-Fitur Utama

### 1. Landing Page (`index.html`)
- **Hero Section**: Tagline persuasif, CTA menuju pendaftaran/login, dan review sosial.
- **Statistik & Metrik**: Tampilan pencapaian transaksi dan kepuasan mitra UMKM.
- **Fitur Unggulan**: Kartu penjelasan pencatatan omzet, modal, margin untung/rugi, dan laporan.
- **Testimoni**: Ulasan nyata dari pelaku usaha UMKM.
- **Modal Interaktif**: Dialog formulir kontak/pendaftaran cepat dengan penutupan via tombol `ESC` atau klik luar.

### 2. Autentikasi Pengguna (`login.php`, `style.css`, `script.js`)
- Validasi username dan kata sandi terhadap database MySQL menggunakan `password_verify()`.
- Pengelolaan status autentikasi melalui `$_SESSION['pengguna']`.
- Fitur interaktif *Toggle Show/Hide Password* (ikon mata).
- Pesan kesalahan login yang informatif.

### 3. Dashboard Finansial (`dasboard.php`, `dasboard.css`)
- **Kartu Rekap Finansial**: Total transaksi, akumulasi modal, total omzet, dan status *Untung* / *Rugi*.
- **Proyeksi & Analisis Bisnis**: Perhitungan otomatis rata-rata omzet, estimasi bulan berikutnya (+20%), margin keuntungan net, dan indikator kesehatan arus kas.
- **Tabel Riwayat Transaksi**: Menampilkan tanggal, modal, omzet, durasi hari, dan hasil keuntungan.
- **Fitur Hapus Histori (Delete)**:
  - **Hapus per Transaksi**: Tombol hapus di setiap baris tabel disertai dialog konfirmasi JavaScript.
  - **Hapus Semua Histori**: Tombol untuk membersihkan semua data transaksi sekaligus.
  - **Cascade Delete**: Menghapus data rincian di tabel `omzet_hari` secara otomatis dan aman melalui database transaction.
  - **Notifikasi Banner**: Feedback flash alert (sukses/gagal) setelah tindakan penghapusan.

### 4. Tambah Transaksi Dinamis (`Tambah_Transaksi.php`)
- Input modal awal usaha.
- Pembuat form omzet harian otomatis sesuai jumlah hari yang dipilih (1 hingga 30 hari).
- Kalkulasi realtime total omzet, selisih laba/rugi, serta status performa sebelum disimpan.
- Penyimpanan data ke database secara asynchronous menggunakan AJAX POST dengan respon JSON.
- Tampilan rekap invoice yang rapi setelah transaksi berhasil dibuat.

### 5. Profil Pengguna (`profil.php`)
- Informasi pengguna yang terhubung dengan session aktif.
- Rincian profil bisnis: nama usaha, kategori, kontak WhatsApp/HP, alamat operasional, dan tanggal bergabung.

---

## 🗄️ Struktur Database (`init_db.sql`)

Database bernama `laba_pro` terdiri dari 3 tabel berelasi:

1. **`users`**:
   - `id`: INT UNSIGNED AUTO_INCREMENT (Primary Key)
   - `username`: VARCHAR(50) UNIQUE
   - `password_hash`: VARCHAR(255)
   - `nama_lengkap`: VARCHAR(100)
   - `created_at`: TIMESTAMP

2. **`transaksi`**:
   - `id`: INT UNSIGNED AUTO_INCREMENT (Primary Key)
   - `user_id`: INT UNSIGNED (Foreign Key `users.id` ON DELETE CASCADE)
   - `modal`: DECIMAL(15,2)
   - `total_omzet`: DECIMAL(15,2)
   - `hasil`: DECIMAL(15,2)
   - `created_at`: TIMESTAMP

3. **`omzet_hari`**:
   - `id`: INT UNSIGNED AUTO_INCREMENT (Primary Key)
   - `transaksi_id`: INT UNSIGNED (Foreign Key `transaksi.id` ON DELETE CASCADE)
   - `hari_ke`: TINYINT UNSIGNED
   - `omzet`: DECIMAL(15,2)

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Prasyarat Sistem
- Web Server & Database: **XAMPP** (Apache & MySQL) atau PHP 8.x + MySQL Server terpasang.
- Web Browser modern (Google Chrome, Mozilla Firefox, Microsoft Edge).

### 2. Konfigurasi Database
1. Buka browser dan akses **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Buat database baru atau langsung buka tab **Import**.
3. Pilih dan impor file **`init_db.sql`** yang ada di folder proyek.
4. Database `laba_pro` beserta tabel dan data dummy awal akan dibuat secara otomatis.

> Pengaturan koneksi database ada pada file [db.php](file:///k:/Tugas%20Kuliah/Pemrograman%20web%201/UTS%20Pemrograman%20web/db.php):
> ```php
> $host = '127.0.0.1';
> $db   = 'laba_pro';
> $user = 'root';
> $pass = ''; // Default XAMPP tanpa password
> ```

### 3. Menjalankan Server Web

#### Opsi A: Menggunakan XAMPP (Direkomendasikan)
1. Pindahkan atau salin folder proyek `UTS Pemrograman web` ke direktori `htdocs`:
   ```text
   C:\xampp\htdocs\UTS Pemrograman web\
   ```
2. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.
3. Buka browser dan akses URL:
   ```text
   http://localhost/UTS%20Pemrograman%20web/
   ```

#### Opsi B: Menggunakan PHP Built-in Server
1. Buka Terminal / PowerShell di folder proyek ini (`k:\Tugas Kuliah\Pemrograman web 1\UTS Pemrograman web`).
2. Pastikan MySQL di XAMPP sudah berjalan (*Start* MySQL).
3. Jalankan perintah:
   ```bash
   php -S localhost:8000
   ```
4. Buka browser dan akses:
   ```text
   http://localhost:8000/index.html
   ```

---

## 🔑 Akun Login Pengujian (Default)

Data pengguna uji coba yang telah disediakan di database (`init_db.sql`):

| Username | Password | Nama Lengkap | Peran |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Admin LabaPro | Administrator / Owner |
| **`user`** | `user123` | Pengguna LabaPro | Pengelola Keuangan |

---

## 👨‍💻 Pengembang
- **Mata Kuliah**: Pemrograman Web 1 (Tugas / UTS)
- **Aplikasi**: LabaPro - Solusi Monitoring Finansial UMKM
