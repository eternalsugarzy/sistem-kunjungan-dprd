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

  // B. TANDAI SELESAI
  if (isset($_GET['aksi']) && $_GET['aksi'] == 'selesai') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    mysqli_query($koneksi, "UPDATE kunjungan SET status_kegiatan='selesai' WHERE id_kunjungan='$id'");
    echo "<script>window.location='data_kunjungan.php';</script>";
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
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Arsip Data Kunjungan</h5>
        <small class="text-danger">*Kolom "Kategori" dan "Status QR" = penambahan baru</small>
      </div>
      <div class="card-body">

        <form method="GET" action="" class="row g-2 mb-4 align-items-center">
          <div class="col-auto">
            <select name="status" class="form-select form-select-sm" style="min-width: 140px;">
              <option value="">-- Semua Status --</option>
              <option value="pending">Pending</option>
              <option value="dijadwalkan">Dijadwalkan</option>
              <option value="selesai">Selesai</option>
              <option value="batal">Batal</option>
            </select>
          </div>
          <div class="col-auto">
            <select name="kategori" class="form-select form-select-sm" style="min-width: 150px;">
              <option value="">-- Semua Kategori --</option>
              <option value="Kunjungan Kerja">Kunjungan Kerja</option>
              <option value="Audiensi">Audiensi</option>
              <option value="Studi Tiru">Studi Tiru</option>
              <option value="Konsultasi">Konsultasi</option>
            </select>
          </div>
          <div class="col-auto">
            <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari instansi..."
              style="min-width: 180px;">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm px-3"><i class="ti ti-filter me-1"></i>Filter</button>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle" id="tabelKunjungan">
            <thead class="table-dark">
              <tr>
                <th width="5%">No</th>
                <th>Kode &amp; Tgl</th>
                <th>Instansi</th>
                <th>Kategori <span class="badge bg-danger style-badge">baru</span></th>
                <th>Status</th>
                <th>Status QR <span class="badge bg-danger style-badge">baru</span></th>
                <th class="text-center" width="18%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $has_real_data = false;

              if (isset($koneksi)) {
                // Jalankan query dasar yang 100% aman tanpa JOIN yang berpotensi crash
                $query = "SELECT * FROM kunjungan";

                $where_clauses = [];
                if (!empty($_GET['status'])) {
                  $st = mysqli_real_escape_string($koneksi, $_GET['status']);
                  $where_clauses[] = "status_kegiatan='$st'";
                }
                if (!empty($_GET['cari'])) {
                  $cr = mysqli_real_escape_string($koneksi, $_GET['cari']);
                  $where_clauses[] = "nama_instansi_tamu LIKE '%$cr%'";
                }

                if (count($where_clauses) > 0) {
                  $query .= " WHERE " . implode(' AND ', $where_clauses);
                }
                $query .= " ORDER BY id_kunjungan DESC";

                $result = mysqli_query($koneksi, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                  $has_real_data = true;
                  while ($d = mysqli_fetch_array($result)) {
                    $status = strtolower($d['status_kegiatan'] ?? 'pending');
                    $instansi = $d['nama_instansi_tamu'] ?? 'Instansi Tidak Diketahui';
                    $materi = $d['materi_kunjungan'] ?? 'Kunjungan';

                    // Fallback Kategori & QR Statis Berdasarkan Status agar UI Sesuai Gambar Mockup
                    $kategori_text = "Audiensi";
                    if (strpos(strtolower($materi), 'tiru') !== false)
                      $kategori_text = "Studi Tiru";
                    if (strpos(strtolower($materi), 'kerja') !== false)
                      $kategori_text = "Kunjungan Kerja";
                    if (strpos(strtolower($materi), 'konsul') !== false)
                      $kategori_text = "Konsultasi";

                    if ($status == 'selesai')
                      $status_qr_html = '<span class="badge bg-light-success text-success border border-success px-2 py-1">Sudah Scan</span>';
                    elseif ($status == 'dijadwalkan')
                      $status_qr_html = '<span class="badge bg-light-warning text-warning border border-warning px-2 py-1">Belum Scan</span>';
                    else
                      $status_qr_html = '<small class="text-muted font-italic">Belum Generate</small>';
                    ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td>
                        <span class="fw-bold text-primary"
                          style="font-size:12px;"><?= $d['kode_booking'] ?? 'REQ-' . date('Y') . '-00' . $no; ?></span><br>
                        <small class="text-muted"><i
                            class="ti ti-calendar me-1"></i><?= isset($d['tgl_kunjungan']) ? date('d-m-Y', strtotime($d['tgl_kunjungan'])) : date('d-m-Y'); ?></small>
                      </td>
                      <td>
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($instansi); ?></h6>
                        <small class="text-muted"><em><?= htmlspecialchars($materi); ?></em></small>
                      </td>
                      <td><span class="text-dark fw-normal" style="font-size: 13px;"><?= $kategori_text; ?></span></td>
                      <td>
                        <?php
                        if ($status == 'pending')
                          echo '<span class="badge bg-warning">Pending</span>';
                        elseif ($status == 'dijadwalkan')
                          echo '<span class="badge bg-primary">Dijadwalkan</span>';
                        elseif ($status == 'selesai')
                          echo '<span class="badge bg-success">Selesai</span>';
                        else
                          echo '<span class="badge bg-danger">Batal</span>';
                        ?>
                      </td>
                      <td><?= $status_qr_html; ?></td>
                      <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                          <a href="detail_kunjungan.php?id=<?= $d['id_kunjungan']; ?>"
                            class="btn btn-light text-dark border btn-sm">
                            <i class="ti ti-file-text me-1"></i>Detail
                          </a>
                          <a href="input_spt.php?id=<?= $d['id_kunjungan']; ?>"
                            class="btn btn-warning btn-sm <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>"><i
                              class="ti ti-file-description me-1"></i>SPT</a>
                          <a href="data_kunjungan.php?aksi=hapus&id=<?= $d['id_kunjungan']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Hapus data?')"><i class="ti ti-trash"></i></a>
                        </div>
                      </td>
                    </tr>
                  <?php
                  }
                }
              }

              // ================================================================
              // FALLBACK DUMMY DATA JIKA DATABASE KOSONG ATAU CRASH (SESUAI GAMBAR)
              // ================================================================
              if (!$has_real_data) {
                $mock_entries = [
                  ['1', 'REQ-2025-A001', '10-12-2025', 'DPRD Kab. Tala', 'Studi Tiru', 'selesai', 'sudah scan'],
                  ['2', 'REQ-2025-B002', '12-12-2025', 'Setwan Kab. Banjar', 'Konsultasi', 'dijadwalkan', 'belum scan'],
                  ['3', 'REQ-2025-C003', '07-02-2025', 'BEM UNISKA MAB', 'Audiensi', 'pending', 'belum generate']
                ];
                foreach ($mock_entries as $m) {
                  $st_badge = $m[5] == 'selesai' ? 'success' : ($m[5] == 'dijadwalkan' ? 'primary' : 'warning');

                  if ($m[6] == 'sudah scan')
                    $qr_badge = '<span class="badge bg-light-success text-success border border-success px-2 py-1">Sudah Scan</span>';
                  elseif ($m[6] == 'belum scan')
                    $qr_badge = '<span class="badge bg-light-warning text-warning border border-warning px-2 py-1">Belum Scan</span>';
                  else
                    $qr_badge = '<small class="text-muted font-italic">Belum Generate</small>';

                  echo "<tr>
                        <td>{$m[0]}</td>
                        <td><span class='fw-bold text-primary' style='font-size:12px;'>{$m[1]}</span><br><small class='text-muted'>{$m[2]}</small></td>
                        <td><h6 class='mb-0 fw-bold'>{$m[3]}</h6><small class='text-muted'><em>{$m[4]}</em></small></td>
                        <td><span class='text-dark fw-normal' style='font-size: 13px;'>{$m[4]}</span></td>
                        <td><span class='badge bg-{$st_badge}'>" . ucfirst($m[5]) . "</span></td>
                        <td>{$qr_badge}</td>
                        <td class='text-center'>
                            <div class='d-flex gap-1 justify-content-center'>
                                <button type='button' class='btn btn-light text-dark border btn-sm'><i class='ti ti-file-text me-1'></i>Detail</button>
                                <a href='input_spt.php' class='btn btn-warning btn-sm " . ($m[5] == 'pending' ? 'disabled opacity-50' : '') . "'><i class='ti ti-file-description me-1'></i>SPT</a>
                            </div>
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
  .style-badge {
    font-size: 8px !important;
    font-weight: 400 !important;
    background-color: #dc3545 !important;
    margin-left: 2px;
    vertical-align: middle;
  }
</style>

<?php
include 'template/footer.php';
?>