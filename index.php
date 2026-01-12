<?php
include "functions.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
   
    <title>Deteksi Debu Gunung Berapi</title>
    <style>
    table, th, tr,td {
        border: 1px solid black;
        border-collapse: collapse;
        padding:10px;
        text-align: center;
    }
    
    textarea{
        width: 500px;
        height: 50px;
        font-size: 20px
    }
    .map-container { height: 300px; width: 100%; margin-top: 10px; border: 1px solid #ccc; }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <h1>Input Kalimat Bahasa Indoenesia Deteksi Debu Gunung Berapi</h1>
    <form action="" method="post">
        <textarea name="kalimat" autofocus><?= isset($_POST['kalimat']) ? $_POST['kalimat'] : "" ?></textarea>
        <br> <br>
        <input type="submit" name ="scanner" value="Scanner">
        <input type="submit" name ="token" value="Token">
        <input type="submit" name ="parsing" value="Parsing">
        <input type="submit" name ="bersih" value="Bersih">
        <br>
    </form>
    
    <?php
    if(isset($_POST['scanner'])){
        scanner_view($_POST['kalimat']);
    }

    if(isset($_POST['token'])){
        token_view($_POST['kalimat']);
    }

    if(isset($_POST['parsing'])){
        parsing_view($_POST['kalimat']);
    }

    if(isset($_POST['bersih'])){
        header("Refresh:0");
    }
    ?>
</body>
</html>