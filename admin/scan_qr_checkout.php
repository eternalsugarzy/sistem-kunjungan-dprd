<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'scan_checkout'; // Menyalakan menu aktif di sidebar

include 'template/header.php';
include 'template/sidebar.php';
include '../koneksi.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

$pesan_sukses = "";
$pesan_gagal = "";

// ==========================================
// PROSES DATABASE SAAT QR DISCAN / DIINPUT MANUAL
// ==========================================
if (isset($_POST['kode_booking'])) {
    $input_qr = trim($_POST['kode_booking']);
    $kode_booking = '';
    
    // 1. Parsing Compact Token Anti-Copy (KODE|TIMESTAMP|HASH)
    $parts = explode('|', $input_qr);
    if (count($parts) === 3) {
        $k_code = trim($parts[0]);
        $k_ts   = trim($parts[1]);
        $k_hash = trim($parts[2]);
        
        $expected_hash = substr(hash_hmac('sha256', $k_code . '|' . $k_ts, $qr_secret_key), 0, 10);
        if (hash_equals($expected_hash, $k_hash)) {
            $kode_booking = $k_code;
        } else {
            $pesan_gagal = "Gagal! QR Code palsu atau telah dimodifikasi (Digital Signature tidak valid).";
        }
    } else {
        // Fallback jika diketik manual oleh petugas
        $kode_booking = $input_qr;
    }
    
    if ($kode_booking !== '') {
        $kode_booking = mysqli_real_escape_string($koneksi, $kode_booking);
        
        // Cek apakah kode booking tersebut valid di database
        $cek_kunjungan = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE kode_booking = '$kode_booking'");
        
        if ($cek_kunjungan && mysqli_num_rows($cek_kunjungan) > 0) {
            $data = mysqli_fetch_assoc($cek_kunjungan);
            
            $stat_kegiatan = strtolower($data['status_kegiatan']);
            $stat_kehadiran = strtolower($data['status_kehadiran']);
            
            // LOGIKA PENGECEKAN STATUS CHECKOUT
            if ($stat_kegiatan == 'selesai' || $stat_kehadiran == 'selesai') {
                $waktu_pulang = !empty($data['waktu_checkout']) ? date('d/m/Y H:i', strtotime($data['waktu_checkout'])) . " WITA" : "sebelumnya";
                $pesan_gagal = "Gagal! Instansi <b>" . $data['nama_instansi_tamu'] . "</b> <b>SUDAH CHECK-OUT</b> pada " . $waktu_pulang . ". Sesi kunjungan ini telah selesai (Tidak dapat Check-Out berulang kali).";
                
            } elseif ($stat_kegiatan == 'sedang berkunjung' || $stat_kehadiran == 'hadir') {
                // PROSES UTAMA CHECKOUT (Otomatis mengubah 2 status sekaligus)
                $query_update = "UPDATE kunjungan SET 
                                 status_kegiatan = 'selesai', 
                                 status_kehadiran = 'selesai', 
                                 waktu_checkout = NOW() 
                                 WHERE kode_booking = '$kode_booking'";
                                 
                if (mysqli_query($koneksi, $query_update)) {
                    $pesan_sukses = "Sukses! Proses Check-Out Instansi <strong>" . $data['nama_instansi_tamu'] . "</strong> berhasil dicatat. Status kunjungan kini telah <b>Selesai</b>. Jangan lupa kumpulkan Kartu Tamu Sementara.";
                } else {
                    $pesan_gagal = "Terjadi kesalahan internal saat memperbarui database: " . mysqli_error($koneksi);
                }
                
            } else {
                $pesan_gagal = "Gagal! Instansi <b>" . $data['nama_instansi_tamu'] . "</b> belum melakukan Check-In. (Status saat ini: " . ucfirst($stat_kegiatan) . ")";
            }
            
        } else {
            $pesan_gagal = "Gagal! Kode E-Ticket QR (" . $kode_booking . ") tidak terdaftar di sistem.";
        }
    }
}
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Scan QR Code Check-Out</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Scan QR Check-Out</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card h-100 border-dark shadow-sm">
            <div class="card-header bg-dark text-white border-bottom border-dark">
                <h5 class="mb-0 text-white"><i class="ti ti-camera me-2"></i>Kamera Pemindai Kepulangan</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                
                <div id="reader" class="border rounded bg-dark position-relative shadow-inner" style="width: 100%; max-width: 450px; min-height: 300px; overflow: hidden;"></div>
                
                <div class="text-muted small mt-3 text-center">
                    <i class="ti ti-info-circle me-1 text-primary"></i> Pilih kamera dari dropdown di atas area scan, lalu arahkan QR Code ke depan kamera.
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card h-100 border-dark shadow-sm">
            <div class="card-header bg-white border-bottom border-dark">
                <h5 class="mb-0">Log &amp; Input Check-Out</h5>
            </div>
            <div class="card-body">
                
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success border-success" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-circle-check me-2 f-24"></i>
                            <div><?= $pesan_sukses; ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesan_gagal)): ?>
                    <div class="alert alert-danger d-flex align-items-center border-danger" role="alert">
                        <i class="ti ti-circle-x me-2 f-24"></i>
                        <div><?= $pesan_gagal; ?></div>
                    </div>
                <?php endif; ?>

                <form id="form-qr-checkout" method="POST" action="" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Kode Booking / Data QR</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dark"><i class="ti ti-qrcode"></i></span>
                            <input type="text" id="kode_booking_field" name="kode_booking" class="form-control form-control-lg font-monospace border-dark" placeholder="Contoh: REQ-2026-A001" required autofocus>
                        </div>
                        <div class="form-text text-muted">Mendukung scanner barcode USB fisik maupun kamera webcam.</div>
                    </div>
                    <button type="submit" class="btn btn-outline-dark w-100 py-2 fw-bold"><i class="ti ti-logout me-1"></i> Konfirmasi Check-Out</button>
                </form>

                <hr class="my-4 border-dashed">
                
                <h6 class="fw-bold text-dark mb-2">Petunjuk Operasional (Kepulangan):</h6>
                <ol class="text-muted small ps-3" style="line-height: 1.8;">
                    <li>Pastikan tamu telah mengembalikan <b>Kartu Tamu Sementara</b> (ID Card fisik) ke meja resepsionis.</li>
                    <li>Arahkan QR Code yang ada di ID Card tersebut ke lensa kamera.</li>
                    <li>Sistem otomatis akan mencatat waktu kepulangan dan menyelesaikan sesi kunjungan.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function onScanSuccess(decodedText, decodedResult) {
    // Isikan hasil scan barcode ke dalam kolom teks input
    document.getElementById('kode_booking_field').value = decodedText;
    
    // Matikan scanner sejenak agar tidak melakukan submit berkali-kali (looping)
    html5QrcodeScanner.clear();
    
    // Otomatis submit form ke database PHP
    document.getElementById('form-qr-checkout').submit();
}

function onScanFailure(error) {
    // Diabaikan agar konsol tidak penuh
}

// Konfigurasi area kotak bidik kamera
let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", { fps: 15, qrbox: { width: 230, height: 230 } }, /* verbose= */ false
);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

<style>
.border-dashed { border-style: dashed !important; border-width: 1px !important; border-color: #cbd5e1 !important; }
#reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 8px; }
/* Merapikan styling tombol bawaan library agar senada dengan bootstrap */
#reader button {
    display: inline-block;
    font-weight: 500;
    color: #fff;
    background-color: #212529;
    border: 1px solid #212529;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    margin: 10px 5px;
    cursor: pointer;
    transition: 0.2s;
}
#reader button:hover {
    background-color: #343a40;
}
</style>

<?php include 'template/footer.php'; ?>