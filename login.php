<?php
session_start();
require_once __DIR__ . '/db.php';   // Koneksi PDO ke database laba_pro

$pesan_error = '';

// ==============================================================================
// PROSES AUTENTIKASI MENGGUNAKAN DATABASE
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_nama = trim($_POST['nama_lengkap'] ?? '');
    $input_user = trim($_POST['username'] ?? '');
    $input_pass = $_POST['password'] ?? '';

    // Cari user berdasarkan username di tabel users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $input_user]);
    $user_db = $stmt->fetch();

    // Verifikasi password dengan password_verify() (hash bcrypt)
    if ($user_db && password_verify($input_pass, $user_db['password_hash'])) {
        // Login berhasil – simpan data user ke session
        $_SESSION['pengguna'] = [
            'id'         => $user_db['id'],
            'username'   => $user_db['username'],
            'nama'       => !empty($input_nama) ? $input_nama : $user_db['nama_lengkap'],
            'nama_usaha' => 'LabaPro UMKM',
            'kategori'   => 'Usaha Mikro',
            'no_hp'      => '-',
            'alamat'     => '-',
            'bergabung'  => date('F Y', strtotime($user_db['created_at'])),
            'role'       => 'Pemilik Usaha',
        ];
        header('Location: dasboard.php');
        exit;
    } else {
        $pesan_error = 'Username atau kata sandi salah! Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LabaPro - Login</title>

  <!-- Font Google: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- File CSS Eksternal -->
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ======= KARTU LOGIN UTAMA ======= -->
  <div class="kartu-login">

    

    <!-- 2. JUDUL DAN SUBJUDUL -->
    <h1 class="judul">Selamat Datang</h1>
    <p class="sub-judul">Masuk untuk memantau perkembangan usahamu hari ini.</p>

    <!-- KOTAK NOTIFIKASI ERROR (JIKA LOGIN GAGAL) -->
    <div id="alertBox" class="alert-error" style="<?php echo !empty($pesan_error) ? 'display: flex;' : 'display: none;'; ?>">
      <span>⚠️</span>
      <span id="alertMessage"><?php echo htmlspecialchars($pesan_error ?: 'Username atau kata sandi salah!'); ?></span>
    </div>

    <!-- 3. FORM LOGIN -->
    <form id="formLogin" method="POST" action="login.php" onsubmit="return prosesLogin(event)">

      <!-- Input Nama Lengkap (untuk Halaman Profil) -->
      <div class="form-grup">
        <label for="inputNamaLengkap">
          Nama Lengkap <span class="wajib">*</span>
        </label>
        <div class="input-box">
          <span class="ikon-kiri">😉</span>
          <input
            type="text"
            id="inputNamaLengkap"
            name="nama_lengkap"
            placeholder="super dardi"
            required
            autocomplete="name"
          >
        </div>
      </div>

      <!-- Input Username / Email -->
      <div class="form-grup">
        <label for="inputUsername">
          Username atau Email <span class="wajib">*</span>
        </label>
        <div class="input-box">
          <span class="ikon-kiri">👤</span>
          <input
            type="text"
            id="inputUsername"
            name="username"
            placeholder="admin"
            required
            autocomplete="username"
          >
        </div>
      </div>

      <!-- Input Kata Sandi -->
      <div class="form-grup">
        <div class="label-row">
          <label for="inputSandi">
            Kata Sandi <span class="wajib">*</span>
          </label>
          <a href="#" class="link-lupa" onclick="alert('Gunakan akun demo:\nUsername: admin\nPassword: admin123'); return false;">Lupa kata sandi?</a>
        </div>
        <div class="input-box">
          <span class="ikon-kiri">🛠️</span>
          <input
            type="password"
            id="inputSandi"
            name="password"
            placeholder="admin123"
            minlength="4"
            required
            autocomplete="current-password"
          >
          <!-- Tombol tampilkan/sembunyikan password -->
          <button type="button" class="tombol-mata" onclick="togglePassword()" aria-label="Lihat kata sandi">
            <span id="ikonMata">👁</span>
          </button>
        </div>
      </div>

      <!-- Checkbox Ingat Saya -->
      <div class="baris-ingat">
        <input type="checkbox" id="ingatSaya" name="remember">
        <label for="ingatSaya">Ingat saya di perangkat ini</label>
      </div>

      <!-- Tombol Masuk ke Dashboard -->
      <button type="submit" class="tombol-masuk">
        <span>↩</span> Masuk ke Dashboard
      </button>

    </form>

    <!-- 7. KEMBALI KE BERANDA -->
    <a href="index.html" class="kembali-beranda">&larr; Kembali ke Beranda</a>

  </div>

  <!-- File JavaScript Eksternal -->
  <script src="script.js"></script>

</body>
</html>
