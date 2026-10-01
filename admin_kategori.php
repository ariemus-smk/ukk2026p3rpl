<?php
/**
 * ==============================================================
 * KELOLA DATA KATEGORI SARANA (ADMIN)
 * Sesuai Tabel Kategori pada Gambar Kerja ERD
 * ==============================================================
 */

require_once __DIR__ . '/fungsi.php';
wajib_login_admin();

$judulHalaman = "Kelola Kategori Sarana";
$pesanSukses  = "";
$pesanError   = "";

// Proses Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $ket_kategori = trim($_POST['ket_kategori'] ?? '');
    if (empty($ket_kategori)) {
        $pesanError = "Nama keterangan kategori tidak boleh kosong!";
    } else {
        if (tambah_kategori($ket_kategori)) {
            $pesanSukses = "Kategori sarana '<strong>" . htmlspecialchars($ket_kategori) . "</strong>' berhasil ditambahkan.";
        } else {
            $pesanError = "Gagal menambahkan kategori sarana.";
        }
    }
}

// Proses Hapus Kategori
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        if (hapus_kategori($idHapus)) {
            $pesanSukses = "Kategori berhasil dihapus.";
        } else {
            $pesanError = "Gagal menghapus kategori. Kategori ini mungkin masih digunakan pada laporan aspirasi.";
        }
    }
}

// Ambil daftar kategori terbaru
$daftarKategori = ambil_semua_kategori();

require_once __DIR__ . '/header.php';
?>

<section class="section-kategori" id="sectionKelolaKategori">
    <div class="card card-kategori">
        <header class="card-header">
            <h2 class="card-title">Kelola Kategori Sarana Sekolah</h2>
            <p class="card-description">
                Tambahkan atau kelola kelompok sarana dan prasarana yang dapat dipilih oleh siswa saat menyampaikan aspirasi.
            </p>
        </header>

        <div class="card-body">
            <!-- Pesan Notifikasi -->
            <?php if (!empty($pesanSukses)): ?>
                <aside class="alert alert-success">
                    <p><?= $pesanSukses ?></p>
                </aside>
            <?php endif; ?>

            <?php if (!empty($pesanError)): ?>
                <aside class="alert alert-danger">
                    <p><strong>Perhatian:</strong> <?= htmlspecialchars($pesanError) ?></p>
                </aside>
            <?php endif; ?>

            <!-- Form Tambah Kategori -->
            <fieldset class="form-fieldset">
                <legend class="form-legend"><strong>Tambah Kategori Baru</strong></legend>
                
                <form action="admin_kategori.php" method="POST" class="form form-inline" id="formTambahKategori">
                    <div class="form-group">
                        <label for="ket_kategori" class="form-label">Nama / Keterangan Kategori:</label>
                        <input type="text" name="ket_kategori" id="ket_kategori" class="form-control" placeholder="Contoh: Perpustakaan, Lapangan, AC" required maxlength="30">
                        <button type="submit" name="tambah_kategori" class="btn btn-primary" id="btnSimpanKategori">Tambah Kategori</button>
                    </div>
                </form>
            </fieldset>

            <hr class="section-divider">

            <!-- Tabel Daftar Kategori -->
            <header class="table-header">
                <h3>Daftar Kategori Sarana Tersedia (<?= count($daftarKategori) ?>)</h3>
            </header>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" border="1" cellpadding="8" cellspacing="0" width="100%">
                    <thead class="table-head">
                        <tr>
                            <th width="10%">No</th>
                            <th width="20%">ID Kategori</th>
                            <th>Keterangan Kategori</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if (!empty($daftarKategori)): ?>
                            <?php foreach ($daftarKategori as $i => $kat): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= (int)$kat['id_kategori'] ?></td>
                                    <td><?= htmlspecialchars($kat['ket_kategori']) ?></td>
                                    <td>
                                        <a href="admin_kategori.php?hapus=<?= (int)$kat['id_kategori'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" align="center">Belum ada kategori sarana yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
