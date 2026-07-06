<?php

session_start();
require_once 'config.php';

if (isset($_POST['submit'])) {

    $nama       = $_POST['ukm_nama'];
    $slogan     = $_POST['ukm_slogan'];
    $nopengurus = $_POST['ukm_nopengurus'];

    $sql = "INSERT INTO ukm (
                ukm_nama,
                ukm_slogan,
                ukm_nopengurus
            )
            VALUES (
                '$nama',
                '$slogan',
                '$nopengurus'
            )";

    if ($conn->query($sql) === TRUE) {

        header("Location: list_ukm.php");
        exit();

    } else {

        echo "Gagal tambah data: " . $conn->error;

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tambah UKM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet" href="../style/admin.css">
    

</head>

<body>
<style>
    body{
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    }
</style>
<div class="container mt-5 bg-light p-4 rounded shadow">

    <h2 class="pb-3 pt-3 fw-bold">TAMBAH DATA UKM</h2>

    <form method="POST">

        <div class="mb-3">

            <label class="form-label">
                Nama UKM
            </label>

            <input type="text"
                   name="ukm_nama"
                   class="form-control"
                   required>

        </div>

        <div class="mb-3">

            <label class="form-label">
                Slogan UKM
            </label>

            <input type="text"
                   name="ukm_slogan"
                   class="form-control"
                   required>

        </div>

        <div class="mb-3">

            <label class="form-label">
                No Pengurus
            </label>

            <input type="text"
                   name="ukm_nopengurus"
                   class="form-control"
                   required>

        </div>

        <button type="submit"
                name="submit"
                class="btn btn-success">

            Simpan

        </button>

        <a href="list_ukm.php"
           class="btn btn-secondary">

            Kembali

        </a>

    </form>

</div>

</body>
</html>