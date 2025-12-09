<?php
// 1. Set Halaman Aktif
$page = 'dashboard';

// 2. Panggil Template
include 'template/header.php';
include 'template/sidebar.php';

// 3. LOGIC PHP (Hitung Data)
// Kita tambahkan pengecekan error agar tahu jika query gagal

// A. Hitung Pending
$q_pending = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending'");
if ($q_pending) {
    $jml_pending = mysqli_num_rows($q_pending);
} else {
    $jml_pending = "Err"; // Tampilkan Err jika query gagal
}

// B. Hitung Hari Ini
$tgl_ini = date('Y-m-d');
$q_today = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE tgl_kunjungan='$tgl_ini'");
if ($q_today) {
    $jml_today = mysqli_num_rows($q_today);
} else {
    $jml_today = 0;
}

// C. Hitung Selesai
$q_selesai = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='selesai'");
if ($q_selesai) {
    $jml_selesai = mysqli_num_rows($q_selesai);
} else {
    $jml_selesai = 0;
}
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="ti ti-info-circle me-2 f-20"></i>
            <div>
                Selamat Datang, <strong><?= $_SESSION['nama'] ?? 'Admin'; ?></strong>. Sistem siap digunakan.
            </div>
        </div>
    </div>
</div>

<div class="row">
  
  <div class="col-xl-4 col-md-6">
    <div class="card bg-light-warning dashnum-card overflow-hidden">
      <span class="round small bg-warning"></span>
      <span class="round big bg-warning"></span>
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="avtar avtar-lg bg-warning text-white">
                <i class="ti ti-clock"></i>
            </div>
          </div>
        </div>
        <span class="text-dark d-block f-34 f-w-500 my-2">
            <?= $jml_pending; ?>
        </span>
        <p class="mb-0 opacity-75 text-dark">Menunggu Verifikasi</p>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-md-6">
    <div class="card bg-light-primary dashnum-card overflow-hidden">
      <span class="round small bg-primary"></span>
      <span class="round big bg-primary"></span>
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="avtar avtar-lg bg-primary text-white">
                <i class="ti ti-calendar"></i>
            </div>
          </div>
        </div>
        <span class="text-dark d-block f-34 f-w-500 my-2">
            <?= $jml_today; ?>
        </span>
        <p class="mb-0 opacity-75 text-dark">Kunjungan Hari Ini</p>
      </div>
    </div>
  </div>
  
  <div class="col-xl-4 col-md-12">
    <div class="card bg-light-success dashnum-card overflow-hidden">
      <span class="round small bg-success"></span>
      <span class="round big bg-success"></span>
      <div class="card-body">
        <div class="row">
          <div class="col">
            <div class="avtar avtar-lg bg-success text-white">
                <i class="ti ti-checks"></i>
            </div>
          </div>
        </div>
        <span class="text-dark d-block f-34 f-w-500 my-2">
            <?= $jml_selesai; ?>
        </span>
        <p class="mb-0 opacity-75 text-dark">Total Kunjungan Selesai</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-12 col-md-12">
    <div class="card">
      <div class="card-header">
        <h5>5 Permohonan Kunjungan Terbaru</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Instansi</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_terbaru = mysqli_query($koneksi, "SELECT * FROM kunjungan ORDER BY created_at DESC LIMIT 5");
                    if ($q_terbaru && mysqli_num_rows($q_terbaru) > 0) {
                        while($d = mysqli_fetch_array($q_terbaru)){
                    ?>
                    <tr>
                        <td>
                            <h6 class="mb-0"><?= $d['nama_instansi_tamu']; ?></h6>
                            <small class="text-muted"><?= $d['kode_booking']; ?></small>
                        </td>
                        <td><?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?></td>
                        <td>
                            <?php 
                            if($d['status_kegiatan'] == 'pending'){
                                echo '<span class="badge bg-warning">Pending</span>';
                            } elseif($d['status_kegiatan'] == 'dijadwalkan'){
                                echo '<span class="badge bg-primary">Dijadwalkan</span>';
                            } elseif($d['status_kegiatan'] == 'selesai'){
                                echo '<span class="badge bg-success">Selesai</span>';
                            } else {
                                echo '<span class="badge bg-danger">Batal</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo '<tr><td colspan="3" class="text-center">Belum ada data kunjungan</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>