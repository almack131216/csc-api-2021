<?php
// REF: https://www.php.net/manual/en/mysqli.affected-rows.php
// preview: SELECT id,name, upload_date,image_dir FROM catalogue WHERE id_xtra=0 ORDER BY id ASC LIMIT 10
// image_small BUG: SELECT c.id,c.name,c.image_small,c.image_large FROM catalogue AS c WHERE image_large!=image_small AND c.image_small!=''

$myRemoteAddr = $_SERVER['REMOTE_ADDR'];
$myPublicIP = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));

$echoIP = '';
$echoIP .= '<br><strong>IP ADDRESS CHECK</strong>';
$echoIP .= '<br>My public IP: '.$myPublicIP;
$echoIP .= '<br>REMOTE_ADDR (User IP Address): '. $_SERVER['REMOTE_ADDR'];
$echoIP .= '<br>---<br>';

$allowlist = array(
	'141.101.96.7',
	'141.101.96.8',
	'141.101.96.9',
	'141.101.96.16',
    '141.101.96.85',
	'141.101.96.86',
	'141.101.96.83',
	'141.101.96.82',
	'141.101.96.16',
    '217.30.64.117'
);

if(!in_array($_SERVER['REMOTE_ADDR'],$allowlist)){
	$disabled = true;
	echo $echoIP;
	//REF: https://dcblog.dev/grant-access-based-on-ip-address-with-php
    die('This website cannot be accessed from your location.');
	exit();
}

$subcategoryId = 96;//46,42,122,47,57,52,119,96,54,44

$disabled = true;//LIVE must be true
if($disabled){
	echo $echoIP;
	echo 'disabled';
	exit;
}
$safeMode = false;

include("header.php");

function isAttachment($getIdXtra){
    if($getIdXtra==0 || $getIdXtra=='') return false;
    return true;
}
// CHECK folder exists...

function returnToDir($getSize){
    switch($getSize){
        case "thumb":
            $getSize = '/th/';
            break;
        case "large":
            $getSize = '/lg/';
            break;
		case "highres":
            $getSize = '/hi/';
            break;
        default:
            $getSize = '/pr/';
    }
    return $getSize;
}

function checkFolderExists($getSize,$newDir){
    global $foldersCreated,$tableTitle;

    $getSize = returnToDir($getSize);

    if($getSize){
        $newDir = $newDir.$getSize;//thumb/primary/large
    }
    // CHECK / folders exist..
    if(file_exists( $newDir ) ){
        $foldersCreated ++;
        // $tableTitle .= '<p class="info">['.$newDir.'] FOLDER EXISTS</p>';
    }else{//if not, mkdir
        if(!file_exists( $newDir ) && mkdir($newDir, 0755, true)){
            $foldersCreated ++;
            $tableTitle .= '<p class="success">['.$newDir.'] FOLDER CREATED</p>';
        }else{
            $tableTitle .= '<p class="fatal">BASE FOLDER NOT CREATED</p>';
        }                       
    }
}
// (END) CHECK folder exists...

// CHECK file exists...
function checkFileExists( $getSize, $getFilename ){
    global $imgPath,$newDir,$filesMoved,$filesMovedTotal,$table;

    switch($getSize){
        case "thumb":
            $imgPathLive = 'https://www.classicandsportscar.ltd.uk/images_catalogue/thumbs/'.$getFilename;
            $imgPathFrom = $imgPath.'thumbs/'.$getFilename;
            $imgPathTo = $newDir.'/th/'.$getFilename;
            break;
        case "large":
            $imgPathLive = 'https://www.classicandsportscar.ltd.uk/images_catalogue/large/'.$getFilename;
            $imgPathFrom = $imgPath.'large/'.$getFilename;
            $imgPathTo = $newDir.'/lg/'.$getFilename;
            break;
        default:
            $imgPathLive = 'https://www.classicandsportscar.ltd.uk/images_catalogue/'.$getFilename;
            $imgPathFrom = $imgPath.$getFilename;
            $imgPathTo = $newDir.'/pr/'.$getFilename;
    }

    if(file_exists( $imgPathFrom )){
        $table .= '<span class="info">moving...'.$imgPathTo.'</span>';
        if(rename($imgPathFrom, $imgPathTo)) $filesMoved ++;$filesMovedTotal ++;
    }else{
        if(file_exists( $imgPathTo )){
            $filesMoved ++;
            $filesMovedTotal ++;
            $table .= '<span class="good">['.$getSize.'] file already moved</span>';
        }else{
            $table .= '<img src="'.$imgPathLive.'" class="'.$getSize.'">';
            $table .= '<span class="error">['.$getSize.'] cannot find file</span>';
        }
    }
}
// (END) CHECK file exists...

// CHECK file exists...
function returnLiveImage( $getSize, $getDir, $getFilename ){
    global $imgPath,$newDir,$filesMoved,$table;

    $dirs = [];
    $dirs['thumb'] = "thumbs/";
    $dirs['primary'] = "";
    $dirs['large'] = "large/";

    switch($getDir){
        case "from":
            $switchDir = $imgPath;            
            break;
        case "to":
            $switchDir = $newDir;
            $dirs['thumb'] = "/th/";
            $dirs['primary'] = "/pr/";
            $dirs['large'] = "/lg/";
            break;
        case "live":
            $switchDir = 'https://www.classicandsportscar.ltd.uk/images_catalogue/';           
            break;
    }

    return '<img src="'.$switchDir.$dirs[$getSize].$getFilename.'" class="'.$getSize.'">';
}
// (END) CHECK file exists...

$echo = '';
$echo .= $echoIP;

if(isset($_REQUEST['id'])) $itemId = $_REQUEST['id'];
//$itemId = 4697;//1-5-756-1787-13743--TESTIMONIAL: [234-4697]
// TEST: SELECT * FROM `catalogue` WHERE image_dir!='' ORDER BY `id` ASC

if(isset($_REQUEST['p'])) $page = $_REQUEST['p'];
if(isset($_REQUEST['brand'])) $getBrand = $_REQUEST['brand'];
if(isset($_REQUEST['category'])) $getCategory = $_REQUEST['category'];
if(isset($_REQUEST['year']) && isset($_REQUEST['month'])){
    $getYear = $_REQUEST['year'];
    $getMonth = $_REQUEST['month'];
}

if($_REQUEST['reset']==999){
    $isReset = true;
    $echo .= "<h1>IMG PATHS: RESET</h1>";
    $echo .= '<p><a href="'.$_SERVER['php_self'].'?paths=true&reset=0">base</a></p>';
    if(!$safeMode){
        //$sql = "UPDATE catalogue SET image_dir='' WHERE (image_dir != '')";
        //$result = $mysqli->query($sql);
        // $echo .= '???'.$mysqli->affected_rows;
        //$echo .= "Affected rows (UPDATE): %d\n".$mysqli->affected_rows;
    }
    // $sql = "UPDATE catalogue SET image_dir='' WHERE (id=1 OR id=8 OR id=11)";
    // $result = $mysqli->query($sql);
    // echo '>>>>>'.$mysqli->affected_rows;
}else{
    $echo .= "<h1>IMG PATHS: images_catalogue -> images/[year]/[months]</h1>";
    $echo .= '<p><a href="'.$_SERVER['php_self'].'?paths=true">base</a>';
	//$echo .= ' | <a href="'.$_SERVER['php_self'].'?paths=true&reset=1">reset</a></p>';
    // Now, let's fetch five random items and output their names to a list.
    // We'll add less error handling here as you can do that on your own now
    $s = "SELECT c.id,c.id_xtra,c.name,c.image_large,c.upload_date";
    $s .= ",csc.id AS brandID,csc.subcategory AS brandName";
    $f = " FROM catalogue AS c,catalogue_subcats AS csc";//,tbl_regions AS r
    $w = " WHERE c.subcategory=csc.id";
    $w .= " AND c.id_xtra=0 AND c.image_large!=''";
    $w .= " AND (";
	$w .= "c.category=2";
	$w .= " AND c.subcategory=$subcategoryId";
	$w .= ")";
    if($getCategory) $w .= " AND c.category=$getCategory";
    if($itemId) $w .= " AND c.id=$itemId";
    if($getBrand)  $w .= " AND c.subcategory=$getBrand";
    if($getYear && $getMonth) $w .= " AND (c.upload_date >= '".$getYear."-".$getMonth."-01' AND c.upload_date <= '".$getYear."-".$getMonth."-31')";
    $w .= " AND (c.image_dir='')";
    $o = " ORDER BY c.id ASC";
    $l = " LIMIT 5";
    if($page) $l = " LIMIT ".round($page * 10).", 10";
    if($itemId) $l = " LIMIT 1";
    $sql = $s.$f.$w.$o.$l;
    // echo '<p class="sql">'.$sql.'</p>';
}
$echo .= '<p class="sql">'.$sql.'</p>';

if (!$result = $mysqli->query($sql)) {
    $echo .= '<p class="fatal">Sorry, the website is experiencing problems.</p>';
    exit;
}else{
    if($isReset){

    }else{
        if(mysqli_num_rows($result) === 0 ){
            $echo .= '<p class="info">Nothing to do</p>';
            echo $echo;
            exit;
        }
    }    
}


if(!$isReset){
    // Print items in a list, and link to each item
    $arrItems = [];
    $arrDirs = array();

    $table = "<table>\n";
    $tableTitle = "";
    while ($item = $result->fetch_assoc()) {
        $arrItems[] = $item;
    }

    if($itemId){
        $q = "SELECT id,id_xtra,name,image_large FROM catalogue WHERE id_xtra=$itemId AND image_large!=''";
        $result = $mysqli->query($q);
        while ($itemX = $result->fetch_assoc()) {
            $arrItems[] = $itemX;
        }
    }

    $imageCountTotal = (sizeof($arrItems) * 2) + 1;
    $filesMovedTotal = 0;
    for($i=0;$i<sizeof($arrItems);$i++){
        $item = $arrItems[$i];

        $id = $item['id'];
        $id_xtra = $item['id_xtra'];
        $imgFilename = $item['image_large'];
        $imgPath = "../images_catalogue/";
        $filesCount = 2;//thumb,large
        $filesMoved = 0;
 
        // CHECK / folders exist.. if not, mkdir
        if(!isAttachment($id_xtra)){
            // echo '<br>!!!!!!!!!!'.$id_xtra;
            $filesCount = 3;//thumb,primary,large
            $foldersExist = false;
            $foldersCount = 4;
            $foldersCreated = 0;
            $brand = $item['brandName'];            
 
            $date = $item['upload_date'];
            $dateX = explode("-",$date);
            $newDirStr = $dateX[0]."/".$dateX[1];    
            $newDir = "../images/".$newDirStr;
           
            checkFolderExists("",$newDir);
            checkFolderExists("thumb",$newDir);
            checkFolderExists("primary",$newDir);
            checkFolderExists("large",$newDir);

            if ($newDirStr && !in_array($newDirStr, $arrDirs)) {
                array_push($arrDirs,$newDirStr);
                print_r($arrDirs);
    
                if($foldersCreated == $foldersCount){
                    $foldersExist = true;
                    $tableTitle .= '<p class="good">Folder \''.$newDirStr.'\' ready to receive...</p>';
                }
        
                if(!$foldersExist){
                    $tableTitle .= '<p class="fatal">FAIL: Check folders for \''.$newDirStr.'\'</p>';
                    exit;
                }
            }        
            // (END) CHECK
        }        

        

        $table .= "<tr>";
        $table .= "<td><a href='" . $_SERVER['PHP_SELF']."?paths=true&id=".$id."' target='_blank'>".$id."</a></td>\n";
        // $table .= '<td class="img">'.$imgTagThumb.$imgTagPrimary.$imgTagLarge.'</td>';
        $table .= '<td class="img">';
        $table .= returnLiveImage("thumb","from",$imgFilename);
        if(!isAttachment($id_xtra)) $table .= returnLiveImage("primary","from",$imgFilename);
        $table .= returnLiveImage("large","from",$imgFilename);
        $table .= '</td>';
        $table .= '<td class="img">';
        $table .= returnLiveImage("thumb","to",$imgFilename);
        if(!isAttachment($id_xtra)) $table .= returnLiveImage("primary","to",$imgFilename);
        $table .= returnLiveImage("large","to",$imgFilename);
        $table .= '</td>';
        $table .= "<td>".$item['name']."<br>".$item['brandID'].' - '.$item['brandName']."</td>";
        $table .= "<td>";
        $table .= $date;
        $table .= '<br><strong>[y][m] = '.$dateX[0]." - ".$dateX[1].'</strong>';
        $table .= "</td>";
        $table .= '<td class="feedback">';
        // CHECK file exists... if it does, move it to the new directory
        // $table .= returnLiveImage("thumb","live",$imgFilename);
        // $table .= returnLiveImage("primary","live",$imgFilename);
        // $table .= returnLiveImage("large","live",$imgFilename);
        checkFileExists("thumb",$imgFilename);
        if(!isAttachment($id_xtra)){
            // $table .= '--['.$id_xtra.']--IS NOT ATTACHMENT-----';
            checkFileExists("primary",$imgFilename);
        }
        checkFileExists("large",$imgFilename);      

        if($filesMoved){
            $table .= '<span class="success">FILES MOVED '.$filesMoved.'/'.$filesCount.'</span>';
        }
        
        $table .= "</td>";
        $table .= "</tr>\n";
    }
    $table .= "</table>\n";

    if($itemId){
        $table .= '<span class="info">Files moved: '.$filesMovedTotal.'/'.$imageCountTotal.'</span>';
        if($filesMovedTotal == $imageCountTotal){
            if(!$safeMode){
                $sqlUpdate = "UPDATE catalogue SET image_dir='$newDirStr' WHERE id=$itemId LIMIT 1";
                if ($r = $mysqli->query($sqlUpdate)) {
                    $table .= '<span class="success">DB updated SET image_dir=\''.$newDirStr.'\'</span>';
                }
            }            
        }else{
            
        }
    }
}

if($tableTitle) $echo .= $tableTitle;
if($table) $echo .= $table;
echo $echo;

// The script will automatically free the result and close the MySQL
// connection when it exits, but let's just do it anyways
if(!$isReset) $result->free();
$mysqli->close();

include("footer.php");
?>