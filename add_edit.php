<?php
require 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$is_edit = false;
$data = [
    'id' => null,
    'tanggal' => date('Y-m-d'),
    'nomor_surat' => '',
    'asal' => '',
    'perihal' => '',
    'file_path' => null
];

// Jika ada id di query -> edit
if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM mails WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $is_edit = true;
        $data = $row;
    } else {
        die("Data tidak ditemukan atau Anda tidak punya akses.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $nomor_surat = trim($_POST['nomor_surat'] ?? '');
    $asal = trim($_POST['asal'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');

    if (!$tanggal) $errors[] = "Tanggal wajib diisi.";
    if (!$nomor_surat) $errors[] = "Nomor surat wajib diisi.";
    if (!$asal) $errors[] = "Asal/pengirim wajib diisi.";
    if (!$perihal) $errors[] = "Perihal wajib diisi.";

    // Handle file upload opsional
    $uploaded_path = $data['file_path'];
    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','doc','docx','jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $errors[] = "Tipe file tidak diperbolehkan. (pdf, doc, docx, jpg, png, gif)";
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = "Ukuran file maksimal 5MB.";
            } else {
                // Pastikan folder uploads ada
                $upload_dir = 'uploads';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // generate nama file unik
                $fname = uniqid('f_') . '.' . $ext;
                $dest = $upload_dir . '/' . $fname;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors[] = "Gagal menyimpan file.";
                } else {
                    $uploaded_path = $dest;
                    // (opsional) jika edit dan punya file lama, hapus file lama
                    if ($is_edit && !empty($data['file_path']) && file_exists($data['file_path'])) {
                        @unlink($data['file_path']);
                    }
                }
            }
        } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "Error upload file. Kode error: " . $file['error'];
        }
    }

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE mails SET tanggal = ?, nomor_surat = ?, asal = ?, perihal = ?, file_path = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$tanggal, $nomor_surat, $asal, $perihal, $uploaded_path, $data['id'], $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO mails (user_id, tanggal, nomor_surat, asal, perihal, file_path) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$user_id, $tanggal, $nomor_surat, $asal, $perihal, $uploaded_path]);
        }
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= $is_edit ? 'Edit' : 'Tambah' ?> Surat - Catatan Surat</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
  <h2><?= $is_edit ? 'Edit' : 'Tambah' ?> Surat</h2>
  <?php if($errors): ?>
    <div class="errors"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
    <label>Nomor Surat</label>
    <input type="text" name="nomor_surat" value="<?= htmlspecialchars($data['nomor_surat']) ?>" required>
    <label>Asal / Pengirim</label>
    <input type="text" name="asal" value="<?= htmlspecialchars($data['asal']) ?>" required>
    <label>Perihal</label>
    <textarea name="perihal" required><?= htmlspecialchars($data['perihal']) ?></textarea>

    <label>File (opsional)</label>
    <?php if(!empty($data['file_path'])): ?>
      <p>File saat ini: <a href="<?= htmlspecialchars($data['file_path']) ?>" target="_blank">Lihat</a></p>
    <?php endif; ?>
    <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">

    <div style="margin-top:12px;">
      <button type="submit"><?= $is_edit ? 'Simpan Perubahan' : 'Tambah Surat' ?></button>
      <a href="index.php" class="btn-link">Batal</a>
    </div>
  </form>
</div>
</body>
</html>