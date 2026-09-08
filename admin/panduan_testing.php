<?php
$page = 'panduan';
include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">📖 Panduan Pengujian & Demonstrasi Sistem (Walkthrough)</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Panduan Pengujian PDF</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-12">
    <div class="card bg-primary text-white shadow-sm border-0">
      <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <h4 class="text-white fw-bold mb-1"><i class="ti ti-file-text me-2"></i>Dokumen PDF Panduan Ujian & Demonstrasi Tersedia!</h4>
          <p class="mb-0 text-white-50">Gunakan panduan ini saat presentasi/sidang untuk menunjukkan seluruh fitur dan pembuktian 4 poin revisi dosen secara berurutan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="../PANDUAN_PENGUJIAN_SISTEM_DPRD.pdf" target="_blank" class="btn btn-light text-primary fw-bold">
            <i class="ti ti-external-link me-1"></i> Buka PDF di Tab Baru
          </a>
          <a href="../PANDUAN_PENGUJIAN_SISTEM_DPRD.pdf" download="PANDUAN_PENGUJIAN_SISTEM_DPRD.pdf" class="btn btn-warning text-dark fw-bold">
            <i class="ti ti-download me-1"></i> Unduh File PDF
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RINGKASAN REVISI -->
<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="card h-100 border-start border-4 border-info shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold text-info"><i class="ti ti-camera me-1"></i> 1. Foto Tamu Real-Time</h6>
        <p class="small text-muted mb-0">Webcam snapshot & upload foto tamu di buku tamu + tampil di laporan absensi.</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100 border-start border-4 border-success shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold text-success"><i class="ti ti-qrcode me-1"></i> 2. QR Code Signature</h6>
        <p class="small text-muted mb-0">Token ringkas ber-hash HMAC-SHA256, scan super cepat & patuh jadwal.</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100 border-start border-4 border-warning shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold text-warning"><i class="ti ti-building me-1"></i> 3. Kapasitas & Jadwal</h6>
        <p class="small text-muted mb-0">Validasi otomatis tolak jika kapasitas ruangan kurang atau ada bentrok jadwal.</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100 border-start border-4 border-danger shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold text-danger"><i class="ti ti-shield-check me-1"></i> 4. Anti-Duplikat</h6>
        <p class="small text-muted mb-0">Proteksi 1x scan check-in/out, anti double-input nama & master data.</p>
      </div>
    </div>
  </div>
</div>

<!-- PREVIEW EMBED PDF / HTML -->
<div class="row">
  <div class="col-md-12">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="ti ti-eye me-2"></i>Pratinjau Lembar Panduan</h5>
        <a href="../panduan_pengujian.html" target="_blank" class="btn btn-sm btn-outline-secondary">
          <i class="ti ti-browser me-1"></i> Tampilan Web
        </a>
      </div>
      <div class="card-body p-0">
        <iframe src="../panduan_pengujian.html" style="width: 100%; height: 750px; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>
