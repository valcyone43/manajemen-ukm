<?php
session_start();
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}
require_once 'config.php';

$email = $_SESSION['email'];

// Ambil user + ukm_id dari profil
$stmt = $conn->prepare("
    SELECT m.mahasiswa_nama, m.mahasiswa_npm, m.role, u.ukm_id
    FROM mahasiswa m
    LEFT JOIN user_db u ON u.mahasiswa_npm = m.mahasiswa_npm
    WHERE m.mahasiswa_email = ?
");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    redirect('index.php');
}

// Hanya admin dan ketua boleh akses halaman ini
if ($user['role'] !== 'admin' && $user['role'] !== 'ketua') {
    header("Location: home.php");
    exit;
}

$isAdmin    = $user['role'] === 'admin';
$isKetua    = $user['role'] === 'ketua';
$ketuaUkmId = $user['ukm_id'] ?? null; // ukm_id milik ketua dari profil
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Session Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../style/session.css">
</head>
<body>
    <nav class="side-navbar">
        <div class="side-navbar-menu">
            <a href="home.php">Home</a>
            <a href="list_ukm.php">List UKM</a>
            <a href="session.php">Session</a>
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
        <h1>Session Information</h1>
        <p>Session Management</p>
    </div>

    <div class="dashboard-content">
        <?php if ($isAdmin): ?>
            <h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Daftar Semua Sesi</h1>
        <?php else: ?>
            <h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Kelola Sesi UKM Anda</h1>
        <?php endif; ?>

        <br>

        <?php if ($isAdmin || ($isKetua && !empty($ketuaUkmId))): ?>
            <div style="margin-bottom: 20px;">
                <a href="tambah_session.php" class="btn btn-success">+ Tambah Session</a>
            </div>
        <?php elseif ($isKetua && empty($ketuaUkmId)): ?>
            <div class="alert alert-warning" style="max-width:600px;">
                Anda belum terdaftar di UKM manapun. <a href="edit_profiledata.php">Lengkapi profil</a> terlebih dahulu untuk mengelola session.
            </div>
        <?php endif; ?>

        <table border="1" class="user table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Session Title</th>
                    <th>Description</th>
                    <th>Tanggal</th>
                    <th>Speaker</th>
                    <th>UKM</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="insert-session">
                <?php
                if ($isAdmin) {
                    // Admin: tampilkan SEMUA session dari semua UKM
                    $sql = "SELECT s.session_id, s.session_judul, s.session_deskripsi, s.session_hari, s.narasumber, s.ukm_id, u.ukm_nama
                            FROM `session` s
                            LEFT JOIN ukm u ON u.ukm_id = s.ukm_id
                            ORDER BY s.session_hari DESC";
                    $sesResult = $conn->query($sql);
                } else {
                    // Ketua: HANYA session dari UKM miliknya
                    $sesStmt = $conn->prepare(
                        "SELECT s.session_id, s.session_judul, s.session_deskripsi, s.session_hari, s.narasumber, s.ukm_id, u.ukm_nama
                         FROM `session` s
                         LEFT JOIN ukm u ON u.ukm_id = s.ukm_id
                         WHERE s.ukm_id = ?
                         ORDER BY s.session_hari DESC"
                    );
                    $sesStmt->bind_param("i", $ketuaUkmId);
                    $sesStmt->execute();
                    $sesResult = $sesStmt->get_result();
                }

                if (!$sesResult) die("Query failed: " . $conn->error);

                $rowCount = 0;
                while ($row = $sesResult->fetch_assoc()) {
                    $rowCount++;
                    $isSessionMilikKetua = $isAdmin || ($row['ukm_id'] == $ketuaUkmId);
                    echo "<tr>
                        <td>" . htmlspecialchars($row['session_id']) . "</td>
                        <td>" . htmlspecialchars($row['session_judul']) . "</td>
                        <td>" . htmlspecialchars($row['session_deskripsi']) . "</td>
                        <td>" . date('d-m-Y', strtotime($row['session_hari'])) . "</td>
                        <td>" . htmlspecialchars($row['narasumber']) . "</td>
                        <td>" . htmlspecialchars($row['ukm_nama'] ?? '-') . "</td>
                        <td>";

                    if ($isSessionMilikKetua) {
                        echo "<a class=\"btn btn-primary btn-sm mb-1\" href=\"edit_session.php?id={$row['session_id']}\"> Edit </a> "
                           . "<a class=\"btn btn-danger btn-sm mb-1\" href=\"delete_session.php?id={$row['session_id']}\" onclick=\"return confirm('Yakin ingin hapus session ini?')\"> Hapus </a>";
                    } else {
                        echo "<span class=\"text-muted\" style=\"font-size:12px;\">—</span>";
                    }

                    echo "</td></tr>";
                }

                if ($rowCount === 0) {
                    echo '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada session.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        const dropdown = document.querySelector(".dropdown-user");
        const dropdownContent = document.querySelector(".dropdown-content");

        dropdownContent.style.cssText = "display:none; position:fixed; z-index:9999; background:white; min-width:120px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); overflow:hidden;";

        function positionDropdown() {
            const rect = dropdown.getBoundingClientRect();
            dropdownContent.style.top = (rect.bottom + 8) + "px";
            dropdownContent.style.right = (window.innerWidth - rect.right) + "px";
        }

        dropdown.addEventListener("click", function(e) {
            e.stopPropagation();
            if (dropdownContent.style.display === "none") {
                positionDropdown();
                dropdownContent.style.display = "block";
            } else {
                dropdownContent.style.display = "none";
            }
        });

        document.addEventListener("click", function(e) {
            if (!dropdown.contains(e.target)) dropdownContent.style.display = "none";
        });
    </script>
</body>
</html>
