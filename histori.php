<?php
/**
 * ==============================================================
 * HISTORI & LACAK ASPIRASI SISWA
 * Sesuai Dokumen UKK Paket 3 Halaman 2 (Hasil yang diharapkan Siswa):
 * - Dapat melihat status penyelesaian
 * - Dapat melihat histori aspirasi User
 * - Dapat melihat umpan balik aspirasi
 * - Dapat melihat progres perbaikan
 * ==============================================================
 */

require_once __DIR__ . '/fungsi.php';

$judulHalaman = "Histori & Lacak Aspirasi Siswa";

$nisCari = isset($_GET['nis']) ? (int)$_GET['nis'] : 0;
$riwayatAspirasi = [];

if ($nisCari > 0) {
    $riwayatAspirasi = ambil_aspirasi_siswa($nisCari);
}

require_once __DIR__ . '/header.php';
?>

<section class="section-histori" id="sectionHistoriAspirasi">
    <div class="card card-histori">
        <header class="card-header">
            <h2 class="card-title">Histori dan Status Aspirasi Siswa</h2>
            <p class="card-description">
                Gunakan halaman ini untuk memantau status penyelesaian pengaduan sarana dan melihat umpan balik dari pihak sekolah.
            </p>
        </header>

        <div class="card-body">
            <!-- Form Pencarian NIS Siswa -->
            <form action="histori.php" method="GET" class="form form-inline" id="formCariNis">
                <div class="form-group">
                    <label for="inputNis" class="form-label">Masukkan NIS Siswa:</label>
                    <input type="number" name="nis" id="inputNis" class="form-control" placeholder="Contoh: 1021001" required value="<?= $nisCari > 0 ? $nisCari : '' ?>">
                    <button type="submit" class="btn btn-primary" id="btnCariHistori">Lihat Aspirasi Saya</button>
                    <?php if ($nisCari > 0): ?>
                        <a href="histori.php" class="btn btn-secondary">Reset Pencarian</a>
                    <?php endif; ?>
                </div>
            </form>

            <hr class="section-divider">

            <!-- Hasil Tampilan Riwayat -->
            <?php if ($nisCari > 0): ?>
                <header class="table-header">
                    <h3>Daftar Pengaduan untuk NIS: <?= htmlspecialchars((string)$nisCari) ?></h3>
                </header>

                <?php if (!empty($riwayatAspirasi)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" border="1" cellpadding="8" cellspacing="0" width="100%">
                            <thead class="table-head">
                                <tr>
                                    <th>No</th>
                                    <th>No. Pelaporan</th>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Keterangan Kerusakan</th>
                                    <th>Status Penyelesaian</th>
                                    <th>Umpan Balik (Feedback) Sekolah</th>
                                </tr>
                            </thead>
                            <tbody class="table-body">
                                <?php foreach ($riwayatAspirasi as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong>#<?= (int)$row['id_pelaporan'] ?></strong></td>
                                        <td>
                                            <time datetime="<?= htmlspecialchars($row['tanggal']) ?>">
                                                <?= htmlspecialchars(format_tanggal_indonesia($row['tanggal'])) ?>
                                            </time>
                                        </td>
                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['ket_kategori']) ?></td>
                                        <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['ket'])) ?></td>
                                        <td>
                                            <?php
                                            $status = $row['status'];
                                            $badgeClass = 'badge badge-warning';
                                            if ($status === 'Proses') {
                                                $badgeClass = 'badge badge-info';
                                            } elseif ($status === 'Selesai') {
                                                $badgeClass = 'badge badge-success';
                                            }
                                            ?>
                                            <span class="<?= $badgeClass ?>">
                                                <strong><?= htmlspecialchars($status) ?></strong>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['feedback'])): ?>
                                                <article class="feedback-content">
                                                    <em><?= nl2br(htmlspecialchars($row['feedback'])) ?></em>
                                                </article>
                                            <?php else: ?>
                                                <span class="text-muted"><em>Menunggu tanggapan admin/sarpras...</em></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Belum ada riwayat pengaduan sarana yang ditemukan untuk NIS <strong><?= htmlspecialchars((string)$nisCari) ?></strong>.</p>
                        <p>Ingin menyampaikan pengaduan sarana? Silakan buka <a href="index.php">Formulir Aspirasi Siswa</a>.</p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    <p>Silakan ketikkan <strong>Nomor Induk Siswa (NIS)</strong> pada kolom di atas untuk melacak perkembangan aspirasi sarana Anda.</p>
                </div>
            <?php endif; ?>
        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
