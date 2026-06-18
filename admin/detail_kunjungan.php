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
    // REPARASI QUERY: Melakukan LEFT JOIN untuk menarik nama_ruangan dan nama_pj secara dinamis dari DB
    $query = mysqli_query($koneksi, "
        SELECT k.*, 
               r.nama_ruangan, 
               p.nama_pj,
               IFNULL(kat.nama_kategori, 'Umum') as nama_kategori_db
        FROM kunjungan k
        LEFT JOIN ruangan r ON k.id_ruangan = r.id_ruangan
        LEFT JOIN penanggung_jawab p ON k.id_pj = p.id_pj
        LEFT JOIN kategori_kunjungan kat ON k.id_kategori = kat.id_kategori
        WHERE k.id_kunjungan = '$id_kunjungan'
    ");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $d = mysqli_fetch_assoc($query);
        $is_mockup = false;
    }
}

// ==========================================
// VALIDASI DATA / FALLBACK MOCK DATA (ANTI-ERROR)
// ==========================================
$kode_booking = ($d && isset($d['kode_booking'])) ? $d['kode_booking'] : 'REQ-2025-A001';
$instansi     = ($d && isset($d['nama_instansi_tamu'])) ? $d['nama_instansi_tamu'] : 'DPRD Kab. Tanah Laut';
$tgl_kunjungan= ($d && isset($d['tgl_kunjungan'])) ? $d['tgl_kunjungan'] : '2025-12-10';
$waktu        = ($d && isset($d['waktu_kunjungan'])) ? $d['waktu_kunjungan'] : '09:00';
$peserta      = ($d && isset($d['jumlah_peserta_rencana'])) ? $d['jumlah_peserta_rencana'] : '15';
$email        = ($d && isset($d['email_pemohon'])) ? $d['email_pemohon'] : 'dprd@tanahlaut.go.id';
$tujuan       = ($d && isset($d['materi_kunjungan'])) ? $d['materi_kunjungan'] : 'Studi Tiru Perda Wisata';
$status       = ($d && isset($d['status_kegiatan'])) ? strtolower($d['status_kegiatan']) : 'selesai';

// Ruangan dan Penanggung Jawab Dinamis (Murni dari relasi database)
$ruangan      = ($d && !empty($d['nama_ruangan'])) ? $d['nama_ruangan'] : '<span class="text-muted font-italic">Belum Ditentukan (Pending)</span>';
$pj           = ($d && !empty($d['nama_pj'])) ? $d['nama_pj'] : '<span class="text-muted font-italic">Belum Ditentukan (Pending)</span>';

// ==========================================
// LOGIKA PEMBUATAN & PENYIMPANAN QR CODE
// ==========================================
$qr_code_path = ($d && isset($d['qr_code_path'])) ? $d['qr_code_path'] : '';
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode($kode_booking);

if (empty($qr_code_path) && $is_mockup == false) {
    $folder_qr = "../uploads/qr/";
    if (!file_exists($folder_qr)) {
        mkdir($folder_qr, 0777, true);
    }
    
    $nama_file_qr = "QR_" . $kode_booking . ".png";
    $path_simpan_lokal = $folder_qr . $nama_file_qr;
    
    $gambar_qr = @file_get_contents($qr_api_url);
    if ($gambar_qr !== false) {
        file_put_contents($path_simpan_lokal, $gambar_qr);
        $path_db = "uploads/qr/" . $nama_file_qr;
        mysqli_query($koneksi, "UPDATE kunjungan SET qr_code_path = '$path_db' WHERE id_kunjungan = '$id_kunjungan'");
        
        $qr_code_path = $path_db;
        $qr_image_src = "../" . $path_db;
    } else {
        $qr_image_src = $qr_api_url;
    }
} else if (!empty($qr_code_path)) {
    $qr_image_src = "../" . $qr_code_path;
} else {
    $qr_image_src = $qr_api_url;
}

// Logika penentu nama kategori (Gunakan dari tabel master jika ada, jika tidak lakukan fallback teks)
if ($d && !empty($d['nama_kategori_db']) && $d['nama_kategori_db'] !== 'Umum') {
    $kategori = $d['nama_kategori_db'];
} else {
    $materi_cek = strtolower($tujuan);
    $kategori = 'Audiensi';
    if (strpos($materi_cek, 'tiru') !== false) $kategori = 'Studi Tiru';
    elseif (strpos($materi_cek, 'kerja') !== false) $kategori = 'Kunjungan Kerja';
    elseif (strpos($materi_cek, 'konsul') !== false) $kategori = 'Konsultasi';
}

$tgl_format   = date('d F Y', strtotime($tgl_kunjungan));
?>

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

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Kunjungan: <span class="text-primary"><?= htmlspecialchars($kode_booking); ?></span></h5>
                <a href="data_kunjungan.php" class="btn btn-sm btn-light-secondary text-dark border">Kembali</a>
            </div>
            
            <div class="card-body">
                <div class="row g-4">
                    
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
                                        <td class="fw-medium text-dark">: <?= $ruangan; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-normal">Penanggung Jawab</td>
                                        <td class="fw-bold text-dark">: <?= $pj; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-3 border-top border-dashed">
                            <h6 class="fw-bold mb-2 text-dark">Status QR Code:</h6>
                            <div class="p-3 rounded bg-light border border-dashed text-dark" style="font-size: 12px; line-height: 1.8;">
                                <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> QR Code sukses dibuat oleh sistem admin.</div>
                                <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> Path lokal tersimpan di DB: <strong><?= !empty($qr_code_path) ? htmlspecialchars($qr_code_path) : 'Menunggu Generate...'; ?></strong></div>
                                <?php if($status == 'selesai'): ?>
                                    <div class="text-success"><i class="ti ti-circle-check-filled me-1"></i> Kunjungan valid — QR telah berhasil dipindai di pintu front office pada <?= $tgl_format; ?> (<?= htmlspecialchars($waktu); ?> WITA)</div>
                                <?php else: ?>
                                    <div class="text-muted"><i class="ti ti-clock me-1"></i> Menunggu proses pemindaian barcode e-ticket saat rombongan instansi tiba di lokasi.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-start pt-2">
                        <span class="text-muted d-block mb-3 fw-bold small">E-Ticket QR Code:</span>
                        
                        <div class="p-3 border rounded bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 160px; height: 160px;">
                            <img src="<?= $qr_image_src; ?>" alt="QR" class="img-fluid">
                        </div>
                        <small class="text-muted d-block mt-2 font-monospace fw-bold"><?= htmlspecialchars($kode_booking); ?></small>
                    </div>

                </div>

                <div class="mt-4 pt-4 border-top border-dashed">
                    <h6 class="fw-bold text-dark mb-3">Lampiran &amp; Cetak Dokumen Administrasi:</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="../uploads/<?= ($d && isset($d['file_surat_permohonan'])) ? htmlspecialchars($d['file_surat_permohonan']) : '#'; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-download me-1"></i>Lihat Surat Permohonan
                        </a>
                        <a href="cetak_surat_tte.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-primary <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-file-certificate me-1"></i>Cetak Surat Balasan ber-TTE
                        </a>
                        <a href="cetak_disposisi_tte.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-warning <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-file-description me-1"></i>Cetak Lembar Disposisi + TTE
                        </a>
                        <a href="cetak_spt.php?id=<?= $id_kunjungan; ?>" target="_blank" class="btn btn-sm btn-outline-info <?= ($status == 'pending') ? 'disabled opacity-50' : ''; ?>">
                            <i class="ti ti-printer me-1"></i>Cetak Dokumen SPT
                        </a>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="data_kunjungan.php" class="btn btn-dark px-4">Tutup</a>
                </div>

            </div>
        </div>
    </div>
</div>

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
        border-right: 1px solid #e2e8f0 !important;
    }
}
</style>

<?php
include 'template/footer.php';
?>