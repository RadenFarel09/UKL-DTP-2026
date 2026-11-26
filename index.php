<?php
session_start(); 
require 'koneksi.php'; 

if (!isset($_SESSION['id_user'])) { 
    header("Location: login.php?pesan=belum_login");
    exit;
}

$uid  = $_SESSION['id_user']; 
$nama = $_SESSION['nama_lengkap']; 
$role = $_SESSION['role'];

if (isset($_GET['hapus'])) { # Buat hapus transaksi (hanya admmin)
    $id = $_GET['hapus']; 
    $sql_del = ($role == 'admin') ? "DELETE FROM transaksi WHERE id_transaksi = ?" : "DELETE FROM transaksi WHERE id_transaksi = ? AND id_user = ?";
    $params  = ($role == 'admin') ? [$id] : [$id, $uid]; 
    $pdo->prepare($sql_del)->execute($params);
    header("Location: index.php?pesan=hapus");
    exit;
}

$base_sql = "SELECT t.*, k.nama_kategori, k.jenis, d.nama_dompet, d.tipe_akun, u.nama_lengkap # Buat query untuk mengambil data (Disebutkan Variablenya Soalnya kita nggak bis menampilakn angka mmentah dari kolom-kolom di database)
             FROM transaksi t
             INNER JOIN kategori k ON t.id_kategori = k.id_kategori # Buat menghubungkan tabel transaksi dengan kategori menggunakan ID nya
             INNER JOIN dompet d ON t.id_dompet = d.id_dompet # Buat Menghubungkan tabel transaksi dengan dompey menggunakan ID nya
             INNER JOIN users u ON t.id_user = u.id_user"; # Buat menghubungkan tabel transaksi dengan user supaya bisa menampilkan nama anggota keluarga yg buat catatan tsb

if ($role == 'admin') { 
    $riwayat = $pdo->query("$base_sql ORDER BY t.tanggal DESC, t.id_transaksi DESC")->fetchAll(PDO::FETCH_ASSOC); # Buat menampilkan riwayat transaksi untuk semua anggota keluarga (admin)
} else { 
    $stmt = $pdo->prepare("$base_sql WHERE t.id_user = ? ORDER BY t.tanggal DESC, t.id_transaksi DESC"); # Buat menampilkan riwayat transaksi hanya untuk user biasa
    $stmt->execute([$uid]);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$masuk = 0; $keluar = 0; 
foreach ($riwayat as $r) {
    if ($r['jenis'] == 'Pemasukan') $masuk += $r['nominal']; else $keluar += $r['nominal'];
}
$saldo = $masuk - $keluar;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DompetKu</title>
    <style>
        *{box-sizing:border-box;font-family:system-ui,sans-serif;margin:0;padding:0}
        body{background:#f1f5f9;color:#0f172a}
        .nav{background:#0f172a;color:#fff;padding:15px 25px;display:flex;justify-content:space-between;align-items:center;border-bottom:4px solid #0d9488;flex-wrap:wrap;gap:10px}
        .nav span{color:#2dd4bf}
        .badge-role{padding:4px 10px;border-radius:12px;font-size:12px;font-weight:700;background:#fef3c7;color:#b45309}
        .btn-out{background:#ef4444;color:#fff;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600}
        .wrap{max-width:1100px;margin:25px auto;padding:0 15px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:15px;margin-bottom:20px}
        .box{background:#fff;padding:18px;border-radius:12px;border:1px solid #e2e8f0;border-left:5px solid #0d9488}
        .box.in{border-left-color:#16a34a} .box.out{border-left-color:#dc2626}
        .card{background:#fff;padding:20px;border-radius:12px;border:1px solid #e2e8f0}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px}
        .btn{padding:8px 14px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;display:inline-block}
        .btn-add{background:#0d9488;color:#fff} .btn-ed{background:#fef3c7;color:#b45309} .btn-del{background:#fee2e2;color:#b91c1c}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th,td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left}
        th{background:#f8fafc;font-size:12px;color:#64748b;text-transform:uppercase}
        .alert{background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:15px;font-weight:600}
    </style>
</head>
<body>
<div class="nav">
    <h2>Dompet<span>Ku</span></h2>
    <div>
        Halo, <strong><?= htmlspecialchars($nama) ?></strong>
        <span class="badge-role"><?= $role == 'admin' ? 'Admin (Ortu)' : 'Member (Anak)' ?></span>
        <a href="logout.php" class="btn-out">Logout</a>
    </div>
</div>
<div class="wrap">
    <div class="grid">
        <div class="box in"><small>PEMASUKAN</small><h3 style="color:#16a34a">+ Rp <?= number_format($masuk,0,',','.') ?></h3></div>
        <div class="box out"><small>PENGELUARAN</small><h3 style="color:#dc2626">- Rp <?= number_format($keluar,0,',','.') ?></h3></div>
        <div class="box"><small>SISA SALDO</small><h3 style="color:#0f766e">Rp <?= number_format($saldo,0,',','.') ?></h3></div>
    </div>
    <?php if(isset($_GET['pesan'])) echo "<div class='alert'>Operasi data berhasil!</div>"; ?>
    <div class="card">
        <div class="top">
            <h3><?= $role == 'admin' ? 'Semua Transaksi Keluarga' : 'Transaksi Pribadi Saya' ?></h3>
            <a href="tambah.php" class="btn btn-add">+ Catat Transaksi</a>
        </div>
        <div style="overflow-x:auto">
            <table>
                <tr><th>Tanggal</th><th>Anggota</th><th>Keterangan</th><th>Kategori</th><th>Dompet</th><th>Jenis</th><th>Nominal</th><th>Aksi</th></tr>
                <?php foreach($riwayat as $r): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                    <td><strong><?= htmlspecialchars($r['nama_lengkap']) ?></strong></td>
                    <td><?= htmlspecialchars($r['keterangan']) ?></td>
                    <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                    <td><?= htmlspecialchars($r['nama_dompet']) ?> (<?= $r['tipe_akun'] ?>)</td>
                    <td><?= $r['jenis'] ?></td>
                    <td style="font-weight:700;color:<?= $r['jenis']=='Pemasukan'?'#16a34a':'#dc2626' ?>">Rp <?= number_format($r['nominal'],0,',','.') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $r['id_transaksi'] ?>" class="btn btn-ed">Edit</a>
                        <a href="index.php?hapus=<?= $r['id_transaksi'] ?>" class="btn btn-del" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
