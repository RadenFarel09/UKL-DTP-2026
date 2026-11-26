<?php
session_start(); # Buat session untuk menyimpan data login
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') { # Jika form login dikirimkan, maka lakukan proses login
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); # Standar PDO untuk mencegah SQL Injection dan mengambil data user berdasarkan username
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); # Ambil data user sebagai array asosiatif

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) { # Buat validasi password, jika cocok maka simpan data user ke session dan redirect ke index.php
        $_SESSION['id_user']      = $user['id_user'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role']         = $user['role']; # Buat menyimpan role user untuk membedakan hak akses antara admin dan user biasa
        header("Location: index.php"); 
        exit;
    } else {
        header("Location: login.php?pesan=gagal");
        exit;
    }
}
header("Location: login.php"); # Buat redirect ke login.php jika ada yang mencoba mengakses proses-login.php secara langsung dari URL
exit;