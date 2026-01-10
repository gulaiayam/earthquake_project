<?php
require "conn.php";

        if(isset($_POST['preview'])){
            $value = $_POST["rawdata"];
        }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input SIGMET Raw Data</title>
</head>
<body>

<h2>Input Raw SIGMET Data</h2>

<form method="post" action="">
    <textarea name="rawdata" rows="15" cols="100" required><?php echo $value;?></textarea><br><br>
    <button type="submit" name="preview">preview</button>
    <button type="submit" name="insert">insert</button>
</form>

</body>
</html>


<?php
$raw = strtoupper(trim($_POST['rawdata']));
$lines = preg_split("/\r\n|\n|\r/", $raw);


##FUNGSI BANTUAN

function utcToWib($day, $hour, $minute) {
    $dt = new DateTime("now", new DateTimeZone("UTC"));
    $dt->setDate($dt->format("Y"), $dt->format("m"), $day);
    $dt->setTime($hour, $minute);
    $dt->modify("+7 hours");
    return $dt->format("Y-m-d H:i:s");
}


##TRANSMISI

preg_match('/\b(\d{6})\b/', $lines[0], $m);
$transmisi_wib = utcToWib(
    substr($m[1],0,2),
    substr($m[1],2,2),
    substr($m[1],4,2)
);


##VALID TIME

preg_match('/VALID\s(\d{6})\/(\d{6})/', $raw, $v);
$valid_mulai = utcToWib(substr($v[1],0,2), substr($v[1],2,2), substr($v[1],4,2));
$valid_akhir = utcToWib(substr($v[2],0,2), substr($v[2],2,2), substr($v[2],4,2));


##AREA PENERBANGAN

preg_match('/(W\w{3})\s(.+?)\sFIR/', $raw, $f);
$area_penerbangan = $f[1]." ".ucwords(strtolower($f[2]));


##NAMA & POSISI GUNUNG

preg_match('/MT\s([A-Z\s]+?)\sPSN/', $raw, $g);
$nama_gunung = ucfirst(strtolower(trim($g[1])));

preg_match('/PSN\s(S\d{4}\sE\d{5})/', $raw, $p);
$posisi_gunung = $p[1];


##OBS WAKTU

preg_match('/OBS AT (\d{4})Z/', $raw, $o);
$obs = new DateTime("now", new DateTimeZone("UTC"));
$obs->setTime(substr($o[1],0,2), substr($o[1],2,2));
$obs->modify("+7 hours");
$obs_waktu = $obs->format("H:i:s");


##AREA ABU

preg_match('/OBS AT.*?Z\s(.+?)\sSFC\/FL/', $raw, $a);
$area_abu = trim($a[1]);


##KETINGGIAN

preg_match('/FL(\d{3})/', $raw, $k);
$ketinggian_meter = $k[1] * 30;


##PERGERAKAN

preg_match('/MOV\s(\w+)\s(\d+)KT/', $raw, $m);
$arah = [
    "N"=>"Utara","S"=>"Selatan","E"=>"Timur","W"=>"Barat",
    "NE"=>"Timur Laut","NW"=>"Barat Laut",
    "SE"=>"Tenggara","SW"=>"Barat Daya"
];
$pergerakan_abu = $arah[$m[1]]." ".$m[2]." knot";


##INTENSITAS

if (str_contains($raw, "NC"))
    {
        $intensitas = "Tidak berubah";
    } 
elseif (str_contains($raw, "INTSF")) 
    {
        $intensitas = "Menguat";
    }
elseif (str_contains($raw, "WKN")) 
    {
        $intensitas = "Melemah";
    }
else{
    $intensitas = "-";
    } 

##INSERT DATABASE
if(isset($_POST["insert"])){
    $stmt = $con->prepare("
    insert into sigmet_data (
        transmisi_wib, valid_mulai_wib, valid_akhir_wib,
        area_penerbangan, nama_gunung, posisi_gunung,
        obs_waktu, area_abu, ketinggian_meter,
        pergerakan_abu, intensitas_abu
    ) 
    values (?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssssssiss",
        $transmisi_wib,
        $valid_mulai,
        $valid_akhir,
        $area_penerbangan,
        $nama_gunung,
        $posisi_gunung,
        $obs_waktu,
        $area_abu,
        $ketinggian_meter,
        $pergerakan_abu,
        $intensitas
    );

    $stmt->execute();
}



echo "$transmisi_wib <br>";
echo "$valid_mulai <br>"; 
echo "$valid_akhir <br>";
echo "$area_penerbangan <br>";
echo "$nama_gunung <br>";
echo "$posisi_gunung <br>";
echo "$obs_waktu <br>";
echo "$area_abu <br>";
echo "$ketinggian_meter <br>";
echo "$pergerakan_abu <br>";
echo "$intensitas <br>";


