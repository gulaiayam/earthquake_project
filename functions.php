<?php
include "conn.php";

$katatanya = array("berapa", "dimana", "kapan", "tampilkan");
$kata_tidak_diabaikan = array("lokasi","area", "info", "terjadi", "posisi", "pada", "gunung", "terkini", "pergerakan", "di", "dari", "yang", "antara", "waktu", "pada","vulkanik", "abu");
$operator = ["=",">","<","LIKE"];
$mapAtribut = [
    "seluruh" => "*",
    "nama gunung" => "NamaGunung",
    "lokasi" => "NamaGunung",
    "lokasi gunung" => "NamaGunung", 
    "posisi" => "PosisiGunung",
    "posisi gunung" => "PosisiGunung",
    "area abu" => "AreaAbu",
    "ketinggian abu" => "Ketinggian_Meter",
    "ketinggian awan" => "Ketinggian_Meter",
    "pergerakan abu" => "PergerakanAbu",
    "intensitas abu" => "IntensitasAbu",
    "waktu observasi" => "ObsWaktu",
    "tanggal" => "Transmisi_WIB",
    "waktu dikeluarkan" => "Transmisi_WIB",
    "waktu mulai" => "ValidMulai_WIB"
];
$arah_map = [
    "N"  => "Utara",
    "NW" => "Barat Laut",
    "NE" => "Timur Laut",
    "E"  => "Timur",
    "SE" => "Tenggara",
    "SW" => "Barat Daya",
    "S"  => "Selatan",
    "W"  => "Barat"
];
$kata_hubung = ["yang", "dengan", "untuk", "dimana",'pada'];

function scanner($text){
    $text = strtolower($text);
    $text = preg_replace("/[^a-z0-9\s-]/", " ", $text);
    return preg_split("/\s+/", trim($text));
}

function scanner_view($text){
    $words = scanner($text);

    echo "<h3>Hasil Scanner</h3>";
    echo "<table>
            <tr><th>Index</th><th>Kata</th></tr>";

    foreach($words as $i => $word){
        echo "<tr>
                <td>".($i+1)."</td>
                <td>$word</td>
              </tr>";
    }
    echo "</table>";
}

function cekKataTidakDiabaikan($kata){
    global $katatanya, $kata_tidak_diabaikan;

    if(in_array($kata, $katatanya)) return 1;
    if(in_array($kata, $kata_tidak_diabaikan)) return 1;

    return 0;
}

function token_view($text){
    $words = scanner($text);

    echo "<h3>Hasil Token</h3>";
    echo "<table>
            <tr><th>Index</th><th>Kata</th><th>Token</th></tr>";

    foreach($words as $i => $word){
        $token = cekKataTidakDiabaikan($word);
        echo "<tr>
                <td>".($i+1)."</td>
                <td>$word</td>
                <td>$token</td>
              </tr>";
    }
    echo "</table>";
}

function solve_tampilkan($select_cols, $where_conds){
    $str_select = "";

    // Jika User minta "seluruh" -> SELECT *
    if(in_array("*", $select_cols)){
        $str_select = "*";
    }
    // Jika User minta atribut spesifik -> SELECT col1, col2
    elseif(!empty($select_cols)){
        // Pastikan ID ikut agar tampilan rapi
        if(!in_array("DataID", $select_cols)){
            array_unshift($select_cols, "DataID");
        }
        $str_select = implode(", ", $select_cols);
    }
    // Error jika kosong
    else {
        return ["status" => "error", "msg" => "Tidak ada atribut yang dikenali untuk ditampilkan."];
    }

    // Build Where
    $str_where = "";
    if(!empty($where_conds)){
        $arr_conds = [];
        foreach($where_conds as $cond){
            $arr_conds[] = $cond['col'] . " LIKE '%" . $cond['val'] . "%'";
        }
        $str_where = "WHERE " . implode(" AND ", $arr_conds);
    }

    $sql = "SELECT $str_select FROM sigmet_va $str_where";
    return ["status" => "success", "sql" => $sql];
}

function solve_dimana($select_cols, $where_conds){
    // Jika tidak ada target
    if(empty($select_cols)){
        return [
            "status" => "error",
            "msg" => "Setelah kata 'dimana' harus ada atribut yang dikenali."
        ];
    }

    // Ambil atribut pertama
    $str_select = $select_cols[0];

    // Build WHERE
    $str_where = "";
    if(!empty($where_conds)){
        $arr_conds = [];
        foreach($where_conds as $cond){
            $arr_conds[] = $cond['col'] . " LIKE '%" . $cond['val'] . "%'";
        }
        $str_where = "WHERE " . implode(" AND ", $arr_conds);
    }

    $sql = "SELECT $str_select FROM sigmet_va $str_where";
    return ["status" => "success", "sql" => $sql];
}


function parsing_view($text){
    global $con, $mapAtribut, $arah_map, $kata_hubung;

    $words = scanner($text);
    $count = count($words);

    // Variable State
    $select_cols = [];
    $where_conds = [];
    $rule_type = ""; 
    $fase = "";    
    $skip_logic = false;

    // TABEL PARSING
    echo "<h3>Tabel Parsing</h3>";
    echo "<table border='1'>
            <tr><th>Index</th><th>Kata</th><th>Token</th><th>Parsing</th></tr>";

    foreach($words as $i => $word){
        $token = cekKataTidakDiabaikan($word);
        $keterangan = ""; 

        if($skip_logic){
            $skip_logic = false; 
        } 
        else {
            $is_attribute = false;
            $db_field = "";
            
            // Cek 2 kata (Lookahead)
            if($i + 1 < $count){
                $two_words = $word . " " . $words[$i+1];
                if(array_key_exists($two_words, $mapAtribut)){
                    $is_attribute = true;
                    $db_field = $mapAtribut[$two_words];
                    $skip_logic = true; 
                }
            }
            // Cek 1 kata
            if(!$is_attribute && array_key_exists($word, $mapAtribut)){
                $is_attribute = true;
                $db_field = $mapAtribut[$word];
            }

            // DETEKSI RULE DI KATA PERTAMA
            if($i == 0){
                if($word == "tampilkan"){
                    $rule_type = "tampilkan";
                    $fase = "TARGET";
                    $keterangan = "aturan(1)";
                }
                elseif($word == "dimana"){
                    $rule_type = "dimana";
                    $fase = "TARGET";
                    $keterangan = "aturan(2)";
                }
            }
            
            // LOGIKA PENGUMPULAN DATA (General) 
            else if($rule_type != ""){
                
                // Cek Transisi ke WHERE
                if(in_array($word, $kata_hubung)){
                    $fase = "KONDISI";
                }

                if($fase == "TARGET"){
                    if($word == "seluruh"){ 
                        $select_cols[] = "*"; 
                    }
                    elseif($is_attribute){
                        // Masukkan ke penampung kolom target
                        if(!in_array($db_field, $select_cols)) $select_cols[] = $db_field;
                    }
                }
                elseif($fase == "KONDISI"){
                    if($is_attribute){
                        // Mulai kondisi baru
                        $where_conds[] = ["col" => $db_field, "val" => ""];
                    }
                    elseif($token == 0){
                        // Isi value kondisi
                        if(!empty($where_conds)){
                            $idx = count($where_conds) - 1;
                            $old = $where_conds[$idx]["val"];
                            $where_conds[$idx]["val"] = ($old == "" ? $word : $old . " " . $word);
                        }
                    }
                }
            }
        }
        echo "<tr><td>$i</td><td>$word</td><td>$token</td><td>$keterangan</td></tr>";
    }
    echo "</table><br>";

    // DISPATCHER: PANGGIL FUNGSI SESUAI RULE
    $result = ["status" => "none"];

    if($rule_type == "tampilkan"){
        $result = solve_tampilkan($select_cols, $where_conds);
    } 
    elseif($rule_type == "dimana"){
    // VALIDASI
    if(empty($select_cols)){
        echo "<p style='color:red;'><b>Error:</b> Tidak ada atribut yang dikenali.</p>";
        return;
    }
    $result = solve_dimana($select_cols, $where_conds);
    }

    // Cek Hasil
    if($result['status'] == "error"){
        echo "<p style='color:red;'><b>Error:</b> " . $result['msg'] . "</p>";
        return;
    }
    elseif($result['status'] == "none"){
        return;
    }

    // Eksekusi SQL
    $sql = $result['sql'];
    echo "<b>Query: </b> $sql <br><br>";
    
    if(isset($con) && $con){
        $q = mysqli_query($con, $sql);
        if($q){
            echo "<h3>Hasil Pencarian</h3>";
            if(mysqli_num_rows($q) > 0){
                while($row = mysqli_fetch_assoc($q)){
                    echo "<div style='border:1px solid #ccc; padding:15px; margin-bottom:10px; background:#f9f9f9; line-height: 1.6;'>";
                
                    
                    if(isset($row['DataID'])) echo "<b>ID : " . $row["DataID"] . "</b><br>";
                    if(isset($row['Transmisi_WIB'])) echo "Data ini dipublikasikan pada tanggal : " . $row['Transmisi_WIB'] . "<br>";
                    if(isset($row['ValidMulai_WIB'])) echo "Data ini valid pada " . $row['ValidMulai_WIB'] . "<br>";
                    if(isset($row['ValidAkhir_WIB'])) echo "Data ini valid sampai " . $row['ValidAkhir_WIB'] . "<br>";
                    if(isset($row['AreaPenerbangan'])) echo "Data ini dipublikasikan pada stasiun meteorologi yang terletak di " . $row['AreaPenerbangan'] . "<br>";
                    if(isset($row['NamaGunung'])) echo "Gunung yang mengeluarkan abu vulkanik adalah Gunung " . $row['NamaGunung'] . "<br>";
                    if(isset($row['PosisiGunung'])) echo "Posisi gunung ini berada di koordinat " . $row['PosisiGunung'] . "<br>";
                    if(isset($row['ObsWaktu'])) echo "Abu vulkanik diobservasikan pada pukul " . $row['ObsWaktu'] . "<br>";
                    if(isset($row['AreaAbu'])) echo "Area abu vulkanik ini terletak pada titik-titik koordinat berikut: " . $row['AreaAbu'] . "<br>";
                    if(isset($row['Ketinggian_Meter'])) echo "Abu vulkanik ini memiliki ketinggian " . $row['Ketinggian_Meter'] . " meter dari permukaan bumi<br>";

                    if(isset($row['PergerakanAbu'])){
                        $raw = $row['PergerakanAbu'];
                        $arah_indo = $raw;
                        $kecepatan = "-";
                        if(preg_match('/Arah\s+([A-Z]+)\s+(\d+)\s+knot/i', $raw, $m)){
                            $code = strtoupper($m[1]);
                            $arah_indo = isset($arah_map[$code]) ? $arah_map[$code] : $code;
                            $kecepatan = $m[2] . " knot";
                        }
                        echo "Perkiraan abu vulkanik ini akan bergerak ke arah " . $arah_indo . "<br>";
                        if($kecepatan != "-") echo "Kecepatan abu vulkanik sebesar " . $kecepatan . "<br>";
                    }

                    if(isset($row['IntensitasAbu'])) echo "Perkiraan intensitas abu vulkanik adalah: " . $row['IntensitasAbu'] . "<br>";

                    echo "</div>";
                }
            } else {
                echo "<p>Data tidak ditemukan.</p>";
            }
        }
    }
}
?>