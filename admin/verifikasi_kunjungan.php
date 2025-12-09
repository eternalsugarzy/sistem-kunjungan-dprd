<?php
session_start();
// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';

// --- LOGIC 1: PROSES TERIMA KUNJUNGAN ---
if (isset($_POST['terima_kunjungan'])) {
    $id_kunjungan = $_POST['id_kunjungan'];
    $id_ruangan = $_POST['id_ruangan'];
    $id_pj = $_POST['id_pj'];
    
    // Update data: isi ruangan, isi PJ, dan ubah status jadi 'dijadwalkan'
    $query_update = "UPDATE kunjungan SET 
                     id_ruangan='$id_ruangan', 
                     id_pj='$id_pj', 
                     status_kegiatan='dijadwalkan' 
                     WHERE id_kunjungan='$id_kunjungan'";
                     
    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('Kunjungan Berhasil Dijadwalkan!'); window.location='verifikasi_kunjungan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- LOGIC 2: PROSES TOLAK KUNJUNGAN ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'tolak') {
    $id = $_GET['id'];
    $query_tolak = "UPDATE kunjungan SET status_kegiatan='batal' WHERE id_kunjungan='$id'";
    
    if (mysqli_query($koneksi, $query_tolak)) {
        echo "<script>alert('Permohonan Ditolak.'); window.location='verifikasi_kunjungan.php';</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Verifikasi Kunjungan | Admin DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
</head>

<body>
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header">
          <a href="dashboard.php" class="b-brand text-primary">
            <img src="../assets/images/logo-dark.svg" alt="" class="logo logo-lg" style="height: 30px;" />
          </a>
        </div>
        <div class="navbar-content">
          <ul class="pc-navbar">
            <li class="pc-item"><a href="dashboard.php" class="pc-link"><span class="pc-micon"><i class="ti ti-dashboard"></i></span><span class="pc-mtext">Dashboard</span></a></li>
            <li class="pc-item active"><a href="verifikasi_kunjungan.php" class="pc-link"><span class="pc-micon"><i class="ti ti-file-check"></i></span><span class="pc-mtext">Verifikasi Masuk</span></a></li>
            <li class="pc-item"><a href="data_kunjungan.php" class="pc-link"><span class="pc-micon"><i class="ti ti-list"></i></span><span class="pc-mtext">Data Kunjungan</span></a></li>
             <li class="pc-item"><a href="laporan.php" class="pc-link"><span class="pc-micon"><i class="ti ti-printer"></i></span><span class="pc-mtext">Laporan</span></a></li>
          </ul>
        </div>
      </div>
    </nav>
    <header class="pc-header">
      <div class="header-wrapper">
        <div class="ms-auto">
          <ul class="list-unstyled">
             <li class="dropdown pc-h-item header-user-profile">
              <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
                <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar" />
                <span><i class="ti ti-settings"></i></span>
              </a>
              <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                <div class="dropdown-header">
                  <a href="logout.php" class="dropdown-item"><i class="ti ti-logout"></i> <span>Logout</span></a>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </header>
    <div class="pc-container">
      <div class="pc-content">
        
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
                        <th class="text-center">Aksi</th>
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
                        <td class="text-center">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTerima<?= $d['id_kunjungan']; ?>">
                                <i class="ti ti-check"></i> Proses
                            </button>
                            
                            <a href="verifikasi_kunjungan.php?aksi=tolak&id=<?= $d['id_kunjungan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menolak permohonan ini?')">
                                <i class="ti ti-x"></i> Tolak
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
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="id_kunjungan" value="<?= $d['id_kunjungan']; ?>">
                                    
                                    <div class="alert alert-info py-2">
                                        <small>Instansi: <b><?= $d['nama_instansi_tamu']; ?></b></small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tempatkan di Ruangan:</label>
                                        <select name="id_ruangan" class="form-select" required>
                                            <option value="">-- Pilih Ruangan --</option>
                                            <?php
                                            // Ambil Data Ruangan dari Database
                                            $q_ruang = mysqli_query($koneksi, "SELECT * FROM ruangan");
                                            while($r = mysqli_fetch_array($q_ruang)){
                                                echo "<option value='$r[id_ruangan]'>$r[nama_ruangan] (Kapasitas: $r[kapasitas])</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Penanggung Jawab (Penerima Tamu):</label>
                                        <select name="id_pj" class="form-select" required>
                                            <option value="">-- Pilih PJ --</option>
                                            <?php
                                            // Ambil Data PJ dari Database
                                            $q_pj = mysqli_query($koneksi, "SELECT * FROM penanggung_jawab");
                                            while($p = mysqli_fetch_array($q_pj)){
                                                echo "<option value='$p[id_pj]'>$p[nama_pj] - $p[jabatan]</option>";
                                            }
                                            ?>
                                        </select>
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

      </div>
    </div>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/icon/custom-font.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
</body>
</html>