<?php session_start();
require_once 'config.php';
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}
$email = $_SESSION['email'];
$stmt = $conn->prepare(" SELECT * FROM mahasiswa WHERE mahasiswa_email = ? ");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user['role'] != 'admin' && $user['role'] != 'ketua') {
    die("Akses ditolak!");
}
$selectedUkmId = null;
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

if (isset($_GET['ukm_id'])) {
    $selectedUkmId = intval($_GET['ukm_id']);
}

if (isset($_POST['submit'])) {
    $judul = $_POST['session_judul'];
    $deskripsi = $_POST['session_deskripsi'];
    $hari = $_POST['session_hari'];
    $narasumber = $_POST['narasumber'];
    $ukm_id = isset($_POST['ukm_id']) ? intval($_POST['ukm_id']) : null;

    if (!$ukm_id) {
        echo "<div class='alert alert-danger'>Pilih UKM terlebih dahulu.</div>";
    } else {
        $sql = "INSERT INTO session (session_judul, session_deskripsi, session_hari, ukm_id, narasumber) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssiss", $judul, $deskripsi, $hari, $ukm_id, $narasumber);
            if ($stmt->execute()) {
                header("Location: session.php?id=" . $ukm_id);
                exit();
            } else {
                echo "Gagal tambah session: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Gagal menyiapkan query: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    body{
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    }
</style>
<div class="container mt-5 bg-light p-4 rounded shadow">
        <h2 class="pb-3 fw-bold">TAMBAH SESSION</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pilih UKM</label>
                <select name="ukm_id" class="form-control" required>
                    <option value="">-- Pilih UKM --</option>
                    <?php foreach ($ukmOptions as $option) : ?>
                        <option value="<?= htmlspecialchars($option['ukm_id']) ?>" <?= $selectedUkmId == $option['ukm_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['ukm_nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Judul Session</label>
                <input type="text" name="session_judul" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Session</label>
                <input type="text" name="session_deskripsi" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hari Session</label>
                <input type="date" name="session_hari" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Narasumber</label>
                <input type="text" name="narasumber" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-success">Simpan</button>
            <a href="session.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>