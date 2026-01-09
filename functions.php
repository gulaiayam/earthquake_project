<?php
include "conn.php";

$rule;

$tokens = array("Tampilkan","Apa");
$disaster;

$keywords = array("di", "pada", "gempa", "tsunami", "ada");

$di = false;
$pada = true;
$place;
$time;


function exe($text){
    global $rule, $tokens, $place, $di, $pada, $time, $disaster,$keywords;

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

    for($i = 0, $j = 1; $i < count($result); $i++ , $j++){
        $token = 0;
        $production = "-";

        //disaster type
        if(strtolower($result[$i]) == "gempa"){
            $disaster = "gempa";
        }

        else if(strtolower($result[$i]) == "tsunami"){
            $disaster = "tsunami";
        }
        
        for($z = 0;$z < count($tokens);$z++){
            if(strtolower($result[$i]) == strtolower($tokens[$z])){
                $rule = $z+1;
                $token = 1;
                $concat = strval($z+1);
                $production = "Rule $concat";      
            }
        }

        for($z = 0;$z < count($keywords);$z++){
            if(strtolower($result[$i]) == strtolower($keywords[$z])){
                $token = 1;    
            }
        }


        //for asking spesific
        if(strtolower($result[$i]) == "di"){
        $di = true;
        $place = $result[$i+1];
        }

        else if(strtolower($result[$i]) == "pada"){
        $pada = true;
        $time = $result[$i+1];
        } 

        

        echo "
        <tr/>
            <td>
                $j
            </td>
            <td>
                $result[$i]
            </td>
            <td>
                $token
            </td>
            <td>
                $production
            </td>
        </tr>
    ";
    }

    echo"</table> <br><br>";
    
}

function show_table(){
    global $con, $rule, $disaster, $di, $pada, $place, $time;
    
    //default "All atributes"
    if($rule == 1){
        if($disaster != "tsunami"){
            $query = mysqli_query($con,"select * from gempa");

        echo "
        <table>
            <td>
                Index
            </td>
            <td>
                Date
            </td>
            <td>
                Coordinate
            </td>
            <td>
                Latitude
            </td>
            <td>
                Longitude
            </td>
            <td>
                Maginitude
            </td>
            <td>
                Depth
            </td>
            <td>
                Location
            </td>
            <td>
                Tsunami
            </td>";

        while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[coordinate]
                                </td>
                                <td>
                                    $row[latitude]
                                </td>
                                <td>
                                    $row[longitude]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[tsunami]
                                </td>
                            </tr>
                        ";
            }
        }
        else{
            $query = mysqli_query($con,"select * from gempa where tsunami = 'yes'");

            echo "
            <table>
                <td>
                    Index
                </td>
                <td>
                    Date
                </td>
                <td>
                    Coordinate
                </td>
                <td>
                    Latitude
                </td>
                <td>
                    Longitude
                </td>
                <td>
                    Maginitude
                </td>
                <td>
                    Depth
                </td>
                <td>
                    Location
                </td>
                <td>
                    Tsunami
                </td>";

            while ($row = mysqli_fetch_array($query)) {
                            echo "
                                <tr/>
                                    <td>
                                        $row[Id]
                                    </td>
                                    <td>
                                        $row[date]
                                    </td>
                                    <td>
                                        $row[coordinate]
                                    </td>
                                    <td>
                                        $row[latitude]
                                    </td>
                                    <td>
                                        $row[longitude]
                                    </td>
                                    <td>
                                        $row[magnitude]
                                    </td>
                                    <td>
                                        $row[depth] Km
                                    </td>
                                    <td>
                                        $row[location]
                                    </td>
                                    <td>
                                        $row[tsunami]
                                    </td>
                                </tr>
                            ";
                }
            }
        
        echo "</table>";
    }
    //specific question
    else if($rule == 2){

            $time = strval($time);

            if($di == true && $pada == false && $disaster != "tsunami"){
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where location like '%$place%'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                            </tr>
                        ";
                }
            }
            else if($di == true && $pada == false && $disaster == "tsunami"){
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where location like '%$place%' and date like '%$time%' and tsunami = 'yes'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    <td>
                        Tsunami
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                                <td>
                                    $row[tsunami]
                                </td>
                            </tr>
                        ";
                }
            }


            if($di == false && $pada == true && $disaster != "tsunami"){
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where date like '%$time%'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                            </tr>
                        ";
                }
            } 
            else if($di == false && $pada == true && $disaster == "tsunami"){
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where date like '%$time%' and tsunami = 'yes'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    <td>
                        Tsunami
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                                <td>
                                    $row[tsunami]
                                </td>
                            </tr>
                        ";
                }
            }

            if($di == true && $pada == true && $disaster != "tsunami"){
                
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth from gempa where location like '%$place%' and date like '%$time%'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                            </tr>
                        ";
                }
            }
            else if($di == true && $pada == true && $disaster == "tsunami"){
                $query = mysqli_query($con,"select Id, location, date, magnitude, depth, tsunami from gempa where location like '%$place%' and date like '%$time%' and tsunami = 'yes'");

                echo "
                <table>
                    <td>
                        Index
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Date
                    </td>
                    <td>
                        Magnitude
                    </td>
                    <td>
                        Depth
                    </td>
                    <td>
                        Tsunami
                    </td>
                    ";

                while ($row = mysqli_fetch_array($query)) {
                        echo "
                            <tr/>
                                <td>
                                    $row[Id]
                                </td>
                                <td>
                                    $row[location]
                                </td>
                                <td>
                                    $row[date]
                                </td>
                                <td>
                                    $row[magnitude]
                                </td>
                                <td>
                                    $row[depth] Km
                                </td>
                                <td>
                                    $row[tsunami]
                                </td>
                            </tr>
                        ";
                }
            }

        echo "</table>";
    }
    else{
        echo "
        <br>
        Perintah Kurang Jelas.
        <br>
        Berikan Perintah seperti:
        <br>
        'Tampilkan gempa' atau 'Tampilkan tsunami'
        <br>
        Dan
        <br>
        'Apa ada gempa di Jakarta?'
        ";
    }

    
}
