<?php
include "conn.php";

$production = array();
$sql_criterias = array(
                        "semua"=>1,
                        "gunung"=>"nama_gunung",
                        "intensitas"=>"intensitas_abu",
                        "area"=>"area_abu",
                        "ketinggian"=>"ketinggian_meter"
                        );

function exe($text) {
    global $production;

    $words = array("/tampilkan/","/berapa/","/di mana/","/kapan/","/apa/");
    $tokens = array(
                    "/semua/",
                    "/gunung/",
                    "/intensitas/",
                    "/area/",
                    "/ketinggian/",
                );

    

    $result = explode(" ", $text);

    echo "
    <br><br>
    <table>
        <td>
            Index
        </td>
        <td>
            Word
        </td>
        <td>
            Token
        </td>
        <td>
            Parsing
        </td>
        ";

    for($i = 0; $i < count($result); $i++){
        $parsing = 0;
        $token = 0;

        for($j = 0; $j < count($words); $j++){
            if($parsing == 1) break;
            $parsing = preg_match($words[$j], $result[$i]);
        }

        for($j = 0; $j < count($tokens); $j++){
            if($token == 1){
                if($result[$i] != "semua"){
                    if(preg_match('/^\\d+$/',$result[$i])){    
                    $production += [$result[$i] => $result[$i+1]];
                    }

                    if($result[$i+1] == "abu"){
                        $production += [$result[$i] => $result[$i+2]];
                    }else{
                        $production += [$result[$i] => $result[$i+1]];
                    }
                }else if($result[$i] == "semua"){
                    $production += [$result[$i] => 1];
                }
                break;
            } 
            $token = preg_match($tokens[$j], $result[$i]);
        }
        


        

        echo "
        <tr/>
            <td>
                $i
            </td>
            <td>
                $result[$i]
            </td>
            <td>
                $token
            </td>
            <td>
                $parsing
            </td>
        </tr>
    ";
    }

    echo "</table> <br><br>";
    //var_dump($result);
    //echo "<br><br>";
    //var_dump($production);
    //echo "<br><br>";
    return $production;
}

function show_table($array = array()){
    global $con,$sql_criterias;
    $flag = false;
    $i = 0;

        if(sizeof($array)==1){
             $query = mysqli_query($con, "select * from sigmet_data");
             $flag = !$flag;
        }
        else{
            $criteria = "where";

            foreach($array as $x => $x_value){
                if(preg_match("/intensitas/",$x) || preg_match("/area/",$x)){
                    $criteria .= " " . $sql_criterias[$x]. " like '%" . $x_value . "%'";
                }else{
                    $criteria .= " " . $sql_criterias[$x]. " = '" . $x_value. "'";
                }
                
                if($i+1 != count($array)){
                    $criteria .= " and";
                }
                $i++;
            }
            $query = mysqli_query($con, "select * from sigmet_data $criteria");
            $flag = !$flag;
        }
            //echo "select * from sigmet_data $criteria";
            
    

    if($flag){
        echo "
        <table>
            <td>
                Index
            </td>
            <td>
                Transmisi WIB
            </td>
            <td>
                Valid Mulai WIB
            </td>
            <td>
                Valid Akhir WIB
            </td>
            <td>
                Area Penerbangan
            </td>
            <td>
                Nama Gunung
            </td>
            <td>
                Posisi Gunung
            </td>
            <td>
                Area Abu
            </td>
            <td>
                Ketinggian
            </td>
            <td>
                Pergerakan Abu
            </td>
            <td>
                Intentsitas Abu
            </td>
            ";
    while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[id]
                                </td>
                                <td>
                                    $row[transmisi_wib]
                                </td>
                                <td>
                                    $row[valid_mulai_wib]
                                </td>
                                <td>
                                    $row[valid_akhir_wib]
                                </td>
                                <td>
                                    $row[area_penerbangan]
                                </td>
                                <td>
                                    $row[nama_gunung]
                                </td>
                                <td>
                                    $row[posisi_gunung]
                                </td>
                                <td>
                                    $row[area_abu]
                                </td>
                                <td>
                                    $row[ketinggian_meter] m
                                </td>
                                <td>
                                    $row[pergerakan_abu]
                                </td>
                                <td>
                                    $row[intensitas_abu]
                                </td>
                            </tr>
                        ";
            }
    }    
}