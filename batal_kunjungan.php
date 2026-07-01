<?php
// ==========================================
// PENGATURAN INTEGRASI DATABASE & FORM
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';

// Tangkap ID dari buntut URL secara dinamis (?id=...)
$id_kunjungan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$kode_booking  = "";
$instansi_tamu = "";
$tgl_rencana   = "";

// Cari data ke database berdasarkan ID kunjungan yang dikirim
$query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE id_kunjungan = '$id_kunjungan'");

if ($query && mysqli_num_rows($query) > 0) {
    $d = mysqli_fetch_assoc($query);
    $kode_booking  = $d['kode_booking'];
    $instansi_tamu = $d['nama_instansi_tamu'];
    $tgl_rencana   = isset($d['tgl_kunjungan']) ? date('d M Y', strtotime($d['tgl_kunjungan'])) : '-';
} else {
    // Jika ID kosong atau tidak ada di DB, kembalikan secara aman ke halaman cek status
    header("Location: cek_status.php");
    exit();
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Konfirmasi Pembatalan Kunjungan | SIM-KUNJUNGAN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          <img src="assets/images/logo.png" alt="logo" style="height:30px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
      </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white text-center py-3">
                        <h4 class="mb-0 text-white">[ Pembatalan Kunjungan Resmi ]</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="alert alert-warning mb-4">
                            <h6 class="fw-bold">Perhatian!</h6>
                            <small>Kunjungan yang dibatalkan akan merubah status data pada sistem peninjauan Admin.</small>
                        </div>

                        <div class="bg-light p-3 rounded border mb-4" style="font-size: 14px;">
                            <div class="row mb-1">
                                <div class="col-5 text-muted">Kode Booking</div>
                                <div class="col-7 fw-bold text-danger">: <?= htmlspecialchars($kode_booking); ?></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-muted">Instansi</div>
                                <div class="col-7 fw-bold">: <?= htmlspecialchars($instansi_tamu); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-muted">Tgl Rencana</div>
                                <div class="col-7 fw-bold">: <?= $tgl_rencana; ?></div>
                            </div>
                        </div>

                        <form method="POST" action="proses_batal.php">
                            <input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan; ?>"> 
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="alasan_pembatalan" class="form-control border-danger" rows="4" placeholder="Tuliskan alasan mengapa kunjungan dibatalkan (wajib diisi)..." required></textarea>
                            </div>

                            <div class="d-flex justify-content-between gap-3">
                                <a href="cek_status.php" class="btn btn-light border w-50">[ Kembali ]</a>
                                <button type="submit" name="proses_batal" class="btn btn-danger w-50 fw-bold">
                                    [ Batalkan Kunjungan ]
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>