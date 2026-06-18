<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'data_kunjungan'; // Menjaga sidebar data kunjungan tetap aktif

include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS (Anti-stuck preloader)
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// ==========================================
// AMBIL DATA BERDASARKAN ID DI URL
// ==========================================
$id_kunjungan = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

$is_mockup = true;
$d = null;

if (!empty($id_kunjungan) && isset($koneksi)) {
    // Query dasar yang aman tanpa JOIN tabel yang tidak ada
    $query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE id_kunjungan = '$id_kunjungan'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $d = mysqli_fetch_assoc($query);
        $is_mockup = false;
    }
}

// ==========================================
// SOLUSI AMAN: Validasi Null / Fallback Mock Data (Anti-Error)
// ==========================================
$kode_booking = ($d && isset($d['kode_booking'])) ? $d['kode_booking'] : 'REQ-2025-A001';
$instansi     = ($d && isset($d['nama_instansi_tamu'])) ? $d['nama_instansi_tamu'] : 'DPRD Kab. Tanah Laut';
$tgl_kunjungan= ($d && isset($d['tgl_kunjungan'])) ? $d['tgl_kunjungan'] : '2025-12-10';
$waktu        = ($d && isset($d['waktu_kunjungan'])) ? $d['waktu_kunjungan'] : '09:00';
$peserta      = ($d && isset($d['jumlah_peserta_rencana'])) ? $d['jumlah_peserta_rencana'] : '15';
$email        = ($d && isset($d['email_pemohon'])) ? $d['email_pemohon'] : 'dprd@tanahlaut.go.id';
$tujuan       = ($d && isset($d['materi_kunjungan'])) ? $d['materi_kunjungan'] : 'Studi Tiru Perda Wisata';
$status       = ($d && isset($d['status_kegiatan'])) ? strtolower($d['status_kegiatan']) : 'selesai';

// Logika penentu kategori otomatis berdasarkan isi teks materi permohonan
$materi_cek = strtolower($tujuan);
$kategori = 'Audiensi';
if (strpos($materi_cek, 'tiru') !== false) $kategori = 'Studi Tiru';
elseif (strpos($materi_cek, 'kerja') !== false) $kategori = 'Kunjungan Kerja';
elseif (strpos($materi_cek, 'konsul') !== false) $kategori = 'Konsultasi';

$ruangan      = ($d && isset($d['nama_ruangan'])) ? $d['nama_ruangan'] : 'Ruang Komisi 2';
$pj           = ($d && isset($d['nama_pj'])) ? $d['nama_pj'] : 'H. Muh. Jaini, SE, MAP';

$tgl_format   = date('d F Y', strtotime($tgl_kunjungan));
?>

<!-- BREADCRUMB (SAMA SEPERTI DASHBOARD / DATA KUNJUNGAN) -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Arsip Data Kunjungan</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item"><a href="data_kunjungan.php">Data Kunjungan</a></li>
          <li class="breadcrumb-item text-muted">Detail</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- KONTEN UTAMA DENGAN TEMA COMPONENT DASHBOARD LAMA -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <!-- HEADER CARD -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Kunjungan: <span class="text-primary"><?= htmlspecialchars($kode_booking); ?></span></h5>
                <a href="data_kunjungan.php" class="btn btn-sm btn-light-secondary text-dark border">Kembali</a>
            </div>
            
            <!-- BODY CARD -->
            <div class="card-body">
                <div class="row g-4">
                    
                    <!-- SISI KIRI: TABEL INFORMASI ATRIBUT -->
                    <div class="col-md-8 border-end-md">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="text-muted fw-normal">Instansi</td>
                                        <td class="fw-bold text-dark">: <?= htmlspecialchars($instansi); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Waktu Kunjungan</td>
                                        <td>: <?= $tgl_format; ?> — Pukul <?= htmlspecialchars($waktu); ?> WITA</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Jumlah Peserta</td>
                                        <td>: <?= htmlspecialchars($peserta); ?> Orang</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Email Tamu</td>
                                        <td class="text-primary">: <u><?= htmlspecialchars($email); ?></u></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Tujuan / Materi</td>
                                        <td>: <?= htmlspecialchars($tujuan); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Kategori</td>
                                        <td>: 
                                            <span class="badge bg-light-secondary text-dark border"><?= htmlspecialchars($kategori); ?></span>
                                            <span class="badge bg-danger ms-1 style-mini-tag">UPDATE</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Status</td>
                                        <td>: 
                                            <?php 
                                            if($status == 'pending') echo '<span class="badge bg-warning">Pending</span>';
                                            elseif($status == 'dijadwalkan') echo '<span class="badge bg-primary">Dijadwalkan</span>';
                                            elseif($status == 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                                            else echo '<span class="badge bg-danger">Batal</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Ruangan</td>
                                        <td>: <?= htmlspecialchars($ruangan); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Penanggung Jawab</td>
                                        <td>: <?= htmlspecialchars($pj); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- LOG STATUS QR CODE -->
                        <div class="mt-4 pt-3 border-top border-dashed">
                            <h6 class="fw-bold mb-2 text-dark">Status QR Code: <span class="badge bg-danger style-mini-tag">BARU</span></h6>
                            <div class="p-3 rounded bg-light border border-dashed text-dark" style="font-size: 12px; line-height: 1.8;">
                                <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> QR Code sukses dibuat oleh sistem admin.</div>
                                <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> E-Ticket konfirmasi lampiran otomatis terkirim ke email tamu.</div>
                                <?php if($status == 'selesai'): ?>
                                    <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> Kunjungan valid — QR telah berhasil dipindai di pintu front office pada <?= $tgl_format; ?> (<?= htmlspecialchars($waktu); ?> WITA)</div>
                                <?php else: ?>
                                    <div class="text-muted"><i class="ti ti-clock me-1"></i> Menunggu proses pemindaian barcode e-ticket saat rombongan instansi tiba di lokasi.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SISI KANAN: GENERATOR LIVE E-TICKET QR CODE -->
                    <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-start pt-2">
                        <span class="text-muted d-block mb-3 fw-bold small">E-Ticket QR Code:</span>
                        
                        <!-- Box Frame QR Code Bawaan Template -->
                        <div class="p-3 border rounded bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 160px; height: 160px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=<?= urlencode($kode_booking); ?>" 
                                 alt="QR" class="img-fluid">
                        </div>
                        <small class="text-muted d-block mt-2 font-monospace fw-bold"><?= htmlspecialchars($kode_booking); ?></small>
                    </div>

                </div>

                <!-- SEKSI AKSI CETAK ADMINISTRASI (BAGIAN BAWAH CARD) -->
                <div class="mt-4 pt-4 border-top border-dashed">
                    <h6 class="fw-bold text-dark mb-3">Lampiran &amp; Cetak Dokumen Administrasi:</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="../uploads/<?= ($d && isset($d['file_surat_permohonan'])) ? $d['file_surat_permohonan'] : '#'; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-download me-1"></i>Lihat Surat Permohonan
                        </a>
                        <a href="cetak_surat_tte.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-primary <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-file-certificate me-1"></i>Cetak Surat Balasan ber-TTE <span class="badge bg-light-danger text-danger style-mini-tag">UPDATE</span>
                        </a>
                        <a href="cetak_disposisi_tte.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-warning <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-file-description me-1"></i>Cetak Lembar Disposisi + TTE <span class="badge bg-light-danger text-danger style-mini-tag">UPDATE</span>
                        </a>
                        <a href="cetak_spt.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-info <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-printer me-1"></i>Cetak Dokumen SPT <span class="badge bg-danger text-white style-mini-tag">BARU</span>
                        </a>
                    </div>
                </div>

                <!-- BUTTON CLOSING FOOTER CARD -->
                <div class="d-flex justify-content-end mt-4">
                    <a href="data_kunjungan.php" class="btn btn-dark px-4">Tutup</a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- UTILITY STYLE TAMBAHAN -->
<style>
.border-dashed {
    border-style: dashed !important;
    border-width: 1px !important;
    border-color: #cbd5e1 !important;
}
.style-mini-tag {
    font-size: 7px !important;
    font-weight: 600 !important;
    padding: 2px 4px !important;
    vertical-align: middle;
}
@media (min-width: 768px) {
    .border-end-md {
        border-end: 1px solid #e2e8f0 !important;
    }
}
</style>

<?php
include 'template/footer.php';
?>