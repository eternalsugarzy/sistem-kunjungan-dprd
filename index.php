<!doctype html>
<html lang="id">
  <head>
    <title>Portal Kunjungan | DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/fonts/feather.css" />
    <link rel="stylesheet" href="assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="assets/fonts/material.css" />
    
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />
    <link rel="stylesheet" href="assets/css/landing.css" />
  </head>

  <body class="landing-page">
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>

    <nav class="navbar navbar-expand-md navbar-light default fixed-top">
      <div class="container">
        <a class="navbar-brand text-dark" href="#">
          <img src="assets/images/logo.png" alt="logo" class="img-fluid" style="height:70px" /> 
          <span class="fw-bold ms-2">SIM-KUNJUNGAN</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link text-dark active" href="#home">Beranda</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="#alur">Alur Kunjungan</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-secondary ms-2" href="admin/login.php">Login Petugas</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <header id="home" style="padding-top: 150px; padding-bottom: 100px;">
      <div class="container">
        <div class="row align-items-center justify-content-center text-center">
          <div class="col-lg-10">
            <h1 class="mt-sm-3 mb-sm-4 f-w-600 wow fadeInUp" data-wow-delay="0.2s">
              Sistem Informasi Kunjungan Kerja<br>
              <span class="text-primary">DPRD & Sekretariat</span>
            </h1>
            <h4 class="mb-sm-4 text-muted wow fadeInUp" data-wow-delay="0.4s">
              Permudah proses pengajuan kunjungan kerja, studi tiru, dan konsultasi secara digital, transparan, dan terjadwal.
            </h4>
            
            <div class="my-3 my-xl-5 wow fadeInUp" data-wow-delay="0.6s">
              <a href="form_pengajuan.php" class="btn btn-primary btn-lg me-2">
                 <i class="ti ti-file-text"></i> Ajukan Permohonan
              </a>
              <a href="cek_status.php" class="btn btn-outline-secondary btn-lg">
                 <i class="ti ti-search"></i> Cek Status Tiket
              </a>
            </div>

          </div>
        </div>
      </div>
    </header>
    <section id="alur" class="py-5 bg-light">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-md-8">
                <h2 class="fw-bold">Alur Pendaftaran Kunjungan</h2>
                <p class="text-muted">Ikuti langkah mudah berikut untuk melakukan kunjungan kerja.</p>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="avtar avtar-xl bg-light-primary text-primary mb-3">
                            <i class="ti ti-forms f-30"></i>
                        </div>
                        <h5 class="fw-bold">1. Isi Formulir</h5>
                        <p class="text-muted small">Lengkapi data instansi, rencana kunjungan, dan upload surat permohonan resmi.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="avtar avtar-xl bg-light-warning text-warning mb-3">
                            <i class="ti ti-ticket f-30"></i>
                        </div>
                        <h5 class="fw-bold">2. Dapat Kode Tiket</h5>
                        <p class="text-muted small">Sistem akan memberikan <b>Kode Booking</b> unik. Simpan kode ini untuk pengecekan.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="avtar avtar-xl bg-light-info text-info mb-3">
                            <i class="ti ti-user-check f-30"></i>
                        </div>
                        <h5 class="fw-bold">3. Verifikasi Admin</h5>
                        <p class="text-muted small">Admin DPRD akan memverifikasi jadwal dan ketersediaan ruangan.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="avtar avtar-xl bg-light-success text-success mb-3">
                            <i class="ti ti-check f-30"></i>
                        </div>
                        <h5 class="fw-bold">4. Kunjungan Disetujui</h5>
                        <p class="text-muted small">Cek status secara berkala. Jika disetujui, silakan datang sesuai jadwal.</p>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </section>
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">Sistem Informasi Kunjungan Kerja DPRD &copy; 2025</p>
        </div>
    </footer>

    <script src="assets/js/plugins/popper.min.js"></script>
    <script src="assets/js/plugins/simplebar.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/fonts/custom-font.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/plugins/feather.min.js"></script>
    
    <script>
      layout_change('light');
      font_change('Roboto');
      change_box_container('false');
      layout_caption_change('true');
      layout_rtl_change('false');
      preset_change('preset-1');

      let ost = 0;
      document.addEventListener('scroll', function () {
        let cOst = document.documentElement.scrollTop;
        if (cOst == 0) {
          document.querySelector('.navbar').classList.add('top-nav-collapse');
        } else if (cOst > ost) {
          document.querySelector('.navbar').classList.add('top-nav-collapse');
          document.querySelector('.navbar').classList.remove('default');
        } else {
          document.querySelector('.navbar').classList.add('default');
          document.querySelector('.navbar').classList.remove('top-nav-collapse');
        }
        ost = cOst;
      });
    </script>
  </body>
</html>