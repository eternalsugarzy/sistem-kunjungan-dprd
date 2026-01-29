<?php
session_start();

// Menghapus semua session variable
session_unset();

// Menghancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan
echo "<script>
    alert('Anda telah berhasil Logout.');
    window.location='login.php';
</script>";
?>