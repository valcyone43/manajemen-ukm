<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    redirect('index.php');
}

$email = $_SESSION['email'];

// Ambil user + ukm_id dari profil
$stmt = $conn->prepare("
    SELECT m.mahasiswa_nama, m.role, u.ukm_id
    FROM mahasiswa m
    LEFT JOIN user_db u ON u.mahasiswa_npm = m.mahasiswa_npm
    WHERE m.mahasiswa_email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$isAdmin    = $user['role'] === 'admin';
$isKetua    = $user['role'] === 'ketua';
$ketuaUkmId = $user['ukm_id'] ?? null;

// Hanya admin dan ketua yang boleh akses
if (!$isAdmin && !$isKetua) {
    header("Location: list_ukm.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = intval($_GET['id']);

// Proteksi server-side: ketua hanya boleh edit UKM miliknya
if ($isKetua && $id !== (int)$ketuaUkmId) {
    header("Location: list_ukm.php?error=akses_ditolak");
    exit;
}

// Ambil data UKM
$ukmStmt = $conn->prepare("SELECT * FROM ukm WHERE ukm_id = ?");
$ukmStmt->bind_param("i", $id);
$ukmStmt->execute();
$result = $ukmStmt->get_result();

if ($result->num_rows == 0) {
    die("Data tidak ditemukan");
}
$row = $result->fetch_assoc();

// Proses update
if (isset($_POST['update'])) {
    $nama       = $conn->real_escape_string($_POST['ukm_nama']);
    $slogan     = $conn->real_escape_string($_POST['ukm_slogan']);
    $nopengurus = $conn->real_escape_string($_POST['ukm_nopengurus']);

    $updateStmt = $conn->prepare("UPDATE ukm SET ukm_nama=?, ukm_slogan=?, ukm_nopengurus=? WHERE ukm_id=?");
    $updateStmt->bind_param("sssi", $nama, $slogan, $nopengurus, $id);

    if ($updateStmt->execute()) {
        header("Location: list_ukm.php");
        exit;
    } else {
        echo "Gagal update data: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit UKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/list_ukm.css">
</head>
<body>
    <nav class="side-navbar">
        <div class="side-navbar-menu">
            <a href="home.php">Home</a>
            <a href="list_ukm.php">List UKM</a>
            <?php if ($isAdmin || $isKetua) { ?>
                <a href="session.php">Session</a>
            <?php } ?>
            <a href="user_data.php"><?= $isAdmin ? 'User' : 'Profile' ?></a>
        </div>
    </nav>

    <nav class="main-navbar">
        <div class="navbar-title">
            <img src="../img/logouika.png" alt="Logo" class="logo-navbar">
            <span>UKM Universitas IBN Khaldun</span>
        </div>
    </nav>

    <div class="dashboard-content bg-light p-4 rounded shadow" style="margin-top: 80px;">
        <div style="max-width: 600px;">
            <h2 class="pb-3 fw-bold">DATA UKM</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama UKM</label>
                    <input type="text" name="ukm_nama" class="form-control"
                           value="<?= htmlspecialchars($row['ukm_nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slogan UKM</label>
                    <input type="text" name="ukm_slogan" class="form-control"
                           value="<?= htmlspecialchars($row['ukm_slogan']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No Pengurus</label>
                    <input type="text" name="ukm_nopengurus" class="form-control"
                           value="<?= htmlspecialchars($row['ukm_nopengurus']) ?>" required>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <a href="list_ukm.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>
