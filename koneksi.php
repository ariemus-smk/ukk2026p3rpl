<?php
/**
 * ==============================================================
 * KONEKSI DATABASE (PHP NATIVE 8.2 & MySQL)
 * Aplikasi Pengaduan Sarana Sekolah - UKK Paket 3 RPL
 * ==============================================================
 * File ini bertugas menghubungkan aplikasi dengan database MySQL.
 * Konfigurasi dipisahkan ke dalam file "config.php" agar kredensial
 * tidak ikut terunggah (ter-commit) ke Git.
 */

// Jalur file konfigurasi
$configFile        = __DIR__ . '/config.php';
$configExampleFile = __DIR__ . '/config.example.php';

// Muat konfigurasi dari config.php jika ada, atau gunakan template bawaan
if (file_exists($configFile)) {
    $dbConfig = require $configFile;
} elseif (file_exists($configExampleFile)) {
    $dbConfig = require $configExampleFile;
} else {
    $dbConfig = [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'ukk_pengaduan_sarana',
    ];
}

$host     = $dbConfig['host'] ?? 'localhost';
$username = $dbConfig['username'] ?? 'root';
$password = $dbConfig['password'] ?? '';
$database = $dbConfig['database'] ?? 'ukk_pengaduan_sarana';

// Mematikan mysqli exception bawaan PHP 8.1+ agar error dapat ditangani secara manual & ramah bagi siswa
mysqli_report(MYSQLI_REPORT_OFF);

// Melakukan koneksi ke database MySQL
$koneksi = @mysqli_connect($host, $username, $password, $database);

// Periksa apakah koneksi berhasil
if (!$koneksi) {
    // Tampilkan pesan kesalahan yang informatif dan mudah dipahami siswa
    die("
        <div class='alert alert-danger'>
            <h3>Gagal Terhubung ke Database!</h3>
            <p>Error: " . mysqli_connect_error() . "</p>
            <p>Petunjuk untuk siswa/pengguna:</p>
            <ul>
                <li>Pastikan server MySQL sudah berjalan.</li>
                <li>Periksa file <strong>config.php</strong> (salin dari <strong>config.example.php</strong> jika belum ada).</li>
                <li>Pastikan username, password, dan nama database di <strong>config.php</strong> sudah sesuai.</li>
                <li>Pastikan file <strong>db.sql</strong> sudah di-import ke MySQL/phpMyAdmin.</li>
            </ul>
        </div>
    ");
}

// Menetapkan set karakter ke UTF-8
mysqli_set_charset($koneksi, "utf8mb4");
?>
