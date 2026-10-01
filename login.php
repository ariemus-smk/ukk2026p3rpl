<?php
/**
 * ==============================================================
 * LOGIN ADMINISTRATOR
 * ==============================================================
 * File autentikasi bagi petugas/admin sarana sekolah
 */

require_once __DIR__ . '/fungsi.php';

// Jika admin sudah login, langsung arahkan ke panel admin
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: admin_aspirasi.php");
    exit;
}

$judulHalaman = "Login Admin";
$pesanError   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $pesanError = "Username dan password wajib diisi!";
    } else {
        if (proses_login_admin($username, $password)) {
            header("Location: admin_aspirasi.php");
            exit;
        } else {
            $pesanError = "Username atau password yang Anda masukkan salah!";
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<section class="section-login" id="sectionLoginAdmin">
    <div class="card card-login">
        <header class="card-header">
            <h2 class="card-title">Login Petugas / Administrator</h2>
            <p class="card-description">
                Masuk untuk mengelola pengaduan sarana, memperbarui status perbaikan, dan memberikan umpan balik kepada siswa.
            </p>
        </header>

        <div class="card-body">
            <!-- Informasi Login Default untuk Uji Coba Siswa/Penguji -->
            <aside class="alert alert-info">
                <p><strong>Info Pengujian:</strong></p>
                <ul>
                    <li>Username: <code>admin</code></li>
                    <li>Password: <code>admin123</code></li>
                </ul>
            </aside>

            <!-- Pesan Error -->
            <?php if (!empty($pesanError)): ?>
                <aside class="alert alert-danger">
                    <p><strong>Gagal Masuk:</strong> <?= htmlspecialchars($pesanError) ?></p>
                </aside>
            <?php endif; ?>

            <!-- Form Login -->
            <form action="login.php" method="POST" class="form form-login" id="formLogin">
                <div class="form-group">
                    <label for="username" class="form-label">Username:</label><br>
                    <input type="text" name="username" id="username" class="form-control" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password:</label><br>
                    <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btnLogin">Masuk</button>
                    <a href="index.php" class="btn btn-secondary">Kembali ke Halaman Siswa</a>
                </div>
            </form>
        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
