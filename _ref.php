<?php
//https://www.php.net/manual/en/mysqli.examples-basic.php

// Let's pass in a $_GET variable to our example, in this case
// it's id for item_id in our Sakila database. Let's make it
// default to 1, and cast it to an integer as to avoid SQL injection
// and/or related security problems. Handling all of this goes beyond
// the scope of this simple example. Example:
//   http://example.org/script.php?id=42
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
   $id = (int) $_GET['id'];
} else {
   $id = 1;
}

// Perform an SQL query
$sql = "SELECT id,name,detail_1 FROM catalogue";
if (!$result = $mysqli->query($sql)) {
    // Oh no! The query failed. 
    echo "Sorry, the website is experiencing problems.";

    // Again, do not do this on a public site, but we'll show you how
    // to get the error information
    echo "Error: Our query failed to execute and here is why: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    echo "Error: " . $mysqli->error . "\n";
    exit;
}

// Phew, we made it. We know our MySQL connection and query 
// succeeded, but do we have a result?
if ($result->num_rows === 0) {
   // Oh, no rows! Sometimes that's expected and okay, sometimes
   // it is not. You decide. In this case, maybe item_id was too
   // large? 
   echo "We could not find a match for ID $id, sorry about that. Please try again.";
   exit;
}

// Now, we know only one result will exist in this example so let's 
// fetch it into an associated array where the array's keys are the 
// table's column names
$item = $result->fetch_assoc();
echo "Sometimes I see " . $item['id'] . " " . $item['name'] . " on TV.";

// Now, let's fetch five random items and output their names to a list.
// We'll add less error handling here as you can do that on your own now
$sql = "SELECT id,name,detail_1 FROM catalogue WHERE category=2 and name!='' LIMIT 10";
if (!$result = $mysqli->query($sql)) {
    echo "Sorry, the website is experiencing problems.";
    exit;
}

// Print our 5 random items in a list, and link to each item
echo "<ul>\n";
while ($item = $result->fetch_assoc()) {
    echo "<li><a href='" . $_SERVER['SCRIPT_FILENAME'] . "?slug=" . $item['name'] . "'>\n";
    echo $item['id'] . '_____' . $item['detail_1'] .'_____'.$item['name'];
    echo '<br>'.create_slug_prep_items($item['detail_1'],$item['name']);
    echo "</a></li>\n";
}
echo "</ul>\n";

// The script will automatically free the result and close the MySQL
// connection when it exits, but let's just do it anyways
$result->free();
$mysqli->close();

echo "<h1>Connected successfully</h1>";

function create_slug_prep_items($getYear,$getName) {
   $tmp = '';   
   $tmp .= $getName;
   if($getYear!='' && $getYear!=0) $tmp .= '-'.$getYear;
   $tmp = strtolower($tmp);
   return create_slug($tmp);
}

function create_slug($string){
   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}
echo create_slug('<p>#%%_==~!@#$%^&*()_+does this thing work or not</p>');
//returns 'does-this-thing-work-or-not'
?>