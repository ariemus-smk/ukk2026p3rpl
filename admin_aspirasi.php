<?php
/**
 * ==============================================================
 * KELOLA ASPIRASI KESELURUHAN (ADMIN)
 * Sesuai Dokumen UKK Halaman 2:
 * "List aspirasi keseluruhan (per tanggal, per bulan, per siswa, per kategori)"
 * "Status penyelesaian, Umpan balik aspirasi, Histori aspirasi"
 * ==============================================================
 */

require_once __DIR__ . '/fungsi.php';
wajib_login_admin();

$judulHalaman = "Kelola Aspirasi Seluruh Siswa";

// Menyiapkan array filter dari query parameter GET
$filter = [
    'tanggal'     => trim($_GET['tanggal'] ?? ''),
    'bulan'       => (int)($_GET['bulan'] ?? 0),
    'tahun'       => (int)($_GET['tahun'] ?? 0),
    'nis'         => (int)($_GET['nis'] ?? 0),
    'id_kategori' => (int)($_GET['id_kategori'] ?? 0),
];

// Ambil data aspirasi yang telah disaring (atau keseluruhan jika tanpa filter)
$daftarAspirasi = ambil_semua_aspirasi($filter);

// Ambil referensi data untuk dropdown filter
$daftarKategori = ambil_semua_kategori();
$daftarBulan    = daftar_bulan();

// Cek apakah sedang ada filter aktif
$isFiltered = !empty($filter['tanggal']) || $filter['bulan'] > 0 || $filter['tahun'] > 0 || $filter['nis'] > 0 || $filter['id_kategori'] > 0;

require_once __DIR__ . '/header.php';
?>

<section class="section-admin-aspirasi" id="sectionAdminAspirasi">
    <div class="card card-admin">
        <header class="card-header">
            <h2 class="card-title">Daftar Keseluruhan Aspirasi Sarana Sekolah</h2>
            <p class="card-description">
                Administrator dapat melihat histori seluruh pengaduan siswa, menyaring data, dan memberikan umpan balik serta memperbarui status perbaikan.
            </p>
        </header>

        <div class="card-body">
            
            <!-- FORM FILTER: Per Tanggal, Per Bulan, Per Siswa, Per Kategori -->
            <fieldset class="filter-fieldset">
                <legend class="filter-legend"><strong>Filter & Pencarian Aspirasi</strong></legend>
                
                <form action="admin_aspirasi.php" method="GET" class="form form-filter" id="formFilter">
                    
                    <!-- 1. Filter per Tanggal Spesifik -->
                    <div class="form-group filter-item">
                        <label for="filterTanggal" class="form-label">Per Tanggal:</label><br>
                        <input type="date" name="tanggal" id="filterTanggal" class="form-control" value="<?= htmlspecialchars($filter['tanggal']) ?>">
                    </div>

                    <!-- 2. Filter per Bulan & Tahun -->
                    <div class="form-group filter-item">
                        <label for="filterBulan" class="form-label">Per Bulan:</label><br>
                        <select name="bulan" id="filterBulan" class="form-select">
                            <option value="">-- Semua Bulan --</option>
                            <?php foreach ($daftarBulan as $nomorBulan => $namaBulan): ?>
                                <option value="<?= $nomorBulan ?>" <?= $filter['bulan'] === $nomorBulan ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($namaBulan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group filter-item">
                        <label for="filterTahun" class="form-label">Tahun:</label><br>
                        <input type="number" name="tahun" id="filterTahun" class="form-control" placeholder="Contoh: <?= date('Y') ?>" value="<?= $filter['tahun'] > 0 ? $filter['tahun'] : '' ?>">
                    </div>

                    <!-- 3. Filter per Siswa (NIS) -->
                    <div class="form-group filter-item">
                        <label for="filterNis" class="form-label">Per Siswa (NIS):</label><br>
                        <input type="number" name="nis" id="filterNis" class="form-control" placeholder="Masukkan NIS..." value="<?= $filter['nis'] > 0 ? $filter['nis'] : '' ?>">
                    </div>

                    <!-- 4. Filter per Kategori -->
                    <div class="form-group filter-item">
                        <label for="filterKategori" class="form-label">Per Kategori:</label><br>
                        <select name="id_kategori" id="filterKategori" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($daftarKategori as $kat): ?>
                                <option value="<?= (int)$kat['id_kategori'] ?>" <?= $filter['id_kategori'] === (int)$kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['ket_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tombol Aksi Filter -->
                    <div class="form-actions filter-actions">
                        <button type="submit" class="btn btn-primary" id="btnTerapkanFilter">Terapkan Filter</button>
                        <?php if ($isFiltered): ?>
                            <a href="admin_aspirasi.php" class="btn btn-secondary">Reset Filter</a>
                        <?php endif; ?>
                    </div>
                </form>
            </fieldset>

            <hr class="section-divider">

            <!-- Tabel Data Aspirasi -->
            <header class="table-summary-header">
                <h3>
                    Data Aspirasi Ditemukan: <?= count($daftarAspirasi) ?> Pengaduan
                    <?php if ($isFiltered): ?>
                        <small><em>(Sedang memfilter data)</em></small>
                    <?php endif; ?>
                </h3>
            </header>

            <?php if (!empty($daftarAspirasi)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" border="1" cellpadding="8" cellspacing="0" width="100%">
                        <thead class="table-head">
                            <tr>
                                <th>No</th>
                                <th>ID Pelaporan</th>
                                <th>Tanggal</th>
                                <th>Siswa (NIS & Kelas)</th>
                                <th>Kategori</th>
                                <th>Lokasi Sarana</th>
                                <th>Keterangan Kerusakan</th>
                                <th>Status Penyelesaian</th>
                                <th>Umpan Balik (Feedback)</th>
                                <th>Aksi Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($daftarAspirasi as $idx => $row): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><strong>#<?= (int)$row['id_pelaporan'] ?></strong></td>
                                    <td>
                                        <time datetime="<?= htmlspecialchars($row['tanggal']) ?>">
                                            <?= htmlspecialchars(format_tanggal_indonesia($row['tanggal'])) ?>
                                        </time>
                                    </td>
                                    <td>
                                        <strong>NIS: <?= (int)$row['nis'] ?></strong><br>
                                        <small>Kelas: <?= htmlspecialchars($row['kelas']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['ket_kategori']) ?></td>
                                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['ket'])) ?></td>
                                    <td>
                                        <?php
                                        $st = $row['status'];
                                        $badgeClass = 'badge badge-warning';
                                        if ($st === 'Proses') {
                                            $badgeClass = 'badge badge-info';
                                        } elseif ($st === 'Selesai') {
                                            $badgeClass = 'badge badge-success';
                                        }
                                        ?>
                                        <span class="<?= $badgeClass ?>">
                                            <strong><?= htmlspecialchars($st) ?></strong>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['feedback'])): ?>
                                            <article class="feedback-text">
                                                <?= nl2br(htmlspecialchars($row['feedback'])) ?>
                                            </article>
                                        <?php else: ?>
                                            <span class="text-muted"><em>Belum ada umpan balik</em></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Link ke Halaman Utama 2: Umpan Balik Aspirasi -->
                                        <a href="admin_umpan_balik.php?id=<?= (int)$row['id_aspirasi'] ?>" class="btn btn-sm btn-primary">
                                            Beri Umpan Balik / Ubah Status
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Tidak ada data aspirasi yang sesuai dengan kriteria pencarian/filter saat ini.</p>
                </div>
            <?php endif; ?>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
