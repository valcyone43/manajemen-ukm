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
    header("Location: home.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID session tidak ditemukan!");
}

$id = intval($_GET['id']);

// Ambil data session termasuk ukm_id-nya
$sesStmt = $conn->prepare("SELECT * FROM `session` WHERE session_id = ?");
$sesStmt->bind_param("i", $id);
$sesStmt->execute();
$data = $sesStmt->get_result()->fetch_assoc();

if (!$data) {
    die("Session tidak ditemukan.");
}

// Proteksi server-side: ketua hanya boleh edit session dari UKM miliknya
if ($isKetua && $data['ukm_id'] != $ketuaUkmId) {
    header("Location: session.php?error=akses_ditolak");
    exit;
}

// Proses update
if (isset($_POST['submit'])) {
    $judul      = $conn->real_escape_string($_POST['session_judul']);
    $deskripsi  = $conn->real_escape_string($_POST['session_deskripsi']);
    $hari       = $conn->real_escape_string($_POST['session_hari']);
    $narasumber = $conn->real_escape_string($_POST['narasumber']);

    $updateStmt = $conn->prepare("UPDATE `session` SET session_judul=?, session_deskripsi=?, session_hari=?, narasumber=? WHERE session_id=?");
    $updateStmt->bind_param("ssssi", $judul, $deskripsi, $hari, $narasumber, $id);

    if ($updateStmt->execute()) {
        header("Location: session.php");
        exit;
    } else {
        echo "Gagal update session: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Session</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/edit_session.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    body{
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    }
</style>

<div class="container mt-5 bg-light p-4 rounded shadow">
        <h2 class="fw-bold pb-3">SESSION</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Judul Session</label>
                <input type="text" name="session_judul" class="form-control"
                       value="<?= htmlspecialchars($data['session_judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Session</label>
                <input type="text" name="session_deskripsi" class="form-control"
                       value="<?= htmlspecialchars($data['session_deskripsi']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hari Session</label>
                <input type="date" name="session_hari" class="form-control"
                       value="<?= htmlspecialchars($data['session_hari']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Narasumber</label>
                <input type="text" name="narasumber" class="form-control"
                       value="<?= htmlspecialchars($data['narasumber']) ?>" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Update</button>
            <a href="session.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
