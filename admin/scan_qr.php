<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'scan_qr'; // Menyalakan menu aktif di sidebar

include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

$pesan_sukses = "";
$pesan_gagal = "";

// ==========================================
// PROSES DATABASE SAAT QR BERHASIL DISCAN
// ==========================================
if (isset($_POST['kode_booking'])) {
    $kode_booking = mysqli_real_escape_string($koneksi, $_POST['kode_booking']);
    
    // Cek apakah kode booking tersebut valid di database
    $cek_kunjungan = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE kode_booking = '$kode_booking'");
    
    if ($cek_kunjungan && mysqli_num_rows($cek_kunjungan) > 0) {
        $data = mysqli_fetch_assoc($cek_kunjungan);
        
        if (strtolower($data['status_kegiatan']) == 'pending') {
            $pesan_gagal = "Gagal! Permohonan " . $kode_booking . " belum diverifikasi oleh pimpinan.";
        } elseif (strtolower($data['status_kegiatan']) == 'selesai') {
            $pesan_sukses = "Pemberitahuan: Instansi " . $data['nama_instansi_tamu'] . " sebelumnya sudah melakukan scan kedatangan.";
        } else {
            // Update status kegiatan menjadi selesai dan catat waktu kehadiran
            // Jika kolom status_qr belum ada, query ini tetap aman memperbarui status_kegiatan
            $update = mysqli_query($koneksi, "UPDATE kunjungan SET status_kegiatan = 'selesai' WHERE kode_booking = '$kode_booking'");
            
            if ($update) {
                $pesan_sukses = "Sukses! Kedatangan Instansi <strong>" . $data['nama_instansi_tamu'] . "</strong> berhasil dicatat.";
            } else {
                $pesan_gagal = "Terjadi kesalahan internal saat memperbarui database.";
            }
        }
    } else {
        $pesan_gagal = "Gagal! Kode E-Ticket QR (" . $kode_booking . ") tidak terdaftar di sistem.";
    }
}
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Scan QR Code Kedatangan</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Scan QR Kedatangan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>Kamera Pemindai E-Ticket</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                
                <div id="reader" class="border rounded bg-dark position-relative shadow-inner" style="width: 100%; max-width: 450px; min-height: 300px; overflow: hidden;"></div>
                
                <div class="text-muted small mt-3 text-center">
                    <i class="ti ti-info-circle me-1 text-primary"></i> Posisikan barcode kertas atau layar HP tamu tepat di tengah kotak kamera.
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>Log &amp; Input Manual Kehadiran</h5>
            </div>
            <div class="card-body">
                
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="ti ti-circle-check me-2 f-20"></i>
                        <div><?= $pesan_sukses; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesan_gagal)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-circle-x me-2 f-20"></i>
                        <div><?= $pesg_gagal = $pesan_gagal; ?></div>
                    </div>
                <?php endif; ?>

                <form id="form-qr" method="POST" action="" class="mt-2">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Kode Booking / Nomor Tiket</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-qrcode"></i></span>
                            <input type="text" id="kode_booking_field" name="kode_booking" class="form-control form-control-lg font-monospace" placeholder="Contoh: REQ-2025-A001" required>
                        </div>
                        <div class="form-text text-muted">Petugas dapat mengetik kode manual jika kamera bermasalah.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="ti ti-check me-1"></i>Konfirmasi Kehadiran Tamu</button>
                </form>

                <hr class="my-4 border-dashed">
                
                <h6 class="fw-bold text-dark mb-2">Petunjuk Operasional Front Office:</h6>
                <ol class="text-muted small ps-3" style="line-height: 1.8;">
                    <li>Izinkan peramban/browser mengakses perangkat kamera laptop/webcam.</li>
                    <li>Arahkan QR Code E-Ticket rombongan tamu ke depan lensa kamera.</li>
                    <li>Sistem otomatis membaca kode, mengisi form, dan mensubmit data tanpa klik tombol apa pun.</li>
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
    document.getElementById('form-qr').submit();
}

function onScanFailure(error) {
    // Kita biarkan kosong agar konsol browser tidak penuh log error saat mencari kecocokan gambar QR
}

// Konfigurasi area kotak bidik kamera boks pemindai
let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", { fps: 15, qrbox: { width: 230, height: 230 } }, /* verbose= */ false
);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

<style>
.border-dashed { border-style: dashed !important; border-width: 1px !important; border-color: #cbd5e1 !important; }
#reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; }
/* Merapikan styling tombol bawaan library agar senada dengan bootstrap */
#reader button {
    display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    padding: .25rem .5rem;
    font-size: .875rem;
    border-radius: .25rem;
    margin: 5px;
}
</style>

<?php
include 'template/footer.php';
?>
