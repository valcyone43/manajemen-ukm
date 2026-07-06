<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("location: index.php");
    exit();
}

// Total semua
$total_mahasiswa = $conn->query("SELECT COUNT(*) as total FROM mahasiswa")->fetch_assoc()['total'];
$total_ukm = $conn->query("SELECT COUNT(*) as total FROM ukm")->fetch_assoc()['total'];
$total_session = $conn->query("SELECT COUNT(*) as total FROM session")->fetch_assoc()['total'];

// Tambahan bulan ini
$tambah_mahasiswa = $conn->query("SELECT COUNT(*) as total FROM mahasiswa WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetch_assoc()['total'];
$tambah_ukm = $conn->query("SELECT COUNT(*) as total FROM ukm WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetch_assoc()['total'];
$tambah_session = $conn->query("SELECT COUNT(*) as total FROM session WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetch_assoc()['total'];

// Ambil data user login
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE mahasiswa_email = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("location: index.php");
    exit();
}

$userRole = $user['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../style/admin.css">
</head>

<body>

    <nav class="side-navbar">
        <div class="side-navbar-menu">
            <a href="home.php">Home</a>
            <a href="list_ukm.php">List UKM</a>
            <?php if ($user['role'] == 'admin' || $user['role'] == 'ketua') { ?>
                <a href="session.php">Session</a>
            <?php } ?>
            <a href="user_data.php"><?= $user['role'] == 'admin' ? 'User' : 'Profile' ?></a>
        </div>
    </nav>

    <nav class="main-navbar">
        <div class="navbar-title"><img src="../img/logouika.png" alt="Logo" class="logo-navbar">
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
          <div class=images>
        <img src="../img/tes.png" alt="Dashboard Image" class="dashboard-image">
    </div>
        <?php
        if ($userRole == 'admin') {
            echo '<h1>Welcome to Admin Dashboard</h1>
    <p>Halo Admin 👋</p>';

        } elseif ($userRole == 'ketua') {
            echo '<h1>Welcome to Ketua Dashboard</h1>
    <p>Halo ' . htmlspecialchars($user['mahasiswa_nama']) . ' (Ketua) 👋</p>';

        } elseif ($userRole == 'user') {
            echo '<h1>Welcome to User Dashboard</h1>
    <p>Halo ' . htmlspecialchars($user['mahasiswa_nama']) . ' 👋</p>';

        } else {
            echo '<h1>Welcome to Dashboard</h1>';
        }
        ?>
    </div>

    <div class="dashboard-content">
        <div class="stats-row">

            <div class="stat-card">
                <div class="stat-label"><i class="ti ti-users"></i> Total User</div>
                <div class="stat-val"><?php echo $total_mahasiswa; ?></div>
                <div class="stat-sub">
                    <span class="badge-up">+<?php echo $tambah_mahasiswa; ?></span> bulan ini
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label"><i class="ti ti-flag"></i> UKM Aktif</div>
                <div class="stat-val"><?php echo $total_ukm; ?></div>
                <div class="stat-sub">
                    <span class="badge-up">+<?php echo $tambah_ukm; ?></span> bulan ini
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label"><i class="ti ti-calendar-event"></i> Total Session</div>
                <div class="stat-val"><?php echo $total_session; ?></div>
                <div class="stat-sub">
                    <span class="badge-up">+<?php echo $tambah_session; ?></span> bulan ini
                </div>
            </div>

        </div>
    </div>
  
    <script>
        const dropdown = document.querySelector(".dropdown-user");

        dropdown.addEventListener("click", function (e) {
            this.classList.toggle("active");
        });

        // optional: klik luar untuk nutup
        document.addEventListener("click", function (e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove("active");
            }
        });
    </script>

</body>

</html>