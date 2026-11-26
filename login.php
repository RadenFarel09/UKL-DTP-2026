<?php
session_start(); # Buat session untuk menyimpan data login
require 'koneksi.php';

if (isset($_SESSION['id_user'])) { # Jika user sudah login, maka langsung redirect ke index.php
    header("Location: index.php");
    exit;
}

$debug_msg = ''; 
$err = ''; # Buat menampung pesan error login yang ditampilkan ke user

if ($_SERVER['REQUEST_METHOD'] == 'POST') { # Buat proses login jika form login dikirimkan
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try { # Buat query untuk mengambil data user berdasarkan username yang dikirimkan melalui form login
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) { # Jika username tidak ditemukan di database, maka buat pesan debug untuk menandakan bahwa username tidak ada
            $debug_msg = "⚠️ Gagal: Username '<b>$username</b>' tidak ditemukan sama sekali di dalam database tabel `users`!";
        } else {
            # Cek password
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                $_SESSION['id_user']      = $user['id_user'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $debug_msg = "⚠️ Gagal: Username ditemukan, tetapi **Password salah**! Password di database tidak cocok dengan '$password'.";
            }
        }
    } catch (PDOException $e) { # Jika terjadi error teknis dari database, maka buat pesan debug untuk menampilkan error tersebut
        $debug_msg = "⚠️ Error Database: " . $e->getMessage();
    }

    $err = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DompetKu</title>
    <style>
        *{box-sizing:border-box;font-family:system-ui,sans-serif;margin:0;padding:0}
        body{background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .box{background:#fff;width:100%;max-width:420px;padding:30px;border-radius:14px;border-top:5px solid #0d9488}
        h2{text-align:center;margin-bottom:5px} h2 span{color:#0d9488}
        p.sub{text-align:center;color:#64748b;font-size:13px;margin-bottom:20px}
        label{display:block;font-size:13px;font-weight:600;margin-bottom:5px}
        input{width:100%;padding:11px;border:1.5px solid #cbd5e1;border-radius:8px;margin-bottom:15px}
        button{width:100%;padding:12px;background:#0d9488;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer}
        .err{background:#fee2e2;color:#b91c1c;padding:10px;border-radius:8px;font-size:13px;margin-bottom:15px;font-weight:600}
        .debug{background:#fef3c7;color:#b45309;padding:10px;border-radius:8px;font-size:12px;margin-bottom:15px;border:1px solid #fde68a;line-height:1.4}
        .info{margin-top:18px;background:#f8fafc;padding:10px;border-radius:8px;font-size:12px;color:#475569;border:1px dashed #cbd5e1}
    </style>
</head>
<body>
<div class="box">
    <h2>Dompet<span>Ku</span></h2>
    <p class="sub">Sistem Keuangan Keluarga</p>
    
    <?php if ($debug_msg): ?>  # Buat menampilkan pesan debug jika ada error teknis dari database atau username/password salah
        <div class="debug"><?= $debug_msg; ?></div>
    <?php endif; ?> 

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout') echo "<div class='err' style='background:#e0f2fe;color:#0369a1'>Anda telah berhasil logout.</div>"; ?>
    
    <form method="POST" action=""> 
        <label>Username</label>
        <input type="text" name="username" placeholder="ayah / ibu / kakak / adik" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
        <button type="submit">Masuk</button>
    </form>
</div>
</body>
</html>