<?php
// ==========================================
// PENGATURAN DEBUG ERROR & INTEGRASI
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'disposisi'; // Sesuai nama menu di sidebar

include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

$pesan_sukses = "";
$pesan_gagal = "";

// ---------------------------------------------------------
// LOGIC PROSES SIMPAN DISPOSISI PIMPINAN (UPDATE STATUS & TTE)
// ---------------------------------------------------------
if (isset($_POST['proses_disposisi'])) {
    $id_kunjungan = mysqli_real_escape_string($koneksi, $_POST['id_kunjungan']);
    $id_ruangan   = mysqli_real_escape_string($koneksi, $_POST['id_ruangan']);
    $id_pj        = mysqli_real_escape_string($koneksi, $_POST['id_pj']);
    $keputusan    = mysqli_real_escape_string($koneksi, $_POST['keputusan']);
    $passphrase   = mysqli_real_escape_string($koneksi, $_POST['passphrase']); // Validasi TTE dari Proposal

    // Batasan Proposal: Validasi Passphrase sandi frasa rahasia pimpinan untuk mengaktifkan TTE
    if ($passphrase !== "pimpinan123") {
        $pesan_gagal = "Gagal! Passphrase (Sandi Frasa) Keamanan TTE Pimpinan Salah.";
    } else {
        $status_kegiatan = ($keputusan == 'setuju') ? 'dijadwalkan' : 'batal';
        
        // Jika ditolak lewat disposisi, catat alasan & waktu pembatalan agar tercatat di Laporan Kunjungan Batal
        $extra_batal = "";
        if ($status_kegiatan == 'batal') {
            $extra_batal = ", alasan_pembatalan = 'Ditolak oleh Pimpinan pada saat Disposisi', tgl_pembatalan = NOW()";
        }
        
        // Update data kunjungan sesuai parameter penugasan ruangan dan PJ lapangan
        $query_update = "UPDATE kunjungan SET 
                            id_ruangan = '$id_ruangan', 
                            id_pj = '$id_pj', 
                            status_kegiatan = '$status_kegiatan'
                            $extra_batal
                         WHERE id_kunjungan = '$id_kunjungan'";
                         
        if (mysqli_query($koneksi, $query_update)) {
            $pesan_sukses = "Sukses! Lembar Otorisasi Disposisi Ber-TTE Berhasil Diterbitkan.";
        } else {
            $pesan_gagal = "Gagal memproses disposisi: " . mysqli_error($koneksi);
        }
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Disposisi Pimpinan (Validasi &amp; TTE)</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Disposisi Pimpinan</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-sm-12">
        
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success"><i class="ti ti-circle-check me-2"></i><?= $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_gagal)): ?>
            <div class="alert alert-danger"><i class="ti ti-circle-x me-2"></i><?= $pesan_gagal; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Permohonan Menunggu Disposisi</h5>
                <span class="badge bg-danger style-badge">OTORISASI PIMPINAN</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode &amp; Tgl</th>
                                <th>Instansi Tamu</th>
                                <th>Tujuan / Materi</th>
                                <th>Status</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Ambil hanya data yang masih 'pending' untuk diredistribusi disposisinya
                            $q_pending = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending' ORDER BY id_kunjungan DESC");
                            
                            if ($q_pending && mysqli_num_rows($q_pending) > 0) {
                                while ($d = mysqli_fetch_array($q_pending)) {
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="font-monospace fw-bold text-primary"><?= $d['kode_booking']; ?><br><small class="text-muted"><?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?></small></td>
                                <td><h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['nama_instansi_tamu']); ?></h6><small class="text-muted"><?= htmlspecialchars($d['email_pemohon']); ?></small></td>
                                <td><small class="text-dark"><?= htmlspecialchars($d['materi_kunjungan']); ?></small></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-dark font-monospace" data-bs-toggle="modal" data-bs-target="#modalDisposisi<?= $d['id_kunjungan']; ?>">
                                        <i class="ti ti-gavel me-1"></i>[ Disposisi ]
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalDisposisi<?= $d['id_kunjungan']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-dark-card">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold font-monospace">[ Lembar Disposisi: <?= $d['kode_booking']; ?> ]</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_kunjungan" value="<?= $d['id_kunjungan']; ?>">
                                                
                                                <div class="alert alert-light border small text-dark mb-3">
                                                    <strong>Instansi Tamu:</strong> <?= htmlspecialchars($d['nama_instansi_tamu']); ?><br>
                                                    <strong>Materi Kunjungan:</strong> <?= htmlspecialchars($d['materi_kunjungan']); ?>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark">Keputusan Pimpinan *</label>
                                                    <select name="keputusan" class="form-select form-select-sm" required>
                                                        <option value="setuju">Setujui &amp; Jadwalkan Kunjungan</option>
                                                        <option value="tolak">Tolak / Batalkan Permohonan</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark">Rekomendasi Ruangan Pertemuan *</label>
                                                    <select name="id_ruangan" class="form-select form-select-sm" required>
                                                        <?php
                                                        $r_query = mysqli_query($koneksi, "SELECT * FROM ruangan");
                                                        while($r = mysqli_fetch_array($r_query)){
                                                            echo "<option value='{$r['id_ruangan']}'>{$r['nama_ruangan']} ({$r['lantai']})</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark">Penanggung Jawab Lapangan (PJ) *</label>
                                                    <select name="id_pj" class="form-select form-select-sm" required>
                                                        <?php
                                                        $pj_query = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab");
                                                        while($p = mysqli_fetch_array($pj_query)){
                                                            echo "<option value='{$p['id_pj']}'>{$p['nama_pj']} - {$p['jabatan']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label fw-bold text-danger">Passphrase Otorisasi TTE Pimpinan *</label>
                                                    <input type="password" name="passphrase" class="form-control form-control-sm border-danger" placeholder="Masukkan sandi frasa TTE" required>
                                                    <div class="form-text text-muted small" style="font-size:10px;">*Gunakan kata kunci: <code class="text-danger">pimpinan123</code> untuk simulasi sempro.</div>
                                                </div>

                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="proses_disposisi" class="btn btn-dark btn-sm font-monospace">[ ✓ Sahkan Disposisi ]</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center text-muted py-4">Bersih! Tidak ada permohonan kunjungan baru yang memerlukan disposisi pimpinan.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<style>
.border-dark-card { border: 2px solid #2e2e2e !important; }
.style-badge { font-size: 8px !important; font-weight: 700 !important; background-color: #dc3545 !important; }
</style>

<?php include 'template/footer.php'; ?>