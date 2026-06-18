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
$redirect_url = ""; // Variabel baru untuk menampung URL tujuan cetak kartu

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
        } elseif (strtolower($data['status_kegiatan']) == 'selesai' || strtolower($data['status_kehadiran']) == 'hadir') {
            // Jika sudah pernah scan
            $pesan_sukses = "Pemberitahuan: Instansi " . $data['nama_instansi_tamu'] . " sebelumnya sudah melakukan scan kedatangan.";
            // Tetap berikan opsi cetak kartu jika kartu hilang
            $redirect_url = "kartu_tamu.php?kode=" . $kode_booking;
        } else {
            // Update status kehadiran/kegiatan tamu (Menambahkan waktu_scan agar sesuai revisi log kehadiran)
            $update = mysqli_query($koneksi, "UPDATE kunjungan SET status_kegiatan = 'selesai', status_kehadiran = 'hadir', waktu_scan = NOW() WHERE kode_booking = '$kode_booking'");
            
            if ($update) {
                $pesan_sukses = "Sukses! Kedatangan Instansi <strong>" . $data['nama_instansi_tamu'] . "</strong> berhasil dicatat.";
                // Siapkan URL untuk redirect ke kartu tamu
                $redirect_url = "kartu_tamu.php?kode=" . $kode_booking;
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
        <div class="card h-100 border-dark shadow-sm">
            <div class="card-header bg-white border-bottom border-dark">
                <h5 class="mb-0">Kamera Pemindai E-Ticket</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                
                <div id="reader" class="border rounded bg-dark position-relative shadow-inner" style="width: 100%; max-width: 450px; min-height: 300px; overflow: hidden;"></div>
                
                <div class="text-muted small mt-3 text-center">
                    <i class="ti ti-info-circle me-1 text-primary"></i> Posisikan barcode E-Ticket tamu tepat di tengah kotak kamera.
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card h-100 border-dark shadow-sm">
            <div class="card-header bg-white border-bottom border-dark">
                <h5 class="mb-0">Log &amp; Input Kehadiran</h5>
            </div>
            <div class="card-body">
                
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success border-success" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-circle-check me-2 f-24"></i>
                            <div><?= $pesan_sukses; ?></div>
                        </div>
                        
                        <?php if (!empty($redirect_url)): ?>
                        <hr class="border-success opacity-50">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-dark"><i>Mengalihkan ke halaman cetak kartu tamu...</i></small>
                            <a href="<?= $redirect_url ?>" class="btn btn-dark btn-sm">
                                <i class="ti ti-id me-1"></i> Cetak Sekarang
                            </a>
                        </div>
                        <script>
                            setTimeout(function(){
                                window.location.href = '<?= $redirect_url ?>';
                            }, 2500);
                        </script>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesan_gagal)): ?>
                    <div class="alert alert-danger d-flex align-items-center border-danger" role="alert">
                        <i class="ti ti-circle-x me-2 f-24"></i>
                        <div><?= $pesan_gagal; ?></div> </div>
                <?php endif; ?>

                <form id="form-qr" method="POST" action="" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Kode Booking / Nomor Tiket</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dark"><i class="ti ti-qrcode"></i></span>
                            <input type="text" id="kode_booking_field" name="kode_booking" class="form-control form-control-lg font-monospace border-dark" placeholder="Contoh: REQ-2025-A001" required>
                        </div>
                        <div class="form-text text-muted">Petugas dapat mengetik kode manual jika kamera bermasalah.</div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold"><i class="ti ti-check me-1"></i> Konfirmasi Kehadiran</button>
                </form>

                <hr class="my-4 border-dashed">
                
                <h6 class="fw-bold text-dark mb-2">Petunjuk Operasional Keamanan:</h6>
                <ol class="text-muted small ps-3" style="line-height: 1.8;">
                    <li>Izinkan peramban mengakses perangkat kamera laptop/webcam.</li>
                    <li>Arahkan QR Code E-Ticket rombongan tamu ke depan lensa kamera.</li>
                    <li>Sistem otomatis memverifikasi dan <b>menerbitkan Kartu Tamu Sementara</b>.</li>
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

<?php
include 'template/footer.php';
?>