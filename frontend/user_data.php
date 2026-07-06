<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email'])) {
    redirect('index.php');
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE mahasiswa_email = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ==== HANDLE GANTI ROLE (hanya admin) ====
if ($user['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $targetEmail = $_POST['target_email'];
    $newRole = $_POST['new_role'];

    // whitelist supaya role tidak bisa diisi sembarangan lewat request manual
    $allowedRoles = ['user', 'ketua'];
    if (in_array($newRole, $allowedRoles, true)) {
        $updateStmt = $conn->prepare("UPDATE mahasiswa SET role = ? WHERE mahasiswa_email = ? AND role != 'admin'");
        $updateStmt->bind_param("ss", $newRole, $targetEmail);
        $updateStmt->execute();
        $updateStmt->close();
    }

    // Post/Redirect/Get supaya tidak resubmit form saat halaman di-refresh
    redirect('user_data.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <title>User Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../style/user_data.css">
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
        <h1>User Information</h1>
        <p>Profile Management</p>
    </div>


    <div class="dashboard-content">
        <?php
        if (isset($user) && $user['role'] == 'admin') {
            echo '<h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Daftar User yang Terdaftar</h1>';
        } elseif (isset($user) && $user['role'] == 'ketua') {
            echo '<h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Selamat datang Ketua</h1>';
        } elseif (isset($user) && $user['role'] == 'user') {
            echo '<h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Masukan data diri anda dengan benar</h1>';
        } else {
            echo '<h1 class="dashboard-content-title bg-light p-3 rounded shadow fs-4 mb-0">Masukan data diri anda dengan benar</h1>';
        }
        ?>
    
        <br>
        <table border="1" class="user table">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT mahasiswa_nama, mahasiswa_email, role FROM mahasiswa";
                if ($user['role'] !== 'admin') {
                    $sql = "SELECT mahasiswa_nama, mahasiswa_email, role FROM mahasiswa WHERE mahasiswa_email = '" . $conn->real_escape_string($_SESSION['email']) . "'";
                }

                $result = $conn->query($sql);

                if (!$result) {
                    die("Query failed: " . $conn->error);
                }

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>\n";
                    echo "<td>" . htmlspecialchars($row['mahasiswa_nama']) . "</td>\n";
                    echo "<td>" . htmlspecialchars($row['mahasiswa_email']) . "</td>\n";
                    echo "<td>" . htmlspecialchars($row['role']) . "</td>\n";
                    echo "<td>";

                    if ($user['role'] === 'admin') {
                        // Admin bisa pilih role user lain lewat dropdown (kecuali admin lain)
                        if ($row['role'] !== 'admin') {
                            $roleOptions = ['user' => 'User', 'ketua' => 'Ketua'];
                            echo "<form method='POST' class='d-flex gap-2 align-items-center' style='display:flex;'>";
                            echo "<input type='hidden' name='target_email' value='" . htmlspecialchars($row['mahasiswa_email']) . "'>";
                            echo "<select name='new_role' class='form-select form-select-sm' style='width:auto;'>";
                            foreach ($roleOptions as $value => $text) {
                                $selected = ($row['role'] === $value) ? 'selected' : '';
                                echo "<option value='" . $value . "' $selected>" . $text . "</option>";
                            }
                            echo "</select>";
                            echo "<button type='submit' name='change_role' class='btn btn-primary btn-sm'>Simpan</button>";
                            echo "</form>";
                        } else {
                            echo "-";
                        }
                    } elseif ($row['mahasiswa_email'] === $_SESSION['email']) {
                        echo "<a class=\"btn btn-primary btn-sm\" href=\"edit_profiledata.php\"> Edit </a>";
                    }

                    echo "</td>\n";
                    echo "</tr>\n";
                }
                ?>
        </table>
    </div>
        <script>
        const dropdown = document.querySelector(".dropdown-user");
        const dropdownContent = document.querySelector(".dropdown-content");

        // Force hide saat halaman load
        dropdownContent.style.display = "none";

        dropdown.addEventListener("click", function (e) {
            e.stopPropagation();
            if (dropdownContent.style.display === "none") {
                dropdownContent.style.display = "block";
            } else {
                dropdownContent.style.display = "none";
            }
        });

        document.addEventListener("click", function (e) {
            if (!dropdown.contains(e.target)) {
                dropdownContent.style.display = "none";
            }
        });
    </script>
</body>

</html>