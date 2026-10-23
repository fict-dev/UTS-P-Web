<?php
session_start();

// Guard: harus login
if (empty($_SESSION['pengguna'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$nama_user = $_SESSION['pengguna']['nama'] ?? 'Pengelola';
$user_id   = $_SESSION['pengguna']['id']   ?? 0;

// ============================================================
// FITUR DELETE HISTORI TRANSAKSI
// ============================================================

// 1. Hapus satu transaksi berdasarkan ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $hapus_id = intval($_POST['hapus_id']);
    try {
        $pdo->beginTransaction();

        // Hapus data anak di omzet_hari terlebih dahulu
        $delHari = $pdo->prepare("DELETE FROM omzet_hari WHERE transaksi_id = :id");
        $delHari->execute([':id' => $hapus_id]);

        // Hapus data transaksi utama (wajib cek user_id untuk keamanan hak akses)
        $delTrx = $pdo->prepare("DELETE FROM transaksi WHERE id = :id AND user_id = :user_id");
        $delTrx->execute([':id' => $hapus_id, ':user_id' => $user_id]);

        if ($delTrx->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Transaksi berhasil dihapus dari riwayat!'
            ];
        } else {
            $pdo->rollBack();
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'text' => 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.'
            ];
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Gagal menghapus transaksi: ' . $e->getMessage()
        ];
    }
    header('Location: dasboard.php');
    exit;
}

// 2. Hapus semua riwayat transaksi milik user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_semua'])) {
    try {
        $pdo->beginTransaction();

        // Hapus seluruh omzet_hari yang terkait dengan transaksi user ini
        $delAllHari = $pdo->prepare("
            DELETE FROM omzet_hari 
            WHERE transaksi_id IN (SELECT id FROM transaksi WHERE user_id = :user_id)
        ");
        $delAllHari->execute([':user_id' => $user_id]);

        // Hapus seluruh transaksi user
        $delAllTrx = $pdo->prepare("DELETE FROM transaksi WHERE user_id = :user_id");
        $delAllTrx->execute([':user_id' => $user_id]);

        $pdo->commit();
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Semua riwayat transaksi berhasil dihapus!'
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Gagal menghapus semua riwayat: ' . $e->getMessage()
        ];
    }
    header('Location: dasboard.php');
    exit;
}

// Ambil transaksi milik user ini (terbaru di atas)
$stmt = $pdo->prepare("
    SELECT t.id, t.modal, t.total_omzet, t.hasil, t.created_at,
           COUNT(oh.id) AS jumlah_hari
    FROM transaksi t
    LEFT JOIN omzet_hari oh ON oh.transaksi_id = t.id
    WHERE t.user_id = :user_id
    GROUP BY t.id
    ORDER BY t.created_at DESC
");
$stmt->execute([':user_id' => $user_id]);
$riwayat = $stmt->fetchAll();

// Statistik ringkasan
$stmtStats = $pdo->prepare("
    SELECT COUNT(*) AS total_transaksi,
           COALESCE(SUM(total_omzet), 0) AS total_omzet_all,
           COALESCE(SUM(modal), 0)       AS total_modal_all,
           COALESCE(SUM(hasil), 0)       AS total_hasil_all
    FROM transaksi
    WHERE user_id = :user_id
");
$stmtStats->execute([':user_id' => $user_id]);
$stats = $stmtStats->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Finansial - LabaPro</title>
  
  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
  
  <!-- File CSS Terpisah -->
  <link rel="stylesheet" href="dasboard.css"/>
</head>
<body>

  <!-- ================= TOP HEADER TEXT ================= -->
  <div class="top-header-text container-fluid">
    DASHBOARD &bull; Halo, <?php echo htmlspecialchars($nama_user); ?>
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
        <a href="dasboard.php" class="segment-btn active">Transaksi</a>
        <a href="profil.php" class="segment-btn">Profil</a>
        <a href="login.php" class="segment-btn" title="Keluar dari akun">Keluar</a>
      </div>

      <!-- Button Transaksi Baru -->
      <a href="Tambah_Transaksi.php" class="btn-primary" style="text-decoration: none;">
        <span class="material-symbols-outlined">add_circle</span>
        <span>+ Transaksi Baru</span>
      </a>
    </div>
  </header>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="dashboard-main container">

    <!-- Flash Alert Notifikasi -->
    <?php if (isset($_SESSION['flash_message'])): ?>
      <div class="alert-banner alert-<?php echo htmlspecialchars($_SESSION['flash_message']['type']); ?>" id="flashAlert">
        <div class="alert-content">
          <span class="material-symbols-outlined">
            <?php echo $_SESSION['flash_message']['type'] === 'success' ? 'check_circle' : 'error'; ?>
          </span>
          <span><?php echo htmlspecialchars($_SESSION['flash_message']['text']); ?></span>
        </div>
        <button type="button" class="alert-close" onclick="document.getElementById('flashAlert').remove();" title="Tutup">&times;</button>
      </div>
      <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <section class="section-header">
      <h2 class="section-title">Ringkasan Finansial</h2>
      <span class="section-subtitle">Rekapitulasi performa bisnis dari semua transaksi Anda</span>
    </section>

    <!-- Cards Grid Container -->
    <div class="cards-grid">
      
      <!-- KARTU 1: Rekap Total dari DB -->
      <div class="calc-card">
        <div class="card-header">
          <span class="card-tag">Rekap Finansial</span>
          <h3 class="card-heading">Total dari <span><?php echo $stats['total_transaksi']; ?> Transaksi</span></h3>
        </div>

        <div class="card-body">
          <div class="calc-row">
            <span class="label">Total Modal</span>
            <span class="value font-semibold">Rp <?php echo number_format($stats['total_modal_all'], 0, ',', '.'); ?></span>
          </div>
          <div class="calc-row">
            <span class="label">Total Omzet</span>
            <span class="value font-semibold">Rp <?php echo number_format($stats['total_omzet_all'], 0, ',', '.'); ?></span>
          </div>

          <div class="divider"></div>

          <?php if ($stats['total_hasil_all'] >= 0): ?>
          <div class="status-box status-profit">
            <div class="status-info">
              <span class="status-label">Status Keseluruhan:</span>
              <span class="status-badge untung">Untung ✅</span>
            </div>
            <div class="status-amount profit-text">+ Rp <?php echo number_format($stats['total_hasil_all'], 0, ',', '.'); ?></div>
          </div>
          <?php else: ?>
          <div class="status-box status-loss">
            <div class="status-info">
              <span class="status-label">Status Keseluruhan:</span>
              <span class="status-badge rugi">Rugi ⚠️</span>
            </div>
            <div class="status-amount loss-text">- Rp <?php echo number_format(abs($stats['total_hasil_all']), 0, ',', '.'); ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- KARTU 2: Proyeksi & Analisis -->
      <div class="calc-card feature-card">
        <div class="card-header">
          <span class="card-tag tag-amber">Proyeksi &amp; Rekomendasi</span>
          <h3 class="card-heading">Estimasi &amp; Performa Usaha <span>Bulan Depan</span></h3>
        </div>

        <div class="card-body">
          <?php
            $avg_omzet = $stats['total_transaksi'] > 0
              ? $stats['total_omzet_all'] / $stats['total_transaksi'] : 0;
            $estimasi  = $avg_omzet * 1.2;  // estimasi naik 20%
            $margin    = $stats['total_omzet_all'] > 0
              ? round(($stats['total_hasil_all'] / $stats['total_omzet_all']) * 100, 1) : 0;
          ?>
          <div class="calc-row">
            <span class="label">Rata-rata Omzet/Transaksi</span>
            <span class="value font-semibold text-primary">Rp <?php echo number_format($avg_omzet, 0, ',', '.'); ?></span>
          </div>
          <div class="calc-row">
            <span class="label">Estimasi Omzet Berikutnya (+20%)</span>
            <span class="value font-semibold">Rp <?php echo number_format($estimasi, 0, ',', '.'); ?></span>
          </div>

          <div class="divider"></div>

          <div class="insight-box" style="<?php echo $margin < 0 ? 'background:#fef2f2; border-color:#fca5a5;' : ''; ?>">
            <div class="insight-header">
              <span class="material-symbols-outlined" style="color:<?php echo $margin < 0 ? '#dc2626' : 'var(--primary)'; ?>">
                <?php echo $margin < 0 ? 'trending_down' : 'insights'; ?>
              </span>
              <span class="insight-title" style="color:<?php echo $margin < 0 ? '#dc2626' : ''; ?>">
                Margin Keuntungan Net: <strong><?php echo $margin; ?>%</strong>
              </span>
            </div>
            <p class="insight-desc" style="color:<?php echo $margin < 0 ? '#b91c1c' : ''; ?>">
              <?php if ($margin >= 20): ?>
                Kondisi arus kas berada dalam kategori <strong>Sangat Sehat</strong>. Modal operasional aman untuk ekspansi.
              <?php elseif ($margin > 0): ?>
                Margin bisnis <strong>Cukup</strong>. Pertimbangkan efisiensi biaya untuk meningkatkan profitabilitas.
              <?php else: ?>
                Perlu perhatian serius. Tinjau kembali strategi harga dan pengeluaran Anda.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>

    </div>

    <!-- RIWAYAT TRANSAKSI DARI DATABASE -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-top: 40px; margin-bottom: 16px;">
      <div>
        <h2 class="section-title">Riwayat Transaksi</h2>
        <span class="section-subtitle">Daftar riwayat transaksi yang tersimpan di database</span>
      </div>
      <?php if (!empty($riwayat)): ?>
        <form method="POST" action="dasboard.php" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus SEMUA riwayat transaksi? Tindakan ini akan menghapus semua histori dan tidak dapat dibatalkan.');">
          <input type="hidden" name="hapus_semua" value="1">
          <button type="submit" class="btn-clear-all" title="Hapus seluruh histori transaksi">
            <span class="material-symbols-outlined">delete_sweep</span>
            <span>Hapus Semua Histori</span>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <?php if (empty($riwayat)): ?>
      <div class="status-box status-profit" style="text-align: center; padding: 32px;">
        <div style="font-size: 2rem;">📭</div>
        <div class="status-label" style="margin-top: 8px;">Belum ada riwayat transaksi. Klik "+ Transaksi Baru" untuk mulai!</div>
      </div>
    <?php else: ?>
      <div class="form-transaksi-card" style="padding: 0; overflow-x: auto;">
        <table class="invoice-table" style="margin: 0; width: 100%;">
          <thead>
            <tr>
              <th style="width: 48px;">#</th>
              <th>Tanggal</th>
              <th>Modal</th>
              <th>Total Omzet</th>
              <th>Hari</th>
              <th style="text-align:right;">Hasil</th>
              <th style="text-align:center; width: 110px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($riwayat as $i => $trx): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo date('d M Y, H:i', strtotime($trx['created_at'])); ?></td>
              <td>Rp <?php echo number_format($trx['modal'], 0, ',', '.'); ?></td>
              <td>Rp <?php echo number_format($trx['total_omzet'], 0, ',', '.'); ?></td>
              <td><?php echo $trx['jumlah_hari']; ?> hari</td>
              <td style="text-align:right; font-weight: 700;
                color: <?php echo $trx['hasil'] >= 0 ? '#16a34a' : '#dc2626'; ?>;">
                <?php echo $trx['hasil'] >= 0 ? '+ ' : '- '; ?>
                Rp <?php echo number_format(abs($trx['hasil']), 0, ',', '.'); ?>
              </td>
              <td style="text-align:center;">
                <form method="POST" action="dasboard.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi tanggal <?php echo date('d M Y, H:i', strtotime($trx['created_at'])); ?>?');" style="display:inline; margin:0;">
                  <input type="hidden" name="hapus_id" value="<?php echo (int)$trx['id']; ?>">
                  <button type="submit" class="btn-delete" title="Hapus transaksi ini">
                    <span class="material-symbols-outlined">delete</span>
                    <span>Hapus</span>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </main>

</body>
</html>