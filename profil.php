<?php
session_start();

// Cek apakah pengguna sudah login melalui session
if (isset($_SESSION['pengguna'])) {
    $pengguna = $_SESSION['pengguna'];
} else {
    // Data dummy default jika diakses langsung tanpa login
    $pengguna = [
        'username'    => 'admin',
        'email'       => 'admin@tokoberkah.id',
        'nama'        => 'Budi Santoso',
        'nama_usaha'  => 'Toko Berkah Sentosa',
        'kategori'    => 'Ritel & Kelontong Modern',
        'no_hp'       => '0812-3456-7890',
        'alamat'      => 'Jl. Merdeka No. 45, Bandung',
        'bergabung'   => 'Januari 2024',
        'role'        => 'Pemilik Usaha (Owner)'
    ];
}

// Inisial untuk avatar
$nama_arr = explode(' ', trim($pengguna['nama']));
$inisial = strtoupper(substr($nama_arr[0], 0, 1) . (isset($nama_arr[1]) ? substr($nama_arr[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profil Pengguna - LabaPro</title>
  
  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  
  <!-- File CSS Terpisah -->
  <link rel="stylesheet" href="dasboard.css"/>
</head>
<body>

  <!-- ================= TOP HEADER TEXT ================= -->
  <div class="top-header-text container-fluid">
    DASHBOARD
  </div>

  <!-- ================= NAVBAR ================= -->
  <header class="navbar-header container-fluid">
    <!-- Kiri: Logo LabaPro -->
    <a href="dasboard.php" class="brand-logo">
      <div class="brand-icon">
        <span class="material-symbols-outlined">all_inclusive</span>
      </div>
      <span class="brand-name">LabaPro</span>
    </a>

    <!-- Kanan: Segmented Control & Button -->
    <div class="nav-actions">
      <!-- Segmented Control Pill -->
      <div class="segmented-control">
        <a href="dasboard.php" class="segment-btn">Transaksi</a>
        <a href="profil.php" class="segment-btn active">Profil</a>
        <a href="login.php" class="segment-btn" title="Keluar dari akun">Keluar</a>
      </div>

      <!-- Button Kembali ke Dashboard -->
      <a href="dasboard.php" class="btn-primary" style="text-decoration: none;">
        <span class="material-symbols-outlined">arrow_back</span>
        <span>Kembali ke Transaksi</span>
      </a>
    </div>
  </header>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="dashboard-main container">
    <section class="section-header">
      <h2 class="section-title">Profil Pengguna</h2>
      <span class="section-subtitle">Data akun dan informasi bisnis yang terhubung saat login</span>
    </section>

    <!-- KARTU HERO PROFIL UTAMA -->
    <div class="profile-hero-card">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          <?php echo htmlspecialchars($inisial); ?>
        </div>
        <div class="profile-online-badge" title="Status Online"></div>
      </div>

      <div class="profile-hero-info">
        <div class="profile-title-row">
          <h1 class="profile-name"><?php echo htmlspecialchars($pengguna['nama']); ?></h1>
          <span class="badge-role">
            <span class="material-symbols-outlined icon-small">verified</span>
            <?php echo htmlspecialchars($pengguna['role']); ?>
          </span>
        </div>
        <p class="profile-business">
          <span class="material-symbols-outlined icon-small">storefront</span>
          <strong><?php echo htmlspecialchars($pengguna['nama_usaha']); ?></strong>
          <span class="bullet-sep">&bull;</span>
          <span><?php echo htmlspecialchars($pengguna['kategori']); ?></span>
        </p>
      </div>

      <div class="profile-hero-actions">
        <a href="login.php" class="btn-logout" title="Keluar dari akun">
          <span class="material-symbols-outlined">logout</span>
          <span>Logout</span>
        </a>
      </div>
    </div>

  </main>

</body>
</html>
