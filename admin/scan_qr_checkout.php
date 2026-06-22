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
    $kode_booking = mysqli_real_escape_string($koneksi, $_POST['kode_booking']);
    
    // Cek apakah kode booking tersebut valid di database
    $cek_kunjungan = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE kode_booking = '$kode_booking'");
    
    if ($cek_kunjungan && mysqli_num_rows($cek_kunjungan) > 0) {
        $data = mysqli_fetch_assoc($cek_kunjungan);
        
        $stat_kegiatan = strtolower($data['status_kegiatan']);
        $stat_kehadiran = strtolower($data['status_kehadiran']);
        
        // LOGIKA BARU YANG LEBIH TEPAT & KETAT
        if ($stat_kegiatan == 'selesai' || $stat_kehadiran == 'selesai') {
            // Jika sebelumnya sudah checkout
            $waktu_pulang = !empty($data['waktu_checkout']) ? date('d/m/Y H:i', strtotime($data['waktu_checkout'])) : 'Sebelumnya';
            $pesan_sukses = "Pemberitahuan: Instansi <b>" . $data['nama_instansi_tamu'] . "</b> sudah melakukan Check-Out pada " . $waktu_pulang . " WITA.";
            
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
            // Jika tamu belum check-in sama sekali (masih pending/dijadwalkan/batal)
            $pesan_gagal = "Gagal! Instansi <b>" . $data['nama_instansi_tamu'] . "</b> belum melakukan Check-In. (Status saat ini: " . ucfirst($stat_kegiatan) . ")";
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
                    <i class="ti ti-info-circle me-1 text-primary"></i> Posisikan QR Code dari Kartu Tamu Sementara tepat di tengah kotak kamera.
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
                        <label class="form-label fw-bold text-dark">Kode Booking / Nomor Tiket</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dark"><i class="ti ti-qrcode"></i></span>
                            <input type="text" id="kode_booking_field" name="kode_booking" class="form-control form-control-lg font-monospace border-dark" placeholder="Contoh: REQ-2025-A001" required>
                        </div>
                        <div class="form-text text-muted">Petugas dapat mengetik kode manual jika kamera bermasalah.</div>
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
    document.getElementById('kode_booking_field').value = decodedText;
    html5QrcodeScanner.clear();
    document.getElementById('form-qr-checkout').submit();
}
function onScanFailure(error) { }

let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: { width: 230, height: 230 } }, false);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

<style>
.border-dashed { border-style: dashed !important; border-width: 1px !important; border-color: #cbd5e1 !important; }
#reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 8px; }
#reader button { display: inline-block; font-weight: 500; color: #fff; background-color: #212529; border: 1px solid #212529; padding: 0.375rem 0.75rem; font-size: 0.875rem; border-radius: 0.25rem; margin: 10px 5px; cursor: pointer; transition: 0.2s; }
#reader button:hover { background-color: #343a40; }
</style>

<?php include 'template/footer.php'; ?>