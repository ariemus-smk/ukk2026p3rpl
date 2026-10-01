<?php
/**
 * ==============================================================
 * HALAMAN 1: FORM ASPIRASI SISWA
 * Sesuai Dokumen UKK Paket 3 Halaman 2:
 * "Halaman Form Aspirasi Siswa, yang digunakan untuk menyampaikan
 * pengaduan atau masukan terkait sarana dan prasarana sekolah."
 * ==============================================================
 */

require_once __DIR__ . '/fungsi.php';

$judulHalaman = "Form Aspirasi Siswa";
$pesanSukses  = "";
$pesanError   = "";

// Ambil daftar kategori sarana untuk dropdown pilihan
$daftarKategori = ambil_semua_kategori();

// Proses jika formulir dikirimkan (metode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis         = (int)($_POST['nis'] ?? 0);
    $kelas       = trim($_POST['kelas'] ?? '');
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $lokasi      = trim($_POST['lokasi'] ?? '');
    $ket         = trim($_POST['ket'] ?? '');

    // Validasi sederhana
    if ($nis <= 0) {
        $pesanError = "Nomor Induk Siswa (NIS) harus berupa angka valid!";
    } elseif (empty($kelas)) {
        $pesanError = "Kelas harus diisi!";
    } elseif ($id_kategori <= 0) {
        $pesanError = "Silakan pilih salah satu kategori sarana!";
    } elseif (empty($lokasi)) {
        $pesanError = "Lokasi sarana yang rusak/diadukan harus diisi!";
    } elseif (empty($ket)) {
        $pesanError = "Keterangan pengaduan sarana tidak boleh kosong!";
    } else {
        // Panggil fungsi penyimpanan ke database
        $id_pelaporan = simpan_aspirasi($nis, $kelas, $id_kategori, $lokasi, $ket);

        if ($id_pelaporan) {
            $pesanSukses = "Aspirasi berhasil dikirim dengan Nomor Pelaporan <strong>#{$id_pelaporan}</strong>. Anda dapat melihat progres perbaikannya pada menu <a href='histori.php?nis={$nis}'>Histori Aspirasi</a>.";
        } else {
            $pesanError = "Terjadi kesalahan saat menyimpan aspirasi. Pastikan database sudah terpasang dengan benar.";
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<!-- Section: Form Aspirasi Siswa -->
<section class="section-aspirasi" id="sectionFormAspirasi">
    <div class="card card-aspirasi">
        <header class="card-header">
            <h2 class="card-title">Form Penyampaian Pengaduan Sarana Sekolah</h2>
            <p class="card-description">
                Silakan isi data diri Anda beserta sarana atau prasarana sekolah yang perlu diperbaiki atau ditindaklanjuti.
            </p>
        </header>

        <div class="card-body">
            <!-- Notifikasi Pesan Sukses -->
            <?php if (!empty($pesanSukses)): ?>
                <aside class="alert alert-success" role="alert">
                    <p><?= $pesanSukses ?></p>
                </aside>
            <?php endif; ?>

            <!-- Notifikasi Pesan Error -->
            <?php if (!empty($pesanError)): ?>
                <aside class="alert alert-danger" role="alert">
                    <p><strong>Perhatian:</strong> <?= htmlspecialchars($pesanError) ?></p>
                </aside>
            <?php endif; ?>

            <!-- Formulir Aspirasi Siswa -->
            <form action="index.php" method="POST" class="form form-aspirasi" id="formAspirasi">
                
                <fieldset class="form-fieldset">
                    <legend class="form-legend">Identitas Siswa</legend>

                    <div class="form-group">
                        <label for="nis" class="form-label">Nomor Induk Siswa (NIS):</label><br>
                        <input type="number" name="nis" id="nis" class="form-control" placeholder="Contoh: 1021001" required value="<?= htmlspecialchars($_POST['nis'] ?? '') ?>">
                        <small class="form-text">Masukkan NIS aktif Anda.</small>
                    </div>

                    <div class="form-group">
                        <label for="kelas" class="form-label">Kelas:</label><br>
                        <input type="text" name="kelas" id="kelas" class="form-control" placeholder="Contoh: XII RPL 1" required maxlength="10" value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>">
                        <small class="form-text">Contoh penulisan: X RPL 2, XI TKJ 1, XII MM 3.</small>
                    </div>
                </fieldset>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Rincian Pengaduan Sarana</legend>

                    <div class="form-group">
                        <label for="id_kategori" class="form-label">Kategori Sarana:</label><br>
                        <select name="id_kategori" id="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori Sarana --</option>
                            <?php foreach ($daftarKategori as $kat): ?>
                                <option value="<?= (int)$kat['id_kategori'] ?>" <?= (isset($_POST['id_kategori']) && (int)$_POST['id_kategori'] === (int)$kat['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['ket_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lokasi" class="form-label">Lokasi Sarana:</label><br>
                        <input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Contoh: Lab Komputer 2, Toilet Lantai 2 Gedung A" required maxlength="50" value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                        <small class="form-text">Tuliskan lokasi letak sarana dengan jelas.</small>
                    </div>

                    <div class="form-group">
                        <label for="ket" class="form-label">Keterangan Kerusakan / Pengaduan:</label><br>
                        <textarea name="ket" id="ket" class="form-control" rows="4" cols="50" placeholder="Tuliskan kendala atau kerusakan yang dialami secara rinci..." required><?= htmlspecialchars($_POST['ket'] ?? '') ?></textarea>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btnKirimAspirasi">Kirim Aspirasi</button>
                    <button type="reset" class="btn btn-secondary">Reset Formulir</button>
                </div>
            </form>
        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
