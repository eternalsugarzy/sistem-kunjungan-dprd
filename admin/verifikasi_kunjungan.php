<?php
// 1. Set Halaman Aktif untuk Sidebar
$page = 'verifikasi';

// 2. Panggil Template Header & Sidebar
// (Ini sudah otomatis menjalankan session_start() dan include koneksi.php)
include 'template/header.php';
include 'template/sidebar.php';

// ---------------------------------------------------------
// LOGIC PHP (PROSES FORM)
// Ditaruh di sini karena koneksi database sudah tersedia dari header.php
// ---------------------------------------------------------

// A. PROSES TERIMA KUNJUNGAN
if (isset($_POST['terima_kunjungan'])) {
    $id_kunjungan = $_POST['id_kunjungan'];
    $id_ruangan = $_POST['id_ruangan'];
    $id_pj = $_POST['id_pj'];
    
    // Update data: isi ruangan, isi PJ, ubah status jadi 'dijadwalkan',
    // dan catat waktu persetujuan (dipakai untuk Laporan Statistik Penggunaan Sistem:
    // rata-rata waktu proses persetujuan)
    $query_update = "UPDATE kunjungan SET 
                     id_ruangan='$id_ruangan', 
                     id_pj='$id_pj', 
                     status_kegiatan='dijadwalkan',
                     waktu_verifikasi=NOW() 
                     WHERE id_kunjungan='$id_kunjungan'";
                     
    if (mysqli_query($koneksi, $query_update)) {
        // Redirect pakai JavaScript karena HTML sudah ter-load
        echo "<script>alert('Kunjungan Berhasil Dijadwalkan!'); window.location='verifikasi_kunjungan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

// Helper: konversi tanggal ke nama hari berbahasa Indonesia
// (dipakai untuk mencocokkan jadwal ketersediaan pejabat)
function nama_hari_indo($tanggal) {
    $map = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at", 'Saturday' => 'Sabtu'
    ];
    return $map[date('l', strtotime($tanggal))] ?? '';
}

// B. PROSES TOLAK KUNJUNGAN
if (isset($_GET['aksi']) && $_GET['aksi'] == 'tolak') {
    $id = $_GET['id'];
    $query_tolak = "UPDATE kunjungan SET status_kegiatan='batal', alasan_pembatalan='Ditolak oleh Admin pada saat Verifikasi', tgl_pembatalan=NOW(), waktu_verifikasi=NOW() WHERE id_kunjungan='$id'";
    
    if (mysqli_query($koneksi, $query_tolak)) {
        echo "<script>alert('Permohonan Ditolak.'); window.location='verifikasi_kunjungan.php';</script>";
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Verifikasi Permohonan</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Verifikasi</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header">
        <h5>Daftar Permohonan Masuk (Pending)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Masuk</th>
                <th>Instansi & Perihal</th>
                <th>Jadwal Diajukan</th>
                <th>Surat</th>
               
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              // HANYA TAMPILKAN YANG STATUSNYA PENDING
              $query = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending' ORDER BY created_at ASC");
              
              while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td>
                    <?= date('d/m/Y', strtotime($d['created_at'])); ?> <br>
                    <span class="badge bg-light-primary text-primary"><?= $d['kode_booking']; ?></span>
                </td>
                <td>
                    <h6 class="mb-0 fw-bold"><?= $d['nama_instansi_tamu']; ?></h6>
                    <small class="text-muted"><?= $d['materi_kunjungan']; ?></small><br>
                    <small><i class="ti ti-users"></i> <?= $d['jumlah_peserta_rencana']; ?> Orang</small>
                </td>
                <td>
                    <div class="fw-bold"><?= date('d F Y', strtotime($d['tgl_kunjungan'])); ?></div>
                    <small class="text-muted">Pukul <?= $d['waktu_kunjungan']; ?></small>
                </td>
                <td>
                    <a href="../uploads/<?= $d['file_surat_permohonan']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-file-text"></i> Lihat
                    </a>
                </td>
               
              </tr>

              <div class="modal fade" id="modalTerima<?= $d['id_kunjungan']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Jadwalkan Kunjungan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <?php
                    // ==============================================================
                    // PENGECEKAN KONFLIK / KETERSEDIAAN (menghindari konflik kunjungan)
                    // ==============================================================
                    $hari_kunjungan = mysqli_real_escape_string($koneksi, nama_hari_indo($d['tgl_kunjungan']));
                    $tgl_k = mysqli_real_escape_string($koneksi, $d['tgl_kunjungan']);
                    $jam_k = mysqli_real_escape_string($koneksi, $d['waktu_kunjungan']);
                ?>
                <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_kunjungan" value="<?= $d['id_kunjungan']; ?>">
                            
                            <div class="alert alert-info py-2">
                                <small>Instansi: <b><?= $d['nama_instansi_tamu']; ?></b> &mdash; Jadwal: <b><?= date('d/m/Y', strtotime($tgl_k)); ?></b> pukul <b><?= substr($jam_k,0,5); ?></b> (<?= $hari_kunjungan; ?>)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tempatkan di Ruangan:</label>
                                <select name="id_ruangan" class="form-select">
                                    <option value="">-- Pilih Ruangan --</option>
                                    <?php
                                    // Ambil Data Ruangan, cek apakah sudah dipakai kunjungan lain pada tanggal & jam yang sama
                                    $q_ruang = mysqli_query($koneksi, "SELECT * FROM ruangan");
                                    while($r = mysqli_fetch_array($q_ruang)){
                                        $q_bentrok_ruang = mysqli_query($koneksi, "SELECT id_kunjungan FROM kunjungan 
                                            WHERE id_ruangan='{$r['id_ruangan']}' AND tgl_kunjungan='$tgl_k' AND waktu_kunjungan='$jam_k' 
                                            AND status_kegiatan='dijadwalkan' AND id_kunjungan != '{$d['id_kunjungan']}'");
                                        $bentrok_ruang = mysqli_num_rows($q_bentrok_ruang) > 0;
                                        $label = htmlspecialchars($r['nama_ruangan']) . " (Kapasitas: {$r['kapasitas']})";
                                        if ($bentrok_ruang) $label .= " \u{26D4} Sudah terpakai jam ini";
                                        echo "<option value='{$r['id_ruangan']}' data-bentrok='" . ($bentrok_ruang ? 1 : 0) . "'>{$label}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="warn-ruangan form-text text-danger d-none mt-1"><i class="ti ti-alert-triangle"></i> Ruangan ini sudah dipakai kunjungan lain pada tanggal & jam yang sama. Pilih ruangan lain atau lanjutkan dengan risiko bentrok.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Penanggung Jawab (Penerima Tamu):</label>
                                <select name="id_pj" class="form-select" required>
                                    <option value="">-- Pilih PJ --</option>
                                    <?php
                                    // Ambil Data PJ, cek jadwal ketersediaan (jadwal_pejabat) & bentrok kunjungan lain
                                    $q_pj = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab");
                                    while($p = mysqli_fetch_array($q_pj)){
                                        // Apakah PJ ini punya jadwal ketersediaan yang diatur sama sekali?
                                        $q_punya_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal_pejabat WHERE id_pj='{$p['id_pj']}'");
                                        $punya_jadwal = mysqli_num_rows($q_punya_jadwal) > 0;

                                        // Apakah tersedia pada hari & jam kunjungan ini?
                                        $q_tersedia = mysqli_query($koneksi, "SELECT * FROM jadwal_pejabat 
                                            WHERE id_pj='{$p['id_pj']}' AND hari='$hari_kunjungan' AND status_tersedia=1 
                                            AND '$jam_k' >= jam_mulai AND '$jam_k' < jam_selesai");
                                        $tersedia_jadwal = mysqli_num_rows($q_tersedia) > 0;

                                        // Apakah PJ ini sudah punya kunjungan lain (dijadwalkan) di tanggal & jam yang sama?
                                        $q_bentrok_pj = mysqli_query($koneksi, "SELECT id_kunjungan FROM kunjungan 
                                            WHERE id_pj='{$p['id_pj']}' AND tgl_kunjungan='$tgl_k' AND waktu_kunjungan='$jam_k' 
                                            AND status_kegiatan='dijadwalkan' AND id_kunjungan != '{$d['id_kunjungan']}'");
                                        $bentrok_pj = mysqli_num_rows($q_bentrok_pj) > 0;

                                        $label = htmlspecialchars($p['nama_pj']) . " - " . htmlspecialchars($p['jabatan']);
                                        if ($bentrok_pj) {
                                            $label .= " \u{26D4} Sudah ada jadwal lain jam ini";
                                        } elseif ($punya_jadwal && !$tersedia_jadwal) {
                                            $label .= " \u{26A0} Diluar jam ketersediaan ($hari_kunjungan)";
                                        }
                                        echo "<option value='{$p['id_pj']}' data-bentrok='" . ($bentrok_pj ? 1 : 0) . "' data-diluar-jadwal='" . (($punya_jadwal && !$tersedia_jadwal) ? 1 : 0) . "'>{$label}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="warn-pj-bentrok form-text text-danger d-none mt-1"><i class="ti ti-alert-triangle"></i> Pejabat ini sudah dijadwalkan menerima tamu lain pada tanggal & jam yang sama. Disarankan pilih PJ lain untuk menghindari konflik kunjungan.</div>
                                <div class="warn-pj-jadwal form-text text-warning d-none mt-1"><i class="ti ti-clock-exclamation"></i> Berdasarkan Jadwal Ketersediaan Pejabat, PJ ini biasanya <u>tidak bersedia</u> menerima tamu pada hari/jam tersebut. Anda tetap bisa melanjutkan jika ada kesepakatan khusus.</div>
                                <small class="text-muted d-block mt-1">Atur jadwal ketersediaan pejabat di menu <a href="master_jadwal.php" target="_blank">Data Master &raquo; Jadwal Ketersediaan Pejabat</a>.</small>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="terima_kunjungan" class="btn btn-primary">Simpan & Setujui</button>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
              <?php } ?>
              
              <?php if(mysqli_num_rows($query) == 0): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada permohonan baru.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tampilkan/sembunyikan peringatan bentrok ruangan
    document.querySelectorAll('select[name="id_ruangan"]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var opt = sel.options[sel.selectedIndex];
            var warn = sel.parentElement.querySelector('.warn-ruangan');
            if (warn) warn.classList.toggle('d-none', !(opt && opt.dataset.bentrok === '1'));
        });
    });

    // Tampilkan/sembunyikan peringatan bentrok & diluar jadwal PJ
    document.querySelectorAll('select[name="id_pj"]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var opt = sel.options[sel.selectedIndex];
            var wrap = sel.parentElement;
            var warnBentrok = wrap.querySelector('.warn-pj-bentrok');
            var warnJadwal = wrap.querySelector('.warn-pj-jadwal');
            var bentrok = !!(opt && opt.dataset.bentrok === '1');
            var diluarJadwal = !!(opt && opt.dataset.diluarJadwal === '1');
            if (warnBentrok) warnBentrok.classList.toggle('d-none', !bentrok);
            if (warnJadwal) warnJadwal.classList.toggle('d-none', !(diluarJadwal && !bentrok));
        });
    });

    // Konfirmasi tambahan sebelum submit jika terdeteksi konflik
    document.querySelectorAll('form').forEach(function (form) {
        if (!form.querySelector('select[name="id_pj"]')) return;
        form.addEventListener('submit', function (e) {
            var selPj = form.querySelector('select[name="id_pj"]');
            var selRuang = form.querySelector('select[name="id_ruangan"]');
            var optPj = selPj ? selPj.options[selPj.selectedIndex] : null;
            var optRuang = selRuang ? selRuang.options[selRuang.selectedIndex] : null;
            var adaBentrokPj = !!(optPj && optPj.dataset.bentrok === '1');
            var adaBentrokRuang = !!(optRuang && optRuang.dataset.bentrok === '1');
            if (adaBentrokPj || adaBentrokRuang) {
                if (!confirm('Sistem mendeteksi potensi BENTROK jadwal (PJ dan/atau ruangan sudah terpakai pada jam yang sama). Tetap lanjutkan menyetujui kunjungan ini?')) {
                    e.preventDefault();
                }
            }
        });
    });
});
</script>

<?php
// 3. Panggil Template Footer
include 'template/footer.php';
?>