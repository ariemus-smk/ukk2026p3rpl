<?php
/**
 * ==============================================================
 * HEADER TEMPLATE (Murni HTML Semantik Tanpa CSS)
 * ==============================================================
 * Disediakan class dan id yang rapi agar siswa dapat dengan mudah
 * menambahkan file CSS (style.css atau framework CSS) di kemudian hari.
 */
require_once __DIR__ . '/fungsi.php';

$isAdmin = !empty($_SESSION['admin_logged_in']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judulHalaman) ? htmlspecialchars($judulHalaman) . ' - ' : '' ?>Pengaduan Sarana Sekolah</title>
    <!-- 
      CATATAN EDUKASI UNTUK SISWA:
      Halaman ini menggunakan murni tag HTML semantik tanpa CSS apapun.
      Semua elemen telah dilengkapi class dan ID yang terstruktur rapi.
      Untuk mengimplementasikan CSS di kemudian hari, Anda cukup membuat
      file "style.css" dan mengaktifkan tag di bawah ini:
      <link rel="stylesheet" href="style.css">
    -->
</head>
<body class="site-body">
    <!-- Header Utama Website -->
    <header class="site-header" id="mainHeader">
        <div class="header-container">
            <h1 class="brand-title">Aplikasi Pengaduan Sarana Sekolah</h1>
            <p class="brand-subtitle">Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak - Paket 3</p>
        </div>

        <!-- Menu Navigasi -->
        <nav class="navbar" id="mainNavbar">
            <ul class="nav-list">
                <!-- Navigasi Publik / Siswa -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Form Aspirasi Siswa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="histori.php">Histori & Lacak Aspirasi</a>
                </li>

                <!-- Navigasi Khusus Admin -->
                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_aspirasi.php"><strong>Kelola Aspirasi (Admin)</strong></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_kategori.php">Kelola Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>)</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login Admin</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <hr class="header-divider">
    </header>

    <!-- Konten Utama Aplikasi -->
    <main class="main-content" id="mainContent">
        <div class="container">
