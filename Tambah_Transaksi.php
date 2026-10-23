<?php
session_start();

// Guard: harus login dulu
if (empty($_SESSION['pengguna'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$nama_user     = $_SESSION['pengguna']['nama'] ?? 'Pengelola';
$user_id       = $_SESSION['pengguna']['id']   ?? 0;
$tanggal_hari_ini = date('d F Y');

// ============================================================
// SIMPAN TRANSAKSI KE DATABASE (AJAX POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_simpan'])) {
    header('Content-Type: application/json');

    $modal       = floatval($_POST['modal']        ?? 0);
    $total_omzet = floatval($_POST['total_omzet']  ?? 0);
    $hasil       = $total_omzet - $modal;
    $omzet_hari  = json_decode($_POST['omzet_hari'] ?? '[]', true); // array [{hari, omzet}, ...]

    if ($modal <= 0 || $total_omzet <= 0) {
        echo json_encode(['status' => 'error', 'pesan' => 'Modal dan omzet harus lebih dari 0.']);
        exit;
    }

    try {
        // Mulai transaksi DB
        $pdo->beginTransaction();

        // 1. Simpan header transaksi
        $stmt = $pdo->prepare("
            INSERT INTO transaksi (user_id, modal, total_omzet, hasil)
            VALUES (:user_id, :modal, :total_omzet, :hasil)
        ");
        $stmt->execute([
            ':user_id'     => $user_id,
            ':modal'       => $modal,
            ':total_omzet' => $total_omzet,
            ':hasil'       => $hasil,
        ]);
        $transaksi_id = $pdo->lastInsertId();

        // 2. Simpan omzet per hari
        $stmtHari = $pdo->prepare("
            INSERT INTO omzet_hari (transaksi_id, hari_ke, omzet)
            VALUES (:transaksi_id, :hari_ke, :omzet)
        ");
        foreach ($omzet_hari as $item) {
            $stmtHari->execute([
                ':transaksi_id' => $transaksi_id,
                ':hari_ke'      => intval($item['hari']),
                ':omzet'        => floatval($item['omzet']),
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'ok', 'transaksi_id' => $transaksi_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'pesan' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tambah Transaksi - LabaPro</title>
  
  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  
  <!-- File CSS Terpisah -->
  <link rel="stylesheet" href="dasboard.css"/>
</head>
<body>

  <!-- TOP HEADER -->
  <div class="top-header-text container-fluid">
    TAMBAH TRANSAKSI &bull; <?php echo htmlspecialchars($nama_user); ?>
  </div>

  <!-- NAVBAR -->
  <header class="navbar-header container-fluid">
    <a href="dasboard.php" class="brand-logo">
      <div class="brand-icon">
        <span class="material-symbols-outlined">all_inclusive</span>
      </div>
      <span class="brand-name">LabaPro</span>
    </a>

    <div class="nav-actions">
      <div class="segmented-control">
        <a href="dasboard.php" class="segment-btn">Transaksi</a>
        <a href="profil.php" class="segment-btn">Profil</a>
        <a href="login.php" class="segment-btn" title="Keluar dari akun">Keluar</a>
      </div>
      <a href="dasboard.php" class="btn-primary" style="text-decoration: none;">
        <span class="material-symbols-outlined">arrow_back</span>
        <span>Kembali</span>
      </a>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="dashboard-main container">
    <section class="section-header">
      <h2 class="section-title">Tambah Transaksi Baru</h2>
      <span class="section-subtitle">Input omzet harian dan modal untuk mendapatkan laporan laba-rugi otomatis</span>
    </section>

    <!-- FORM TRANSAKSI -->
    <div class="form-transaksi-card">
      <div class="transaksi-grid">

        <!-- KOLOM KIRI: INPUT OMZET HARIAN -->
        <div>
          <div class="form-group-custom">
            <label>
              <span class="material-symbols-outlined icon-small">calendar_today</span>
              Input Omzet Harian
            </label>
            <p class="hint-text">
              Masukkan omzet hari ke-1 terlebih dahulu. Tombol "Tambah Hari" akan aktif setelah hari sebelumnya diisi.
            </p>

            <!-- DAFTAR OMZET PER HARI -->
            <div class="omzet-list" id="omzetList">
              <!-- Item Hari ke-1 (default sudah ada, tidak bisa dihapus) -->
              <div class="omzet-item" data-hari="1">
                <span class="omzet-day-badge">Hari ke-1</span>
                <div class="input-money-box" style="flex: 1;">
                  <span class="input-money-prefix">Rp</span>
                  <input
                    type="number"
                    class="input-money-field omzet-input"
                    placeholder="0"
                    min="0"
                    id="omzet_1"
                    oninput="handleOmzetInput(1)"
                  >
                </div>
              </div>
            </div>

            <!-- TOMBOL TAMBAH / HAPUS HARI -->
            <div class="omzet-action-buttons">
              <button type="button" class="btn-tambah-hari" id="btnTambahHari" onclick="tambahHari()" disabled>
                <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
                + Tambah Hari Berikutnya
              </button>
              <button type="button" class="btn-hapus-hari" id="btnHapusHari" onclick="hapusHari()" style="display:none;">
                <span class="material-symbols-outlined" style="font-size: 16px;">remove_circle</span>
                Hapus Hari Terakhir
              </button>
            </div>
          </div>

          <!-- RINGKASAN TOTAL OMZET LIVE -->
          <div class="status-box status-profit" style="margin-top: 20px;">
            <div class="status-info">
              <span class="status-label">Total Omzet (Semua Hari)</span>
              <span class="status-badge untung" id="labelJumlahHari">0 hari diinput</span>
            </div>
            <div class="status-amount profit-text" id="displayTotalOmzet">Rp 0</div>
          </div>
        </div>

        <!-- KOLOM KANAN: INPUT MODAL -->
        <div>
          <div class="form-group-custom">
            <label>
              <span class="material-symbols-outlined icon-small">savings</span>
              Modal Usaha <span style="color: #ef4444;">*</span>
            </label>
            <p class="hint-text">Modal hanya bisa diinput satu kali untuk seluruh periode perhitungan.</p>
            <div class="input-money-box">
              <span class="input-money-prefix">Rp</span>
              <input
                type="number"
                class="input-money-field"
                placeholder="0"
                min="0"
                id="inputModal"
                oninput="hitungLangsung()"
              >
            </div>
          </div>

          <!-- PREVIEW HASIL KALKULASI -->
          <div class="form-group-custom" style="margin-top: 24px;">
            <label>Preview Perhitungan</label>
            <div style="display: flex; flex-direction: column; gap: 10px;">
              <div class="calc-row" style="padding: 12px; background: #f8fafc; border-radius: 10px;">
                <span class="label">Total Omzet</span>
                <span class="value font-semibold text-primary" id="previewOmzet">Rp 0</span>
              </div>
              <div class="calc-row" style="padding: 12px; background: #f8fafc; border-radius: 10px;">
                <span class="label">Modal</span>
                <span class="value font-semibold" id="previewModal">Rp 0</span>
              </div>
              <div class="divider"></div>
              <div class="calc-row" style="padding: 12px; border-radius: 10px;" id="previewHasilBox">
                <span class="label" style="font-weight: 700;">Hasil (Omzet - Modal)</span>
                <span class="value font-semibold" id="previewHasil" style="font-size: 1.1rem;">Rp 0</span>
              </div>
            </div>
          </div>

          <!-- TOMBOL HITUNG -->
          <button type="button" class="btn-submit-transaksi" onclick="hitungDanTampilkanInvoice()">
            <span class="material-symbols-outlined">calculate</span>
            Hitung &amp; Tampilkan Invoice
          </button>
        </div>

      </div>
    </div>

    <!-- INVOICE HASIL (awalnya tersembunyi) -->
    <div id="invoiceSection" style="display: none;">
      <div class="invoice-card">

        <!-- BADGE STATUS -->
        <div id="invoiceBadge" class="invoice-badge-top"></div>

        <!-- HEADER INVOICE -->
        <div class="invoice-header-box">
          <div>
            <div class="invoice-title">Laporan Laba-Rugi</div>
            <div class="invoice-subtitle">
              Digenerate otomatis &bull; <?php echo $tanggal_hari_ini; ?> &bull;
              <?php echo htmlspecialchars($nama_user); ?>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">LabaPro</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Sistem Finansial Bisnis UMKM</div>
          </div>
        </div>

        <!-- TABEL RINCIAN OMZET PER HARI -->
        <table class="invoice-table" id="invoiceTable">
          <thead>
            <tr>
              <th>Periode</th>
              <th>Keterangan</th>
              <th style="text-align: right;">Nominal</th>
            </tr>
          </thead>
          <tbody id="invoiceTableBody">
            <!-- Diisi dinamis oleh JavaScript -->
          </tbody>
          <tfoot>
            <tr style="background: #f0fdf4;">
              <td colspan="2" style="font-weight: 800; color: var(--primary);">TOTAL OMZET</td>
              <td style="text-align: right; font-weight: 800; color: var(--primary); font-size: 1rem;" id="invoiceTotalOmzet">Rp 0</td>
            </tr>
            <tr>
              <td colspan="2" style="font-weight: 700; color: var(--text-body);">Modal Usaha</td>
              <td style="text-align: right; font-weight: 700; color: var(--text-body);" id="invoiceModal">Rp 0</td>
            </tr>
            <tr style="border-top: 2px solid #e2e8f0;">
              <td colspan="2" style="font-weight: 800; font-size: 1.05rem;">
                Total (Omzet - Modal)
              </td>
              <td style="text-align: right; font-weight: 800; font-size: 1.15rem;" id="invoiceHasil">Rp 0</td>
            </tr>
          </tfoot>
        </table>

        <!-- BANNER STATUS AKHIR: UNTUNG / RUGI -->
        <div class="invoice-status-banner" id="statusBanner">
          <div class="status-headline" id="statusHeadline"></div>
          <div class="status-desc" id="statusDesc"></div>
        </div>

        <!-- TOMBOL AKSI INVOICE -->
        <div style="display: flex; gap: 12px; margin-top: 24px; justify-content: flex-end; flex-wrap: wrap;">
          <button type="button" class="btn-primary" onclick="resetForm()" style="background: #64748b; box-shadow: none;">
            <span class="material-symbols-outlined">restart_alt</span>
            <span>Input Baru</span>
          </button>
          <a href="dasboard.php" class="btn-primary" style="text-decoration: none;">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Ke Dashboard</span>
          </a>
        </div>

      </div>
    </div>

  </main>

  <!-- JAVASCRIPT LOGIKA TRANSAKSI -->
  <script>
    // =====================================================================
    // STATE DATA
    // =====================================================================
    let jumlahHari = 1;

    // =====================================================================
    // FORMAT MATA UANG
    // =====================================================================
    function formatRupiah(angka) {
      return 'Rp ' + angka.toLocaleString('id-ID');
    }

    // =====================================================================
    // HITUNG TOTAL OMZET DARI SEMUA INPUT
    // =====================================================================
    function getTotalOmzet() {
      let total = 0;
      document.querySelectorAll('.omzet-input').forEach(input => {
        const val = parseFloat(input.value) || 0;
        total += val;
      });
      return total;
    }

    // =====================================================================
    // TANGANI INPUT OMZET — aktifkan tombol tambah hari jika hari ini sudah diisi
    // =====================================================================
    function handleOmzetInput(hari) {
      const inputHariIni = document.getElementById('omzet_' + hari);
      const val = parseFloat(inputHariIni.value) || 0;
      const btnTambah = document.getElementById('btnTambahHari');

      // Tombol tambah hari hanya aktif jika hari saat ini (hari terakhir) terisi
      if (hari === jumlahHari && val > 0) {
        btnTambah.disabled = false;
      } else if (hari === jumlahHari && val <= 0) {
        btnTambah.disabled = true;
      }

      hitungLangsung();
    }

    // =====================================================================
    // TAMBAH BARIS HARI BARU
    // =====================================================================
    function tambahHari() {
      jumlahHari++;
      const daftarOmzet = document.getElementById('omzetList');
      const hariKe = jumlahHari;

      const div = document.createElement('div');
      div.className = 'omzet-item';
      div.setAttribute('data-hari', hariKe);
      div.innerHTML = `
        <span class="omzet-day-badge">Hari ke-${hariKe}</span>
        <div class="input-money-box" style="flex: 1;">
          <span class="input-money-prefix">Rp</span>
          <input
            type="number"
            class="input-money-field omzet-input"
            placeholder="0"
            min="0"
            id="omzet_${hariKe}"
            oninput="handleOmzetInput(${hariKe})"
          >
        </div>
      `;
      daftarOmzet.appendChild(div);

      // Tombol tambah dikunci dulu sampai hari baru diisi
      document.getElementById('btnTambahHari').disabled = true;

      // Tampilkan tombol hapus jika lebih dari 1 hari
      document.getElementById('btnHapusHari').style.display = 'inline-flex';

      // Scroll ke item baru
      div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      // Fokus ke input baru
      setTimeout(() => document.getElementById('omzet_' + hariKe).focus(), 200);
    }

    // =====================================================================
    // HAPUS BARIS HARI TERAKHIR
    // =====================================================================
    function hapusHari() {
      if (jumlahHari <= 1) return;
      const daftarOmzet = document.getElementById('omzetList');
      const itemTerakhir = daftarOmzet.querySelector(`[data-hari="${jumlahHari}"]`);
      if (itemTerakhir) daftarOmzet.removeChild(itemTerakhir);
      jumlahHari--;

      // Cek apakah hari yang sekarang jadi terakhir sudah terisi → aktifkan kembali tombol tambah
      const inputTerakhir = document.getElementById('omzet_' + jumlahHari);
      const val = parseFloat(inputTerakhir.value) || 0;
      document.getElementById('btnTambahHari').disabled = val <= 0;

      if (jumlahHari <= 1) {
        document.getElementById('btnHapusHari').style.display = 'none';
      }

      hitungLangsung();
    }

    // =====================================================================
    // HITUNG LANGSUNG (PREVIEW)
    // =====================================================================
    function hitungLangsung() {
      const totalOmzet = getTotalOmzet();
      const modal = parseFloat(document.getElementById('inputModal').value) || 0;
      const hasil = totalOmzet - modal;

      // Update display total omzet
      document.getElementById('displayTotalOmzet').textContent = formatRupiah(totalOmzet);
      document.getElementById('previewOmzet').textContent = formatRupiah(totalOmzet);
      document.getElementById('previewModal').textContent = formatRupiah(modal);
      document.getElementById('previewHasil').textContent = formatRupiah(Math.abs(hasil));

      // Jumlah hari yang terisi
      let hariTerisi = 0;
      document.querySelectorAll('.omzet-input').forEach(input => {
        if ((parseFloat(input.value) || 0) > 0) hariTerisi++;
      });
      document.getElementById('labelJumlahHari').textContent = hariTerisi + ' hari diinput';

      // Warna preview hasil
      const previewBox = document.getElementById('previewHasilBox');
      const previewHasil = document.getElementById('previewHasil');
      if (hasil >= 0) {
        previewBox.style.background = '#f0fdf4';
        previewHasil.style.color = '#16a34a';
        previewHasil.textContent = '+ ' + formatRupiah(hasil);
      } else {
        previewBox.style.background = '#fef2f2';
        previewHasil.style.color = '#dc2626';
        previewHasil.textContent = '- ' + formatRupiah(Math.abs(hasil));
      }
    }

    // =====================================================================
    // HITUNG DAN TAMPILKAN INVOICE + SIMPAN KE DATABASE
    // =====================================================================
    function hitungDanTampilkanInvoice() {
      const modal = parseFloat(document.getElementById('inputModal').value) || 0;
      const totalOmzet = getTotalOmzet();

      // Validasi: minimal 1 hari diisi
      if (totalOmzet <= 0) {
        alert('⚠️ Harap masukkan omzet minimal untuk hari ke-1 terlebih dahulu!');
        document.getElementById('omzet_1').focus();
        return;
      }

      // Validasi: modal harus diisi
      if (modal <= 0) {
        alert('⚠️ Harap masukkan nominal modal usaha terlebih dahulu!');
        document.getElementById('inputModal').focus();
        return;
      }

      // Kumpulkan data omzet per hari
      const omzetPerHari = [];
      document.querySelectorAll('.omzet-input').forEach((input, idx) => {
        omzetPerHari.push({ hari: idx + 1, omzet: parseFloat(input.value) || 0 });
      });

      // === SIMPAN KE DATABASE VIA AJAX ===
      const formData = new FormData();
      formData.append('ajax_simpan', '1');
      formData.append('modal', modal);
      formData.append('total_omzet', totalOmzet);
      formData.append('omzet_hari', JSON.stringify(omzetPerHari));

      fetch('Tambah_Transaksi.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'ok') {
            console.log('✅ Transaksi disimpan ke DB. ID:', data.transaksi_id);
          } else {
            console.warn('⚠️ Gagal simpan ke DB:', data.pesan);
          }
        })
        .catch(err => console.warn('❌ Network error saat simpan:', err));

      const hasil = totalOmzet - modal;
      const isUntung = hasil >= 0;

      // === ISI TABEL RINCIAN ===
      const tbody = document.getElementById('invoiceTableBody');
      tbody.innerHTML = '';

      const inputs = document.querySelectorAll('.omzet-input');
      inputs.forEach((input, idx) => {
        const hariKe = idx + 1;
        const val = parseFloat(input.value) || 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>Hari ke-${hariKe}</td>
          <td style="color: var(--text-muted);">Omzet Penjualan</td>
          <td style="text-align: right; font-weight: 600;">${formatRupiah(val)}</td>
        `;
        tbody.appendChild(tr);
      });

      // Total omzet, modal, hasil di tfoot
      document.getElementById('invoiceTotalOmzet').textContent = formatRupiah(totalOmzet);
      document.getElementById('invoiceModal').textContent = formatRupiah(modal);

      const invoiceHasil = document.getElementById('invoiceHasil');
      if (isUntung) {
        invoiceHasil.textContent = '+ ' + formatRupiah(hasil);
        invoiceHasil.style.color = '#16a34a';
      } else {
        invoiceHasil.textContent = '- ' + formatRupiah(Math.abs(hasil));
        invoiceHasil.style.color = '#dc2626';
      }

      // === BADGE STATUS ===
      const badge = document.getElementById('invoiceBadge');
      badge.textContent = isUntung ? '✅ STATUS: KEUNTUNGAN' : '⚠️ STATUS: KERUGIAN';
      badge.className = 'invoice-badge-top';
      badge.style.background = isUntung ? '#dcfce7' : '#fee2e2';
      badge.style.color = isUntung ? '#15803d' : '#b91c1c';
      badge.style.border = isUntung ? '1px solid #86efac' : '1px solid #fca5a5';

      // === BANNER STATUS AKHIR ===
      const banner = document.getElementById('statusBanner');
      const headline = document.getElementById('statusHeadline');
      const desc = document.getElementById('statusDesc');

      if (isUntung) {
        banner.className = 'invoice-status-banner banner-profit';
        headline.textContent = '🎉 SELAMAT! Anda Mengalami Keuntungan!';
        desc.textContent = `Total keuntungan bersih Anda sebesar: ${formatRupiah(hasil)} dari ${inputs.length} hari penjualan`;
      } else {
        banner.className = 'invoice-status-banner banner-loss';
        headline.textContent = '⚠️ PERHATIAN! Anda Mengalami Kerugian!';
        desc.textContent = `Anda mengalami kerugian sebesar: ${formatRupiah(Math.abs(hasil))} dari ${inputs.length} hari penjualan. Tinjau kembali strategi harga dan pengeluaran Anda.`;
      }

      // === TAMPILKAN INVOICE ===
      const invoiceSection = document.getElementById('invoiceSection');
      invoiceSection.style.display = 'block';
      invoiceSection.scrollIntoView({ behavior: 'smooth' });
    }

    // =====================================================================
    // RESET FORM
    // =====================================================================
    function resetForm() {
      // Hapus semua hari kecuali hari ke-1
      const daftarOmzet = document.getElementById('omzetList');
      while (jumlahHari > 1) {
        const itemTerakhir = daftarOmzet.querySelector(`[data-hari="${jumlahHari}"]`);
        if (itemTerakhir) daftarOmzet.removeChild(itemTerakhir);
        jumlahHari--;
      }

      // Reset semua input
      document.getElementById('omzet_1').value = '';
      document.getElementById('inputModal').value = '';

      // Reset preview
      hitungLangsung();

      // Sembunyikan invoice
      document.getElementById('invoiceSection').style.display = 'none';

      // Kunci tombol tambah hari dan sembunyikan hapus
      document.getElementById('btnTambahHari').disabled = true;
      document.getElementById('btnHapusHari').style.display = 'none';

      // Scroll ke atas
      window.scrollTo({ top: 0, behavior: 'smooth' });
      setTimeout(() => document.getElementById('omzet_1').focus(), 300);
    }

    // Jalankan preview awal
    hitungLangsung();
  </script>

</body>
</html>
