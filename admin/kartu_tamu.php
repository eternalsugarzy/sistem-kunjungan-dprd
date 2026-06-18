<?php
// PERBAIKAN: Tambahkan '../' agar sistem mencari koneksi.php di luar folder admin/
include '../koneksi.php';

$kode_booking = isset($_GET['kode']) ? mysqli_real_escape_string($koneksi, $_GET['kode']) : '';
$data = null;

if (!empty($kode_booking)) {
    // Ambil data kunjungan dan gabungkan dengan tabel ruangan
    $query = mysqli_query($koneksi, "SELECT k.*, r.nama_ruangan FROM kunjungan k 
                                     LEFT JOIN ruangan r ON k.id_ruangan = r.id_ruangan 
                                     WHERE k.kode_booking = '$kode_booking'");
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
    }
}

// Jika data tidak ditemukan, alihkan kembali ke scanner
if (!$data) {
    echo "<script>alert('Kode Booking tidak ditemukan!'); window.location.href='scan_qr.php';</script>";
    exit;
}

// Format variabel untuk ditampilkan
$instansi = $data['nama_instansi_tamu'];
$tanggal = date('d M Y', strtotime($data['tgl_kunjungan']));
$ruangan = !empty($data['nama_ruangan']) ? $data['nama_ruangan'] : 'Belum ditentukan';
$batas_waktu = !empty($data['batas_waktu_kunjungan']) ? date('H:i', strtotime($data['batas_waktu_kunjungan'])) . " WITA" : '15:00 WITA';

// PERBAIKAN: Tambahkan '../' pada path QR Code dan Assets karena file ini di dalam folder admin/
$qr_path = !empty($data['qr_code_path']) ? '../' . $data['qr_code_path'] : '../assets/images/sample-qr.png';
?>

<!doctype html>
<html lang="id">
<head>
    <title>Kartu Tamu Sementara | SIM-KUNJUNGAN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    
    <style>
        /* Desain ID Card */
        .id-card {
            width: 350px;
            border: 2px solid #343a40;
            border-radius: 15px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        .id-card-header {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
        }
        .id-card-body {
            padding: 20px;
            text-align: center;
        }
        .qr-placeholder {
            width: 150px;
            height: 150px;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }
        
        /* Menghilangkan elemen yang tidak perlu saat di-print */
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .id-card { box-shadow: none; margin-top: 0; }
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

    <div class="container py-5">
        <div class="text-center mb-4 no-print">
            <h3 class="fw-bold">Penerbitan Kartu Tamu</h3>
            <p class="text-muted">Cetak atau kalungkan kartu ini selama berada di area instansi.</p>
        </div>

        <div class="id-card">
            <div class="id-card-header">
                <h5 class="mb-0 text-white fw-bold">KARTU TAMU SEMENTARA</h5>
                <small>DPRD Kota Banjarmasin</small>
            </div>
            <div class="id-card-body">
                
                <div class="qr-placeholder border border-dark rounded bg-light">
                    <img src="<?= $qr_path; ?>" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                
                <h5 class="fw-bold mb-0 text-uppercase"><?= $instansi; ?></h5>
                <p class="text-muted mb-2">Kode: <strong><?= $kode_booking; ?></strong></p>
                
                <hr class="border-dashed my-2">
                
                <div class="row text-start mt-3" style="font-size: 0.9rem;">
                    <div class="col-5 text-muted fw-bold">Tanggal</div>
                    <div class="col-7">: <?= $tanggal; ?></div>
                    
                    <div class="col-5 text-muted fw-bold">Tujuan</div>
                    <div class="col-7">: <?= $ruangan; ?></div>
                    
                    <div class="col-5 text-muted fw-bold">Batas Waktu</div>
                    <div class="col-7 text-danger fw-bold">: <?= $batas_waktu; ?></div>
                </div>
            </div>
            <div class="bg-dark text-white text-center py-2" style="font-size: 0.75rem;">
                Harap dikembalikan/checkout sebelum batas waktu.
            </div>
        </div>
        <div class="text-center mt-4 no-print d-flex justify-content-center gap-2">
            <a href="scan_qr.php" class="btn btn-outline-dark">[ Kembali ke Scanner ]</a>
            <button onclick="window.print()" class="btn btn-dark">
                <i class="ti ti-printer me-1"></i> [ Cetak Kartu ]
            </button>
        </div>
    </div>

    <script src="../assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>