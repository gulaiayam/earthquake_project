<?php
include "conn.php";

$katatanya = array("berapa", "dimana", "kapan", "tampilkan");
$kata_tidak_diabaikan = array("info", "terjadi", "posisi", "pada", "gunung", "terkini", "bumi", "di", "dari", "yang", "antara", "berlokasi", "pada","ketinggian", "abu");
$atribut = array("Transmisi_WIB","ValidMulai_WIB","ValidAkhir_WIB","AreaPenerbangan","NamaGunung","PosisiGunung","ObsWaktu","AreaAbu","Ketinggian_Meter","PergerakanAbu","IntensitasAbu");

$mapAtribut = [
    "seluruh" => "*",
    "nama gunung" => "NamaGunung",
    "posisi gunung" => "PosisiGunung",
    "area abu" => "AreaAbu",
    "ketinggian abu" => "Ketinggian_Meter",
    "pergerakan abu" => "PergerakanAbu",
    "intensitas abu" => "IntensitasAbu",
    "waktu observasi" => "ObsWaktu",
    "waktu dikeluarkan" => "Transmisi_WIB",
    "waktu valid" => "ValidMulai_WIB"
];


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
    global $katatanya, $kata_tidak_diabaikan, $atribut;

    if(in_array($kata, $katatanya)) return 1;
    if(in_array($kata, $kata_tidak_diabaikan)) return 1;
    if(in_array($kata, $atribut)) return 1;

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
    global $con, $mapAtribut;

    $words = scanner($text);
    $kalimat = implode(" ", $words);

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
        
        if($word == "tampilkan"){
            $keterangan_parsing = "aturan1";
        }

        echo "<tr>
                <td>$i</td>
                <td>$word</td>
                <td>$token</td>
                <td>$keterangan_parsing</td>
              </tr>";
    }
    echo "</table><br><hr>";

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