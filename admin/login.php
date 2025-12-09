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
        
        // Cek Password (Sementara plain text sesuai request awal)
        // Nanti jika ingin lebih aman bisa pakai password_verify()
        if ($password == $data['password']) {
            // Set Session
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
    <title>Login Admin | Sistem Kunjungan DPRD</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
  </head>
  <body>
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <div class="auth-main">
      <div class="auth-wrapper v3">
        <div class="auth-form">
          <div class="card my-5">
            <div class="card-body">
              
              <a href="#" class="d-flex justify-content-center">
                <h3 class="text-secondary"><b>ADMIN PANEL</b></h3>
              </a>

              <div class="row">
                <div class="d-flex justify-content-center">
                  <div class="auth-header">
                    <h2 class="text-secondary mt-5"><b>Sistem Kunjungan</b></h2>
                    <p class="f-16 mt-2">Masukan username dan password untuk masuk</p>
                  </div>
                </div>
              </div>

              <form method="POST" action="">
                  
                  <h5 class="my-4 d-flex justify-content-center">Login Administrator</h5>
                  
                  <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required />
                    <label for="floatingInput">Username</label>
                  </div>

                  <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="floatingInput1" placeholder="Password" required />
                    <label for="floatingInput1">Password</label>
                  </div>

                  <div class="d-flex mt-1 justify-content-between">
                    <div class="form-check">
                      </div>
                  </div>

                  <div class="d-grid mt-4">
                    <button type="submit" name="btn_login" class="btn btn-secondary">Sign In</button>
                  </div>

              </form>
              <hr />
              <div class="d-flex justify-content-center">
                  <small><a href="../index.php">Kembali ke Halaman Depan</a></small>
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