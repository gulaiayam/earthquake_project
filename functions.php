<?php
include "conn.php";

$katatanya = array("berapa", "dimana", "kapan", "tampilkan");
$kata_tidak_diabaikan = array("area", "info", "terjadi", "posisi", "pada", "gunung", "terkini", "bumi", "di", "dari", "yang", "antara", "berlokasi", "pada","vulkanik", "abu");
$operator = ["=",">","<","LIKE"];
$mapAtribut = [
    "seluruh" => "*",
    "nama gunung" => "NamaGunung",
    "posisi gunung" => "PosisiGunung",
    "area abu" => "AreaAbu",
    "ketinggian abu" => "Ketinggian_Meter",
    "ketinggian awan" => "Ketinggian_Meter",
    "pergerakan abu" => "PergerakanAbu",
    "intensitas abu" => "IntensitasAbu",
    "waktu observasi" => "ObsWaktu",
    "waktu dikeluarkan" => "Transmisi_WIB",
    "waktu valid" => "ValidMulai_WIB"
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
$kata_hubung = ["yang", "dengan", "untuk", "dimana"];

function scanner($text){
    $text = strtolower($text);
    $text = preg_replace("/[^a-z0-9\s]/", " ", $text);
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

function parsing_view($text){
    global $con, $mapAtribut, $arah_map, $kata_hubung;

    $words = scanner($text);
    $count = count($words);

    // Variabel logika SQL
    $select_cols = [];
    $where_conds = [];
    $is_rule_1 = false;      
    $fase = "";              
    $skip_logic = false;      

    echo "<h3>Tabel Parsing</h3>";
    echo "<table border='1'>
            <tr>
                <th>Index</th>
                <th>Kata</th>
                <th>Token</th>
                <th>Parsing</th>
            </tr>";

    foreach($words as $i => $word){
        $token = cekKataTidakDiabaikan($word);
        $keterangan_parsing = ""; 

        if($skip_logic){
            $skip_logic = false; 
        } 
        else {
            $is_attribute = false;
            $db_field = "";
            
            if($i + 1 < $count){
                $two_words = $word . " " . $words[$i+1];
                if(array_key_exists($two_words, $mapAtribut)){
                    $is_attribute = true;
                    $db_field = $mapAtribut[$two_words];
                    $skip_logic = true; 
                }
            }
            if(!$is_attribute && array_key_exists($word, $mapAtribut)){
                $is_attribute = true;
                $db_field = $mapAtribut[$word];
            }

            // ATURAN 1
            if($i == 0){
                if($word == "tampilkan"){
                    $is_rule_1 = true;
                    $fase = "TARGET"; 
                    $keterangan_parsing = "aturan(1)";
                }
            }
            else if($is_rule_1){
                if($fase == "TARGET"){
                    if($word == "seluruh"){ $select_cols[] = "*"; }
                    elseif($is_attribute){ if(!in_array("*", $select_cols)) $select_cols[] = $db_field; }
                    elseif(in_array($word, $kata_hubung)){ $fase = "KONDISI"; }
                }
                elseif($fase == "KONDISI"){
                    if($is_attribute){ $where_conds[] = ["col" => $db_field, "val" => ""]; }
                    elseif($token == 0){ 
                        if(!empty($where_conds)){
                            $last_idx = count($where_conds) - 1;
                            $old_val = $where_conds[$last_idx]["val"];
                            $where_conds[$last_idx]["val"] = ($old_val == "" ? $word : $old_val . " " . $word);
                        }
                    }
                }
            }
        }

        echo "<tr><td>$i</td><td>$word</td><td>$token</td><td>$keterangan_parsing</td></tr>";
    }
    echo "</table><br>";

    if($is_rule_1){
        $str_select = empty($select_cols) ? "*" : implode(", ", $select_cols);
        
        $str_where = "";
        if(!empty($where_conds)){
            $arr_conds = [];
            foreach($where_conds as $cond){
                $arr_conds[] = $cond['col'] . " LIKE '%" . $cond['val'] . "%'";
            }
            $str_where = "WHERE " . implode(" AND ", $arr_conds);
        }

        $sql = "SELECT * FROM sigmet_va $str_where"; // Force SELECT * agar data lengkap untuk narasi

        echo "<b>Query: </b> $sql <br><br>";
        
        if(isset($con) && $con){
            $q = mysqli_query($con, $sql);
            if($q){
                echo "<h3>Hasil Pencarian</h3>";
                
                if(mysqli_num_rows($q) > 0){
                    while($row = mysqli_fetch_assoc($q)){
                        
                        $raw_pergerakan = $row['PergerakanAbu'];
                        $arah_indo = "Tidak diketahui";
                        $kecepatan = "Tidak diketahui";

                        // Regex untuk mengambil Kode Arah (Huruf Besar) dan Angka (Speed)
                        // Mencari pola: Huruf Kapital (N/SE/dll) diikuti spasi lalu Angka
                        if(preg_match('/Arah\s+([A-Z]+)\s+(\d+)\s+knot/i', $raw_pergerakan, $matches)){
                            $kode_arah = strtoupper($matches[1]);
                            $nilai_speed = $matches[2];           
                            
                            // Translate Arah
                            if(isset($arah_map[$kode_arah])){
                                $arah_indo = $arah_map[$kode_arah];
                            } else {
                                $arah_indo = $kode_arah;
                            }
                            $kecepatan = $nilai_speed . " knot";
                        } else {
                            $arah_indo = $raw_pergerakan;
                            $kecepatan = "-";
                        }

                        echo "<div style='border:1px solid #ccc; padding:15px; margin-bottom:10px; background:#f9f9f9; line-height: 1.6;'>";
                        
                        echo "ID :".$row["DataID"]. "<br>";
                        echo "Data ini dipublikasikan pada tanggal : " . $row['Transmisi_WIB'] . "<br>";
                        echo "Data ini valid pada " . $row['ValidMulai_WIB'] . "<br>";
                        echo "Data ini valid sampai " . $row['ValidAkhir_WIB'] . "<br>";
                        echo "Data ini dipublikasikan pada stasiun meteorologi yang terletak di " . $row['AreaPenerbangan'] . "<br>";
                        echo "Gunung yang mengeluarkan abu vulkanik adalah Gunung " . $row['NamaGunung'] . "<br>";
                        echo "Posisi gunung ini berada di koordinat " . $row['PosisiGunung'] . "<br>";
                        echo "Abu vulkanik diobservasikan pada pukul " . $row['ObsWaktu'] . "<br>";
                        echo "Area abu vulkanik ini terletak pada titik-titik koordinat berikut: " . $row['AreaAbu'] . "<br>";
                        echo "Abu vulkanik ini memiliki ketinggian " . $row['Ketinggian_Meter'] . " meter dari permukaan bumi<br>";
                        echo "Perkiraan abu vulkanik ini akan bergerak ke arah " . $arah_indo . "<br>";
                        if($kecepatan != "-"){
                            echo "Kecepatan abu vulkanik sebesar " . $kecepatan . "<br>";
                        }
                        echo "Perkiraan intensitas abu vulkanik adalah: " . $row['IntensitasAbu'] . "<br>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Data tidak ditemukan.</p>";
                }
            }
        }
    }
}


// // function exe($text){
//     global $rule, $tokens, $place, $di, $pada, $time, $disaster,$keywords;

//     $result = explode(" ", $text);

//     echo "
//     <br><br>
//     <table>
//         <td>
//             Index
//         </td>
//         <td>
//             Word
//         </td>
//         <td>
//             Token
//         </td>
//         <td>
//             Parsing
//         </td>
//         ";

//     for($i = 0, $j = 1; $i < count($result); $i++ , $j++){
//         $token = 0;
//         $production = "-";

//         //disaster type
//         if(strtolower($result[$i]) == "gempa"){
//             $disaster = "gempa";
//         }

//         else if(strtolower($result[$i]) == "tsunami"){
//             $disaster = "tsunami";
//         }
        
//         for($z = 0;$z < count($tokens);$z++){
//             if(strtolower($result[$i]) == strtolower($tokens[$z])){
//                 $rule = $z+1;
//                 $token = 1;
//                 $concat = strval($z+1);
//                 $production = "Rule $concat";      
//             }
//         }

//         for($z = 0;$z < count($keywords);$z++){
//             if(strtolower($result[$i]) == strtolower($keywords[$z])){
//                 $token = 1;    
//             }
//         }


//         //for asking spesific
//         if(strtolower($result[$i]) == "di"){
//         $di = true;
//         $place = $result[$i+1];
//         }

//         else if(strtolower($result[$i]) == "pada"){
//         $pada = true;
//         $time = $result[$i+1];
//         } 

        

//         echo "
//         <tr/>
//             <td>
//                 $j
//             </td>
//             <td>
//                 $result[$i]
//             </td>
//             <td>
//                 $token
//             </td>
//             <td>
//                 $production
//             </td>
//         </tr>
//     ";
//     }

//     echo"</table> <br><br>";
    
// // }

// // function show_table(){
// //     global $con, $rule, $disaster, $di, $pada, $place, $time;
    
// //     //default "All atributes"
// //     if($rule == 1){
// //         if($disaster != "tsunami"){
// //             $query = mysqli_query($con,"select * from gempa");

// //         echo "
// //         <table>
// //             <td>
// //                 Index
// //             </td>
// //             <td>
// //                 Date
// //             </td>
// //             <td>
// //                 Coordinate
// //             </td>
// //             <td>
// //                 Latitude
// //             </td>
// //             <td>
// //                 Longitude
// //             </td>
// //             <td>
// //                 Maginitude
// //             </td>
// //             <td>
// //                 Depth
// //             </td>
// //             <td>
// //                 Location
// //             </td>
// //             <td>
// //                 Tsunami
// //             </td>";

// //         while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[coordinate]
// //                                 </td>
// //                                 <td>
// //                                     $row[latitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[longitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[tsunami]
// //                                 </td>
// //                             </tr>
// //                         ";
// //             }
// //         }
// //         else{
// //             $query = mysqli_query($con,"select * from gempa where tsunami = 'yes'");

// //             echo "
// //             <table>
// //                 <td>
// //                     Index
// //                 </td>
// //                 <td>
// //                     Date
// //                 </td>
// //                 <td>
// //                     Coordinate
// //                 </td>
// //                 <td>
// //                     Latitude
// //                 </td>
// //                 <td>
// //                     Longitude
// //                 </td>
// //                 <td>
// //                     Maginitude
// //                 </td>
// //                 <td>
// //                     Depth
// //                 </td>
// //                 <td>
// //                     Location
// //                 </td>
// //                 <td>
// //                     Tsunami
// //                 </td>";

// //             while ($row = mysqli_fetch_array($query)) {
// //                             echo "
// //                                 <tr/>
// //                                     <td>
// //                                         $row[Id]
// //                                     </td>
// //                                     <td>
// //                                         $row[date]
// //                                     </td>
// //                                     <td>
// //                                         $row[coordinate]
// //                                     </td>
// //                                     <td>
// //                                         $row[latitude]
// //                                     </td>
// //                                     <td>
// //                                         $row[longitude]
// //                                     </td>
// //                                     <td>
// //                                         $row[magnitude]
// //                                     </td>
// //                                     <td>
// //                                         $row[depth] Km
// //                                     </td>
// //                                     <td>
// //                                         $row[location]
// //                                     </td>
// //                                     <td>
// //                                         $row[tsunami]
// //                                     </td>
// //                                 </tr>
// //                             ";
// //                 }
// //             }
        
// //         echo "</table>";
// //     }
// //     //specific question
// //     else if($rule == 2){

// //             $time = strval($time);

// //             if($di == true && $pada == false && $disaster != "tsunami"){
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where location like '%$place%'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             }
// //             else if($di == true && $pada == false && $disaster == "tsunami"){
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where location like '%$place%' and date like '%$time%' and tsunami = 'yes'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     <td>
// //                         Tsunami
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                                 <td>
// //                                     $row[tsunami]
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             }


// //             if($di == false && $pada == true && $disaster != "tsunami"){
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where date like '%$time%'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             } 
// //             else if($di == false && $pada == true && $disaster == "tsunami"){
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where date like '%$time%' and tsunami = 'yes'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     <td>
// //                         Tsunami
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                                 <td>
// //                                     $row[tsunami]
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             }

// //             if($di == true && $pada == true && $disaster != "tsunami"){
                
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where location like '%$place%' and date like '%$time%'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             }
// //             else if($di == true && $pada == true && $disaster == "tsunami"){
// //                 $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where location like '%$place%' and date like '%$time%' and tsunami = 'yes'");

// //                 echo "
// //                 <table>
// //                     <td>
// //                         Index
// //                     </td>
// //                     <td>
// //                         Location
// //                     </td>
// //                     <td>
// //                         Date
// //                     </td>
// //                     <td>
// //                         Magnitude
// //                     </td>
// //                     <td>
// //                         Depth
// //                     </td>
// //                     <td>
// //                         Tsunami
// //                     </td>
// //                     ";

// //                 while ($row = mysqli_fetch_array($query)) {
// //                         echo "
// //                             <tr/>
// //                                 <td>
// //                                     $row[Id]
// //                                 </td>
// //                                 <td>
// //                                     $row[location]
// //                                 </td>
// //                                 <td>
// //                                     $row[date]
// //                                 </td>
// //                                 <td>
// //                                     $row[magnitude]
// //                                 </td>
// //                                 <td>
// //                                     $row[depth] Km
// //                                 </td>
// //                                 <td>
// //                                     $row[tsunami]
// //                                 </td>
// //                             </tr>
// //                         ";
// //                 }
// //             }

// //         echo "</table>";
// //     }
// //     else{
// //         echo "
// //         <br>
// //         Perintah Kurang Jelas.
// //         <br>
// //         Berikan Perintah seperti:
// //         <br>
// //         'Tampilkan gempa' atau 'Tampilkan tsunami'
// //         <br>
// //         Dan
// //         <br>
// //         'Apa ada gempa di Jakarta?'
// //         ";
// //     }

    
// // }


    // $select = "*";
    // $where = "";
    // // ===== RULE: tampilkan <atribut> =====
    // foreach($mapAtribut as $key => $column){
    //     if(strpos($kalimat, $key) !== false){
    //         $select = $column;
    //     }
    // }

    // // ===== RULE: kondisi (dengan | yang | untuk) =====
    // if(preg_match("/(dengan|yang|untuk) (.+)/", $kalimat, $m)){
    //     $kondisi = trim($m[2]);

    //     // ===== operator =====
    //     if(preg_match("/(.+)\s*(=|>|<|like)\s*(.+)/", $kondisi, $k)){
    //         $atributKondisi = trim($k[1]);
    //         $operator = strtoupper($k[2]);
    //         $data = trim($k[3]);

    //         // mapping atribut kondisi
    //         foreach($mapAtribut as $key => $column){
    //             if(strpos($atributKondisi, $key) !== false){
    //                 if(is_numeric($data)){
    //                     $where = "WHERE $column $operator $data";
    //                 } else {
    //                     if($operator == "LIKE"){
    //                         $where = "WHERE $column LIKE '%$data%'";
    //                     } else {
    //                         $where = "WHERE $column $operator '$data'";
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // ===== contoh: "untuk gunung lewotolo" =====
    //     else if(preg_match("/gunung\s+(.+)/", $kondisi, $k2)){
    //         $gunung = $k2[1];
    //         $where = "WHERE NamaGunung LIKE '%$gunung%'";
    //     }
    // }

    // // ===== SQL =====
    // $sql = "SELECT $select FROM sigmet_va $where";
    // echo "<b>Query:</b> $sql <br><br>";

    // $query = mysqli_query($con, $sql);

    // echo "<table border='1'>";
    // while($row = mysqli_fetch_assoc($query)){
    //     echo "<tr>";
    //     foreach($row as $val){
    //         echo "<td>$val</td>";
    //     }
    //     echo "</tr>";
    // }
    // echo "</table>";