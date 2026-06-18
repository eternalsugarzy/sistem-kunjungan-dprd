<!doctype html>
<html lang="id">
<head>
    <title>Kartu Tamu Sementara | SIM-KUNJUNGAN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="assets/css/style-preset.css" />
    
    <style>
        /* Desain ID Card */
        .id-card {
            width: 350px;
            border: 2px solid #343a40;
            border-radius: 15px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        .id-card-header {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
        }
        .id-card-body {
            padding: 20px;
            text-align: center;
        }
        .qr-placeholder {
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }
        
        /* Menghilangkan elemen yang tidak perlu saat di-print */
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .id-card { box-shadow: none; margin-top: 0; }
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

    <div class="container py-5">
        <div class="text-center mb-4 no-print">
            <h3 class="fw-bold">Penerbitan Kartu Tamu</h3>
            <p class="text-muted">Cetak atau tunjukkan kartu ini selama berada di area instansi.</p>
        </div>

        <div class="id-card">
            <div class="id-card-header">
                <h5 class="mb-0 text-white fw-bold">KARTU TAMU SEMENTARA</h5>
                <small>DPRD Kota Banjarmasin</small>
            </div>
            <div class="id-card-body">
                <div class="qr-placeholder">
                    <img src="assets/images/sample-qr.png" alt="QR Code" style="max-width: 100%; height: auto; display:none;" id="qr-img">
                    <i class="ti ti-qrcode text-muted" style="font-size: 4rem;"></i>
                </div>
                
                <h5 class="fw-bold mb-0">DPRD Kab. Tanah Laut</h5> <p class="text-muted mb-2">Kode: <strong>REQ-2025-A001</strong></p>
                
                <hr class="border-dashed my-2">
                
                <div class="row text-start mt-3" style="font-size: 0.9rem;">
                    <div class="col-5 text-muted fw-bold">Tanggal</div>
                    <div class="col-7">: 10 Des 2025</div>
                    
                    <div class="col-5 text-muted fw-bold">Tujuan</div>
                    <div class="col-7">: Ruang Komisi 2</div>
                    
                    <div class="col-5 text-muted fw-bold">Batas Waktu</div>
                    <div class="col-7 text-danger fw-bold">: 15:00 WITA</div>
                </div>
            </div>
            <div class="bg-dark text-white text-center py-2" style="font-size: 0.75rem;">
                Harap dikembalikan/checkout sebelum batas waktu.
            </div>
        </div>
        <div class="text-center mt-4 no-print d-flex justify-content-center gap-2">
            <a href="scan_qr.php" class="btn btn-outline-dark">[ Kembali ]</a>
            <button onclick="window.print()" class="btn btn-dark">
                <i class="ti ti-printer me-1"></i> [ Cetak Kartu ]
            </button>
        </div>
    </div>

    <script src="assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>