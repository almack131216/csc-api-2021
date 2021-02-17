<?php

function getFriendlyURL($string) {
    setlocale(LC_CTYPE, 'en_US.UTF8');
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    $string = preg_replace('~[^\-\pL\pN\s]+~u', '-', $string);
    $string = str_replace(' ', '-', $string);
    $string = trim($string, "-");
    $string = strtolower($string);
    return $string;
} 

function returnSqlCommonSelectItems(){
    $ret = " catalogue.id,catalogue.status,catalogue.name,catalogue.slug";
    $ret .= ",catalogue.category";
    $ret .= ",catalogue.upload_date AS createdAt,catalogue.upload_date AS updatedAt";
    $ret .= ",catalogue.image_large AS image,catalogue.image_dir AS imageDir,catalogue.image_hi AS imageHi";
    return $ret;
}

function returnSqlCommonSelectBrandArr(){
    $ret = ",catalogue_subcat.id AS catalogue_subcat_id";
    $ret .= ",catalogue_subcat.subcategory AS catalogue_subcat_brand";
    $ret .= ",catalogue_subcat.slug AS catalogue_subcat_slug";
    return $ret;
}

function returnSqlInnerJoinBrands(){
    $ret = " INNER JOIN catalogue_subcats AS catalogue_subcat";
    $ret .= " ON catalogue.subcategory=catalogue_subcat.id";
    return $ret;
}

function removeBadChars( $text ) {
    $text = utf8_encode($text);
    $pattern = array (	"/&nbsp;/",
                        "/&pound;/",
                        "/&#39;/",
                        "/&rsquo;/",
                        "/&ldquo;/",
                        "/&amp;/",
                        "/Â/",
                        "/â/" );
    
    $replace = array (	" ",
                        "£",
                        "'",
                        "'",
                        "\"",
                        "&",
                        "",
                        "'" );
                    
    return preg_replace( $pattern, $replace, $text );
}

// Now, let's fetch five random items and output their names to a list.
// We'll add less error handling here as you can do that on your own now
$sql = null;
$sqlCust = null;
$debug = "";

if($_REQUEST['api'] === 'brands'){
    $sql = "SELECT id,subcategory AS brand,slug FROM catalogue_subcats WHERE category=2 and subcategory!='' ORDER BY subcategory ASC";
}
if(isset($_REQUEST['id'])) {
    $itemId = $_REQUEST['id'];
}
if(isset($_REQUEST['preview'])) {
    $preview = true;
}
if($_REQUEST['api'] === 'items'){
    $isStockPage = false;
    $isItemListPage = true;
    $sqlSelect = "";
    $sqlSelectCommonStock = ",catalogue.subcategory AS brand,catalogue.detail_1 AS year";
    $sqlSelectCommonPrice = ",catalogue.price,catalogue.price_details";
    $sqlSelectCommonExcerpt = ",catalogue.detail_6 AS excerpt,catalogue.description AS brief";
    $sqlWhere = "";
    $sqlGroup = " GROUP BY catalogue.id,catalogue.name";
    $sqlOrder = " ORDER BY catalogue.upload_date DESC";
    $qLimit = " LIMIT 500";
    if(isset($_REQUEST['limit'])) {
        $qLimit = " LIMIT ".$_REQUEST['limit'];        
    }
    if($_REQUEST['spec'] === 'Live') {
        $isStockPage = true;
        $sqlSelect .= $sqlSelectCommonStock; 
        // $sqlSelect .= $sqlSelectCommonExcerpt;        
        $sqlSelect .= $sqlSelectCommonPrice;               
        $sqlWhere .= " AND catalogue.category=2";
		if($preview){
			$sqlWhere .= " AND catalogue.status=0";
		}else{
			$sqlWhere .= " AND catalogue.status=1";
		}  
    }
    if($_REQUEST['spec'] === 'Archive') {
        $isStockPage = true;
        $sqlSelect .= $sqlSelectCommonStock;
        // $sqlSelect .= $sqlSelectCommonExcerpt; 
        $sqlWhere .= " AND catalogue.category=2 AND catalogue.status=2";        

        if(isset($_REQUEST['brandId'])){
            $sqlWhere .= " AND catalogue.subcategory=".$_REQUEST['brandId'];
        }
        if(isset($_REQUEST['brandName'])){
            $sqlWhere .= " AND catalogue_subcat.slug='".$_REQUEST['brandName']."'";
        }else{
            $sqlWhere .= " AND catalogue.upload_date >= '2020-01-01'";
        }
    }
    if($_REQUEST['spec'] === 'Press') {
        $isStockPage = false;
        $sqlSelect .= ",catalogue.detail_2 AS source";
        $sqlWhere .= " AND catalogue.category=4 AND catalogue.status=1";
    }
    if($_REQUEST['spec'] === 'Testimonials') {
        $isStockPage = false;
        $sqlSelect .= ",catalogue.detail_2 AS related";
        $sqlWhere .= " AND catalogue.category=3 AND catalogue.status=1";
    }
    if($_REQUEST['spec'] === 'News') {
        $isStockPage = false;
        $sqlSelect .= ",catalogue.detail_2 AS source";
        $sqlWhere .= " AND catalogue.category=5 AND catalogue.status=1";
    }
    if($_REQUEST['spec'] === 'History') {
        $isStockPage = false;
        $sqlSelect .= ",catalogue.detail_2 AS source";
        $sqlWhere .= " AND catalogue.category=10 AND catalogue.status=1";
    }
    if($_REQUEST['spec'] === 'Restoration') {
        $isStockPage = false;
        // $sqlSelect .= ",catalogue.detail_2 AS source";
        $sqlWhere .= " AND catalogue.category=11 AND catalogue.status=1";
    }
    if($_REQUEST['spec'] === 'Homepage') {
        $sqlCust = "(SELECT";
        $sqlCust .= returnSqlCommonSelectItems();
        $sqlCust .= $sqlSelectCommonExcerpt;
        $sqlCust .= returnSqlCommonSelectBrandArr();
        $sqlCust .= " FROM catalogue AS catalogue";
        $sqlCust .= returnSqlInnerJoinBrands();
        $sqlCust .= " WHERE catalogue.category=3 AND catalogue.status=1";
        $sqlCust .= " ORDER BY catalogue.upload_date DESC";
        $sqlCust .= " LIMIT 2";
        $sqlCust .= ") UNION (SELECT";
        $sqlCust .= returnSqlCommonSelectItems();
        $sqlCust .= $sqlSelectCommonExcerpt;
        $sqlCust .= returnSqlCommonSelectBrandArr();
        $sqlCust .= " FROM catalogue AS catalogue";
        $sqlCust .= returnSqlInnerJoinBrands();
        $sqlCust .= " WHERE catalogue.category=5 AND catalogue.status=1";
        $sqlCust .= " ORDER BY catalogue.upload_date DESC";
        $sqlCust .= " LIMIT 2";
        $sqlCust .= ")";
    }
    
    if(isset($itemId)) {
        $sqlGroup = "";
        $isItemListPage = false;
        if($_REQUEST['spec'] === 'ItemRelated') $isItemListPage = true;
        $sqlSelect .= ",catalogue.detail_6 AS excerpt";
        $sqlSelect .= ",catalogue.description";
        $sqlWhere = " AND catalogue.id=".$itemId;
        // $sqlWhere .= " OR catalogue.id_xtra=".$itemId.")";
    }else{
        $sqlSelect .= $sqlSelectCommonExcerpt;
    }
    // id,status,name,slug,category,brand,year,price,price_details,excerpt,createdAt,updatedAt,image

//     $sql = <<<EOD
//     SELECT `catalogue`.`id`, `catalogue`.`status`, `catalogue`.`name`, `catalogue`.`slug`, `catalogue`.`category`, `catalogue`.`subcategory` AS `brand`, `catalogue`.`detail_1` AS `year`, `catalogue`.`price`, `catalogue`.`price_details`, `catalogue`.`detail_1` AS `excerpt`, `catalogue`.`upload_date` AS `createdAt`, `catalogue`.`upload_date` AS `updatedAt`, `catalogue`.`image_large` AS `image`, `catalogue_subcat`.`id` AS `catalogue_subcat.id`, `catalogue_subcat`.`subcategory` AS `catalogue_subcat.brand`, `catalogue_subcat`.`slug` AS `catalogue_subcat.slug` FROM `catalogue` AS `catalogue` INNER JOIN `catalogue_subcats` AS `catalogue_subcat` ON `catalogue`.`subcategory` = `catalogue_subcat`.`id` WHERE `catalogue`.`id_xtra` = 0 AND `catalogue`.`category` = 2 AND `catalogue`.`status` = 1 GROUP BY `catalogue`.`id`,`catalogue`.`name` ORDER BY `catalogue`.`upload_date` DESC
// EOD;
$sql = "SELECT ";
$sql .= returnSqlCommonSelectItems();
$sql .= $sqlSelect;
// $sql .= ",`catalogue_subcat`.`id` AS `catalogue_subcat.id`, `catalogue_subcat`.`subcategory` AS `catalogue_subcat.brand`, `catalogue_subcat`.`slug` AS `catalogue_subcat.slug`";
$sql .= returnSqlCommonSelectBrandArr();
// $sql .= ",catalogue_subcat.id, catalogue_subcat.subcategory, catalogue_subcat.slug";
$sql .= " FROM catalogue AS catalogue";
// $sql .= " INNER JOIN `catalogue_subcats` AS `catalogue_subcat` ON `catalogue`.`subcategory` = `catalogue_subcat`.`id`";
$sql .= returnSqlInnerJoinBrands();
$sql .= " WHERE catalogue.id_xtra = 0";
$sql .= $sqlWhere;
$sql .= $sqlGroup;
$sql .= $sqlOrder;
$sql .= $qLimit;
}

if($sqlCust){
    $sql = $sqlCust;
}
// echo $sql;
if($sql){
    $debug .= $sql;

    if (!$result = $mysqli->query($sql)) {
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
        // $dbdata[]=$row;
        $row['id'] = intval($row['id']);
        // $row['name'] = htmlspecialchars($row['name']);//£        
        // $row['name'] = removeBadChars($row['name']);
        $row['name'] = removeBadChars($row['name']);
        // echo '<br>POUND ???: '.$itemName;
        // echo '<br>'.$itemName;

        $row['status'] = intval($row['status']);
        $row['category'] = intval($row['category']);
        if($isStockPage){
            $row['brand'] = intval($row['brand']);
            $row['year'] = intval($row['year']);
            if(isset($row['price'])) $row['price'] = intval($row['price']);
        }
        if($isItemListPage && !$row['excerpt']){
            $tmpExcerpt = strip_tags($row['brief']);
            $tmpExcerpt = removeBadChars($tmpExcerpt);
            $row['excerpt'] = implode(' ', array_slice(explode(' ', $tmpExcerpt), 0, 30));
        }
        if($isItemListPage && $row['excerpt']){
            $row['excerpt'] = removeBadChars($row['excerpt']);
            $row['brief'] = '';
        }        
        
        if(!$isItemListPage && isset($row['description'])){
            // REF: https://www.w3resource.com/php/function-reference/addcslashes.php
            $description = removeBadChars($row['description']);
            $row['description'] = addcslashes($description,'"');
        }

        $row['catalogue_subcat'] = array();
        $row['catalogue_subcat']['id'] = intval($row['catalogue_subcat_id']);
        $row['catalogue_subcat']['brand'] = $row['catalogue_subcat_brand'];
        $row['catalogue_subcat']['slug'] = $row['catalogue_subcat_slug'];
        $dbdata[]=$row;
        $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['name'].' | ';
    }

    $ignore = false;
    if($_REQUEST['spec'] === 'ItemRelated') $ignore = true;
    if(!$ignore && isset($itemId)){
        $sql = "SELECT id, name, image_large AS image FROM catalogue WHERE id_xtra=$itemId AND image_large!=''";
        $sql .= " ORDER BY position_initem, id ASC";        

        if (!$result = $mysqli->query($sql)) {
            // return "Sorry, the website is experiencing problems.";
            // exit;
        }else{
            if(mysqli_num_rows($result) === 0 ){
                // return "Nothing to do";
                // exit;
            } else {
                $tmpCount = 0;
                //Fetch into associative array
                while ( $row = $result->fetch_assoc())  {
                    $tmpCount = $tmpCount + 1;
                    $row['id'] = intval($row['id']);
                    $row['name'] = removeBadChars($row['name']);    
                    if(!$row['name']) $row['name'] = $itemName;
                    $dbdata[]=$row;
                    $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['name'].' | ';
                }
            }
        }
        
    }
    
    if($printDebug){
        echo $debug;
        echo $sql;
        echo '<br>------------<br>';
    }

    echo json_encode($dbdata, JSON_PRETTY_PRINT);

    // The script will automatically free the result and close the MySQL
    // connection when it exits, but let's just do it anyways
    $result->free();
    $mysqli->close();
}
// items for sale
// SELECT `catalogue`.`id`, `catalogue`.`status`, `catalogue`.`name`, `catalogue`.`slug`, `catalogue`.`category`, `catalogue`.`subcategory` AS `brand`, `catalogue`.`detail_1` AS `year`, `catalogue`.`price`, `catalogue`.`price_details`, `catalogue`.`description` AS `excerpt`, `catalogue`.`upload_date` AS `createdAt`, `catalogue`.`upload_date` AS `updatedAt`, `catalogue`.`image_large` AS `image`, `catalogue_subcat`.`id` AS `catalogue_subcat.id`, `catalogue_subcat`.`subcategory` AS `catalogue_subcat.brand`, `catalogue_subcat`.`slug` AS `catalogue_subcat.slug` FROM `catalogue` AS `catalogue` INNER JOIN `catalogue_subcats` AS `catalogue_subcat` ON `catalogue`.`subcategory` = `catalogue_subcat`.`id` WHERE `catalogue`.`id_xtra` = 0 AND `catalogue`.`category` = 4 AND `catalogue`.`status` = 1 ORDER BY `catalogue`.`upload_date` DESC;
?>