<?php
/**
 * ==============================================================
 * PROSEDUR DAN FUNGSI UTAMA (PHP NATIVE 8.2)
 * Aplikasi Pengaduan Sarana Sekolah - UKK Paket 3 RPL
 * ==============================================================
 * Sesuai petunjuk soal UKK halaman 3:
 * - Menggunakan Prosedur dan Fungsi
 * - Menggunakan Array
 * - Query yang efisien (menggunakan prepared statement & JOIN)
 */

require_once __DIR__ . '/koneksi.php';

/**
 * Memulai session PHP dengan aman jika belum aktif
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Array daftar status penyelesaian aspirasi
 * Digunakan untuk dropdown dan validasi status
 * 
 * @return array
 */
function daftar_status(): array {
    return ['Menunggu', 'Proses', 'Selesai'];
}

/**
 * Array daftar nama bulan dalam bahasa Indonesia
 * Digunakan untuk filter pencarian per bulan
 * 
 * @return array
 */
function daftar_bulan(): array {
    return [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
}

/**
 * Fungsi untuk memformat tanggal ke format Indonesia (contoh: 15 Februari 2026)
 * 
 * @param string|null $tanggal (Format: YYYY-MM-DD)
 * @return string
 */
function format_tanggal_indonesia(?string $tanggal): string {
    if (empty($tanggal) || $tanggal === '0000-00-00') {
        return '-';
    }
    $bulanIndo = daftar_bulan();
    $pecah = explode('-', $tanggal);
    if (count($pecah) !== 3) {
        return $tanggal;
    }
    $tahun = $pecah[0];
    $bulan = (int)$pecah[1];
    $hari  = (int)$pecah[2];

    return $hari . ' ' . ($bulanIndo[$bulan] ?? $pecah[1]) . ' ' . $tahun;
}

/**
 * Fungsi untuk mengambil semua data kategori sarana sekolah
 * 
 * @return array
 */
function ambil_semua_kategori(): array {
    global $koneksi;
    $query = "SELECT id_kategori, ket_kategori FROM kategori ORDER BY ket_kategori ASC";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Prosedur untuk menambah kategori sarana baru (khusus Admin)
 * 
 * @param string $ket_kategori
 * @return bool
 */
function tambah_kategori(string $ket_kategori): bool {
    global $koneksi;
    $ket_kategori = trim($ket_kategori);
    if (empty($ket_kategori)) {
        return false;
    }
    $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (ket_kategori) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $ket_kategori);
    $sukses = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $sukses;
}

/**
 * Prosedur untuk menghapus kategori sarana
 * 
 * @param int $id_kategori
 * @return bool
 */
function hapus_kategori(int $id_kategori): bool {
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_kategori);
    $sukses = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $sukses;
}

/**
 * Prosedur untuk menyimpan aspirasi pengaduan dari siswa
 * Alur:
 * 1. Simpan/update data siswa (nis & kelas) jika belum terdaftar.
 * 2. Simpan ke tabel input_aspirasi.
 * 3. Simpan entri awal ke tabel aspirasi dengan status default 'Menunggu'.
 * 
 * @param int $nis
 * @param string $kelas
 * @param int $id_kategori
 * @param string $lokasi
 * @param string $ket
 * @return int|bool Mengembalikan id_pelaporan jika berhasil, false jika gagal
 */
function simpan_aspirasi(int $nis, string $kelas, int $id_kategori, string $lokasi, string $ket): int|bool {
    global $koneksi;

    // Bersihkan spasi input
    $kelas  = trim($kelas);
    $lokasi = trim($lokasi);
    $ket    = trim($ket);

    if ($nis <= 0 || empty($kelas) || $id_kategori <= 0 || empty($lokasi) || empty($ket)) {
        return false;
    }

    // 1. Cek apakah NIS sudah ada di tabel siswa
    $stmtSiswa = mysqli_prepare($koneksi, "SELECT nis FROM siswa WHERE nis = ?");
    mysqli_stmt_bind_param($stmtSiswa, "i", $nis);
    mysqli_stmt_execute($stmtSiswa);
    mysqli_stmt_store_result($stmtSiswa);

    if (mysqli_stmt_num_rows($stmtSiswa) === 0) {
        // Jika belum ada, daftarkan siswa
        $stmtInsertSiswa = mysqli_prepare($koneksi, "INSERT INTO siswa (nis, kelas) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmtInsertSiswa, "is", $nis, $kelas);
        mysqli_stmt_execute($stmtInsertSiswa);
        mysqli_stmt_close($stmtInsertSiswa);
    } else {
        // Jika sudah ada, perbarui data kelas jika berubah
        $stmtUpdateSiswa = mysqli_prepare($koneksi, "UPDATE siswa SET kelas = ? WHERE nis = ?");
        mysqli_stmt_bind_param($stmtUpdateSiswa, "si", $kelas, $nis);
        mysqli_stmt_execute($stmtUpdateSiswa);
        mysqli_stmt_close($stmtUpdateSiswa);
    }
    mysqli_stmt_close($stmtSiswa);

    // 2. Simpan ke tabel input_aspirasi
    $stmtInput = mysqli_prepare($koneksi, "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket, tanggal) VALUES (?, ?, ?, ?, CURRENT_DATE)");
    mysqli_stmt_bind_param($stmtInput, "iiss", $nis, $id_kategori, $lokasi, $ket);
    if (!mysqli_stmt_execute($stmtInput)) {
        mysqli_stmt_close($stmtInput);
        return false;
    }
    $id_pelaporan = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmtInput);

    // 3. Simpan ke tabel aspirasi (relasi ke id_pelaporan dengan status awal 'Menunggu')
    $statusAwal = 'Menunggu';
    $stmtAspirasi = mysqli_prepare($koneksi, "INSERT INTO aspirasi (id_pelaporan, status, id_kategori, feedback) VALUES (?, ?, ?, NULL)");
    mysqli_stmt_bind_param($stmtAspirasi, "isi", $id_pelaporan, $statusAwal, $id_kategori);
    mysqli_stmt_execute($stmtAspirasi);
    mysqli_stmt_close($stmtAspirasi);

    return $id_pelaporan;
}

/**
 * Fungsi untuk mengambil histori aspirasi berdasarkan NIS Siswa
 * Menghubungkan tabel input_aspirasi, aspirasi, dan kategori (JOIN)
 * 
 * @param int $nis
 * @return array
 */
function ambil_aspirasi_siswa(int $nis): array {
    global $koneksi;

    $query = "SELECT 
                i.id_pelaporan,
                i.nis,
                s.kelas,
                i.lokasi,
                i.ket,
                i.tanggal,
                k.ket_kategori,
                a.id_aspirasi,
                a.status,
                a.feedback
              FROM input_aspirasi i
              JOIN siswa s ON i.nis = s.nis
              JOIN kategori k ON i.id_kategori = k.id_kategori
              JOIN aspirasi a ON i.id_pelaporan = a.id_pelaporan
              WHERE i.nis = ?
              ORDER BY i.id_pelaporan DESC";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $nis);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Fungsi untuk mengambil seluruh aspirasi dengan opsi filter
 * Mendukung filter:
 * - per tanggal
 * - per bulan & tahun
 * - per siswa (NIS)
 * - per kategori
 * 
 * @param array $filter
 * @return array
 */
function ambil_semua_aspirasi(array $filter = []): array {
    global $koneksi;

    $sql = "SELECT 
                i.id_pelaporan,
                i.nis,
                s.kelas,
                i.lokasi,
                i.ket,
                i.tanggal,
                k.id_kategori,
                k.ket_kategori,
                a.id_aspirasi,
                a.status,
                a.feedback
            FROM input_aspirasi i
            JOIN siswa s ON i.nis = s.nis
            JOIN kategori k ON i.id_kategori = k.id_kategori
            JOIN aspirasi a ON i.id_pelaporan = a.id_pelaporan
            WHERE 1=1";

    $params = [];
    $types  = "";

    // 1. Filter per Tanggal spesifik
    if (!empty($filter['tanggal'])) {
        $sql .= " AND i.tanggal = ?";
        $params[] = $filter['tanggal'];
        $types   .= "s";
    }

    // 2. Filter per Bulan
    if (!empty($filter['bulan']) && (int)$filter['bulan'] >= 1 && (int)$filter['bulan'] <= 12) {
        $sql .= " AND MONTH(i.tanggal) = ?";
        $params[] = (int)$filter['bulan'];
        $types   .= "i";
    }

    // Filter per Tahun (jika disertakan bersama bulan atau mandiri)
    if (!empty($filter['tahun'])) {
        $sql .= " AND YEAR(i.tanggal) = ?";
        $params[] = (int)$filter['tahun'];
        $types   .= "i";
    }

    // 3. Filter per Siswa (berdasarkan NIS)
    if (!empty($filter['nis'])) {
        $sql .= " AND i.nis = ?";
        $params[] = (int)$filter['nis'];
        $types   .= "i";
    }

    // 4. Filter per Kategori
    if (!empty($filter['id_kategori'])) {
        $sql .= " AND i.id_kategori = ?";
        $params[] = (int)$filter['id_kategori'];
        $types   .= "i";
    }

    $sql .= " ORDER BY i.id_pelaporan DESC";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Fungsi untuk mengambil satu data aspirasi berdasarkan id_aspirasi
 * Digunakan pada form Umpan Balik Admin
 * 
 * @param int $id_aspirasi
 * @return array|null
 */
function ambil_aspirasi_by_id(int $id_aspirasi): ?array {
    global $koneksi;

    $query = "SELECT 
                a.id_aspirasi,
                a.id_pelaporan,
                a.status,
                a.feedback,
                i.nis,
                s.kelas,
                i.lokasi,
                i.ket,
                i.tanggal,
                k.id_kategori,
                k.ket_kategori
              FROM aspirasi a
              JOIN input_aspirasi i ON a.id_pelaporan = i.id_pelaporan
              JOIN siswa s ON i.nis = s.nis
              JOIN kategori k ON a.id_kategori = k.id_kategori
              WHERE a.id_aspirasi = ?";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_aspirasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ?: null;
}

/**
 * Prosedur untuk menyimpan umpan balik dan merubah status penyelesaian
 * Sesuai Halaman Utama 2 di Soal UKK
 * 
 * @param int $id_aspirasi
 * @param string $status ('Menunggu', 'Proses', 'Selesai')
 * @param string $feedback
 * @return bool
 */
function simpan_umpan_balik(int $id_aspirasi, string $status, string $feedback): bool {
    global $koneksi;

    $daftarStatus = daftar_status();
    if (!in_array($status, $daftarStatus, true)) {
        return false;
    }

    $feedback = trim($feedback);

    $stmt = mysqli_prepare($koneksi, "UPDATE aspirasi SET status = ?, feedback = ? WHERE id_aspirasi = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $status, $feedback, $id_aspirasi);
    $sukses = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $sukses;
}

/**
 * Fungsi autentikasi login Admin
 * Memeriksa kecocokan username dan password
 * Mendukung verifikasi password_hash dan plaintext fallback untuk memudahkan pembelajaran siswa.
 * 
 * @param string $username
 * @param string $password
 * @return bool
 */
function proses_login_admin(string $username, string $password): bool {
    global $koneksi;

    $username = trim($username);
    $password = trim($password);

    if (empty($username) || empty($password)) {
        return false;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT username, password FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$admin) {
        return false;
    }

    // Cek password hash atau plain string
    $valid = password_verify($password, $admin['password']) || ($password === $admin['password']);

    if ($valid) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $admin['username'];
        return true;
    }

    return false;
}

/**
 * Fungsi untuk memeriksa apakah admin sudah login
 * Jika belum, otomatis diarahkan ke halaman login.php
 */
function wajib_login_admin(): void {
    if (empty($_SESSION['admin_logged_in'])) {
        header("Location: login.php");
        exit;
    }
}
?>
