<?php

$disabled = false;
if($disabled){
	echo 'disabled';
	exit;
}

echo "<h1>RELATED: detail_2 -> related</h1>";
// Now, let's fetch five random items and output their names to a list.
// We'll add less error handling here as you can do that on your own now
$sql = "SELECT id,name,detail_2 FROM catalogue WHERE (detail_2!='0' AND detail_2!='') AND related='' ORDER BY id ASC LIMIT 10";
if (!$result = $mysqli->query($sql)) {
    echo "Sorry, the website is experiencing problems.";
    exit;
}else{
    if(mysqli_num_rows($result) === 0 ){
        echo "Nothing to do";
        exit;
    }
}
echo $sql;

// Print our 5 random items in a list, and link to each item
echo "<table>\n";
while ($item = $result->fetch_assoc()) {
    $id = $item['id'];
    $detail_2 = $item['detail_2'];

    echo "<tr>";
    echo "<td><a href='" . $_SERVER['SCRIPT_FILENAME'] . "?slug=" . $item['subcategory'] . "'>".$id."</a></td>\n";
    echo "<td>".$item['name']."</td>";
    echo "<td>".$detail_2."</td>";
    echo "</tr>\n";

    $sqlUpdate = "UPDATE catalogue SET related='$detail_2' WHERE id=$id";
    if ($r = $mysqli->query($sqlUpdate)) {
        echo "<tr><td colspan=3>updated</td></tr>";
    }
}
echo "</table>\n";

// The script will automatically free the result and close the MySQL
// connection when it exits, but let's just do it anyways
$result->free();
$mysqli->close();
?>