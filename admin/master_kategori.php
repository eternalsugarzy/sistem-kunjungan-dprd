<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'master'; // Menjaga dropdown Pengaturan Data di sidebar tetap terbuka

include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

$pesan_sukses = "";
$pesan_gagal = "";

// Cek apakah tabel kategori_kunjungan ada di database kamu
$table_exists = false;
if (isset($koneksi)) {
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'kategori_kunjungan'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        $table_exists = true;
    }
}

// ---------------------------------------------------------
// LOGIC PHP: TAMBAH KATEGORI (HANYA BERJALAN JIKA TABEL ADA)
// ---------------------------------------------------------
if (isset($_POST['tambah_kategori']) && $table_exists) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $kode_kategori = mysqli_real_escape_string($koneksi, $_POST['kode_kategori']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    $query_ins = "INSERT INTO kategori_kunjungan (nama_kategori, kode_kategori, deskripsi, is_active) 
                  VALUES ('$nama_kategori', '$kode_kategori', '$deskripsi', 1)";
                  
    if (mysqli_query($koneksi, $query_ins)) {
        $pesan_sukses = "Kategori Baru Berhasil Ditambahkan!";
    } else {
        $pesan_gagal = "Gagal menambah data: " . mysqli_error($koneksi);
    }
}

// ---------------------------------------------------------
// LOGIC PHP: HAPUS KATEGORI
// ---------------------------------------------------------
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && $table_exists) {
    $id_kategori = mysqli_real_escape_string($koneksi, $_GET['id']);
    if (mysqli_query($koneksi, "DELETE FROM kategori_kunjungan WHERE id_kategori='$id_kategori'")) {
        echo "<script>window.location='master_kategori.php';</script>";
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Data Master Kategori Kunjungan</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Pengaturan Data</li>
          <li class="breadcrumb-item text-muted">Kategori Kunjungan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <!-- PEMBERITAHUAN JIKA TABEL BELUM DIBUAT -->
    <?php if (!$table_exists): ?>
        <div class="col-12">
            <div class="alert alert-warning border-dashed-line d-flex align-items-center" role="alert">
                <i class="ti ti-database-exclamation me-2 f-20"></i>
                <div>
                    <strong>Info Sistem:</strong> Tabel <code>kategori_kunjungan</code> belum terdeteksi di database kamu. Saat ini sistem menampilkan <strong>Mode Simulasi / Data Tiruan</strong> agar sidang Sempro kamu berjalan lancar tanpa error[cite: 2].
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- KOLOM KIRI: FORM INPUT KATEGORI -->
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card border border-dark h-100">
            <div class="card-header bg-white border-bottom-dark py-3">
                <h5 class="mb-0 fw-bold text-dark font-monospace">[ Tambah Kategori Baru ]</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-2">
                        <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Nama Kategori *</label>
                        <input type="text" name="nama_kategori" class="form-control form-control-sm" placeholder="Contoh: Studi Tiru" required <?= !$table_exists ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Kode Kategori *</label>
                        <input type="text" name="kode_kategori" class="form-control form-control-sm font-monospace" placeholder="Contoh: KTG-01" required <?= !$table_exists ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold mb-1" style="font-size:13px;">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Penjelasan singkat mengenai kategori kunjungan..." <?= !$table_exists ? 'disabled' : ''; ?>></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="tambah_kategori" class="btn btn-dark text-white py-2 fw-bold font-monospace" style="border-radius:0;" <?= !$table_exists ? 'disabled' : ''; ?>>
                            [ Simpan Kategori ]
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: DAFTAR KATEGORI -->
    <div class="col-xl-8 col-md-12 mb-4">
        <div class="card border border-dark h-100">
            <div class="card-header bg-white border-bottom-dark py-3">
                <h5 class="mb-0 fw-bold text-dark font-monospace">[ Tabel Referensi Kategori Kunjungan ]</h5>
            </div>
            <div class="card-body p-0">
                
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success m-3"><i class="ti ti-circle-check me-2"></i><?= $pesan_sukses; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="8%">No</th>
                                <th width="20%">Kode</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $real_data = false;

                            if ($table_exists) {
                                $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori_kunjungan ORDER BY id_kategori DESC");
                                if ($q_kat && mysqli_num_rows($q_kat) > 0) {
                                    $real_data = true;
                                    while ($k = mysqli_fetch_array($q_kat)) {
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($k['kode_kategori']); ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($k['nama_kategori']); ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($k['deskripsi'] ?: '-'); ?></td>
                                <td class="text-center">
                                    <a href="master_kategori.php?aksi=hapus&id=<?= $k['id_kategori']; ?>" class="btn btn-sm btn-light text-danger border" onclick="return confirm('Hapus kategori ini?')">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                                    }
                                }
                            }

                            // FALLBACK MOCKUP DATA PROP-SEMPRO JIKA TABEL DB BELUM DI-BUILD
                            if (!$real_data) {
                                $mock_kategori = [
                                    ['KTG-01', 'Kunjungan Kerja', 'Agenda dinas resmi antar lembaga legislatif daerah.'],
                                    ['KTG-02', 'Audiensi', 'Pertemuan formal mendengar aspirasi organisasi / masyarakat.'],
                                    ['KTG-03', 'Studi Tiru', 'Kunjungan komparatif adopsi rancangan peraturan daerah.'],
                                    ['KTG-04', 'Konsultasi', 'Koordinasi teknis penanganan masalah atau birokrasi daerah.']
                                ];
                                foreach ($mock_kategori as $index => $m) {
                                    echo "<tr>
                                        <td>" . ($index + 1) . "</td>
                                        <td class='font-monospace fw-bold text-primary'>{$m[0]}</td>
                                        <td class='fw-bold text-dark'>{$m[1]}</td>
                                        <td class='text-muted small'>{$m[2]}</td>
                                        <td class='text-center'>
                                            <button type='button' class='btn btn-sm btn-light text-muted border' disabled>[X]</button>
                                        </td>
                                    </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dark { border: 2px solid #2e2e2e !important; border-radius: 4px !important; }
.border-bottom-dark { border-bottom: 2px solid #2e2e2e !important; }
.border-dashed-line { border-style: dashed !important; border-color: #eab308 !important; }
</style>

<?php include 'template/footer.php'; ?>