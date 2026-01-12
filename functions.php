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
$kata_hubung = ["yang", "dengan", "untuk", "dimana", "pada", "dari", "sampai"];

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
    if(preg_match("/^\d{4}-\d{2}-\d{2}$/", $kata)) return 1;
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


// Input: S0816 atau E12330
// Output: -8.2667 atau 123.5
function parse_coordinate($coord_str){
    $coord_str = trim($coord_str);
    if(strlen($coord_str) < 5) return 0; // Invalid

    $dir = strtoupper(substr($coord_str, 0, 1)); // N, S, E, W
    $val_str = substr($coord_str, 1); // 0816
    
    // Format SIGMET biasanya DDMM (Degrees Minutes)
    // S0816 -> 08 Derajat 16 Menit
    // E12330 -> 123 Derajat 30 Menit
    
    // Logic split DD dan MM agak tricky karena panjang digit beda (Lat 4 digit, Long 5 digit)
    if($dir == 'N' || $dir == 'S'){
        // Latitude (Format DDMM, misal 0816)
        $deg = substr($val_str, 0, 2);
        $min = substr($val_str, 2, 2);
    } else {
        // Longitude (Format DDDMM, misal 12330)
        $deg = substr($val_str, 0, 3);
        $min = substr($val_str, 3, 2);
    }
    
    $decimal = floatval($deg) + (floatval($min) / 60);
    
    if($dir == 'S' || $dir == 'W'){
        $decimal = $decimal * -1;
    }
    
    return $decimal;
}

function visualisasi_map($id, $posisi_gunung_raw = null, $area_abu_raw = null){

    // Jika dua-duanya kosong, hentikan
    if(empty($posisi_gunung_raw) && empty($area_abu_raw)){
        return;
    }

    $lat_gunung = 0;
    $lon_gunung = 0;
    $js_polygon = "[]";

    // 1. Parse Posisi Gunung (jika ada)
    if(!empty($posisi_gunung_raw)){
        $parts = preg_split("/\s+/", trim($posisi_gunung_raw));
        if(count($parts) >= 2){
            $lat_gunung = parse_coordinate($parts[0]);
            $lon_gunung = parse_coordinate($parts[1]);
        }
    }

    // 2. Parse Area Abu (jika ada)
    if(!empty($area_abu_raw)){
        $polygon_coords = [];
        $raw_poly = preg_split("/\s*-\s*/", trim($area_abu_raw));

        foreach($raw_poly as $point){
            $p = preg_split("/\s+/", trim($point));
            if(count($p) >= 2){
                $lat = parse_coordinate($p[0]);
                $lon = parse_coordinate($p[1]);
                $polygon_coords[] = "[$lat, $lon]";
            }
        }
        $js_polygon = "[" . implode(",", $polygon_coords) . "]";
    }

    // Titik tengah peta:
    // Jika ada posisi → pakai posisi
    // Jika tidak ada → pakai titik pertama polygon
    if(empty($posisi_gunung_raw) && !empty($area_abu_raw) && !empty($polygon_coords)){
        $lat_gunung = parse_coordinate(preg_split("/\s+/", trim($raw_poly[0]))[0]);
        $lon_gunung = parse_coordinate(preg_split("/\s+/", trim($raw_poly[0]))[1]);
    }

    // 3. Generate HTML & JS
    $map_id = "map_" . $id;
    $map_var = "mapVar_" . $id;

    echo "
    <div id='$map_id' class='map-container'></div>
    <script>
        var $map_var = L.map('$map_id').setView([$lat_gunung, $lon_gunung], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo($map_var);

        // --- MARKER (Jika ada Posisi Gunung)
        ".(!empty($posisi_gunung_raw) ? "
        var goldIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        L.marker([$lat_gunung, $lon_gunung], {icon: goldIcon})
         .addTo($map_var)
         .bindPopup('<b>Posisi Gunung</b><br>$posisi_gunung_raw');
        " : "")."

        // --- POLYGON (Jika ada Area Abu)
        var polygonCoords = $js_polygon;
        if(polygonCoords.length > 0){
            var polygon = L.polygon(polygonCoords, {
                color: 'gray',
                fillColor: '#808080',
                fillOpacity: 0.5
            }).addTo($map_var);

            $map_var.fitBounds(polygon.getBounds());
        }
    </script>
    <br>";
}


function solve_tampilkan($select_cols, $where_conds, $date_range){
    $str_select = "";

    // 1. SELECT
    if(in_array("*", $select_cols)){
        $str_select = "*";
    } elseif(!empty($select_cols)){
        if(!in_array("DataID", $select_cols)) array_unshift($select_cols, "DataID");
        $str_select = implode(", ", $select_cols);
    } else {
        return ["status" => "error", "msg" => "Tidak ada atribut yang dikenali."];
    }

    // 2. WHERE STANDARD
    $arr_conds = [];
    if(!empty($where_conds)){
        foreach($where_conds as $cond){
            // Jika kolom adalah Transmisi_WIB dan kita punya range, skip (prioritas range)
            if($cond['col'] == 'Transmisi_WIB' && !empty($date_range)) continue;
            
            $arr_conds[] = $cond['col'] . " LIKE '%" . $cond['val'] . "%'";
        }
    }

    // Menggunakan
    if(!empty($date_range)){
        if(isset($date_range['start']) && isset($date_range['end'])){
            $start = $date_range['start'];
            $end = $date_range['end'];
            // Menggunakan DATE() agar jam diabaikan (YYYY-MM-DD)
            $arr_conds[] = "DATE(Transmisi_WIB) >= '$start'";
            $arr_conds[] = "DATE(Transmisi_WIB) <= '$end'";
        }
    }

    $str_where = "";
    if(!empty($arr_conds)){
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
    $date_range  = [];
    $rule_type = ""; 
    $fase = "";    
    $skip_logic = false;
    $context_date = "";

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

                    // Logic Khusus "Dari" dan "Sampai"
                    if($word == "dari"){
                        $context_date = "start";
                    } elseif($word == "sampai"){
                        $context_date = "end";
                    } else {
                        $context_date = ""; // Reset jika kata hubung lain
                    }
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
                    
                    // Cek Apakah ini Tanggal (Format YYYY-MM-DD)
                    if(preg_match("/^\d{4}-\d{2}-\d{2}$/", $word)){
                        if($context_date == "start"){
                            $date_range['start'] = $word;
                        } elseif($context_date == "end"){
                            $date_range['end'] = $word;
                        } else {
                            // Jika tanggal muncul tanpa "dari/sampai", anggap sebagai nilai kondisi biasa
                            // Default ke Transmisi_WIB jika belum ada kolom
                             if(empty($where_conds) || $where_conds[count($where_conds)-1]['val'] != ""){
                                $where_conds[] = ["col" => "Transmisi_WIB", "val" => $word];
                            } else {
                                // Append ke val terakhir
                                $idx = count($where_conds) - 1;
                                $where_conds[$idx]["val"] = $word;
                            }
                        }
                    }
                    // 2. Jika Atribut Biasa
                    elseif($is_attribute){
                        $where_conds[] = ["col" => $db_field, "val" => ""];
                        $context_date = "";
                    }
                    // 3. Jika Data Biasa (Bukan tanggal, bukan atribut)
                    elseif($token == 0){
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
        $result = solve_tampilkan($select_cols, $where_conds, $date_range);
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
                    if(isset($row['Ketinggian_Meter'])) echo "Abu vulkanik ini memiliki ketinggian " . $row['Ketinggian_Meter'] . " meter<br>";

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
                    
                    // Cek jika data koordinat tersedia
                    $need_map = in_array("PosisiGunung", $select_cols) || in_array("AreaAbu", $select_cols);

                    if($need_map){
                        if(!in_array("PosisiGunung", $select_cols)) $select_cols[] = "PosisiGunung";
                        if(!in_array("AreaAbu", $select_cols)) $select_cols[] = "AreaAbu";
                    }
                    // Teks hanya tampil jika diminta user
                    if(in_array("PosisiGunung", $select_cols) && isset($row['PosisiGunung'])){
                        echo "Posisi gunung ini berada di koordinat " . $row['PosisiGunung'] . "<br>";
                    }

                    if(in_array("AreaAbu", $select_cols) && isset($row['AreaAbu'])){
                        echo "Area abu vulkanik ini terletak pada titik-titik koordinat berikut: " . $row['AreaAbu'] . "<br>";
                    }

                    if(isset($row['PosisiGunung']) || isset($row['AreaAbu'])){
                        $pos = isset($row['PosisiGunung']) ? $row['PosisiGunung'] : null;
                        $area = isset($row['AreaAbu']) ? $row['AreaAbu'] : null;

                        visualisasi_map($row['DataID'], $pos, $area);
                    }

                    echo "</div>";

                }
            } else {
                echo "<p>Data tidak ditemukan.</p>";
            }
        }
    }
}
?>