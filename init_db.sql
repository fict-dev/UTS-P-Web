-- init_db.sql – Create database and tables for LabaPro application
-- Use this script with MySQL (XAMPP) to set up the required schema.

-- 1. Create database
CREATE DATABASE IF NOT EXISTS laba_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laba_pro;

-- 2. Table: users
-- Stores login credentials and profile information.
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert dummy users (passwords are hashed with password_hash())
INSERT INTO users (username, password_hash, nama_lengkap) VALUES
    ('admin', '$2y$10$kiuGiAWGlGGDXP3vMO7jYuru8yLQTT24Ny4OADV/MZZtkmMxDam2q', 'Admin LabaPro'),
    ('user',  '$2y$10$ALssicunvDF1PLn.65zJ5OsqZvjpanopsvVD1YcqrwKS5GlaHW1Cq', 'Pengguna LabaPro');
-- Hashes correspond to: admin → "admin123" | user → "user123"

-- 3. Table: transaksi
-- One record per transaksi (set of daily omzet entries) per user.
CREATE TABLE IF NOT EXISTS transaksi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    modal DECIMAL(15,2) NOT NULL,
    total_omzet DECIMAL(15,2) NOT NULL,
    hasil DECIMAL(15,2) NOT NULL, -- total_omzet - modal
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table: omzet_hari
-- Stores omzet per day linked to a transaksi.
CREATE TABLE IF NOT EXISTS omzet_hari (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT UNSIGNED NOT NULL,
    hari_ke TINYINT UNSIGNED NOT NULL,
    omzet DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Optional: index for faster look‑ups
CREATE INDEX idx_omzet_hari_transaksi ON omzet_hari(transaksi_id);

-- ---------------------------------------------------------------
-- After running this script, you can use the provided db.php
-- (PDO connection) to interact with the tables.
-- ---------------------------------------------------------------
