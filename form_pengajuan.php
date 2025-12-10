<?php
include 'koneksi.php';

$pesan_sukses = "";
$pesan_error = "";
$kode_booking_baru = "";

// PROSES SAAT TOMBOL KIRIM DITEKAN
if (isset($_POST['kirim_pengajuan'])) {
    
    // 1. TANGKAP DATA INPUT
    $nama_instansi = mysqli_real_escape_string($koneksi, $_POST['nama_instansi']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    $tgl_surat = $_POST['tgl_surat'];
    $tgl_kunjungan = $_POST['tgl_kunjungan'];
    $jam = $_POST['jam'];
    $jumlah = $_POST['jumlah'];
    $materi = mysqli_real_escape_string($koneksi, $_POST['materi']);

    // 2. GENERATE KODE BOOKING UNIK (Contoh: REQ-2025-X7A2)
    $tahun = date('Y');
    $random = strtoupper(substr(md5(time()), 0, 4)); // 4 karakter acak
    $kode_booking = "REQ-$tahun-$random";

    // 3. PROSES UPLOAD FILE SURAT
    $file_nama = $_FILES['file_surat']['name'];
    $file_tmp = $_FILES['file_surat']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_nama, PATHINFO_EXTENSION));
    
    // Validasi Ekstensi File
    $allowed = array('pdf', 'jpg', 'jpeg', 'png');
    
    if(in_array($file_ext, $allowed)){
        // Rename file agar tidak bentrok (tambah timestamp)
        $file_baru = "SURAT_" . time() . "." . $file_ext;
        $folder_tujuan = "uploads/" . $file_baru;

        // --- TAMBAHAN KODE: CEK FOLDER ---
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true); // Buat folder jika belum ada
        }
        
        if(move_uploaded_file($file_tmp, $folder_tujuan)){
            
            // 4. SIMPAN KE DATABASE
            $query = "INSERT INTO kunjungan 
                      (kode_booking, email_pemohon, nama_instansi_tamu, alamat_instansi, 
                       tgl_surat_permohonan, tgl_kunjungan, waktu_kunjungan, 
                       jumlah_peserta_rencana, materi_kunjungan, file_surat_permohonan, status_kegiatan)
                      VALUES 
                      ('$kode_booking', '$email', '$nama_instansi', '$alamat', 
                       '$tgl_surat', '$tgl_kunjungan', '$jam', 
                       '$jumlah', '$materi', '$file_baru', 'pending')";
            
            if(mysqli_query($koneksi, $query)){
                $kode_booking_baru = $kode_booking; // Simpan untuk ditampilkan di notifikasi sukses
                $pesan_sukses = "Permohonan Berhasil Dikirim!";
            } else {
                $pesan_error = "Database Error: " . mysqli_error($koneksi);
            }

        } else {
            $pesan_error = "Gagal mengupload file. Pastikan folder 'uploads' sudah dibuat.";
        }
    } else {
        $pesan_error = "Format file salah! Harap upload PDF atau Gambar (JPG/PNG).";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <title>Form Pengajuan | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />
</head>

<body class="bg-light">
    
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          <img src="assets/images/logo.png" alt="logo" style="height:30px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Kembali ke Beranda</a>
      </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php if($kode_booking_baru != ""): ?>
                <div class="card shadow border-success mb-4">
                    <div class="card-body text-center p-5">
                        <div class="avtar avtar-xl bg-light-success text-success mb-3 mx-auto">
                            <i class="ti ti-check f-40"></i>
                        </div>
                        <h2 class="mt-3 text-success">Permohonan Terkirim!</h2>
                        <p class="lead">Mohon simpan Kode Tiket di bawah ini untuk mengecek status:</p>
                        
                        <div class="alert alert-success d-inline-block px-5 py-3 mt-2">
                            <h1 class="mb-0 fw-bold"><?= $kode_booking_baru; ?></h1>
                        </div>
                        
                        <p class="mt-3 text-muted">Kami akan memproses permohonan Anda secepatnya.</p>
                        <div class="mt-4">
                            <a href="cek_status.php" class="btn btn-primary me-2">Cek Status Sekarang</a>
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($pesan_error != ""): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="ti ti-alert-circle me-2 f-20"></i>
                    <div>
                        <b>Terjadi Kesalahan:</b> <?= $pesan_error; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($kode_booking_baru == ""): ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 text-white"><i class="ti ti-file-text me-2"></i>Formulir Rencana Kunjungan Kerja</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            
                            <h5 class="text-primary mb-3">1. Data Instansi Pemohon</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Instansi / DPRD <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_instansi" class="form-control" placeholder="Contoh: DPRD Kab. Banjar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Kontak <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="humas@instansi.go.id" required>
                                    <small class="text-muted">Untuk notifikasi status.</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat Instansi</label>
                                <textarea name="alamat" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor HP / WhatsApp CP <span class="text-danger">*</span></label>
                                <input type="number" name="no_hp" class="form-control" placeholder="08..." required>
                            </div>

                            <hr class="my-4">

                            <h5 class="text-primary mb-3">2. Rencana Pelaksanaan</h5>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Surat</label>
                                    <input type="date" name="tgl_surat" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rencana Tgl Kunjungan <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_kunjungan" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jam <span class="text-danger">*</span></label>
                                    <input type="time" name="jam" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Jumlah Peserta (Orang) <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" class="form-control" placeholder="0" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Materi / Tujuan Kunjungan <span class="text-danger">*</span></label>
                                    <input type="text" name="materi" class="form-control" placeholder="Contoh: Konsultasi terkait Perda..." required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="text-primary mb-3">3. Berkas Pendukung</h5>
                            <div class="mb-3">
                                <label class="form-label">Upload Surat Permohonan Resmi (PDF/JPG) <span class="text-danger">*</span></label>
                                <input type="file" name="file_surat" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Pastikan surat bertanda tangan & stempel basah. Maks 2MB.</small>
                            </div>

                            <div class="alert alert-secondary d-flex align-items-center" role="alert">
                                <i class="ti ti-info-circle me-2"></i>
                                <div>Pastikan data yang Anda isi sudah benar sebelum mengirim.</div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="kirim_pengajuan" class="btn btn-primary btn-lg">
                                    <i class="ti ti-send me-2"></i>Kirim Permohonan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>