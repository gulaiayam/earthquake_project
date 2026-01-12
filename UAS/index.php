<?php
include "functions.php";

        if(isset($_POST['exe'])){
            $value = $_POST["area"];
        }
        
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h2>Input Commands</h2>

    <form action="" method="post">
        <textarea name="area" rows="15" cols="100" id=""><?php echo $value;?></textarea>
        <br>
        <input type="submit" name ="exe" value="Execute">
        <input type="submit" name ="refresh" value="Reset">
        
    </form>

    <?php
        if(isset($_POST['exe'])){
            show_table(exe($_POST['area']));
        }
        if(isset($_POST['refresh'])){
            header("Refresh:0");
        }
    ?>

</body>
</html>



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
        font-size: 20px;
    }
</style>

