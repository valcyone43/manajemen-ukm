<?php
ob_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$host = "localhost";
$username = "root";
$password = "";
$dbname = "manajemen_ukm";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirect($url) {
    if (headers_sent($file, $line)) {
        $msg = "Redirect failed — headers already sent in $file:$line";
        error_log($msg);
        echo "<pre>" . htmlspecialchars($msg) . "</pre>";
        exit();
    }
    header("Location: " . $url);
    exit();
}