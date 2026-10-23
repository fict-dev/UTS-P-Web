<?php
// db.php – Centralized PDO connection
// Adjust the credentials according to your local MySQL setup.
$host = '127.0.0.1';
$port = '3306';
$db   = 'laba_pro';
$user = 'root';        // XAMPP default user
$pass = '';            // XAMPP default password (empty)

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In a production app you would log this rather than echo.
    exit('❌ Gagal terhubung ke database: ' . $e->getMessage());
}
?>
