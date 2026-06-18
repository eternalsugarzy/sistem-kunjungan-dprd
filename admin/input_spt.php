<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'data_kunjungan';
include 'template/header.php';
include 'template/sidebar.php';
include '../koneksi.php';

// SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

$id_kunjungan = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
$pesan_sukses = "";
$pesan_error = "";

// Cek apakah data kunjungan valid
$cek_kunjungan = mysqli_query($koneksi, "SELECT kode_booking, nama_instansi_tamu FROM kunjungan WHERE id_kunjungan = '$id_kunjungan'");
if (!$cek_kunjungan || mysqli_num_rows($cek_kunjungan) == 0) {
    echo "<script>alert('Data kunjungan tidak ditemukan!'); window.location.href='data_kunjungan.php';</script>";
    exit;
}
$data_kunjungan = mysqli_fetch_assoc($cek_kunjungan);

// Cek apakah data SPT sudah ada sebelumnya (untuk fitur Update)
$cek_spt = mysqli_query($koneksi, "SELECT * FROM spt_tugas WHERE id_kunjungan = '$id_kunjungan'");
$data_spt = ($cek_spt && mysqli_num_rows($cek_spt) > 0) ? mysqli_fetch_assoc($cek_spt) : null;

// PROSES SIMPAN DATA
if (isset($_POST['simpan_spt'])) {
    $jenis_petugas = mysqli_real_escape_string($koneksi, $_POST['jenis_petugas']);
    $no_spt = mysqli_real_escape_string($koneksi, $_POST['no_spt']);
    $tgl_spt = mysqli_real_escape_string($koneksi, $_POST['tgl_spt']);
    $nama_pegawai = mysqli_real_escape_string($koneksi, $_POST['nama_pegawai']);
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $jumlah_ditugaskan = mysqli_real_escape_string($koneksi, $_POST['jumlah_ditugaskan']);

    if ($data_spt) {
        $query = "UPDATE spt_tugas SET 
                  jenis_petugas = '$jenis_petugas', no_spt = '$no_spt', tgl_spt = '$tgl_spt', 
                  nama_pegawai = '$nama_pegawai', nip = '$nip', jabatan = '$jabatan', 
                  jumlah_ditugaskan = '$jumlah_ditugaskan' 
                  WHERE id_kunjungan = '$id_kunjungan'";
    } else {
        $query = "INSERT INTO spt_tugas (id_kunjungan, jenis_petugas, no_spt, tgl_spt, nama_pegawai, nip, jabatan, jumlah_ditugaskan) 
                  VALUES ('$id_kunjungan', '$jenis_petugas', '$no_spt', '$tgl_spt', '$nama_pegawai', '$nip', '$jabatan', '$jumlah_ditugaskan')";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>
                alert('Data SPT berhasil disimpan! Mengalihkan ke halaman cetak...');
                window.location.href = 'cetak_spt.php?id=$id_kunjungan';
              </script>";
        exit;
    } else {
        $pesan_error = "Gagal menyimpan data SPT: " . mysqli_error($koneksi);
    }
}
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-5">Penerbitan Surat Perintah Tugas (SPT)</h5>
                </div>
                <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="data_kunjungan.php">Arsip Kunjungan</a></li>
                    <li class="breadcrumb-item text-muted">Input SPT</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm">
                <i class="ti ti-alert-circle f-24 me-2"></i> 
                <div><?= $pesan_error; ?></div>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
            <div class="card-body d-flex justify-content-between align-items-center p-3">
                <div>
                    <h5 class="mb-1 text-white"><i class="ti ti-file-certificate me-2"></i>Form Administrasi SPT</h5>
                    <small class="text-white-50">Silakan lengkapi form penugasan di bawah ini sebelum mencetak dokumen.</small>
                </div>
                <div class="text-end">
                    <span class="d-block text-white-50 small mb-1">Tiket Rujukan:</span>
                    <span class="badge bg-white text-dark f-14 px-3 py-2 border-dark"><?= $data_kunjungan['kode_booking']; ?></span>
                </div>
            </div>
        </div>

        <form method="POST" action="">
            
            <div class="card shadow-sm border-dark mb-4">
                <div class="card-header bg-white border-bottom border-dark">
                    <h6 class="fw-bold mb-0 text-dark">A. Nomor & Tanggal Surat</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="ti ti-hash text-muted me-1"></i>Nomor SPT <span class="text-danger">*</span></label>
                            <input type="text" name="no_spt" class="form-control form-control-lg border-dark" placeholder="090/DPRD/SPT/2026" value="<?= $data_spt['no_spt'] ?? ''; ?>" required>
                            <small class="text-muted mt-1 d-block">Format resmi nomor surat keluar Sekretariat.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="ti ti-calendar-event text-muted me-1"></i>Tanggal Terbit <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_spt" class="form-control form-control-lg border-dark" value="<?= $data_spt['tgl_spt'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-dark mb-4">
                <div class="card-header bg-white border-bottom border-dark d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">B. Detail Pegawai/Pejabat yang Ditugaskan</h6>
                    <span class="badge bg-light-secondary text-dark">Melayani: <?= $data_kunjungan['nama_instansi_tamu']; ?></span>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="ti ti-briefcase text-muted me-1"></i>Kategori Peran <span class="text-danger">*</span></label>
                        <select name="jenis_petugas" class="form-select form-select-lg border-dark" required>
                            <option value="">-- Pilih Jenis Penugasan --</option>
                            <option value="dprd" <?= (isset($data_spt['jenis_petugas']) && $data_spt['jenis_petugas'] == 'dprd') ? 'selected' : ''; ?>>Penerima Tamu Resmi (Anggota DPRD / Komisi)</option>
                            <option value="pendamping" <?= (isset($data_spt['jenis_petugas']) && $data_spt['jenis_petugas'] == 'pendamping') ? 'selected' : ''; ?>>Staf Pendamping (Pegawai Sekretariat DPRD)</option>
                        </select>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-bold"><i class="ti ti-user text-muted me-1"></i>Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pegawai" class="form-control border-dark" placeholder="Contoh: H. Ahmad Saiduns, S.Kom, MAP" value="<?= $data_spt['nama_pegawai'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold"><i class="ti ti-id text-muted me-1"></i>Nomor Induk Pegawai (NIP)</label>
                            <input type="text" name="nip" class="form-control border-dark" placeholder="Kosongkan jika Honorer/Dewan" value="<?= $data_spt['nip'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-9">
                            <label class="form-label fw-bold"><i class="ti ti-award text-muted me-1"></i>Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control border-dark" placeholder="Contoh: Ketua Komisi I / Staf Ahli" value="<?= $data_spt['jabatan'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="ti ti-users text-muted me-1"></i>Banyaknya <span class="text-danger">*</span></label>
                            <div class="input-group border-dark">
                                <input type="number" name="jumlah_ditugaskan" class="form-control border-dark text-center" value="<?= $data_spt['jumlah_ditugaskan'] ?? '1'; ?>" min="1" required>
                                <span class="input-group-text bg-light border-dark">Orang</span>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="card-footer bg-light border-top border-dark d-flex justify-content-between p-3">
                    <a href="data_kunjungan.php" class="btn btn-outline-dark px-4"><i class="ti ti-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="simpan_spt" class="btn btn-dark px-4 fw-bold">
                        <i class="ti ti-printer me-1"></i> Simpan Data & Cetak Dokumen SPT
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<?php include 'template/footer.php'; ?>