<!doctype html>
<html lang="id">
  <head>
    <title>Portal Smart Guest | SIM-KUNJUNGAN DPRD</title>
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

    <nav class="navbar navbar-expand-md navbar-light default fixed-top shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark" href="#">
          <img src="assets/images/logo.png" alt="logo" class="img-fluid" style="height:40px" /> 
          <span class="fw-bold ms-2">SIM-KUNJUNGAN</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item">
              <a class="nav-link text-dark active fw-bold" href="#home">Beranda</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark fw-bold" href="#alur">Alur E-Ticketing</a>
            </li>
            <li class="nav-item ms-md-3 mt-2 mt-md-0">
                <a class="btn btn-dark" href="admin/login.php"><i class="ti ti-lock me-1"></i> Login Petugas</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <header id="home" style="padding-top: 150px; padding-bottom: 100px; background-color: #f8f9fa;">
      <div class="container">
        <div class="row align-items-center justify-content-center text-center">
          <div class="col-lg-10">
            <h1 class="mt-sm-3 mb-sm-4 f-w-700 wow fadeInUp" data-wow-delay="0.2s" style="line-height: 1.4;">
              Manajemen Kunjungan Cerdas<br>
              <span class="text-dark bg-warning px-3 py-1 rounded d-inline-block mt-2">[ Smart Guest ] Berbasis E-Ticketing</span>
            </h1>
            <h5 class="mb-sm-4 text-muted wow fadeInUp fw-normal" data-wow-delay="0.4s">
              Permudah pengajuan kunjungan Anda ke DPRD tanpa perlu membuat akun. <br>Dapatkan E-Ticket QR Code untuk proses Check-in dan Check-out yang lebih cepat.
            </h5>
            
            <div class="my-4 my-xl-5 wow fadeInUp d-flex justify-content-center gap-3 flex-wrap" data-wow-delay="0.6s">
              <a href="form_pengajuan.php" class="btn btn-dark btn-lg">
                 <i class="ti ti-file-text me-2"></i> Ajukan Kunjungan
              </a>
              <a href="cek_status.php" class="btn btn-outline-dark btn-lg bg-white">
                 <i class="ti ti-search me-2"></i> Cek Status & E-Ticket
              </a>
            </div>

          </div>
        </div>
      </div>
    </header>

    <section id="alur" class="py-5 bg-white">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark border-bottom border-dark border-3 pb-2 d-inline-block">Alur Smart Guest</h2>
                <p class="text-muted mt-3">Panduan proses kunjungan dengan sistem E-Ticketing QR Code.</p>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body p-4">
                        <div class="avtar avtar-xl bg-dark text-white mb-3 mx-auto">
                            <i class="ti ti-forms f-30"></i>
                        </div>
                        <h5 class="fw-bold">1. Isi Formulir</h5>
                        <p class="text-muted small">Lengkapi data instansi dan email aktif. Sistem akan memberikan <b>Kode Booking</b> sebagai nomor pelacakan.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body p-4">
                        <div class="avtar avtar-xl bg-dark text-white mb-3 mx-auto">
                            <i class="ti ti-user-check f-30"></i>
                        </div>
                        <h5 class="fw-bold">2. Verifikasi & Jadwal</h5>
                        <p class="text-muted small">Admin akan menyesuaikan jadwal pejabat. Pantau terus status pengajuan Anda melalui menu Cek Status.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body p-4">
                        <div class="avtar avtar-xl bg-dark text-white mb-3 mx-auto">
                            <i class="ti ti-qrcode f-30"></i>
                        </div>
                        <h5 class="fw-bold">3. Terima E-Ticket</h5>
                        <p class="text-muted small">Jika disetujui, Anda akan mendapatkan <b>E-Ticket ber-QR Code</b> yang dikirimkan langsung ke Email Anda.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body p-4">
                        <div class="avtar avtar-xl bg-dark text-white mb-3 mx-auto">
                            <i class="ti ti-id f-30"></i>
                        </div>
                        <h5 class="fw-bold">4. Scan & Check-In</h5>
                        <p class="text-muted small">Tunjukkan QR Code kepada Keamanan untuk di-scan dan dapatkan <b>Kartu Tamu Sementara</b>.</p>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-auto border-top border-warning border-3">
        <div class="container text-center">
            <p class="mb-0">SIM-KUNJUNGAN (Smart Guest) &copy; 2026 | Sekretariat DPRD</p>
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