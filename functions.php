<?php
include "conn.php";

$katatanya = array("berapa", "dimana", "kapan");
$kata_tidak_diabaikan = array("lokasi","area", "info", "terjadi", "posisi", "pada", "gunung", "terkini", "pergerakan", "di", "dari", "yang", "antara", "waktu", "pada","vulkanik", "abu","tampilkan", "seluruh");
$operator = ["=",">","<","LIKE"];
$mapAtribut = [
    "seluruh" => "*",
    "nama gunung" => "nama_gunung",
    "lokasi" => "nama_gunung",
    "lokasi gunung" => "nama_gunung", 
    "posisi" => "posisi_gunung",
    "posisi gunung" => "posisi_gunung",
    "area abu" => "area_abu",
    "ketinggian abu" => "ketinggian_meter",
    "ketinggian awan" => "ketinggian_meter",
    "pergerakan abu" => "PergerakanAbu",
    "intensitas abu" => "intensitas_abu",
    "waktu observasi" => "orb_waktu",
    "tanggal" => "transmisi_wib",
    "waktu dikeluarkan" => "transmisi_wib",
    "waktu mulai" => "valid_mulai_wib"
];
// $arah_map = [
//     "N"  => "Utara",    
//     "NW" => "Barat Laut",
//     "NE" => "Timur Laut",
//     "E"  => "Timur",
//     "SE" => "Tenggara",
//     "SW" => "Barat Daya",
//     "S"  => "Selatan",
//     "W"  => "Barat"
// ];
$kata_hubung = ["yang", "dengan", "untuk", "dimana", "pada", "dari", "sampai"];

function scanner($text){
    $text = strtolower($text);
    $text = preg_replace("/[^a-z0-9\s-]/", " ", $text);
    return preg_split("/\s+/", trim($text));
}

function scanner_view($text){
    $words = scanner($text);

    echo "<h3>Tabel</h3>";
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

    echo "<h3>Tabel</h3>";
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

function parse_coordinate($coord_str){
    $coord_str = trim($coord_str);
    
    // Validasi format: Harus diawali N/S/E/W diikuti angka
    if(!preg_match("/^[NSEW]\d+$/", $coord_str)) return 0;

    $dir = strtoupper(substr($coord_str, 0, 1)); 
    $val_str = substr($coord_str, 1); 

    if($dir == 'N' || $dir == 'S'){
        if(strlen($val_str) != 4) return 0;
        $deg = substr($val_str, 0, 2);
        $min = substr($val_str, 2, 2);
    } else {
        if(strlen($val_str) != 5) return 0;
        $deg = substr($val_str, 0, 3);
        $min = substr($val_str, 3, 2);
    }
    
    // Konversi Derajat Menit ke Desimal
    $decimal = floatval($deg) + (floatval($min) / 60);
    
    // Jika South atau West, nilai negatif
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

    // Pola Regex: Mencari pasangan (N/S + 4 digit) spasi (E/W + 5 digit)
    // Ini akan mengabaikan kata "WI", "-", atau teks sampah lainnya.
    $pattern = "/([NS]\d{4})\s+([EW]\d{5})/";

    // Parse Posisi Gunung (Ambil match pertama saja)
    if(!empty($posisi_gunung_raw)){
        if(preg_match($pattern, $posisi_gunung_raw, $matches)){
            // matches[1] adalah Lat, matches[2] adalah Lon
            $lat_gunung = parse_coordinate($matches[1]);
            $lon_gunung = parse_coordinate($matches[2]);
        }
    }

    // parse Area Abu (Ambil semua match untuk membentuk Polygon)
    $polygon_coords = [];
    if(!empty($area_abu_raw)){
        // preg_match_all akan mencari SEMUA pasangan koordinat dalam string
        if(preg_match_all($pattern, $area_abu_raw, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $lat = parse_coordinate($m[1]);
                $lon = parse_coordinate($m[2]);
                $polygon_coords[] = "[$lat, $lon]";
            }
        }
        $js_polygon = "[" . implode(",", $polygon_coords) . "]";
    }

    // Titik tengah peta default jika posisi gunung tidak ada tapi area abu ada
    if($lat_gunung == 0 && $lon_gunung == 0 && !empty($polygon_coords)){
        // Ambil titik pertama dari polygon sebagai center
        // Format string "[$lat, $lon]", kita bersihkan kurung siku
        $first_point = str_replace(['[',']'], '', $polygon_coords[0]);
        $fp = explode(',', $first_point);
        $lat_gunung = $fp[0];
        $lon_gunung = $fp[1];
    }

    // enerate HTML & JS (Sama seperti sebelumnya)
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
        if(!in_array("id", $select_cols)) array_unshift($select_cols, "id");
        $str_select = implode(", ", $select_cols);
    } else {
        return ["status" => "error", "msg" => "Tidak ada atribut yang dikenali."];
    }

    // 2. WHERE STANDARD
    $arr_conds = [];
    if(!empty($where_conds)){
        foreach($where_conds as $cond){
            // Jika kolom adalah transmisi_wib dan kita punya range, skip (prioritas range)
            if($cond['col'] == 'transmisi_wib' && !empty($date_range)) continue;
            
            $arr_conds[] = $cond['col'] . " LIKE '%" . $cond['val'] . "%'";
        }
    }

    // Menggunakan
    if(!empty($date_range)){
        if(isset($date_range['start']) && isset($date_range['end'])){
            $start = $date_range['start'];
            $end = $date_range['end'];
            // Menggunakan DATE() agar jam diabaikan (YYYY-MM-DD)
            $arr_conds[] = "DATE(transmisi_wib) >= '$start'";
            $arr_conds[] = "DATE(transmisi_wib) <= '$end'";
        }
    }

    $str_where = "";
    if(!empty($arr_conds)){
        $str_where = "WHERE " . implode(" AND ", $arr_conds);
    }

    $sql = "SELECT $str_select FROM sigmet_data $str_where";
    return ["status" => "success", "sql" => $sql];
}

function solve_kata_tanya($rule_type, $select_cols, $where_conds){
    // Jika tidak ada atribut target
    if(empty($select_cols)){
        return [
            "status" => "error",
            "msg" => "Setelah kata '$rule_type' harus ada atribut yang dikenali."
        ];
    }

    // Ambil atribut pertama sebagai jawaban utama
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

    $sql = "SELECT $str_select FROM sigmet_data $str_where";
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
    echo "<h3>Tabel</h3>";
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
                elseif($word == "berapa"){
                    $rule_type = "berapa";
                    $fase = "TARGET";
                    $keterangan = "aturan(3)";
                }
                elseif($word == "kapan"){
                    $rule_type = "kapan";
                    $fase = "TARGET";
                    $keterangan = "aturan(4)";
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
                            // Default ke transmisi_wib jika belum ada kolom
                             if(empty($where_conds) || $where_conds[count($where_conds)-1]['val'] != ""){
                                $where_conds[] = ["col" => "transmisi_wib", "val" => $word];
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
    elseif(in_array($rule_type, ["dimana","berapa","kapan"])){
        $result = solve_kata_tanya($rule_type, $select_cols, $where_conds);
    }

    // Cek Hasil
    if($result['status'] == "error"){
        echo "<p style='color:red;'><b>Error:</b> " . $result['msg'] . "</p>";
        return;
    }
    elseif($result['status'] == "none"){
        echo "<p style='color:red;'><b>Error: TIdak ada aturan yang ditemukan </b></p>";
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
                
                    
                    if(isset($row['id'])) echo "<b>id : " . $row["id"] . "</b><br>";
                    if(isset($row['transmisi_wib'])) echo "Data ini dipublikasikan pada tanggal : " . $row['transmisi_wib'] . "<br>";
                    if(isset($row['valid_mulai_wib'])) echo "Data ini valid pada " . $row['valid_mulai_wib'] . "<br>";
                    if(isset($row['valid_akhir_wib'])) echo "Data ini valid sampai " . $row['valid_akhir_wib'] . "<br>";
                    if(isset($row['area_penerbangan'])) echo "Data ini dipublikasikan pada stasiun meteorologi yang terletak di " . $row['area_penerbangan'] . "<br>";
                    if(isset($row['nama_gunung'])) echo "Gunung yang mengeluarkan abu vulkanik adalah Gunung " . $row['nama_gunung'] . "<br>";
                    if(isset($row['orb_waktu'])) echo "Abu vulkanik diobservasikan pada pukul " . $row['orb_waktu'] . "<br>";
                    if(isset($row['ketinggian_meter'])) echo "Abu vulkanik ini memiliki ketinggian " . $row['ketinggian_meter'] . " meter<br>";
                    if(isset($row['pergerakan_abu'])) echo "Abu vulkanik ini bergerak ke arah " . $row['pergerakan_abu'] . " meter<br>";
                    if(isset($row['intensitas_abu'])) echo "Perkiraan intensitas abu vulkanik adalah: " . $row['intensitas_abu'] . "<br>";
                    
                    // Cek jika data koordinat tersedia
                    $need_map = in_array("posisi_gunung", $select_cols) || in_array("area_abu", $select_cols);

                    if($need_map){
                        if(!in_array("posisi_gunung", $select_cols)) $select_cols[] = "posisi_gunung";
                        if(!in_array("area_abu", $select_cols)) $select_cols[] = "area_abu";
                    }
                    // Teks hanya tampil jika diminta user
                    if(in_array("posisi_gunung", $select_cols) && isset($row['posisi_gunung'])){
                        echo "Posisi gunung ini berada di koordinat " . $row['posisi_gunung'] . "<br>";
                    }

                   if(in_array("area_abu", $select_cols) && isset($row['area_abu'])){
                        echo "Area abu vulkanik ini terletak pada titik-titik koordinat berikut: " . $row['area_abu'] . "<br>";
                    }

                    if(isset($row['posisi_gunung']) || isset($row['area_abu'])){
                        $pos = isset($row['posisi_gunung']) ? $row['posisi_gunung'] : null;
                        $area = isset($row['area_abu']) ? $row['area_abu'] : null;

                        visualisasi_map($row['id'], $pos, $area);
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