<?php
session_start();
if (!isset($_SESSION['email'])) {
    redirect('index.php');
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
    <title>Ketua Dashboard</title>
    <link rel="stylesheet" href="../style/ketua.css">
</head>

<body>
    <nav class="side-navbar">
        <div class="side-navbar-menu">
            <a href="home.php">Home</a>
            <a href="list_ukm.php">List UKM</a>
            <a href="session.php">Session</a>
            <a href="User_data.php">Profile</a>
        </div>
    </nav>


    <nav class="main-navbar">
        <div class="navbar-title">
            <img src="../img/logouika.png" alt="Logo" class="logo-navbar">
            <span>UKM Universitas IBN Khaldun</span>
        </div>
        <div class="dropdown-content">
            <a href="logout.php">Logout</a>
        </div>
    </nav>


    <div class="dashboard-header">
        <h1>Welcome to Ketua Dashboard</h1>
        <p>Oui Ketua what's up?</p>
    </div>


    <div class="dashboard-content">

    </div>

</body>

</html>