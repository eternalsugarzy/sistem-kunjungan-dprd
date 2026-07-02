<?php
include 'koneksi.php';

$pesan_sukses = "";
$pesan_error = "";
$kode_booking_baru = "";

// Ambil data kategori untuk dropdown (Ditambahkan sesuai revisi)
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori_kunjungan WHERE is_active = 1");

// PROSES SAAT TOMBOL KIRIM DITEKAN
if (isset($_POST['kirim_pengajuan'])) {
    
    // 1. TANGKAP DATA INPUT
    $nama_instansi = mysqli_real_escape_string($koneksi, $_POST['nama_instansi']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']); // Menangkap input kategori
    
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
    $file_size = $_FILES['file_surat']['size']; // Ambil ukuran file
    $file_ext = strtolower(pathinfo($file_nama, PATHINFO_EXTENSION));
    
    // Validasi Ekstensi File
    $allowed = array('pdf', 'jpg', 'jpeg', 'png');
    
    if(in_array($file_ext, $allowed)){
        // Validasi Ukuran File Maksimal 5MB (Sesuai Proposal)
        if($file_size <= 5242880) { 
            // Rename file agar tidak bentrok (tambah timestamp)
            $file_baru = "SURAT_" . time() . "." . $file_ext;
            $folder_tujuan = "uploads/" . $file_baru;

            // --- CEK FOLDER ---
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true); // Buat folder jika belum ada
            }
            
            if(move_uploaded_file($file_tmp, $folder_tujuan)){
                
                // 4. SIMPAN KE DATABASE
                $query = "INSERT INTO kunjungan 
                          (kode_booking, email_pemohon, no_hp_pemohon, id_kategori, nama_instansi_tamu, alamat_instansi, 
                           tgl_surat_permohonan, tgl_kunjungan, waktu_kunjungan, 
                           jumlah_peserta_rencana, materi_kunjungan, file_surat_permohonan, status_kegiatan)
                          VALUES 
                          ('$kode_booking', '$email', '$no_hp', '$id_kategori', '$nama_instansi', '$alamat', 
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
            $pesan_error = "Ukuran file terlalu besar! Maksimal 5MB.";
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

    <style>
        /* --- KUSTOMISASI UKURAN TEKS --- */
        body {
            font-size: 1.1rem; 
        }
        .form-label {
            font-size: 1.15rem; 
            font-weight: 600; 
            color: #212529;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            font-size: 1.1rem; 
            padding: 0.75rem 1rem; 
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            padding: 12px 15px !important;
        }
        .text-muted {
            font-size: 0.95rem; 
        }
        .btn-besar {
            font-size: 1.2rem;
            padding: 12px 25px;
            font-weight: bold;
        }
        .kode-tiket {
            font-size: 3.5rem; /* Diperbesar sedikit lagi untuk kode tiket */
            letter-spacing: 2px;
        }
        /* Style khusus untuk teks peringatan SIMPAN */
        .teks-peringatan {
            font-size: 1.35rem;
            color: #212529;
        }
        .teks-simpan {
            font-size: 1.8rem;
            color: #dc3545; /* Merah */
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: underline;
        }
    </style>
</head>

<body class="bg-light">
    
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm py-3">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold fs-4" href="index.php">
          <img src="assets/images/logo.png" alt="logo" style="height:35px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
        <a href="index.php" class="btn btn-outline-secondary btn-besar fs-6 px-3 py-2">Kembali ke Beranda</a>
      </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <?php if($kode_booking_baru != ""): ?>
                <div class="card shadow border-success mb-4">
                    <div class="card-body text-center p-5">
                        <div class="avtar avtar-xl bg-light-success text-success mb-3 mx-auto" style="width: 80px; height: 80px;">
                            <i class="ti ti-check" style="font-size: 40px;"></i>
                        </div>
                        <h2 class="mt-3 text-success fw-bold">Permohonan Terkirim!</h2>
                        
                        <!-- TEKS PERINGATAN YANG DIPERTEGAS -->
                        <div class="bg-warning bg-opacity-10 border border-warning rounded p-3 mt-4 mb-2 mx-auto" style="max-width: 80%;">
                            <p class="mb-0 teks-peringatan">
                                Mohon <span class="teks-simpan">SIMPAN</span> Kode Tiket di bawah ini untuk mengecek status permohonan Anda:
                            </p>
                        </div>
                        
                        <div class="alert alert-success d-inline-block px-5 py-4 mt-3 border-2 border-success shadow-sm">
                            <h1 class="mb-0 fw-bold kode-tiket"><?= $kode_booking_baru; ?></h1>
                        </div>
                        
                        <p class="mt-4 text-dark fs-5">Kami akan memproses permohonan Anda secepatnya.</p>
                        <div class="mt-4">
                            <a href="cek_status.php" class="btn btn-primary btn-besar me-2">Cek Status Sekarang</a>
                            <a href="index.php" class="btn btn-outline-secondary btn-besar">Kembali</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($pesan_error != ""): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4 p-3 border-2 border-danger" role="alert">
                    <i class="ti ti-alert-circle me-3" style="font-size: 2rem;"></i>
                    <div class="fs-5">
                        <b>Terjadi Kesalahan:</b> <?= $pesan_error; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($kode_booking_baru == ""): ?>
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-dark text-white p-4">
                        <h3 class="mb-0 text-white fw-bold"><i class="ti ti-file-text me-2"></i>[ Formulir Rencana Kunjungan Kerja ]</h3>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" enctype="multipart/form-data">
                            
                            <h4 class="text-dark bg-light mb-4 border-start border-5 border-dark section-title">1. Data Instansi Pemohon</h4>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label">Nama Instansi / DPRD <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_instansi" class="form-control" placeholder="Contoh: DPRD Kab. Banjar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Aktif <span class="badge bg-secondary fs-6 ms-1">BARU</span> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="email@instansi.go.id" required>
                                    <div class="text-muted mt-1"><i class="ti ti-info-circle"></i> E-Ticket QR Code dikirim ke sini</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Alamat Instansi</label>
                                <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap instansi pemohon..."></textarea>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label">No HP / WhatsApp CP <span class="text-danger">*</span></label>
                                    <input type="number" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kategori Kunjungan <span class="badge bg-secondary fs-6 ms-1">BARU</span> <span class="text-danger">*</span></label>
                                    <select name="kategori" class="form-select" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php 
                                        if($kategori_query){
                                            while($row = mysqli_fetch_assoc($kategori_query)): 
                                        ?>
                                            <option value="<?= $row['id_kategori'] ?>"><?= $row['nama_kategori'] ?></option>
                                        <?php 
                                            endwhile; 
                                        }
                                        ?>
                                    </select>
                                    <div class="text-muted mt-1">Untuk keperluan pendataan Sekretariat</div>
                                </div>
                            </div>

                            <h4 class="text-dark bg-light mb-4 mt-5 border-start border-5 border-dark section-title">2. Rencana Pelaksanaan</h4>
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">Tanggal Surat</label>
                                    <input type="date" name="tgl_surat" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">Tgl Rencana Kunjungan <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_kunjungan" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jam Kunjungan <span class="text-danger">*</span></label>
                                    <input type="time" name="jam" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">Jml Peserta (Orang) <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" class="form-control" placeholder="0" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Materi / Tujuan Kunjungan <span class="text-danger">*</span></label>
                                    <input type="text" name="materi" class="form-control" placeholder="Contoh: Konsultasi terkait Perda Wisata..." required>
                                </div>
                            </div>

                            <h4 class="text-dark bg-light mb-4 mt-5 border-start border-5 border-dark section-title">3. Berkas Pendukung</h4>
                            <div class="mb-4 border border-2 border-dashed p-4 text-center rounded bg-light">
                                <label class="form-label fw-bold d-block fs-5 text-dark mb-3">Upload Surat Permohonan Resmi (PDF/JPG) <span class="text-danger">*</span></label>
                                <input type="file" name="file_surat" class="form-control form-control-lg w-100 mx-auto mb-3" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="text-muted fs-6"><i class="ti ti-upload me-1"></i> Format: PDF, JPG | Maksimal: 5MB</div>
                            </div>

                            <div class="alert alert-secondary d-flex align-items-center mt-4 p-3 border-2 border-secondary" role="alert">
                                <i class="ti ti-mail me-3" style="font-size: 2rem;"></i>
                                <div class="fs-6">
                                    <b>Perhatian:</b> Pastikan email aktif - E-Ticket QR Code akan dikirim otomatis ke alamat email setelah permohonan disetujui.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5">
                                <button type="reset" class="btn btn-light border-2 border-secondary btn-besar">Reset Form</button>
                                <button type="submit" name="kirim_pengajuan" class="btn btn-dark btn-besar">
                                    <i class="ti ti-send me-2"></i> Kirim Permohonan
                                </button>
                            </div>
                            
                            <div class="text-center mt-4">
                                <div class="text-white bg-dark px-4 py-2 rounded d-inline-block fs-6">
                                    Kode booking akan dikirim ke email & ditampilkan di layar setelah pengiriman berhasil
                                </div>
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