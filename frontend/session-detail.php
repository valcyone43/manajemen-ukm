<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}

$email = $_SESSION['email'];

// Ambil data user + ukm_id yang sudah didaftarkan di profil
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

$userUkmId      = $user['ukm_id'] ?? null;   // null = belum daftar UKM
$isAdmin        = $user['role'] === 'admin';
$isKetua        = $user['role'] === 'ketua';
$isUserBiasa    = $user['role'] === 'user';
$requestedUkmId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Apakah halaman ini adalah UKM milik user yang login?
$isMyUkm = ($userUkmId !== null && $requestedUkmId == $userUkmId);

// Ambil detail UKM yang diminta
$ukm = null;
if ($requestedUkmId) {
    $ukmStmt = $conn->prepare("SELECT ukm_id, ukm_nama, ukm_slogan, ukm_nopengurus FROM ukm WHERE ukm_id = ?");
    $ukmStmt->bind_param("i", $requestedUkmId);
    $ukmStmt->execute();
    $ukm = $ukmStmt->get_result()->fetch_assoc();
    $ukmStmt->close();
}

if (!$ukm) {
    header("Location: list_ukm.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title><?= htmlspecialchars($ukm['ukm_nama']) ?> — Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../style/session.css">
    <style>
        .ukm-info-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 28px 32px;
            margin-bottom: 32px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ukm-info-card h2 {
            font-size: 26px;
            font-weight: 700;
            color: #106156;
            margin: 0 0 4px;
        }
        .ukm-info-card .slogan {
            font-size: 15px;
            color: #555;
            font-style: italic;
        }
        .ukm-info-card .contact {
            font-size: 14px;
            color: #333;
        }
        .ukm-info-card .contact span {
            font-weight: 600;
        }

        /* Badge member */
        .badge-member {
            display: inline-block;
            background: #eaf3de;
            color: #3b6d11;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
        }
        .badge-guest {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
        }

        /* Section header session */
        .session-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Alert daftar dulu */
        .alert-daftar {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            color: #5d4037;
        }
        .alert-daftar svg {
            flex-shrink: 0;
            margin-top: 2px;
        }
        .alert-daftar a {
            color: #106156;
            font-weight: 600;
        }

        /* Lock overlay untuk UKM lain */
        .session-locked {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            color: #6c757d;
        }
        .session-locked svg {
            margin-bottom: 10px;
            opacity: 0.4;
        }
        .session-locked p {
            margin: 0;
            font-size: 14px;
        }
    </style>
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

    <div class="dashboard-content">

<div class="ukm-info-card">
    <div>
        <h2><?= htmlspecialchars($ukm['ukm_nama']) ?></h2>
        
        <?php if ($isAdmin): ?>
            <span class="badge-member">Administrator</span>

        <?php elseif ($isKetua): ?>
            <?php if ($isMyUkm): ?>
                <span class="badge-member" style="background: #e0f2fe; color: #0369a1;">✓ Ketua UKM</span>
            <?php else: ?>
                <span class="badge-guest">Anda Bukan Ketua UKM ini</span>
            <?php endif; ?>

        <?php elseif ($isUserBiasa): ?>
            <?php if ($isMyUkm): ?>
                <span class="badge-member">✓ Anggota UKM ini</span>
            <?php else: ?>
                <span class="badge-guest">Bukan anggota</span>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
    
    <?php if (!empty($ukm['ukm_slogan'])): ?>
        <div class="slogan">"<?= htmlspecialchars($ukm['ukm_slogan']) ?>"</div>
    <?php endif; ?>
    <?php if (!empty($ukm['ukm_nopengurus'])): ?>
        <div class="contact">📞 <span>No. Pengurus:</span> <?= htmlspecialchars($ukm['ukm_nopengurus']) ?></div>
    <?php endif; ?>
</div>


        <div class="session-section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
            </svg>
            Jadwal Session
        </div>

<?php if (!$isAdmin && empty($userUkmId)): ?>
    <div class="alert-daftar">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#f59e0b" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        <div>
            <strong>Anda belum terdaftar di UKM manapun.</strong><br>
            Daftarkan diri terlebih dahulu melalui <a href="edit_profiledata.php">halaman Profile</a> untuk dapat mengakses fitur session.
        </div>
    </div>

<?php elseif ($isAdmin || $isMyUkm): ?>
    <table border="1" class="user table">
        <thead>
            <tr>
                <th>No</th>
                <th>Session Title</th>
                <th>Description</th>
                <th>Tanggal</th>
                <th>Speaker</th>
                <?php if ($isAdmin || ($isKetua && $isMyUkm)): ?>
                    <th>Aksi</th> 
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $sesStmt = $conn->prepare("SELECT session_id, session_judul, session_deskripsi, session_hari, narasumber FROM `session` WHERE ukm_id = ?");
            $sesStmt->bind_param("i", $requestedUkmId);
            $sesStmt->execute();
            $sesResult = $sesStmt->get_result();
            $rowCount = 0;
            
            while ($row = $sesResult->fetch_assoc()) {
                $rowCount++;
                echo "<tr>
                    <td>" . $rowCount . "</td> 
                    <td>" . htmlspecialchars($row['session_judul']) . "</td>
                    <td>" . htmlspecialchars($row['session_deskripsi']) . "</td>
                    <td>" . date('d-m-Y', strtotime($row['session_hari'])) . "</td>
                    <td>" . htmlspecialchars($row['narasumber']) . "</td>";
                
                
                if ($isAdmin || ($isKetua && $isMyUkm)) {
                    echo "<td>
                        <div class=\"d-flex flex-wrap gap-1\">
                            <a class=\"btn btn-primary btn-sm\" href=\"edit_session.php?id={$row['session_id']}\"> Edit </a>
                            <a class=\"btn btn-danger btn-sm\" href=\"delete_session.php?id={$row['session_id']}\" onclick=\"return confirm('Yakin ingin hapus session ini?')\"> Hapus </a>
                        </div>
                    </td>";
                }
                echo "</tr>";
            }
            
            if ($rowCount === 0) {
                $totalColspan = ($isAdmin || ($isKetua && $isMyUkm)) ? 6 : 5;
                echo "<tr><td colspan=\"{$totalColspan}\" class=\"text-center text-muted py-3\">Belum ada session untuk UKM ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>

<?php elseif ($isKetua && !$isMyUkm): ?>
    <table border="1" class="user table">
        <thead>
            <tr>
                <th>No</th>
                <th>Session Title</th>
                <th>Description</th>
                <th>Tanggal</th>
                <th>Speaker</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sesStmt = $conn->prepare("SELECT session_id, session_judul, session_deskripsi, session_hari, narasumber FROM `session` WHERE ukm_id = ?");
            $sesStmt->bind_param("i", $requestedUkmId);
            $sesStmt->execute();
            $sesResult = $sesStmt->get_result();
            $rowCount = 0;
            while ($row = $sesResult->fetch_assoc()) {
                $rowCount++;
                echo "<tr>
                    <td>" . $rowCount . "</td>
                    <td>" . htmlspecialchars($row['session_judul']) . "</td>
                    <td>" . htmlspecialchars($row['session_deskripsi']) . "</td>
                    <td>" . date('d-m-Y', strtotime($row['session_hari'])) . "</td>
                    <td>" . htmlspecialchars($row['narasumber']) . "</td>
                </tr>";
            }
            if ($rowCount === 0) {
                echo '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada session untuk UKM ini.</td></tr>';
            }
            ?>
        </tbody>
    </table>

<?php elseif ($isUserBiasa && !$isMyUkm): ?>
    <div class="session-locked">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#adb5bd" viewBox="0 0 16 16">
            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
        </svg>
        <p><strong>Jadwal session hanya tersedia untuk anggota UKM ini.</strong></p>
        <p class="mt-1">Anda terdaftar di UKM lain. Untuk melihat session, buka detail UKM Anda.</p>
    </div>

<?php endif; ?>

        <div class="mt-4">
            <a href="list_ukm.php" class="btn btn-secondary btn-sm">← Kembali ke List UKM</a>
        </div>

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
            dropdownContent.style.display = dropdownContent.style.display === "none" ? (positionDropdown(), "block") : "none";
        });

        document.addEventListener("click", function(e) {
            if (!dropdown.contains(e.target)) dropdownContent.style.display = "none";
        });
    </script>
</body>
</html>
