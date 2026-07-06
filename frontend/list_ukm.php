<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}
$email = $_SESSION['email'];

// Ambil data user + ukm_id dari user_db
$query = $conn->prepare("
    SELECT m.role, m.mahasiswa_nama, m.mahasiswa_npm, u.ukm_id
    FROM mahasiswa m
    LEFT JOIN user_db u ON u.mahasiswa_npm = m.mahasiswa_npm
    WHERE m.mahasiswa_email = ?
");
$query->bind_param("s", $email);
$query->execute();
$user = $query->get_result()->fetch_assoc();

$isAdmin     = $user['role'] === 'admin';
$isKetua     = $user['role'] === 'ketua';
$ketuaUkmId  = $user['ukm_id'] ?? null; // ukm_id milik ketua dari profil
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>List UKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
        <div class="navbar-menu">
            <div class="dropdown-user">
                <span class="user-name">
                    <?= htmlspecialchars($user['mahasiswa_nama']) ?> ▼
                </span>
                <div class="dropdown-content">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-header">
        <h1>List UKM Ibn Khaldun</h1>
        <p>Nyari UKM apa boss</p>
    </div>

    <div class="dashboard-content">
        <?php if ($isAdmin) { ?>
            <div style="margin-bottom: 20px;">
                <a href="tambah_ukm.php" class="btn btn-success">+ Tambah UKM</a>
            </div>
        <?php } ?>

        <table border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama UKM</th>
                    <th>Slogan UKM</th>
                    <th>No Pengurus</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT ukm_id, ukm_nama, ukm_slogan, ukm_nopengurus FROM ukm");
                if (!$result) die("Query failed: " . $conn->error);

                while ($row = $result->fetch_assoc()) {
                    $isUkmMilikKetua = ($ketuaUkmId == $row['ukm_id']);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ukm_id']) ?></td>
                        <td><?= htmlspecialchars($row['ukm_nama']) ?></td>
                        <td><?= htmlspecialchars($row['ukm_slogan']) ?></td>
                        <td><?= htmlspecialchars($row['ukm_nopengurus']) ?></td>
                        <td>
                            <?php if ($isAdmin) { ?>
                                <a class="btn btn-primary btn-sm" href="edit_ukm.php?id=<?= $row['ukm_id'] ?>"> Edit </a>
                                <a class="btn btn-danger btn-sm" href="delete_ukm.php?id=<?= $row['ukm_id'] ?>" onclick="return confirm('Yakin ingin hapus data ini?')"> Hapus </a>
                                <a class="btn btn-success btn-sm mt-1" href="session-detail.php?id=<?= $row['ukm_id'] ?>"> Detail </a>
                            <?php } elseif ($isKetua) { ?>
                                <?php if ($isUkmMilikKetua) { ?>
                                  
                                    <a class="btn btn-primary btn-sm" href="edit_ukm.php?id=<?= $row['ukm_id'] ?>"> Edit </a>
                                    <a class="btn btn-warning btn-sm" href="session-detail.php?id=<?= $row['ukm_id'] ?>"> Detail </a>
                                <?php } else { ?>
                             
                                    <a class="btn btn-warning btn-sm" href="session-detail.php?id=<?= $row['ukm_id'] ?>"> Detail </a>
                                <?php } ?>
                            <?php } else { ?>
                                <a class="btn btn-warning btn-sm" href="session-detail.php?id=<?= $row['ukm_id'] ?>"> Detail </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        const dropdown = document.querySelector(".dropdown-user");
        const dropdownContent = document.querySelector(".dropdown-content");

        dropdownContent.style.display = "none";

        dropdown.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdownContent.style.display = dropdownContent.style.display === "none" ? "block" : "none";
        });

        document.addEventListener("click", function(e) {
            if (!dropdown.contains(e.target)) dropdownContent.style.display = "none";
        });
    </script>
</body>
</html>
