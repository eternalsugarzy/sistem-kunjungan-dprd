<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'data_kunjungan';
include 'template/header.php';
include 'template/sidebar.php';
include '../koneksi.php';

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
        // UPDATE JIKA SUDAH ADA
        $query = "UPDATE spt_tugas SET 
                  jenis_petugas = '$jenis_petugas', no_spt = '$no_spt', tgl_spt = '$tgl_spt', 
                  nama_pegawai = '$nama_pegawai', nip = '$nip', jabatan = '$jabatan', 
                  jumlah_ditugaskan = '$jumlah_ditugaskan' 
                  WHERE id_kunjungan = '$id_kunjungan'";
    } else {
        // INSERT JIKA BELUM ADA
        $query = "INSERT INTO spt_tugas (id_kunjungan, jenis_petugas, no_spt, tgl_spt, nama_pegawai, nip, jabatan, jumlah_ditugaskan) 
                  VALUES ('$id_kunjungan', '$jenis_petugas', '$no_spt', '$tgl_spt', '$nama_pegawai', '$nip', '$jabatan', '$jumlah_ditugaskan')";
    }

    if (mysqli_query($koneksi, $query)) {
        // Langsung arahkan ke halaman cetak setelah berhasil simpan
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
                    <h5 class="m-b-5">Pembuatan Surat Perintah Tugas (SPT)</h5>
                </div>
                <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="data_kunjungan.php">Data Kunjungan</a></li>
                    <li class="breadcrumb-item text-muted">Buat SPT</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i> <?= $pesan_error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-dark">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white">Form Input SPT: <?= $data_kunjungan['kode_booking']; ?></h6>
                <span class="badge bg-light text-dark"><?= $data_kunjungan['nama_instansi_tamu']; ?></span>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor Surat (SPT) <span class="text-danger">*</span></label>
                            <input type="text" name="no_spt" class="form-control" placeholder="Contoh: 090/DPRD/SPT/2026" value="<?= $data_spt['no_spt'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal SPT <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_spt" class="form-control" value="<?= $data_spt['tgl_spt'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Petugas / Peran <span class="text-danger">*</span></label>
                        <select name="jenis_petugas" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="dprd" <?= (isset($data_spt['jenis_petugas']) && $data_spt['jenis_petugas'] == 'dprd') ? 'selected' : ''; ?>>Anggota DPRD (Penerima Tamu)</option>
                            <option value="pendamping" <?= (isset($data_spt['jenis_petugas']) && $data_spt['jenis_petugas'] == 'pendamping') ? 'selected' : ''; ?>>Staf Pendamping (Sekretariat)</option>
                        </select>
                    </div>

                    <hr class="border-dashed my-4">
                    <h6 class="fw-bold mb-3 bg-light p-2 border-start border-4 border-dark">Pegawai yang Ditugaskan:</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Pegawai / Pejabat <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pegawai" class="form-control" placeholder="Nama Lengkap & Gelar" value="<?= $data_spt['nama_pegawai'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIP</label>
                            <input type="text" name="nip" class="form-control" placeholder="Kosongkan jika bukan ASN" value="<?= $data_spt['nip'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Ketua Komisi / Staf Humas" value="<?= $data_spt['jabatan'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jumlah Orang <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_ditugaskan" class="form-control" value="<?= $data_spt['jumlah_ditugaskan'] ?? '1'; ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="detail_kunjungan.php?id=<?= $id_kunjungan; ?>" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" name="simpan_spt" class="btn btn-dark px-4">
                            <i class="ti ti-device-floppy me-1"></i> Simpan & Lanjut Cetak
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>