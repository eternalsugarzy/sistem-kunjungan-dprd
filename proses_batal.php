<?php
// ==========================================
// PENGATURAN ENGINE PROSES UPDATE DATA
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php'; 

$status_sukses = false;
$pesan_error = "";
$kode_booking = "";
$instansi_tamu = "";

if (isset($_POST['proses_batal'])) {
    
    $id_kunjungan      = intval($_POST['id_kunjungan']);
    $alasan_pembatalan = mysqli_real_escape_string($koneksi, $_POST['alasan_pembatalan']);
    
    // 1. Tarik info kode booking asli sebelum dieksekusi agar teks pesan sukses tidak meleset
    $q_detail = mysqli_query($koneksi, "SELECT kode_booking, nama_instansi_tamu FROM kunjungan WHERE id_kunjungan = '$id_kunjungan'");
    if ($q_detail && mysqli_num_rows($q_detail) > 0) {
        $d_detail      = mysqli_fetch_assoc($q_detail);
        $kode_booking  = $d_detail['kode_booking'];
        $instansi_tamu = $d_detail['nama_instansi_tamu'];
    }

    // 2. Cek apakah ada kolom alasan_pembatalan di tabel kunjungan kamu
    $list_kolom = [];
    $check_fields = mysqli_query($koneksi, "SHOW COLUMNS FROM kunjungan");
    if ($check_fields && mysqli_num_rows($check_fields) > 0) {
        while ($row_kolom = mysqli_fetch_assoc($check_fields)) {
            $list_kolom[] = $row_kolom['Field'];
        }
    }

    // 3. Eksekusi UPDATE merubah status_kegiatan menjadi 'batal' (Otomatis ter-update ke Admin)
    if (in_array('alasan_pembatalan', $list_kolom)) {
        $query_update = "UPDATE kunjungan SET 
                            status_kegiatan = 'batal', 
                            alasan_pembatalan = '$alasan_pembatalan', 
                            tgl_pembatalan = NOW() 
                         WHERE id_kunjungan = '$id_kunjungan'";
    } else {
        $query_update = "UPDATE kunjungan SET status_kegiatan = 'batal', tgl_pembatalan = NOW() WHERE id_kunjungan = '$id_kunjungan'";
    }

    if (mysqli_query($koneksi, $query_update)) {
        $status_sukses = true;
    } else {
        $pesan_error = mysqli_error($koneksi);
    }
} else {
    header("Location: cek_status.php");
    exit();
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Status Pembatalan | SIM-KUNJUNGAN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          SIM-KUNJUNGAN
        </a>
      </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <?php if ($status_sukses): ?>
                    <div class="card shadow border-danger">
                        <div class="card-body text-center p-5">
                            <div class="text-danger mb-4">
                                <i class="fa-solid fa-circle-xmark" style="font-size: 55px;"></i>
                            </div>
                            <h3 class="text-danger fw-bold mb-3">Pembatalan Berhasil!</h3>
                            
                            <div class="p-3 bg-light rounded border border-danger mb-3" style="font-size: 15px; line-height: 1.5;">
                                Kode Kunjungan <strong class="text-danger font-monospace"><?= htmlspecialchars($kode_booking); ?></strong> telah mematalkan pesanan agenda kunjungan.
                            </div>
                            
                            <p class="text-muted small">
                                Data ini otomatis terrekam ke sistem pusat dan status kegiatan pada halaman admin Anda sekarang telah berubah menjadi batal.
                            </p>

                            <div class="mt-4">
                                <a href="cek_status.php" class="btn btn-outline-secondary px-4">[ Selesai ]</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow border-warning">
                        <div class="card-body text-center p-5">
                            <h3 class="text-warning fw-bold mb-2">Gagal Memproses</h3>
                            <div class="alert alert-danger text-start small"><?= $pesan_error; ?></div>
                            <a href="javascript:history.back()" class="btn btn-dark px-4">Kembali</a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>