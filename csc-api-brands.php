<?php
$printDebug = false;

$q = "SELECT csc.id,csc.subcategory AS brand,csc.slug, COUNT(c.id) as itemCount";
$q .= " FROM catalogue_subcats AS csc";
$q .= " LEFT JOIN catalogue AS c ON c.subcategory = csc.id";
$q .= " WHERE c.status=2 AND c.category=2";
$q .= " GROUP BY csc.id";
// $q .= " ORDER BY itemCount DESC";
$q .= " ORDER BY csc.subcategory ASC";
// echo $q;

// echo $q;
if($q){
    $debug .= $q;

    if (!$result = $mysqli->query($q)) {
        return "Sorry, the website is experiencing problems.";
        exit;
    }else{
        if(mysqli_num_rows($result) === 0 ){
            return "Nothing to do";
            exit;
        }
    }

    //Initialize array variable
    $dbdata = array();
    $tmpCount = 0;
    //Fetch into associative array
    while ( $row = $result->fetch_assoc())  {
        $tmpCount = $tmpCount + 1;
        // $row['id'] = intval($row['id']);
        // $row['brand'] = $row['subcategory'];
        // $row['slug'] = $row['slug'];
        // $row['count'] = $row['itemCount'];
        
        $dbdata[]=$row;
        $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['brand'].' | ';
    }
    
    if($printDebug){
        echo $debug;
        echo $q;
        echo '<br>------------<br>';
    }

    echo json_encode($dbdata, JSON_PRETTY_PRINT);

    // The script will automatically free the result and close the MySQL
    // connection when it exits, but let's just do it anyways
    $result->free();
    $mysqli->close();
}
?>