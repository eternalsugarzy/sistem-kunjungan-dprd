<?php
// --- TAMBAHAN LOGIC NOTIFIKASI ---
// Hitung jumlah data pending langsung di sini agar muncul di semua halaman
// Kita gunakan include_once untuk memastikan koneksi ada, meski biasanya header sudah membawanya.
if (isset($koneksi)) {
    $q_notif = mysqli_query($koneksi, "SELECT * FROM kunjungan WHERE status_kegiatan='pending'");
    $jml_notif = mysqli_num_rows($q_notif);
} else {
    $jml_notif = 0;
}
?>

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
        <li class="pc-item <?= ($page == 'dashboard') ? 'active' : '' ?>">
          <a href="dashboard.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Manajemen</label>
          <i class="ti ti-news"></i>
        </li>
        <li class="pc-item <?= ($page == 'verifikasi') ? 'active' : '' ?>">
          <a href="verifikasi_kunjungan.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-file-check"></i></span>
            <span class="pc-mtext">Verifikasi Masuk</span>
            
            <?php if($jml_notif > 0): ?>
                <span class="pc-badge badge bg-danger rounded-pill ms-2"><?= $jml_notif; ?></span>
            <?php endif; ?>
            
          </a>
        </li>
        <li class="pc-item <?= ($page == 'data_kunjungan') ? 'active' : '' ?>">
          <a href="data_kunjungan.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-list"></i></span>
            <span class="pc-mtext">Data Kunjungan</span>
          </a>
        </li>
         <li class="pc-item <?= ($page == 'buku_tamu') ? 'active' : '' ?>">
          <a href="buku_tamu.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-book"></i></span>
            <span class="pc-mtext">Buku Tamu</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Data Master</label>
          <i class="ti ti-apps"></i>
        </li>
        <li class="pc-item pc-hasmenu <?= ($page == 'master') ? 'active' : '' ?>">
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
        <li class="pc-item <?= ($page == 'laporan') ? 'active' : '' ?>">
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
          <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
            <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar" />
            <span><i class="ti ti-settings"></i></span>
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header">
              <h4>
                Halo, <span class="small text-muted"><?= $_SESSION['nama'] ?? 'Admin'; ?></span>
              </h4>
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