<?php
session_start();
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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../style/user.css">
</head>

<body>
    <nav class="side-navbar">
        <div class="side-navbar-menu">
            <a href="home.php">Home</a>
            <a href="list_ukm.php">List UKM</a>
            <?php if ($user['role'] == 'admin' || $user['role'] == 'ketua') { ?>
                <a href="session.php">Session</a>
            <?php } ?>
            <a href="User_data.php"><?= $user['role'] == 'admin' ? 'User' : 'Profile' ?></a>
        </div>
    </nav>


    <nav class="main-navbar">
        <div class="navbar-title">Dashboard</div>
        <div class="navbar-menu">
            <a href="index.php" class="logout-btn">Logout</a>
        </div>
    </nav>


    <div class="dashboard-header">
        <h1>Welcome to User Dashboard</h1>
        <p>Halo, <?= htmlspecialchars($user['mahasiswa_nama']) ?> 👋</p>
    </div>


    <div class="dashboard-content">

    </div>

</body>

</html>