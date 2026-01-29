<?php
session_start();
include '../koneksi.php'; // Mundur 1 langkah cari koneksi di root

// Jika tombol login ditekan
if (isset($_POST['btn_login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Cek username di database admin
    $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
    
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        
        // Cek Password
        if ($password == $data['password']) {
            $_SESSION['status'] = "login";
            $_SESSION['id_admin'] = $data['id_admin'];
            $_SESSION['nama'] = $data['nama_pengguna'];
            $_SESSION['level'] = $data['level'];
            
            echo "<script>alert('Login Berhasil! Selamat Datang, " . $data['nama_pengguna'] . "'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Password Salah!');</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!');</script>";
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Login Administrator | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); /* Gradasi Biru Muda */
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); /* Bayangan Lembut */
        }
        .logo-login {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .btn-primary {
            background-color: #1e88e5;
            border-color: #1e88e5;
            padding: 10px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }
        .form-floating > label {
            padding-left: 15px;
        }
        .form-control {
            border-radius: 10px;
            padding-left: 15px;
        }
    </style>
  </head>

  <body>
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>

    <div class="auth-wrapper">
        <div class="card">
            <div class="card-body text-center p-4 p-sm-5">
                
                <div class="mb-4">
                    <img src="../assets/images/logo.png" class="logo-login" alt="Logo Pemkot">
                    <h4 class="mt-3 fw-bold text-dark">ADMINISTRATOR PANEL</h4>
                    <p class="text-muted mb-0">Sistem Informasi Kunjungan DPRD</p>
                </div>

                <form method="POST" action="">
                    
                    <div class="text-start mb-3">
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required />
                            <label for="floatingInput">Username</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="floatingInput1" placeholder="Password" required />
                            <label for="floatingInput1">Password</label>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="btn_login" class="btn btn-primary btn-lg shadow-sm">
                            MASUK SISTEM
                        </button>
                    </div>

                </form>
                <hr class="my-4" />
                
                <div>
                    <a href="../index.php" class="text-decoration-none text-muted fw-bold">
                        <i class="feather icon-arrow-left me-1"></i> Kembali ke Halaman Depan
                    </a>
                </div>

            </div>
        </div>
        
        <div class="text-center mt-3 text-muted small">
            &copy; 2025 Sekretariat DPRD Kota Banjarmasin
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