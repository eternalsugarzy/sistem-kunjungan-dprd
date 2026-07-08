<?php
// 1. Set Halaman Aktif untuk Sidebar
$page = 'verifikasi';

// 2. Panggil Template Header & Sidebar
include 'template/header.php';
include 'template/sidebar.php';

// Helper: konversi tanggal ke nama hari berbahasa Indonesia
function nama_hari_indo($tanggal) {
    $map = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at", 'Saturday' => 'Sabtu'
    ];
    return $map[date('l', strtotime($tanggal))] ?? '';
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Verifikasi Permohonan Masuk</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Verifikasi</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="alert alert-info d-flex align-items-start shadow-sm">
      <i class="ti ti-info-circle f-20 me-2 mt-1"></i>
      <div>
        Halaman ini menampilkan seluruh permohonan kunjungan yang masuk dan masih berstatus
        <b>pending</b>. Untuk <b>menyetujui, menunjuk Penanggung Jawab, dan menjadwalkan</b>
        kunjungan, silakan lanjut ke menu <a href="disposisi_pimpinan.php"><b>Disposisi Pimpinan</b></a>.
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header">
        <h5>Daftar Permohonan Masuk (Pending)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th width="5%">No</th>
                <th>Tanggal Masuk</th>
                <th>Instansi &amp; Perihal</th>
                <th>Jadwal Diahukan</th>
                <th class="text-center">Surat</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              // HANYA TAMPILKAN YANG STATUSNYA PENDING
              $query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending' ORDER BY created_at ASC");

              while ($d = mysqli_fetch_array($query)) {
                  $hari_kunjungan = nama_hari_indo($d['tgl_kunjungan']);
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td>
                    <?= date('d/m/Y', strtotime($d['created_at'])); ?> <br>
                    <span class="badge bg-light-primary text-primary"><?= $d['kode_booking']; ?></span>
                </td>
                <td>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['nama_instansi_tamu']); ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($d['materi_kunjungan']); ?></small><br>
                    <small><i class="ti ti-users"></i> <?= $d['jumlah_peserta_rencana']; ?> Orang</small>
                </td>
                <td>
                    <div class="fw-bold"><?= date('d F Y', strtotime($d['tgl_kunjungan'])); ?></div>
                    <small class="text-muted">Pukul <?= substr($d['waktu_kunjungan'],0,5); ?> (<?= $hari_kunjungan; ?>)</small>
                </td>
                <td class="text-center">
                    <a href="../uploads/<?= $d['file_surat_permohonan']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-file-text"></i> Lihat
                    </a>
                </td>
                <td class="text-center">
                    <span class="badge bg-warning">Menunggu Disposisi</span>
                </td>
              </tr>
              <?php } ?>

              <?php if(mysqli_num_rows($query) == 0): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada permohonan baru.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// 3. Panggil Template Footer
include 'template/footer.php';
?>