<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}
$email = $_SESSION['email'];

$columnCheck = $conn->query("SHOW COLUMNS FROM user_db LIKE 'ukm_id'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE user_db ADD COLUMN ukm_id INT DEFAULT NULL");
}

$stmt = $conn->prepare(
    "SELECT m.mahasiswa_nama, m.mahasiswa_npm, m.mahasiswa_email,
            u.user_prodi, u.user_fakultas, u.ukm_id
     FROM mahasiswa m
     LEFT JOIN user_db u ON u.mahasiswa_npm = m.mahasiswa_npm
     WHERE m.mahasiswa_email = ?"
);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die('User tidak ditemukan.');
}
$oldNpm = $user['mahasiswa_npm'];
$currentUkmId = $user['ukm_id'] ?? null;

$existingProdi = $user['user_prodi'] ?? '';
$existingFakultas = $user['user_fakultas'] ?? '';

$ukmOptions = [];
$ukmStmt = $conn->prepare("SELECT ukm_id, ukm_nama FROM ukm ORDER BY ukm_nama ASC");
if ($ukmStmt) {
    $ukmStmt->execute();
    $ukmResult = $ukmStmt->get_result();
    while ($ukmRow = $ukmResult->fetch_assoc()) {
        $ukmOptions[] = $ukmRow;
    }
    $ukmStmt->close();
}

if (isset($_POST['submit'])) {
    $nama = trim($_POST['mahasiswa_nama']);
    $npm = trim($_POST['mahasiswa_npm']);
    
    // BACKEND PROTECTION: Jika data sudah ada, gunakan data lama. Jika belum ada, ambil dari POST.
    $prodi = !empty($existingProdi) ? $existingProdi : trim($_POST['user_prodi'] ?? '');
    $fakultas = !empty($existingFakultas) ? $existingFakultas : trim($_POST['user_fakultas'] ?? '');
    
    $ukmId = isset($_POST['ukm_id']) ? intval($_POST['ukm_id']) : null;

    $update = $conn->prepare("UPDATE mahasiswa SET mahasiswa_nama = ?, mahasiswa_npm = ? WHERE mahasiswa_email = ?");
    if (!$update) {
        die("Prepare failed: " . $conn->error);
    }
    $update->bind_param("sis", $nama, $npm, $email);
    if (!$update->execute()) {
        die("Gagal update profil: " . $update->error);
    }

    $check = $conn->prepare("SELECT user_id FROM user_db WHERE mahasiswa_npm = ?");
    if (!$check) {
        die("Prepare failed: " . $conn->error);
    }
    $check->bind_param("i", $oldNpm);
    $check->execute();
    $check->store_result();

    $userId = null;
    if ($check->num_rows > 0) {
        $check->bind_result($userId);
        $check->fetch();

        $updateDetail = $conn->prepare("UPDATE user_db SET mahasiswa_npm = ?, user_prodi = ?, user_fakultas = ?, ukm_id = ? WHERE mahasiswa_npm = ?");
        if (!$updateDetail) {
            die("Prepare failed: " . $conn->error);
        }
        $updateDetail->bind_param("issii", $npm, $prodi, $fakultas, $ukmId, $oldNpm);
        if (!$updateDetail->execute()) {
            die("Gagal update profil detail: " . $updateDetail->error);
        }
    } else {
        $nextIdResult = $conn->query("SELECT COALESCE(MAX(user_id), 0) + 1 AS next_id FROM user_db");
        if (!$nextIdResult) {
            die("Query failed: " . $conn->error);
        }
        $nextIdRow = $nextIdResult->fetch_assoc();
        $userId = (int) $nextIdRow['next_id'];

        $insert = $conn->prepare("INSERT INTO user_db (user_id, mahasiswa_npm, user_prodi, user_fakultas, ukm_id) VALUES (?, ?, ?, ?, ?)");
        if (!$insert) {
            die("Prepare failed: " . $conn->error);
        }
        $insert->bind_param("iissi", $userId, $npm, $prodi, $fakultas, $ukmId);
        if (!$insert->execute()) {
            die("Gagal insert profil detail: " . $insert->error);
        }
    }

    if ($userId !== null) {
        $updateUserFk = $conn->prepare("UPDATE mahasiswa SET user_id = ? WHERE mahasiswa_email = ?");
        if (!$updateUserFk) {
            die("Prepare failed: " . $conn->error);
        }
        $updateUserFk->bind_param("is", $userId, $email);
        if (!$updateUserFk->execute()) {
            die("Gagal update foreign key user_id: " . $updateUserFk->error);
        }
    }

    redirect('user_data.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/user_data.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-profile-container {
            padding-bottom: 320px;
            min-height: calc(100vh - 120px);
        }
    </style>
</head>

<body>
    <nav class="main-navbar">
        <div class="navbar-title">
            <img src="../img/logouika.png" alt="Logo" class="logo-navbar">
            <span>UKM Universitas IBN Khaldun</span>
        </div>
    </nav>
    <div class="container mt-5 edit-profile-container">
        <h2 class="fw-bold pb-3">EDIT PROFILE</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="mahasiswa_nama" class="form-control" value="<?= htmlspecialchars($user['mahasiswa_nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['mahasiswa_email']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">NPM</label>
                <input type="text" name="mahasiswa_npm" class="form-control" value="<?= htmlspecialchars($user['mahasiswa_npm']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Program Studi</label>
                <?php if (!empty($user['user_prodi'])) : ?>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_prodi']) ?>" readonly>
                    <small class="text-muted">Anda sudah memilih program studi. Untuk mengubah, hubungi admin.</small>
                <?php else : ?>
                    <select name="user_prodi" class="form-select" required>
                        <option value="">-- Pilih Program Studi --</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                        <option value="Manajemen">Manajemen</option>
                        <option value="Akuntansi">Akuntansi</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Fakultas</label>
                <?php if (!empty($user['user_fakultas'])) : ?>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_fakultas']) ?>" readonly>
                    <small class="text-muted">Anda sudah memilih fakultas. Untuk mengubah, hubungi admin.</small>
                <?php else : ?>
                    <select name="user_fakultas" class="form-select" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <option value="FTI">Fakultas Teknik Informatika</option>
                        <option value="FIH">Fakultas Ilmu Hukum</option>
                        <option value="FE">Fakultas Ekonomi</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">UKM</label>
                <?php if (!empty($currentUkmId)) :
                    $selectedName = '';
                    foreach ($ukmOptions as $opt) {
                        if ($opt['ukm_id'] == $currentUkmId) { $selectedName = $opt['ukm_nama']; break; }
                    }
                ?>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($selectedName) ?>" readonly>
                    <input type="hidden" name="ukm_id" value="<?= htmlspecialchars($currentUkmId) ?>">
                    <small class="text-muted">Anda sudah memilih fakultas. Untuk mengubah, hubungi admin.</small>
                <?php else : ?>
                    <select name="ukm_id" class="form-select">
                        <option value="">-- Pilih UKM --</option>
                        <?php foreach ($ukmOptions as $opt) : ?>
                            <option value="<?= $opt['ukm_id'] ?>"><?= htmlspecialchars($opt['ukm_nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                <a href="user_data.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>

