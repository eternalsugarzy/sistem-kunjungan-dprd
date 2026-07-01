<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI TEMPLATE
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'feedback'; // Indikator sidebar aktif untuk menu feedback

include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Data Feedback &amp; Kuesioner</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Laporan</li>
          <li class="breadcrumb-item text-muted">Feedback Pengunjung</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Riwayat Kepuasan Pelayanan Tamu / Pengunjung</h5>
        <div>
          <!-- TOMBOL REKAP MASSAL BARU -->
          <a href="cetak_rekap_feedback.php" target="_blank" class="btn btn-sm btn-primary me-2">
            <i class="ti ti-printer"></i> Cetak Rekap Laporan
          </a>
          <span class="badge bg-light-primary text-primary border font-monospace">Tabel Feedback</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th width="12%">Kode Booking</th>
                <th width="23%">Instansi / Lembaga</th>
                <th width="15%">Nama / Jabatan</th>
                <th width="12%" class="text-center">Rating Avg</th>
                <th width="23%">Kritik &amp; Saran</th>
                <th width="10%" class="text-center">Aksi</th> </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $has_feedback = false;

              if (isset($koneksi)) {
                  $query_fb = mysqli_query($koneksi, "
                      SELECT f.*, k.kode_booking, k.nama_instansi_tamu, k.tgl_kunjungan 
                      FROM feedback_kunjungan f
                      LEFT JOIN kunjungan k ON f.id_kunjungan = k.id_kunjungan
                      ORDER BY f.id_feedback DESC
                  ");

                  if ($query_fb && mysqli_num_rows($query_fb) > 0) {
                      $has_feedback = true;
                      while ($row = mysqli_fetch_assoc($query_fb)) {
                          $booking   = !empty($row['kode_booking']) ? $row['kode_booking'] : '-';
                          $instansi  = !empty($row['nama_instansi_tamu']) ? $row['nama_instansi_tamu'] : 'Umum / Non-Instansi';
                          $pemberi   = !empty($row['nama_pemberi']) ? htmlspecialchars($row['nama_pemberi']) : 'Anonim';
                          $jabatan   = !empty($row['jabatan_pemberi']) ? ' (' . htmlspecialchars($row['jabatan_pemberi']) . ')' : '';
                          $identitas = $pemberi . $jabatan;
                          $bintang   = !empty($row['rating_keseluruhan']) ? intval(round($row['rating_keseluruhan'])) : 5;
                          $saran     = !empty($row['komentar_saran']) ? htmlspecialchars($row['komentar_saran']) : '<span class="text-muted font-italic">Tidak ada komentar/saran</span>';
              ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($booking); ?></span></td>
                <td><b><?= htmlspecialchars($instansi); ?></b></td>
                <td><small><?= $identitas; ?></small></td>
                <td class="text-center">
                    <div class="text-warning mb-1" style="font-size: 14px;">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $bintang ? '&#9733;' : '&#9734;';
                        }
                        ?>
                    </div>
                    <small class="badge bg-light-warning text-dark style-mini-tag fw-bold"><?= $bintang; ?>.0 / 5.0</small>
                </td>
                <td>
                    <p class="mb-0" style="white-space: normal; word-break: break-word; font-size: 13px; line-height: 1.4;">
                        <?= $saran; ?>
                    </p>
                </td>
                <td class="text-center">
                    <a href="cetak_feedback.php?id=<?= $row['id_feedback']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="ti ti-printer"></i> Cetak
                    </a>
                </td>
              </tr>
              <?php
                      }
                  }
              }

              if (!$has_feedback) {
                  echo '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data feedback.</td></tr>';
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
.style-mini-tag {
    font-size: 10px !important;
    padding: 2px 6px !important;
}
</style>

<?php include 'template/footer.php'; ?>