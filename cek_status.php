<?php
include 'koneksi.php';

$data = null;
$pesan_error = "";

// Cek jika ada pengiriman data pencarian
if (isset($_POST['btn_cek']) || isset($_GET['kode'])) {
    
    // Ambil kode dari POST (Form) atau GET (URL)
    $kode_input = isset($_POST['kode_booking']) ? $_POST['kode_booking'] : $_GET['kode'];
    $kode_input = mysqli_real_escape_string($koneksi, $kode_input);

    if(!empty($kode_input)){
        // Cari data di database (JOIN dengan tabel ruangan dan admin agar infonya lengkap)
        $query = "SELECT kunjungan.*, ruangan.nama_ruangan, penanggung_jawab.nama_pj 
                  FROM kunjungan 
                  LEFT JOIN ruangan ON kunjungan.id_ruangan = ruangan.id_ruangan
                  LEFT JOIN penanggung_jawab ON kunjungan.id_pj = penanggung_jawab.id_pj
                  WHERE kode_booking = '$kode_input'";
        
        $hasil = mysqli_query($koneksi, $query);
        
        if(mysqli_num_rows($hasil) > 0){
            $data = mysqli_fetch_assoc($hasil);
        } else {
            $pesan_error = "Kode Booking <b>$kode_input</b> tidak ditemukan. Silakan cek kembali.";
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <title>Cek Status Permohonan | DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          <img src="assets/images/logo-dark.svg" alt="logo" style="height:30px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Kembali ke Beranda</a>
      </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <h3 class="mb-3">Lacak Permohonan</h3>
                        <p class="text-muted">Masukkan Kode Booking / Kode Tiket Anda</p>
                        
                        <form method="POST" action="">
                            <div class="input-group mb-3">
                                <input type="text" name="kode_booking" class="form-control form-control-lg text-center" placeholder="Contoh: REQ-2025-X7A2" value="<?= isset($_POST['kode_booking']) ? $_POST['kode_booking'] : '' ?>" required>
                                <button class="btn btn-primary" type="submit" name="btn_cek">
                                    <i class="ti ti-search me-1"></i> Cek Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if($pesan_error != ""): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    <div><?= $pesan_error; ?></div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if($data != null): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    
                    <div class="card-header d-flex justify-content-between align-items-center 
                        <?php 
                            if($data['status_kegiatan'] == 'pending') echo 'bg-warning';
                            elseif($data['status_kegiatan'] == 'dijadwalkan') echo 'bg-primary'; 
                            elseif($data['status_kegiatan'] == 'selesai') echo 'bg-success'; 
                            else echo 'bg-danger'; 
                        ?> text-white">
                        <h5 class="mb-0 text-white">Status: <?= strtoupper($data['status_kegiatan']); ?></h5>
                        <span class="badge bg-white text-dark"><?= $data['kode_booking']; ?></span>
                    </div>

                    <div class="card-body p-4">
                        
                        <div class="text-center mb-4">
                            <?php if($data['status_kegiatan'] == 'pending'): ?>
                                <i class="ti ti-clock text-warning" style="font-size: 4rem;"></i>
                                <h3 class="mt-2">Sedang Diverifikasi</h3>
                                <p>Mohon menunggu, admin sedang mengecek jadwal.</p>
                            
                            <?php elseif($data['status_kegiatan'] == 'dijadwalkan' || $data['status_kegiatan'] == 'selesai'): ?>
                                <i class="ti ti-calendar-check text-success" style="font-size: 4rem;"></i>
                                <h3 class="mt-2">Jadwal Terkonfirmasi!</h3>
                                <p>Silakan datang sesuai waktu yang ditentukan.</p>

                            <?php else: ?>
                                <i class="ti ti-x text-danger" style="font-size: 4rem;"></i>
                                <h3 class="mt-2">Permohonan Ditolak</h3>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Instansi Pemohon</small>
                                <p class="mb-0 h6"><?= $data['nama_instansi_tamu']; ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Tanggal Rencana</small>
                                <p class="mb-0 h6"><?= date('d F Y', strtotime($data['tgl_kunjungan'])); ?> (Jam <?= $data['waktu_kunjungan']; ?>)</p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <small class="text-muted fw-bold">Tujuan / Materi</small>
                                <p class="mb-0"><?= $data['materi_kunjungan']; ?></p>
                            </div>
                        </div>

                        <?php if($data['status_kegiatan'] == 'dijadwalkan' || $data['status_kegiatan'] == 'selesai'): ?>
                        <div class="alert alert-primary mt-3">
                            <h6 class="alert-heading fw-bold"><i class="ti ti-info-circle me-1"></i> Informasi Pelaksanaan</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="fw-bold">Lokasi Ruangan:</small><br>
                                    <?= $data['nama_ruangan'] ?? 'Belum ditentukan'; ?>
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-bold">Penerima Tamu (PJ):</small><br>
                                    <?= $data['nama_pj'] ?? 'Sekretariat DPRD'; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                    
                    <div class="card-footer bg-light text-center">
                        <small>Data ini digenerate otomatis oleh sistem.</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>