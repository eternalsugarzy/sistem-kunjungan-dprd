<?php
// ==========================================
// PENGATURAN DEBUG ERROR (WAJIB PALING ATAS)
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'master';
include 'template/header.php';
include 'template/sidebar.php';

// PAKSA SEMBUNYIKAN LOADER CSS
echo '<style>.loader-bg, .preloader, #pc-loader, .pc-loader { display: none !important; visibility: hidden !important; opacity: 0 !important; }</style>';

// Daftar hari yang dipakai konsisten di seluruh sistem
$daftar_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];

// ---------------------------------------------------------
// LOGIC PHP (CRUD JADWAL KETERSEDIAAN PEJABAT)
// ---------------------------------------------------------

// 1. ACTION: TAMBAH JADWAL
if (isset($_POST['tambah'])) {
    $id_pj        = mysqli_real_escape_string($koneksi, $_POST['id_pj']);
    $hari         = mysqli_real_escape_string($koneksi, $_POST['hari']);
    $jam_mulai    = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
    $jam_selesai  = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
    $status       = isset($_POST['status_tersedia']) ? 1 : 0;

    if ($jam_selesai <= $jam_mulai) {
        echo "<script>alert('Jam selesai harus lebih besar dari jam mulai!'); window.location='master_jadwal.php';</script>";
        exit;
    }

    // Cegah duplikasi: pejabat yang sama pada hari & jam yang saling tumpang tindih
    $q_cek = mysqli_query($koneksi, "SELECT * FROM jadwal_pejabat 
        WHERE id_pj='$id_pj' AND hari='$hari' 
        AND ('$jam_mulai' < jam_selesai AND '$jam_selesai' > jam_mulai)");
    if (mysqli_num_rows($q_cek) > 0) {
        echo "<script>alert('Gagal: Jadwal pada hari & jam tersebut sudah ada / tumpang tindih untuk pejabat ini.'); window.location='master_jadwal.php';</script>";
        exit;
    }

    $query = "INSERT INTO jadwal_pejabat (id_pj, hari, jam_mulai, jam_selesai, status_tersedia) 
              VALUES ('$id_pj', '$hari', '$jam_mulai', '$jam_selesai', '$status')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Jadwal Berhasil Ditambah!'); window.location='master_jadwal.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 2. ACTION: EDIT JADWAL
if (isset($_POST['edit'])) {
    $id           = mysqli_real_escape_string($koneksi, $_POST['id_jadwal']);
    $id_pj        = mysqli_real_escape_string($koneksi, $_POST['id_pj']);
    $hari         = mysqli_real_escape_string($koneksi, $_POST['hari']);
    $jam_mulai    = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
    $jam_selesai  = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
    $status       = isset($_POST['status_tersedia']) ? 1 : 0;

    if ($jam_selesai <= $jam_mulai) {
        echo "<script>alert('Jam selesai harus lebih besar dari jam mulai!'); window.location='master_jadwal.php';</script>";
        exit;
    }

    $query = "UPDATE jadwal_pejabat SET 
                id_pj='$id_pj', hari='$hari', jam_mulai='$jam_mulai', 
                jam_selesai='$jam_selesai', status_tersedia='$status' 
              WHERE id_jadwal='$id'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Jadwal Berhasil Diupdate!'); window.location='master_jadwal.php';</script>";
    } else {
        echo "<script>alert('Gagal Update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 3. ACTION: HAPUS JADWAL
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    if (mysqli_query($koneksi, "DELETE FROM jadwal_pejabat WHERE id_jadwal='$id'")) {
        echo "<script>alert('Jadwal Dihapus!'); window.location='master_jadwal.php';</script>";
    }
}

// 4. ACTION: TOGGLE CEPAT STATUS TERSEDIA / TIDAK
if (isset($_GET['aksi']) && $_GET['aksi'] == 'toggle') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    mysqli_query($koneksi, "UPDATE jadwal_pejabat SET status_tersedia = 1 - status_tersedia WHERE id_jadwal='$id'");
    header("location: master_jadwal.php");
    exit;
}

// Ambil seluruh data PJ untuk dropdown & pengelompokan
$list_pj = [];
$q_pj_all = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab ORDER BY nama_pj ASC");
while ($p = mysqli_fetch_assoc($q_pj_all)) {
    $list_pj[] = $p;
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-5">Jadwal Ketersediaan Pejabat</h5>
        </div>
        <ul class="breadcrumb mb-3" style="background:transparent; padding:0; font-size:11px;">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item text-muted">Data Master</li>
          <li class="breadcrumb-item text-muted">Jadwal Pejabat</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="alert alert-info d-flex align-items-start shadow-sm">
      <i class="ti ti-info-circle f-20 me-2 mt-1"></i>
      <div>
        <b>Kegunaan:</b> Atur hari & jam pejabat/kepala bagian bersedia menerima tamu.
        Jadwal ini otomatis dipakai sebagai pengingat saat admin memverifikasi dan menjadwalkan
        kunjungan (menu <b>Verifikasi Masuk</b>), agar tidak terjadi konflik/bentrok penunjukan
        Penanggung Jawab pada jam yang sama.
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card shadow-sm border-dark">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">Daftar Jadwal Ketersediaan</h5>
        <button class="btn btn-light text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
            <i class="ti ti-plus me-1"></i> Tambah Jadwal
        </button>
      </div>
      <div class="card-body">
        <?php if (empty($list_pj)): ?>
          <div class="alert alert-warning mb-0">
            Belum ada data Penanggung Jawab. Silakan tambahkan pejabat terlebih dahulu di menu
            <a href="master_pj.php"><b>Data Master &raquo; Penanggung Jawab</b></a>.
          </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th width="22%">Pejabat / PJ</th>
                <th width="13%">Hari</th>
                <th width="18%">Jam Bersedia Menerima Tamu</th>
                <th width="12%" class="text-center">Status</th>
                <th class="text-center" width="10%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              // Bangun daftar urutan hari secara aman (menghindari masalah apostrof pada "Jum'at")
              $field_hari = "'" . implode("','", array_map(function($h) use ($koneksi) {
                  return mysqli_real_escape_string($koneksi, $h);
              }, $daftar_hari)) . "'";
              $query = mysqli_query($koneksi, "SELECT j.*, p.nama_pj, p.jabatan 
                  FROM jadwal_pejabat j 
                  LEFT JOIN penanggung_jawab p ON j.id_pj = p.id_pj 
                  ORDER BY p.nama_pj ASC, FIELD(j.hari,$field_hari), j.jam_mulai ASC");
              if (mysqli_num_rows($query) > 0) {
                  while ($d = mysqli_fetch_array($query)) {
              ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td>
                    <b><?= htmlspecialchars($d['nama_pj'] ?? '-'); ?></b><br>
                    <small class="text-muted"><?= htmlspecialchars($d['jabatan'] ?? '-'); ?></small>
                </td>
                <td><?= htmlspecialchars($d['hari']); ?></td>
                <td>
                    <span class="font-monospace"><?= substr($d['jam_mulai'],0,5); ?> - <?= substr($d['jam_selesai'],0,5); ?> WITA</span>
                </td>
                <td class="text-center">
                    <a href="master_jadwal.php?aksi=toggle&id=<?= $d['id_jadwal']; ?>" class="text-decoration-none" title="Klik untuk ubah status">
                    <?php if ($d['status_tersedia'] == 1): ?>
                        <span class="badge bg-success">Tersedia</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Tidak Tersedia</span>
                    <?php endif; ?>
                    </a>
                </td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_jadwal']; ?>">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <a href="master_jadwal.php?aksi=hapus&id=<?= $d['id_jadwal']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus jadwal ini?')">
                            <i class="ti ti-trash"></i>
                        </a>
                    </div>
                </td>
              </tr>

              <!-- MODAL EDIT PER-BARIS -->
              <div class="modal fade" id="modalEdit<?= $d['id_jadwal']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                      <h5 class="modal-title fw-bold text-white">Edit Jadwal</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_jadwal" value="<?= $d['id_jadwal']; ?>">
                            <div class="mb-2">
                                <label class="form-label mb-1 fw-bold">Pejabat / PJ *</label>
                                <select name="id_pj" class="form-select form-select-sm border-dark" required>
                                    <?php foreach ($list_pj as $p): ?>
                                        <option value="<?= $p['id_pj']; ?>" <?= ($p['id_pj'] == $d['id_pj']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($p['nama_pj']); ?> - <?= htmlspecialchars($p['jabatan']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1 fw-bold">Hari *</label>
                                <select name="hari" class="form-select form-select-sm border-dark" required>
                                    <?php foreach ($daftar_hari as $h): ?>
                                        <option value="<?= $h; ?>" <?= ($h == $d['hari']) ? 'selected' : ''; ?>><?= $h; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="form-label mb-1 fw-bold">Jam Mulai *</label>
                                    <input type="time" name="jam_mulai" class="form-control form-control-sm border-dark" value="<?= substr($d['jam_mulai'],0,5); ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1 fw-bold">Jam Selesai *</label>
                                    <input type="time" name="jam_selesai" class="form-control form-control-sm border-dark" value="<?= substr($d['jam_selesai'],0,5); ?>" required>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status_tersedia" <?= ($d['status_tersedia'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label">Bersedia menerima tamu pada slot ini</label>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-dark btn-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
              <?php
                  }
              } else {
                  echo '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada jadwal ketersediaan yang diatur.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold text-white">Tambah Jadwal Ketersediaan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-2">
                  <label class="form-label mb-1 fw-bold">Pejabat / PJ *</label>
                  <select name="id_pj" class="form-select form-select-sm border-dark" required>
                      <option value="">-- Pilih Pejabat --</option>
                      <?php foreach ($list_pj as $p): ?>
                          <option value="<?= $p['id_pj']; ?>"><?= htmlspecialchars($p['nama_pj']); ?> - <?= htmlspecialchars($p['jabatan']); ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="mb-2">
                  <label class="form-label mb-1 fw-bold">Hari *</label>
                  <select name="hari" class="form-select form-select-sm border-dark" required>
                      <option value="">-- Pilih Hari --</option>
                      <?php foreach ($daftar_hari as $h): ?>
                          <option value="<?= $h; ?>"><?= $h; ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="row mb-2">
                  <div class="col-6">
                      <label class="form-label mb-1 fw-bold">Jam Mulai *</label>
                      <input type="time" name="jam_mulai" class="form-control form-control-sm border-dark" required>
                  </div>
                  <div class="col-6">
                      <label class="form-label mb-1 fw-bold">Jam Selesai *</label>
                      <input type="time" name="jam_selesai" class="form-control form-control-sm border-dark" required>
                  </div>
              </div>
              <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="status_tersedia" checked>
                  <label class="form-check-label">Bersedia menerima tamu pada slot ini</label>
              </div>
          </div>
          <div class="modal-footer bg-light">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="tambah" class="btn btn-dark btn-sm">Simpan Jadwal</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>