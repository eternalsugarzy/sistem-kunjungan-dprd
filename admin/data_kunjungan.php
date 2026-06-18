<?php
// ==========================================
// PENGATURAN DEBUG ERROR (WAJIB PALING ATAS)
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Set Halaman Aktif untuk Sidebar
$page = 'data_kunjungan';

// 2. Panggil Template Header & Sidebar
include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS (Anti-stuck untuk div.loader-bg)
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// ---------------------------------------------------------
// LOGIC PHP (HAPUS & SELESAI) - AMAN DENGAN ISSET CHECK
// ---------------------------------------------------------
if (isset($koneksi)) {
  // A. HAPUS DATA
  if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    $cek_file = mysqli_query($koneksi, "SELECT file_surat_permohonan FROM kunjungan WHERE id_kunjungan='$id'");
    if ($cek_file && mysqli_num_rows($cek_file) > 0) {
      $data_file = mysqli_fetch_assoc($cek_file);
      $path_file = "../uploads/" . $data_file['file_surat_permohonan'];
      if (!empty($data_file['file_surat_permohonan']) && file_exists($path_file)) {
        unlink($path_file);
      }
    }

    $query_hapus = "DELETE FROM kunjungan WHERE id_kunjungan='$id'";
    if (mysqli_query($koneksi, $query_hapus)) {
      echo "<script>alert('Data Berhasil Dihapus!'); window.location='data_kunjungan.php';</script>";
    }
  }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Data Semua Kunjungan</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Data Kunjungan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card shadow-sm border-dark">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">Arsip Data Kunjungan</h5>
        <small class="text-warning">*Kategori dinamis terhubung ke tabel master</small>
      </div>
      <div class="card-body">

        <form method="GET" action="" class="row g-2 mb-4 align-items-center">
          <div class="col-auto">
            <select name="status" class="form-select border-dark" style="min-width: 160px;">
              <option value="">-- Semua Status --</option>
              <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
              <option value="dijadwalkan" <?= (isset($_GET['status']) && $_GET['status'] == 'dijadwalkan') ? 'selected' : ''; ?>>Dijadwalkan</option>
              <option value="sedang berkunjung" <?= (isset($_GET['status']) && $_GET['status'] == 'sedang berkunjung') ? 'selected' : ''; ?>>Sedang Berkunjung</option>
              <option value="selesai" <?= (isset($_GET['status']) && $_GET['status'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
              <option value="batal" <?= (isset($_GET['status']) && $_GET['status'] == 'batal') ? 'selected' : ''; ?>>Batal</option>
            </select>
          </div>
          <div class="col-auto">
            <input type="text" name="cari" class="form-control border-dark" placeholder="Cari instansi..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>" style="min-width: 200px;">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-dark px-4"><i class="ti ti-filter me-1"></i>Filter</button>
            <?php if(isset($_GET['status']) || isset($_GET['cari'])): ?>
                <a href="data_kunjungan.php" class="btn btn-outline-danger ms-2">Reset</a>
            <?php endif; ?>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle" id="tabelKunjungan">
            <thead class="table-dark">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Kode &amp; Tgl</th>
                <th width="20%">Instansi</th>
                <th width="12%">Kategori</th>
                <th width="12%" class="text-center">Status</th>
                <th width="10%" class="text-center">QR Scan</th>
                <th class="text-center" width="26%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $any_data = false;

              if (isset($koneksi)) {
                $query = "SELECT kunjungan.*, IFNULL(kategori_kunjungan.nama_kategori, 'Umum') as nama_kategori 
                          FROM kunjungan 
                          LEFT JOIN kategori_kunjungan ON kunjungan.id_kategori = kategori_kunjungan.id_kategori";

                $where_clauses = [];
                if (!empty($_GET['status'])) {
                  $st = mysqli_real_escape_string($koneksi, $_GET['status']);
                  $where_clauses[] = "kunjungan.status_kegiatan='$st'";
                }
                if (!empty($_GET['cari'])) {
                  $cr = mysqli_real_escape_string($koneksi, $_GET['cari']);
                  $where_clauses[] = "kunjungan.nama_instansi_tamu LIKE '%$cr%'";
                }

                if (count($where_clauses) > 0) {
                  $query .= " WHERE " . implode(' AND ', $where_clauses);
                }
                $query .= " ORDER BY kunjungan.id_kunjungan DESC";

                $result = mysqli_query($koneksi, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                  $any_data = true;
                  while ($d = mysqli_fetch_array($result)) {
                    $status = strtolower($d['status_kegiatan'] ?? 'pending');
                    $instansi = $d['nama_instansi_tamu'] ?? 'Instansi Tidak Diketahui';
                    $kategori_text = $d['nama_kategori'];

                    if ($status == 'selesai' || $status == 'sedang berkunjung') {
                      $status_qr_html = '<span class="badge bg-light-success text-success border border-success px-2 py-1">Sudah Scan</span>';
                    } elseif ($status == 'dijadwalkan') {
                      $status_qr_html = '<span class="badge bg-light-warning text-warning border border-warning px-2 py-1">Belum Scan</span>';
                    } else {
                      $status_qr_html = '<small class="text-muted font-italic">Belum Generate</small>';
                    }
                    ?>
                    <tr>
                      <td class="text-center"><?= $no++; ?></td>
                      <td>
                        <span class="fw-bold text-dark font-monospace" style="font-size:13px;"><?= htmlspecialchars($d['kode_booking']); ?></span><br>
                        <small class="text-muted"><i class="ti ti-calendar me-1"></i><?= date('d M Y', strtotime($d['tgl_kunjungan'])); ?></small>
                      </td>
                      <td>
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($instansi); ?></h6>
                        <small class="text-muted"><em><?= htmlspecialchars($d['materi_kunjungan'] ?? ''); ?></em></small>
                      </td>
                      <td>
                        <span class="badge bg-light-secondary text-dark border" style="font-size: 11px;"><?= htmlspecialchars($kategori_text); ?></span>
                      </td>
                      <td class="text-center">
                        <?php
                        if ($status == 'pending') {
                            echo '<span class="badge bg-warning">Pending</span>';
                        } elseif ($status == 'dijadwalkan') {
                            echo '<span class="badge bg-primary">Dijadwalkan</span>';
                        } elseif ($status == 'sedang berkunjung') {
                            echo '<span class="badge bg-info text-white"><i class="ti ti-loader rotate-refresh me-1"></i>Berkunjung</span>';
                        } elseif ($status == 'selesai') {
                            echo '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Selesai</span>';
                        } else {
                            echo '<span class="badge bg-danger">Batal</span>';
                        }
                        ?>
                      </td>
                      <td class="text-center"><?= $status_qr_html; ?></td>
                      <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                          <a href="detail_kunjungan.php?id=<?= $d['id_kunjungan']; ?>" class="btn btn-dark btn-sm">
                            <i class="ti ti-file-text me-1"></i>Detail
                          </a>
                          <a href="input_spt.php?id=<?= $d['id_kunjungan']; ?>" class="btn btn-outline-dark btn-sm <?= ($status == 'pending' || $status == 'batal') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-file-description me-1"></i>Input SPT
                          </a>
                          <a href="data_kunjungan.php?aksi=hapus&id=<?= $d['id_kunjungan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus permanen arsip kunjungan ini?')">
                            <i class="ti ti-trash me-1"></i>Hapus
                          </a>
                        </div>
                      </td>
                    </tr>
                    <?php
                  }
                }
              }

              if (!$any_data) {
                echo '<tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-folder-off f-24 d-block mb-2"></i>Tidak ada arsip data kunjungan yang tersimpan.</td></tr>';
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
/* Animasi kecil untuk status sedang berkunjung */
@keyframes spin { 100% { transform: rotate(360deg); } }
.rotate-refresh { display: inline-block; animation: spin 2s linear infinite; }
</style>

<?php
include 'template/footer.php';
?>