<!doctype html>
<html lang="id">
<head>
    <title>Pembatalan Kunjungan | SIM-KUNJUNGAN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand text-dark fw-bold" href="index.php">
          <img src="assets/images/logo.png" alt="logo" style="height:30px" class="me-2" />
          SIM-KUNJUNGAN
        </a>
      </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white text-center py-3">
                        <h4 class="mb-0 text-white"><i class="ti ti-alert-triangle me-2"></i>[ Pembatalan Kunjungan ]</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="alert alert-warning mb-4" role="alert">
                            <h6 class="alert-heading fw-bold">Perhatian!</h6>
                            <p class="mb-0 small">Kunjungan yang sudah dibatalkan tidak dapat dikembalikan. Anda harus membuat pengajuan baru jika ingin berkunjung kembali.</p>
                        </div>

                        <div class="bg-light p-3 rounded border mb-4">
                            <div class="row mb-1">
                                <div class="col-5 text-muted">Kode Booking</div>
                                <div class="col-7 fw-bold">: REQ-2025-A001</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-muted">Instansi</div>
                                <div class="col-7 fw-bold">: DPRD Kab. Tanah Laut</div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-muted">Tgl Rencana</div>
                                <div class="col-7 fw-bold">: 10 Des 2025</div>
                            </div>
                        </div>

                        <form method="POST" action="proses_batal.php">
                            <input type="hidden" name="id_kunjungan" value="1"> 
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="alasan_pembatalan" class="form-control border-danger" rows="4" placeholder="Tuliskan alasan mengapa kunjungan dibatalkan (wajib diisi)..." required></textarea>
                                <small class="text-muted mt-1 d-block">Data ini akan terekam pada Laporan Kunjungan Batal.</small>
                            </div>

                            <div class="d-flex justify-content-between gap-3">
                                <a href="cek_status.php" class="btn btn-light border w-50">[ Kembali ]</a>
                                <button type="submit" name="proses_batal" class="btn btn-danger w-50 fw-bold">
                                    [ Batalkan Kunjungan ]
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>