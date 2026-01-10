<?php
include "conn.php";



function exe($text){

    $words = array("/tampilkan/","/berapa/","/di mana/","/kapan/","/apa/");

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
        


        

        echo "
        <tr/>
            <td>
                $i
            </td>
            <td>
                $result[$i]
            </td>
            <td>
                
            </td>
            <td>
                $parsing
            </td>
        </tr>
    ";
    }

    echo"</table> <br><br>";
    
}