/**
 * ==============================================================================
 * LABAPRO / MONITORING UMKM - LOGIKA LOGIN & VALIDASI DUMMY
 * ==============================================================================
 */

// Data Dummy Pengguna untuk Validasi
const DUMMY_USERS = [
  {
    username: 'admin',
    email: 'admin@tokoberkah.id',
    password: 'admin123',
    nama: 'Budi Santoso'
  },
  {
    username: 'user',
    email: 'user@tokoberkah.id',
    password: 'user123',
    nama: 'Hendra Wijaya'
  }
];

// Toggle Tampilkan / Sembunyikan Password
function togglePassword() {
  const inputSandi = document.getElementById('inputSandi');
  const ikonMata = document.getElementById('ikonMata');

  if (!inputSandi) return;

  if (inputSandi.type === 'password') {
    inputSandi.type = 'text';
    if (ikonMata) ikonMata.textContent = '🙈';
  } else {
    inputSandi.type = 'password';
    if (ikonMata) ikonMata.textContent = '👁';
  }
}

// Proses Validasi Form Login
function prosesLogin(event) {
  const inputNamaEl = document.getElementById('inputNamaLengkap');
  const inputUserEl = document.getElementById('inputUsername');
  const inputPassEl = document.getElementById('inputSandi');
  const alertBox = document.getElementById('alertBox');
  const alertMessage = document.getElementById('alertMessage');

  const namaLengkap = inputNamaEl ? inputNamaEl.value.trim() : '';
  const username = inputUserEl ? inputUserEl.value.trim() : '';
  const password = inputPassEl ? inputPassEl.value : '';

  // Cek apakah kredensial cocok dengan data dummy
  const matchedUser = DUMMY_USERS.find(u => 
    (u.username.toLowerCase() === username.toLowerCase() || u.email.toLowerCase() === username.toLowerCase())
    && u.password === password
  );

  if (matchedUser) {
    // KREDENSIAL VALID:
    const namaTampil = namaLengkap || matchedUser.nama;

    if (window.location.protocol === 'file:') {
      // Jika dibuka lewat file://, redirect langsung via JS
      event.preventDefault();
      alert(`Login berhasil!\nSelamat datang, ${namaTampil}.`);
      window.location.href = 'dasboard.php';
      return false;
    }
    // Jika via web server (http://), izinkan form POST ke login.php untuk set session
    return true;
  } else {
    // KREDENSIAL TIDAK VALID:
    // Cegah submit, tetap di halaman login, tampilkan pesan error
    event.preventDefault();

    if (alertBox) {
      if (alertMessage) {
        alertMessage.textContent = 'Username atau kata sandi salah! Silakan coba lagi.';
      }
      alertBox.style.display = 'flex';
      alertBox.classList.add('shake');
      setTimeout(() => alertBox.classList.remove('shake'), 500);
    }

    if (inputPassEl) {
      inputPassEl.value = '';
      inputPassEl.focus();
    }

    return false;
  }
}
