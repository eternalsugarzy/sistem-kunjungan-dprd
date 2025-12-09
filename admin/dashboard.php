<?php
session_start();
// 1. Cek Keamanan: Jika belum login, tendang ke login.php
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';

// 2. Hitung Data untuk Widget Dashboard
// Hitung Kunjungan Pending
$q_pending = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending'");
$jml_pending = mysqli_num_rows($q_pending);

// Hitung Kunjungan Hari Ini
$tgl_ini = date('Y-m-d');
$q_today = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE tgl_kunjungan='$tgl_ini'");
$jml_today = mysqli_num_rows($q_today);

// Hitung Total Kunjungan Selesai
$q_selesai = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='selesai'");
$jml_selesai = mysqli_num_rows($q_selesai);
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Dashboard Admin | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
  </head>
  <body>
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header">
          <a href="dashboard.php" class="b-brand text-primary">
            <img src="../assets/images/logo-dark.svg" alt="" class="logo logo-lg" style="height: 30px;" />
          </a>
        </div>
        <div class="navbar-content">
          <ul class="pc-navbar">
            
            <li class="pc-item pc-caption">
              <label>Home</label>
              <i class="ti ti-dashboard"></i>
            </li>
            <li class="pc-item active">
              <a href="dashboard.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                <span class="pc-mtext">Dashboard</span>
              </a>
            </li>

            <li class="pc-item pc-caption">
              <label>Manajemen Kunjungan</label>
              <i class="ti ti-news"></i>
            </li>
            <li class="pc-item">
              <a href="verifikasi_kunjungan.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-file-check"></i></span>
                <span class="pc-mtext">Verifikasi Masuk</span>
                <?php if($jml_pending > 0): ?>
                    <span class="pc-badge badge bg-danger rounded-pill"><?= $jml_pending ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li class="pc-item">
              <a href="data_kunjungan.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-list"></i></span>
                <span class="pc-mtext">Data Kunjungan</span>
              </a>
            </li>
             <li class="pc-item">
              <a href="buku_tamu.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-book"></i></span>
                <span class="pc-mtext">Buku Tamu (Hari H)</span>
              </a>
            </li>

            <li class="pc-item pc-caption">
              <label>Data Master</label>
              <i class="ti ti-apps"></i>
            </li>
            <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                <span class="pc-mtext">Pengaturan Data</span>
                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
              </a>
              <ul class="pc-submenu">
                <li class="pc-item"><a class="pc-link" href="master_ruangan.php">Data Ruangan</a></li>
                <li class="pc-item"><a class="pc-link" href="master_pj.php">Penanggung Jawab</a></li>
                <li class="pc-item"><a class="pc-link" href="manajemen_user.php">Manajemen Admin</a></li>
              </ul>
            </li>

            <li class="pc-item pc-caption">
                <label>Output</label>
                <i class="ti ti-printer"></i>
            </li>
            <li class="pc-item">
                <a href="laporan.php" class="pc-link">
                    <span class="pc-micon"><i class="ti ti-file-analytics"></i></span>
                    <span class="pc-mtext">Cetak Laporan</span>
                </a>
            </li>

          </ul>
        </div>
      </div>
    </nav>
    <header class="pc-header">
      <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
          <ul class="list-unstyled">
            <li class="pc-h-item header-mobile-collapse">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="pc-h-item pc-sidebar-popup">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
          </ul>
        </div>
        <div class="ms-auto">
          <ul class="list-unstyled">
            <li class="dropdown pc-h-item header-user-profile">
              <a
                class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0"
                data-bs-toggle="dropdown"
                href="#"
                role="button"
                aria-haspopup="false"
                aria-expanded="false"
              >
                <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar" />
                <span>
                  <i class="ti ti-settings"></i>
                </span>
              </a>
              <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                <div class="dropdown-header">
                  <h4>
                    Halo, 
                    <span class="small text-muted">
                        <?= $_SESSION['nama']; ?> </span>
                  </h4>
                  <p class="text-muted"><?= ucfirst($_SESSION['level']); ?></p>
                  <hr />
                  <a href="logout.php" class="dropdown-item">
                    <i class="ti ti-logout"></i>
                    <span>Logout</span>
                  </a>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </header>
    <div class="pc-container">
      <div class="pc-content">
        
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-primary d-flex align-items-center" role="alert">
                    <i class="ti ti-info-circle me-2 f-20"></i>
                    <div>
                        Selamat Datang di <strong>Sistem Informasi Pendaftaran Kunjungan DPRD</strong>.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
          <div class="col-xl-4 col-md-6">
            <div class="card bg-warning-dark dashnum-card text-white overflow-hidden">
              <span class="round small"></span>
              <span class="round big"></span>
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <div class="avtar avtar-lg">
                      <i class="text-white ti ti-clock"></i>
                    </div>
                  </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">
                  <?= $jml_pending; ?>
                </span>
                <p class="mb-0 opacity-50">Menunggu Verifikasi</p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-md-6">
            <div class="card bg-primary-dark dashnum-card text-white overflow-hidden">
              <span class="round small"></span>
              <span class="round big"></span>
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <div class="avtar avtar-lg">
                      <i class="text-white ti ti-calendar"></i>
                    </div>
                  </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">
                  <?= $jml_today; ?>
                </span>
                <p class="mb-0 opacity-50">Kunjungan Hari Ini</p>
              </div>
            </div>
          </div>
          
          <div class="col-xl-4 col-md-12">
            <div class="card bg-success-dark dashnum-card text-white overflow-hidden">
              <span class="round small"></span>
              <span class="round big"></span>
              <div class="card-body">
                <div class="row">
                  <div class="col">
                    <div class="avtar avtar-lg">
                      <i class="text-white ti ti-checks"></i>
                    </div>
                  </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">
                  <?= $jml_selesai; ?>
                </span>
                <p class="mb-0 opacity-50">Total Kunjungan Selesai</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xl-12 col-md-12">
            <div class="card">
              <div class="card-header">
                <h5>5 Permohonan Kunjungan Terbaru</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Instansi</th>
                                <th>Tanggal Kunjungan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_terbaru = mysqli_query($koneksi, "SELECT * FROM kunjungan ORDER BY created_at DESC LIMIT 5");
                            while($d = mysqli_fetch_array($q_terbaru)){
                            ?>
                            <tr>
                                <td>
                                    <h6 class="mb-0"><?= $d['nama_instansi_tamu']; ?></h6>
                                    <small class="text-muted"><?= $d['no_register'] ?? '-'; ?></small>
                                </td>
                                <td><?= date('d-m-Y', strtotime($d['tgl_kunjungan'])); ?></td>
                                <td>
                                    <?php 
                                    if($d['status_kegiatan'] == 'pending'){
                                        echo '<span class="badge bg-warning">Pending</span>';
                                    } elseif($d['status_kegiatan'] == 'dijadwalkan'){
                                        echo '<span class="badge bg-primary">Dijadwalkan</span>';
                                    } elseif($d['status_kegiatan'] == 'selesai'){
                                        echo '<span class="badge bg-success">Selesai</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Batal</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if(mysqli_num_rows($q_terbaru) == 0): ?>
                                <tr><td colspan="3" class="text-center">Belum ada data</td></tr>
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
    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid">
        <div class="row">
          <div class="col-sm-6 my-1">
            <p class="m-0">
              Sistem Kunjungan DPRD &#9829; PKL Project
            </p>
          </div>
        </div>
      </div>
    </footer>

    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/icon/custom-font.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
  </body>
</html> 