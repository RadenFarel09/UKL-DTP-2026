<?php
$host = 'localhost'; # Buat koneksi ke database MySQL
$dbname = 'db_dtp_keuangan';
$username = 'root';
$password = '';

try { # Buat Mengantisipasi jika koneksi gagal, maka akan menampilkan pesan error
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
