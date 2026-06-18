<?php
include 'koneksi.php';

$data = null;
$pesan_error = "";

// Cek jika ada pengiriman data pencarian
if (isset($_POST['btn_cek']) || isset($_GET['kode'])) {

    // Ambil kode dari POST (Form) atau GET (URL)
    $kode_input = isset($_POST['kode_booking']) ? $_POST['kode_booking'] : $_GET['kode'];
    $kode_input = mysqli_real_escape_string($koneksi, $kode_input);

    if (!empty($kode_input)) {
        // Cari data di database (JOIN dengan tabel ruangan dan admin agar infonya lengkap)
        $query = "SELECT kunjungan.*, ruangan.nama_ruangan, penanggung_jawab.nama_pj 
                  FROM kunjungan 
                  LEFT JOIN ruangan ON kunjungan.id_ruangan = ruangan.id_ruangan
                  LEFT JOIN penanggung_jawab ON kunjungan.id_pj = penanggung_jawab.id_pj
                  WHERE kode_booking = '$kode_input'";

        $hasil = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($hasil) > 0) {
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
    <title>Cek Status & E-Ticket | SIM-KUNJUNGAN DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />

    <style>
        /* Desain Khusus Kotak QR Code E-Ticket */
        .qr-ticket-box {
            border: 2px dashed #343a40;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            background-color: #fff;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm border-bottom border-dark">
        <div class="container">
            <a class="navbar-brand text-dark fw-bold" href="index.php">
                <img src="assets/images/logo.png" alt="logo" style="height:30px" class="me-2" />
                [SG] SIM-KUNJUNGAN
            </a>
            <a href="index.php" class="btn btn-sm btn-outline-dark">Kembali ke Beranda</a>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <div class="card shadow-sm mb-4 border-dark">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0 text-white"><i class="ti ti-search me-2"></i>Lacak Status & E-Ticket</h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <p class="text-muted">Masukkan Kode Booking / Kode Tiket Anda (Cth: REQ-2025-XXXX)</p>

                        <form method="POST" action="">
                            <div class="input-group mb-3">
                                <input type="text" name="kode_booking" class="form-control form-control-lg text-center border-dark"
                                    placeholder="REQ-..."
                                    value="<?= isset($_POST['kode_booking']) ? $_POST['kode_booking'] : (isset($_GET['kode']) ? $_GET['kode'] : '') ?>"
                                    required>
                                <button class="btn btn-dark px-4" type="submit" name="btn_cek">
                                    [ Cek Status ]
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($pesan_error != ""): ?>
                <div class="alert alert-danger d-flex align-items-center border-danger" role="alert">
                    <i class="ti ti-alert-circle me-2 f-24"></i>
                    <div><?= $pesan_error; ?></div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if ($data != null): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-dark">

                    <div class="card-header d-flex justify-content-between align-items-center 
                        <?php
                            if ($data['status_kegiatan'] == 'pending') echo 'bg-warning';
                            elseif ($data['status_kegiatan'] == 'dijadwalkan') echo 'bg-success';
                            elseif ($data['status_kegiatan'] == 'selesai') echo 'bg-info';
                            else echo 'bg-danger';
                        ?> text-dark border-bottom border-dark">
                        
                        <h5 class="mb-0 fw-bold <?php if($data['status_kegiatan'] == 'dijadwalkan' || $data['status_kegiatan'] == 'batal') echo 'text-white'; ?>">
                            Status: <?= strtoupper($data['status_kegiatan']); ?>
                        </h5>
                        <span class="badge bg-white text-dark border border-dark f-14 px-3 py-2"><?= $data['kode_booking']; ?></span>
                    </div>

                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <?php if ($data['status_kegiatan'] == 'pending'): ?>
                            <i class="ti ti-clock text-warning" style="font-size: 4rem;"></i>
                            <h3 class="mt-2 fw-bold">Sedang Diverifikasi</h3>
                            <p class="text-muted">Mohon menunggu, admin sedang mengecek ketersediaan jadwal pejabat dan ruangan.</p>

                            <?php elseif ($data['status_kegiatan'] == 'dijadwalkan'): ?>
                            <h3 class="mt-2 fw-bold text-success"><i class="ti ti-circle-check me-2"></i>Kunjungan Disetujui!</h3>
                            <p class="text-muted mb-3">Tunjukkan E-Ticket QR Code ini kepada petugas Keamanan saat kedatangan.</p>
                            
                            <div class="qr-ticket-box mb-3 bg-light">
                                <?php 
                                    // PERBAIKAN: Logika Cek QR Code Lokal vs API
                                    $qr_path = "";
                                    if (!empty($data['qr_code_path']) && file_exists($data['qr_code_path'])) {
                                        // Jika file lokal ada, pakai file lokal
                                        $qr_path = $data['qr_code_path'];
                                    } else {
                                        // Jika file lokal belum ada, generate langsung dari API
                                        $qr_path = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($data['kode_booking']);
                                    }
                                ?>
                                <img src="<?= $qr_path ?>" alt="QR Code E-Ticket" style="width: 150px; height: 150px; object-fit: contain;">
                                <div class="mt-2 fw-bold"><?= $data['kode_booking']; ?></div>
                            </div>
                            <br>
                            <a href="cetak_tiket.php?kode=<?= $data['kode_booking']; ?>" target="_blank" class="btn btn-dark mt-2 btn-sm px-3">
                                <i class="ti ti-printer me-1"></i> Cetak E-Ticket
                            </a>

                            <?php elseif ($data['status_kegiatan'] == 'selesai'): ?>
                            <i class="ti ti-flag-checkered text-info" style="font-size: 4rem;"></i>
                            <h3 class="mt-2 fw-bold">Kunjungan Selesai</h3>
                            <p class="text-muted">Terima kasih atas kunjungan Anda. Silakan berikan penilaian terhadap pelayanan kami.</p>
                            
                            <?php else: ?>
                            <i class="ti ti-x text-danger" style="font-size: 4rem;"></i>
                            <h3 class="mt-2 fw-bold text-danger">Kunjungan Dibatalkan</h3>
                            <div class="alert alert-danger text-start d-inline-block mt-2">
                                <strong>Alasan Batal:</strong><br>
                                <?= !empty($data['alasan_pembatalan']) ? $data['alasan_pembatalan'] : 'Dibatalkan oleh sistem/admin.'; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <hr class="border-dashed my-4">

                        <h6 class="fw-bold bg-light p-2 border-start border-4 border-dark">[ Detail Pemohon ]</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Instansi Pemohon</small>
                                <p class="mb-0 h6"><?= $data['nama_instansi_tamu']; ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Tanggal & Waktu Rencana</small>
                                <p class="mb-0 h6">
                                    <?= date('d F Y', strtotime($data['tgl_kunjungan'])); ?> 
                                    <span class="badge bg-dark ms-1"><?= substr($data['waktu_kunjungan'], 0, 5); ?> WITA</span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <small class="text-muted fw-bold">Tujuan / Materi</small>
                                <p class="mb-0 border p-2 bg-light rounded text-dark"><?= $data['materi_kunjungan']; ?></p>
                            </div>
                        </div>

                        <?php if ($data['status_kegiatan'] == 'dijadwalkan' || $data['status_kegiatan'] == 'selesai'): ?>
                        <h6 class="fw-bold bg-light p-2 border-start border-4 border-dark">[ Informasi Pelaksanaan ]</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Lokasi Ruangan:</small><br>
                                <span class="h6"><?= !empty($data['nama_ruangan']) ? $data['nama_ruangan'] : 'Belum ditentukan'; ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted fw-bold">Penerima Tamu (PJ):</small><br>
                                <span class="h6"><?= !empty($data['nama_pj']) ? $data['nama_pj'] : 'Sekretariat DPRD'; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer bg-white border-top border-dark text-center p-3 d-flex justify-content-center gap-2">
                        
                        <?php if ($data['status_kegiatan'] == 'pending' || $data['status_kegiatan'] == 'dijadwalkan'): ?>
                            <a href="batal_kunjungan.php?kode=<?= $data['kode_booking']; ?>" class="btn btn-outline-danger">
                                <i class="ti ti-ban me-1"></i> Batalkan Kunjungan
                            </a>
                        <?php endif; ?>

                        <?php if ($data['status_kegiatan'] == 'selesai'): ?>
                            <a href="feedback.php?id=<?= $data['id_kunjungan']; ?>" class="btn btn-dark">
                                <i class="ti ti-star me-1"></i> Beri Feedback Pelayanan
                            </a>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>