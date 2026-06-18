<?php
include 'koneksi.php';

$pesan_sukses = "";
$pesan_error = "";

// PROSES SAAT TOMBOL KIRIM DITEKAN (Untuk dikerjakan teman Back-End Anda)
if (isset($_POST['kirim_feedback'])) {
    // Simulasi tangkap data untuk Back-End
    $id_kunjungan = 1; // Contoh: Ini harusnya ditangkap dari URL/Session, misal $_GET['id']
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pemberi']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan_pemberi']);
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar_saran']);
    
    // Checkbox anonim (jika dicentang nilainya 1, jika tidak 0)
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    // Tangkap nilai bintang (1 sampai 5)
    $rating_pelayanan = isset($_POST['rating_pelayanan']) ? $_POST['rating_pelayanan'] : 0;
    $rating_fasilitas = isset($_POST['rating_fasilitas']) ? $_POST['rating_fasilitas'] : 0;
    $rating_waktu = isset($_POST['rating_ketepatan_waktu']) ? $_POST['rating_ketepatan_waktu'] : 0;
    
    // Hitung rata-rata
    $rating_keseluruhan = ($rating_pelayanan + $rating_fasilitas + $rating_waktu) / 3;

    // TODO: Teman Back-End Anda tinggal membuat query INSERT ke tabel 'feedback_kunjungan' di sini.
    // ...
    
    $pesan_sukses = "Terima kasih! Feedback Anda sangat berarti bagi peningkatan pelayanan kami.";
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
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />

    <style>
        /* --- CSS KHUSUS UNTUK STAR RATING --- */
        .rating-group {
            display: inline-flex;
            flex-direction: row-reverse; /* Dibalik agar hover efek CSS bekerja dari kiri ke kanan */
            justify-content: flex-end;
        }
        .rating-group input {
            display: none; /* Sembunyikan radio button asli */
        }
        .rating-group label {
            color: #ddd; /* Warna bintang default (abu-abu) */
            font-size: 2rem;
            padding: 0 5px;
            cursor: pointer;
            transition: color 0.2s;
        }
        /* Efek saat di-hover atau saat radio button terpilih */
        .rating-group label:hover,
        .rating-group label:hover ~ label,
        .rating-group input:checked ~ label {
            color: #ffc107; /* Warna emas Bootstrap */
        }
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
                            <i class="ti ti-heart f-40"></i>
                        </div>
                        <h3 class="mt-3 text-success"><?= $pesan_sukses; ?></h3>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>

                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-1 text-white">[ Bagaimana Pengalaman Kunjungan Anda? ]</h4>
                        <small class="text-light">DPRD Kota Banjarmasin — REQ-2025-0055 | DPRD Kab. Tanah Laut | 10 Desember 2025</small>
                    </div>
                    <div class="card-body p-4 px-md-5">
                        <form method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Pemberi Feedback <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemberi" class="form-control" placeholder="Nama lengkap Anda" required>
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
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="anonim" value="1">
                                <label class="form-check-label text-muted" for="anonim">
                                    Kirim sebagai anonim (nama tidak ditampilkan di laporan)
                                </label>
                            </div>

                            <div class="alert alert-secondary d-flex align-items-center mb-4 border border-1" role="alert">
                                <i class="ti ti-chart-bar me-2 f-20"></i>
                                <div><small>Data feedback digunakan untuk Laporan Statistik Kepuasan Pelayanan pimpinan DPRD</small></div>
                            </div>

                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <a href="cek_status.php" class="btn btn-light border px-4 py-2">[ Lewati ]</a>
                                <button type="submit" name="kirim_feedback" class="btn btn-dark px-4 py-2">
                                    [ Kirim Feedback ]
                                </button>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer bg-dark text-white text-center py-2">
                        <small>Data tersimpan di tabel feedback_kunjungan - digunakan untuk Laporan Statistik Dashboard</small>
                    </div>
                </div>
                
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>