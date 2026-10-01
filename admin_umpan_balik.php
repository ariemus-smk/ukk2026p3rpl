<?php
/**
 * ==============================================================
 * HALAMAN 2: UMPAN BALIK ASPIRASI
 * Sesuai Dokumen UKK Halaman 2:
 * "Halaman Umpan balik aspirasi yang digunakan untuk memberikan
 * umpan balik dari aspirasi dan merubah status penyelesaian."
 * ==============================================================
 */

require_once __DIR__ . '/fungsi.php';
wajib_login_admin();

$judulHalaman = "Umpan Balik & Status Aspirasi";
$pesanSukses  = "";
$pesanError   = "";

$id_aspirasi = (int)($_GET['id'] ?? 0);

if ($id_aspirasi <= 0) {
    header("Location: admin_aspirasi.php");
    exit;
}

// Ambil data aspirasi saat ini
$aspirasi = ambil_aspirasi_by_id($id_aspirasi);

if (!$aspirasi) {
    die("Data aspirasi dengan ID #{$id_aspirasi} tidak ditemukan. <a href='admin_aspirasi.php'>Kembali</a>");
}

// Proses form saat tombol simpan ditekan (metode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statusBaru   = trim($_POST['status'] ?? '');
    $feedbackBaru = trim($_POST['feedback'] ?? '');

    $daftarStatusValid = daftar_status();

    if (!in_array($statusBaru, $daftarStatusValid, true)) {
        $pesanError = "Status yang dipilih tidak valid!";
    } elseif (empty($feedbackBaru)) {
        $pesanError = "Umpan balik (feedback) wajib diisi agar siswa mengetahui perkembangan sarana!";
    } else {
        $berhasil = simpan_umpan_balik($id_aspirasi, $statusBaru, $feedbackBaru);

        if ($berhasil) {
            $pesanSukses = "Umpan balik dan status aspirasi berhasil diperbarui menjadi '<strong>" . htmlspecialchars($statusBaru) . "</strong>'!";
            // Ambil ulang data yang terbaru dari database
            $aspirasi = ambil_aspirasi_by_id($id_aspirasi);
        } else {
            $pesanError = "Gagal memperbarui umpan balik. Silakan periksa koneksi database Anda.";
        }
    }
}

$daftarStatus = daftar_status();

require_once __DIR__ . '/header.php';
?>

<section class="section-umpan-balik" id="sectionUmpanBalik">
    <div class="card card-umpan-balik">
        <header class="card-header">
            <h2 class="card-title">Halaman Umpan Balik dan Pembaruan Status</h2>
            <p class="card-description">
                Tanggapi keluhan sarana siswa dengan memberikan penjelasan tindakan perbaikan dan mengubah status pekerjaan.
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
                    <p><strong>Gagal:</strong> <?= htmlspecialchars($pesanError) ?></p>
                </aside>
            <?php endif; ?>

            <!-- Rincian Data Aspirasi yang Dilaporkan Siswa -->
            <fieldset class="info-fieldset">
                <legend class="info-legend"><strong>Rincian Laporan Pengaduan Siswa</strong></legend>

                <table class="table-detail" border="1" cellpadding="6" cellspacing="0" width="100%">
                    <tr>
                        <th width="30%">Nomor Pelaporan</th>
                        <td><strong>#<?= (int)$aspirasi['id_pelaporan'] ?></strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pengaduan</th>
                        <td><?= htmlspecialchars(format_tanggal_indonesia($aspirasi['tanggal'])) ?></td>
                    </tr>
                    <tr>
                        <th>Identitas Siswa</th>
                        <td>NIS: <?= (int)$aspirasi['nis'] ?> &mdash; Kelas: <?= htmlspecialchars($aspirasi['kelas']) ?></td>
                    </tr>
                    <tr>
                        <th>Kategori Sarana</th>
                        <td><?= htmlspecialchars($aspirasi['ket_kategori']) ?></td>
                    </tr>
                    <tr>
                        <th>Lokasi Sarana</th>
                        <td><?= htmlspecialchars($aspirasi['lokasi']) ?></td>
                    </tr>
                    <tr>
                        <th>Keterangan Kerusakan</th>
                        <td><?= nl2br(htmlspecialchars($aspirasi['ket'])) ?></td>
                    </tr>
                    <tr>
                        <th>Status Saat Ini</th>
                        <td>
                            <strong><?= htmlspecialchars($aspirasi['status']) ?></strong>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <br>

            <!-- Formulir Pengisian Umpan Balik & Status -->
            <form action="admin_umpan_balik.php?id=<?= $id_aspirasi ?>" method="POST" class="form form-umpan-balik" id="formUmpanBalik">
                <fieldset class="form-fieldset">
                    <legend class="form-legend"><strong>Tindakan Petugas / Administrator</strong></legend>

                    <!-- Pilihan Status Penyelesaian -->
                    <div class="form-group">
                        <label for="selectStatus" class="form-label">Ubah Status Penyelesaian:</label><br>
                        <select name="status" id="selectStatus" class="form-select" required>
                            <?php foreach ($daftarStatus as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= ($aspirasi['status'] === $st) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">
                            Keterangan:
                            <em>Menunggu</em> (Laporan baru masuk),
                            <em>Proses</em> (Sedang diperbaiki/ditangani),
                            <em>Selesai</em> (Sarana sudah tuntas diperbaiki).
                        </small>
                    </div>

                    <br>

                    <!-- Input Umpan Balik / Feedback -->
                    <div class="form-group">
                        <label for="textareaFeedback" class="form-label">Umpan Balik (Feedback) untuk Siswa:</label><br>
                        <textarea name="feedback" id="textareaFeedback" class="form-control" rows="5" cols="60" placeholder="Tuliskan umpan balik atau perkembangan perbaikan sarana di sini..." required><?= htmlspecialchars($aspirasi['feedback'] ?? '') ?></textarea>
                        <small class="form-text">Umpan balik ini akan langsung terbaca oleh siswa saat memeriksa histori aspirasi.</small>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btnSimpanUmpanBalik">Simpan Umpan Balik & Status</button>
                    <a href="admin_aspirasi.php" class="btn btn-secondary">Kembali ke Daftar Aspirasi</a>
                </div>
            </form>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
