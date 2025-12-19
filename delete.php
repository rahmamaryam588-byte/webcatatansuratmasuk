<?php
require 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// ambil dulu untuk mengecek kepemilikan dan menghapus file jika ada
$stmt = $pdo->prepare("SELECT file_path FROM mails WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$row = $stmt->fetch();
if (!$row) {
    die("Data tidak ditemukan atau akses ditolak.");
}

if (!empty($row['file_path']) && file_exists($row['file_path'])) {
    @unlink($row['file_path']);
}

$stmt = $pdo->prepare("DELETE FROM mails WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header('Location: index.php');
exit;