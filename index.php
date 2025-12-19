<?php
require 'config.php';
require_login();

$user_id = $_SESSION['user_id'];

// Ambil data surat milik user yang login
$stmt = $pdo->prepare("SELECT * FROM mails WHERE user_id = ? ORDER BY tanggal DESC, id DESC");
$stmt->execute([$user_id]);
$mails = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard - Catatan Surat Masuk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="topbar">
  <div>Selamat datang💅, <?= htmlspecialchars($_SESSION['email']) ?></div>
  <div><a href="add_edit.php">+ Tambah Surat</a> | <a href="logout.php">Logout</a></div>
</div>

<div class="container">
  <h2>Daftar Surat Masuk🎀</h2>
  <?php if(empty($mails)): ?>
    <p>Tidak ada data. Tambah <a href="add_edit.php">surat baru</a>.</p>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Nomor Surat</th>
          <th>Asal / Pengirim</th>
          <th>Perihal</th>
          <th>File</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach($mails as $row): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
            <td><?= htmlspecialchars($row['asal']) ?></td>
            <td><?= htmlspecialchars($row['perihal']) ?></td>
            <td>
  <?php if($row['file_path']): ?>
    <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">Lihat</a>
  <?php else: echo "—"; endif; ?>
</td>
            <td>
              <a href="add_edit.php?id=<?= $row['id'] ?>">Edit</a> |
              <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus surat ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>