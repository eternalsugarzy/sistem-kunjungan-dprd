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

// Helper: konversi tanggal ke nama hari berbahasa Indonesia
// (dipakai untuk mencocokkan jadwal ketersediaan pejabat)
if (!function_exists('nama_hari_indo')) {
    function nama_hari_indo($tanggal) {
        $map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at", 'Saturday' => 'Sabtu'
        ];
        return $map[date('l', strtotime($tanggal))] ?? '';
    }
}

// Helper: cek apakah PJ TIDAK tersedia (di luar jadwal ketersediaan ATAU bentrok
// dengan kunjungan lain) pada hari & jam kunjungan tertentu.
// Return true jika bermasalah (tidak tersedia/bentrok), false jika aman.
if (!function_exists('cek_pj_tidak_tersedia')) {
    function cek_pj_tidak_tersedia($koneksi, $id_pj, $id_kunjungan) {
        $id_pj = mysqli_real_escape_string($koneksi, $id_pj);
        $id_kunjungan = mysqli_real_escape_string($koneksi, $id_kunjungan);

        // Ambil tanggal & jam kunjungan yang sedang diproses
        $q = mysqli_query($koneksi, "SELECT tgl_kunjungan, waktu_kunjungan FROM kunjungan WHERE id_kunjungan='$id_kunjungan'");
        if (!$q || mysqli_num_rows($q) == 0) return false;
        $k = mysqli_fetch_assoc($q);
        $tgl = $k['tgl_kunjungan'];
        $jam = $k['waktu_kunjungan'];
        $hari = mysqli_real_escape_string($koneksi, nama_hari_indo($tgl));

        // 1. Cek bentrok: PJ sudah dijadwalkan menerima tamu lain di tanggal & jam sama
        $q_bentrok = mysqli_query($koneksi, "SELECT id_kunjungan FROM kunjungan 
            WHERE id_pj='$id_pj' AND tgl_kunjungan='$tgl' AND waktu_kunjungan='$jam' 
            AND status_kegiatan='dijadwalkan' AND id_kunjungan != '$id_kunjungan'");
        if ($q_bentrok && mysqli_num_rows($q_bentrok) > 0) return true;

        // 2. Cek jadwal ketersediaan: apakah PJ punya jadwal sama sekali?
        $q_punya = mysqli_query($koneksi, "SELECT id_jadwal FROM jadwal_pejabat WHERE id_pj='$id_pj'");
        $punya_jadwal = ($q_punya && mysqli_num_rows($q_punya) > 0);
        if (!$punya_jadwal) return false; // Tidak diatur jadwalnya => dianggap bebas

        // Apakah tersedia pada hari & jam kunjungan ini?
        $q_tersedia = mysqli_query($koneksi, "SELECT id_jadwal FROM jadwal_pejabat 
            WHERE id_pj='$id_pj' AND hari='$hari' AND status_tersedia=1 
            AND '$jam' >= jam_mulai AND '$jam' < jam_selesai");
        $tersedia = ($q_tersedia && mysqli_num_rows($q_tersedia) > 0);

        // Punya jadwal tapi tidak tersedia di slot ini => bermasalah
        return !$tersedia;
    }
}

// ---------------------------------------------------------
// LOGIC PROSES SIMPAN DISPOSISI PIMPINAN (UPDATE STATUS & TTE)
// ---------------------------------------------------------
if (isset($_POST['proses_disposisi'])) {
    $id_kunjungan = mysqli_real_escape_string($koneksi, $_POST['id_kunjungan']);
    $id_ruangan   = mysqli_real_escape_string($koneksi, $_POST['id_ruangan']);
    $id_pj        = mysqli_real_escape_string($koneksi, $_POST['id_pj']);
    $keputusan    = mysqli_real_escape_string($koneksi, $_POST['keputusan']);
    $passphrase   = mysqli_real_escape_string($koneksi, $_POST['passphrase']); // Validasi TTE dari Proposal

    // Konfirmasi override jika PJ di luar jam ketersediaan / bentrok (dikirim dari JS)
    $konfirmasi_override = isset($_POST['konfirmasi_override']) && $_POST['konfirmasi_override'] == '1';

    // Batasan Proposal: Validasi Passphrase sandi frasa rahasia pimpinan untuk mengaktifkan TTE
    if ($passphrase !== "pimpinan123") {
        $pesan_gagal = "Gagal! Passphrase (Sandi Frasa) Keamanan TTE Pimpinan Salah.";
    } elseif ($keputusan == 'setuju' && !$konfirmasi_override && cek_pj_tidak_tersedia($koneksi, $id_pj, $id_kunjungan)) {
        // Validasi sisi server: PJ yang dipilih tidak tersedia pada hari/jam kunjungan
        // dan pimpinan belum mencentang konfirmasi override.
        $pesan_gagal = "Gagal! Penanggung Jawab yang dipilih berada DI LUAR jam ketersediaan atau sudah dijadwalkan menerima tamu lain pada waktu tersebut. Silakan pilih PJ lain, atau centang kotak konfirmasi untuk tetap melanjutkan.";
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

                                                <?php
                                                    // Hitung hari & jam kunjungan ini untuk pencocokan jadwal ketersediaan
                                                    $hari_kunjungan = mysqli_real_escape_string($koneksi, nama_hari_indo($d['tgl_kunjungan']));
                                                    $tgl_k = mysqli_real_escape_string($koneksi, $d['tgl_kunjungan']);
                                                    $jam_k = mysqli_real_escape_string($koneksi, $d['waktu_kunjungan']);
                                                ?>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark">Penanggung Jawab Lapangan (PJ) *</label>
                                                    <small class="d-block text-muted mb-1" style="font-size:10px;">Jadwal kunjungan: <b><?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?></b> pukul <b><?= substr($d['waktu_kunjungan'],0,5); ?></b> (<?= $hari_kunjungan; ?>)</small>
                                                    <select name="id_pj" class="form-select form-select-sm select-pj" required>
                                                        <option value="">-- Pilih Penanggung Jawab --</option>
                                                        <?php
                                                        $pj_query = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab");
                                                        while($p = mysqli_fetch_array($pj_query)){
                                                            // Apakah PJ ini punya jadwal ketersediaan sama sekali?
                                                            $q_punya = mysqli_query($koneksi, "SELECT id_jadwal FROM jadwal_pejabat WHERE id_pj='{$p['id_pj']}'");
                                                            $punya_jadwal = ($q_punya && mysqli_num_rows($q_punya) > 0);

                                                            // Apakah tersedia pada hari & jam kunjungan ini?
                                                            $q_tersedia = mysqli_query($koneksi, "SELECT id_jadwal FROM jadwal_pejabat 
                                                                WHERE id_pj='{$p['id_pj']}' AND hari='$hari_kunjungan' AND status_tersedia=1 
                                                                AND '$jam_k' >= jam_mulai AND '$jam_k' < jam_selesai");
                                                            $tersedia_jadwal = ($q_tersedia && mysqli_num_rows($q_tersedia) > 0);

                                                            // Apakah PJ ini sudah punya kunjungan lain (dijadwalkan) di tanggal & jam sama?
                                                            $q_bentrok = mysqli_query($koneksi, "SELECT id_kunjungan FROM kunjungan 
                                                                WHERE id_pj='{$p['id_pj']}' AND tgl_kunjungan='$tgl_k' AND waktu_kunjungan='$jam_k' 
                                                                AND status_kegiatan='dijadwalkan' AND id_kunjungan != '{$d['id_kunjungan']}'");
                                                            $bentrok_pj = ($q_bentrok && mysqli_num_rows($q_bentrok) > 0);

                                                            $label = htmlspecialchars($p['nama_pj']) . " - " . htmlspecialchars($p['jabatan']);
                                                            $diluar = ($punya_jadwal && !$tersedia_jadwal);
                                                            if ($bentrok_pj) {
                                                                $label .= " \u{26D4} Sudah ada jadwal lain jam ini";
                                                            } elseif ($diluar) {
                                                                $label .= " \u{26A0} Diluar jam ketersediaan";
                                                            } elseif ($punya_jadwal && $tersedia_jadwal) {
                                                                $label .= " \u{2705} Tersedia";
                                                            }
                                                            echo "<option value='{$p['id_pj']}' data-bentrok='" . ($bentrok_pj ? 1 : 0) . "' data-diluar-jadwal='" . ($diluar ? 1 : 0) . "'>{$label}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <div class="warn-pj-disposisi form-text text-danger d-none mt-1" style="font-size:11px;">
                                                        <i class="ti ti-alert-triangle"></i> PJ ini <b>tidak tersedia</b> (di luar jam ketersediaan atau sudah dijadwalkan menerima tamu lain) pada hari/jam kunjungan ini.
                                                    </div>
                                                    <div class="konfirmasi-wrap d-none mt-2 p-2" style="background:#fff3cd;border:1px solid #ffe69c;border-radius:4px;">
                                                        <label style="font-size:11px;font-weight:600;" class="text-dark mb-0">
                                                            <input type="checkbox" name="konfirmasi_override" value="1" class="chk-override"> Saya tetap menunjuk PJ ini meskipun di luar jadwal ketersediaan (ada kesepakatan khusus).
                                                        </label>
                                                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Untuk setiap dropdown PJ di tiap modal disposisi
    document.querySelectorAll('select[name="id_pj"]').forEach(function (sel) {
        var wrap   = sel.closest('.mb-3');
        var warn   = wrap ? wrap.querySelector('.warn-pj-disposisi') : null;
        var konfWrap = wrap ? wrap.querySelector('.konfirmasi-wrap') : null;
        var chk    = wrap ? wrap.querySelector('.chk-override') : null;

        function evaluate() {
            var opt = sel.options[sel.selectedIndex];
            var bermasalah = !!(opt && (opt.dataset.bentrok === '1' || opt.dataset.diluarJadwal === '1'));
            if (warn) warn.classList.toggle('d-none', !bermasalah);
            if (konfWrap) konfWrap.classList.toggle('d-none', !bermasalah);
            if (!bermasalah && chk) chk.checked = false; // reset centang bila ganti ke PJ tersedia
        }
        sel.addEventListener('change', evaluate);
        evaluate();
    });

    // Cegah submit bila PJ bermasalah tapi belum dicentang konfirmasi
    document.querySelectorAll('form').forEach(function (form) {
        var selPj = form.querySelector('select[name="id_pj"]');
        if (!selPj) return;
        form.addEventListener('submit', function (e) {
            var keputusan = form.querySelector('select[name="keputusan"]');
            // Hanya berlaku bila keputusan = setuju
            if (keputusan && keputusan.value !== 'setuju') return;

            var opt = selPj.options[selPj.selectedIndex];
            var bermasalah = !!(opt && (opt.dataset.bentrok === '1' || opt.dataset.diluarJadwal === '1'));
            var chk = form.querySelector('.chk-override');
            if (bermasalah && (!chk || !chk.checked)) {
                e.preventDefault();
                alert('Penanggung Jawab yang dipilih berada DI LUAR jam ketersediaan (atau sudah dijadwalkan menerima tamu lain) pada hari/jam kunjungan ini.\n\nSilakan pilih PJ lain, atau centang kotak konfirmasi jika tetap ingin menunjuk PJ ini.');
            }
        });
    });
});
</script>

<style>
.border-dark-card { border: 2px solid #2e2e2e !important; }
.style-badge { font-size: 8px !important; font-weight: 700 !important; background-color: #dc3545 !important; }
</style>

<?php include 'template/footer.php'; ?>