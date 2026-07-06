]<?php

require_once 'config.php';

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $sql = "DELETE FROM ukm WHERE ukm_id = '$id'";

    if ($conn->query($sql) === TRUE) {

        header("Location: list_ukm.php");
        exit();

    } else {

        echo "Gagal menghapus data: " . $conn->error;

    }
} else {

    echo "ID tidak ditemukan";

}
?>
