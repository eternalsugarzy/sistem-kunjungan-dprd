<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php'; 

$pesan_sukses = "";
$pesan_error = "";

$id_kunjungan = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Ambil info kunjungan dari DB
$instansi_tamu = "DPRD Kab. Tanah Laut";
$kode_booking  = "REQ-2026-A001";
$tgl_agenda    = date('d F Y');

$q_info = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE id_kunjungan = '$id_kunjungan'");
if ($q_info && mysqli_num_rows($q_info) > 0) {
    $d_info = mysqli_fetch_assoc($q_info);
    $instansi_tamu = $d_info['nama_instansi_tamu'];
    $kode_booking  = $d_info['kode_booking'];
    $tgl_agenda    = isset($d_info['tgl_kunjungan']) ? date('d F Y', strtotime($d_info['tgl_kunjungan'])) : date('d F Y');
}

// PROSES SIMPAN DATA (Kunci Nama Kolom Sesuai phpMyAdmin Kamu)
if (isset($_POST['kirim_feedback'])) {
    
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $nama    = ($is_anonymous == 1) ? "Anonim" : mysqli_real_escape_string($koneksi, $_POST['nama_pemberi']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan_pemberi']);
    $saran   = mysqli_real_escape_string($koneksi, $_POST['komentar_saran']);
    
    $rating_pelayanan = isset($_POST['rating_pelayanan']) ? intval($_POST['rating_pelayanan']) : 5;
    $rating_fasilitas = isset($_POST['rating_fasilitas']) ? intval($_POST['rating_fasilitas']) : 5;
    $rating_waktu     = isset($_POST['rating_ketepatan_waktu']) ? intval($_POST['rating_ketepatan_waktu']) : 5;
    
    // Hitung rata-rata eksak
    $rating_keseluruhan = ($rating_pelayanan + $rating_fasilitas + $rating_waktu) / 3;

    // Perintah INSERT mengunci nama kolom asli di database kamu (Anti-Null)
    $query_insert = "INSERT INTO feedback_kunjungan (
                        id_kunjungan, 
                        nama_pemberi, 
                        jabatan_pemberi, 
                        rating_pelayanan, 
                        rating_fasilitas, 
                        rating_ketepatan_waktu, 
                        rating_keseluruhan, 
                        komentar_saran, 
                        is_anonymous
                    ) VALUES (
                        '$id_kunjungan', 
                        '$nama', 
                        '$jabatan', 
                        '$rating_pelayanan', 
                        '$rating_fasilitas', 
                        '$rating_waktu', 
                        '$rating_keseluruhan', 
                        '$saran', 
                        '$is_anonymous'
                    )";
    
    if (mysqli_query($koneksi, $query_insert)) {
        $pesan_sukses = "Terima kasih! Feedback Anda sangat berarti bagi peningkatan pelayanan kami.";
    } else {
        $pesan_error = "Gagal menyimpan feedback: " . mysqli_error($koneksi);
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <title>Feedback Pelayanan | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />

    <style>
        .rating-group {
            display: inline-flex;
            flex-direction: row-reverse; 
            justify-content: flex-end;
        }
        .rating-group input {
            display: none; 
        }
        .rating-group label {
            color: #ddd; 
            font-size: 2rem;
            padding: 0 5px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-group label:hover,
        .rating-group label:hover ~ label,
        .rating-group input:checked ~ label {
            color: #ffc107; 
        }
        .border-dashed { border-style: dashed !important; border-width: 1px !important; }
    </style>
</head>

<body class="bg-light">
    
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          <img src="assets/images/logo.png" alt="logo" style="height:30px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
        <div class="d-flex">
            <span class="text-muted mt-1 me-3">Beranda &gt; Beri Feedback</span>
        </div>
      </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                
                <?php if($pesan_sukses != ""): ?>
                <div class="card shadow border-success mb-4">
                    <div class="card-body text-center p-5">
                        <div class="avtar avtar-xl bg-light-success text-success mb-3 mx-auto">
                            <i class="fa-solid fa-heart" style="font-size: 40px;"></i>
                        </div>
                        <h3 class="mt-3 text-success"><?= $pesan_sukses; ?></h3>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>

                <?php if($pesan_error != ""): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> <?= $pesan_error; ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-1 text-white" style="color: #fff !important;">[ Bagaimana Pengalaman Kunjungan Anda? ]</h4>
                        <small class="text-light" style="color: #cbd5e1 !important;">
                            <?= htmlspecialchars($instansi_tamu); ?> — <?= htmlspecialchars($kode_booking); ?> | Agenda: <?= $tgl_agenda; ?>
                        </small>
                    </div>
                    <div class="card-body p-4 px-md-5">
                        <form method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Pemberi Feedback <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemberi" id="nama_pemberi_input" class="form-control" placeholder="Nama lengkap Anda" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Jabatan</label>
                                <input type="text" name="jabatan_pemberi" class="form-control" placeholder="Jabatan Anda di instansi">
                            </div>

                            <hr class="my-4 border-dashed">

                            <div class="mb-3">
                                <label class="form-label fw-bold d-block mb-1">Rating Kualitas Pelayanan Petugas <span class="text-danger">*</span></label>
                                <div class="rating-group">
                                    <input type="radio" id="pel_5" name="rating_pelayanan" value="5" required/><label for="pel_5"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="pel_4" name="rating_pelayanan" value="4"/><label for="pel_4"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="pel_3" name="rating_pelayanan" value="3"/><label for="pel_3"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="pel_2" name="rating_pelayanan" value="2"/><label for="pel_2"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="pel_1" name="rating_pelayanan" value="1"/><label for="pel_1"><i class="fa-solid fa-star"></i></label>
                                </div>
                                <div class="text-muted small mt-n1">1 = Sangat Buruk | 5 = Sangat Baik</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold d-block mb-1">Rating Kualitas Fasilitas & Ruangan <span class="text-danger">*</span></label>
                                <div class="rating-group">
                                    <input type="radio" id="fas_5" name="rating_fasilitas" value="5" required/><label for="fas_5"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="fas_4" name="rating_fasilitas" value="4"/><label for="fas_4"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="fas_3" name="rating_fasilitas" value="3"/><label for="fas_3"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="fas_2" name="rating_fasilitas" value="2"/><label for="fas_2"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="fas_1" name="rating_fasilitas" value="1"/><label for="fas_1"><i class="fa-solid fa-star"></i></label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold d-block mb-1">Rating Ketepatan Waktu Pelaksanaan <span class="text-danger">*</span></label>
                                <div class="rating-group">
                                    <input type="radio" id="wak_5" name="rating_ketepatan_waktu" value="5" required/><label for="wak_5"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="wak_4" name="rating_ketepatan_waktu" value="4"/><label for="wak_4"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="wak_3" name="rating_ketepatan_waktu" value="3"/><label for="wak_3"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="wak_2" name="rating_ketepatan_waktu" value="2"/><label for="wak_2"><i class="fa-solid fa-star"></i></label>
                                    <input type="radio" id="wak_1" name="rating_ketepatan_waktu" value="1"/><label for="wak_1"><i class="fa-solid fa-star"></i></label>
                                </div>
                            </div>

                            <hr class="my-4 border-dashed">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Komentar / Saran (opsional)</label>
                                <textarea name="komentar_saran" class="form-control" rows="3" placeholder="Tuliskan saran atau komentar untuk peningkatan pelayanan..."></textarea>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="anonim" value="1" onchange="toggleAnonim(this)">
                                <label class="form-check-label text-muted" for="anonim">
                                    Kirim sebagai anonim (nama tidak ditampilkan di laporan)
                                </label>
                            </div>

                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <a href="index.php" class="btn btn-light border px-4 py-2">[ Kembali ]</a>
                                <button type="submit" name="kirim_feedback" class="btn btn-dark px-4 py-2">
                                    [ Kirim Feedback ]
                                </button>
                            </div>

                        </form>
                    </div>
                    <div class="card-header bg-dark text-white text-center py-2" style="border-radius: 0 0 7px 7px;">
                        <small style="color: #cbd5e1 !important;">Data tersimpan di tabel feedback_kunjungan - digunakan untuk Laporan Statistik Dashboard</small>
                    </div>
                </div>
                
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
    function toggleAnonim(chk) {
        const nameInput = document.getElementById('nama_pemberi_input');
        if (chk.checked) {
            nameInput.value = "Anonim";
            nameInput.readOnly = true;
            nameInput.required = false;
        } else {
            nameInput.value = "";
            nameInput.readOnly = false;
            nameInput.required = true;
        }
    }
    </script>
</body>
</html>