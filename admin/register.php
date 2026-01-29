<?php
include '../koneksi.php';

if (isset($_POST['btn_register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan, silakan pilih yang lain!');</script>";
    } else {
        // Simpan data sebagai 'pengunjung'
        $query = "INSERT INTO admin (nama_pengguna, username, password, level) VALUES ('$nama', '$username', '$password', 'pengunjung')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal Mendaftar: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Daftar Akun | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .logo-login {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 10px;
            padding-left: 15px;
        }
    </style>
  </head>

  <body>
    <div class="auth-wrapper">
        <div class="card">
            <div class="card-body text-center p-4">
                
                <div class="mb-4">
                    <img src="../assets/images/logo.png" class="logo-login" alt="Logo">
                    <h4 class="mt-3 fw-bold text-dark">DAFTAR AKUN</h4>
                    <p class="text-muted mb-0">Sistem Informasi Kunjungan DPRD</p>
                </div>

                <form method="POST" action="">
                    
                    <div class="text-start mb-3">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap / Instansi</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: DPRD Kab. Banjar" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Buat Username" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Buat Password" required />
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="btn_register" class="btn btn-success btn-lg shadow-sm">
                            DAFTAR SEKARANG
                        </button>
                    </div>

                </form>
                
                <hr class="my-4" />
                
                <p class="mb-0 text-muted">Sudah punya akun?</p>
                <a href="login.php" class="btn btn-outline-primary w-100 mt-2">Login Disini</a>

            </div>
        </div>
    </div>
  </body>
</html>