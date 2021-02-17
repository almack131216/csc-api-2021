<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$errors = array();
if ($_SERVER['REQUEST_METHOD'] === "POST") {
	$fName = $_POST['fName'];
	$lName = $_POST['lName'];
	$tel = $_POST['tel'];
	$address = nl2br($_POST['address']);
	$pCode = $_POST['pCode'];
	$mobile = $_POST['mobile'];
	$carMake = $_POST['carMake'];
	$carModel = $_POST['carModel'];
	$carYear = $_POST['carYear'];
	$carColor = $_POST['carColor'];
	$carNotes = nl2br($_POST['carNotes']);

  if (empty($_POST['email'])) {
    $errors[] = 'Email is empty';
  } else {
    $email = $_POST['email'];
    
    // validating the email
    //if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     //   $errors[] = 'Invalid email';
    //}
  }

  if (empty($errors)) {
    $date = date('F j, Y h:i A');

$emailBody = 'Name: '.$fName.' '.$lName.'<br>';
$emailBody .= 'Email: '.$email.'<br>';
$emailBody .= 'Contact No: '.$tel.'<br>';
$emailBody .= 'Mobile: '.$mobile.'<br>';
$emailBody .= 'Address: '.$address.'<br>';
$emailBody .= 'Post Code: '.$pCode.'<br>';
$emailBody .= '<br>';
$emailBody .= 'Make: '.$carMake.'<br>';
$emailBody .= 'Model: '.$carModel.'<br>';
$emailBody .= 'Year: '.$carYear.'<br>';
$emailBody .= 'Colour: '.$carColor.'<br>';
$emailBody .= 'Notes: '.$carNotes;
$emailBody .= '<br><br>Sent: '.$date;

//$emailBody = 'test';

	$to = "sales@classicandsportscar.ltd.uk";
	$email_from	= "sales@classicandsportscar.ltd.uk";
	$bcc = "alex@amactive.net";
	$bcc = "al_2003_1@hotmail.com";
	//$to = "200224@mailinator.com";

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=UTF-8\r\n";
	$headers .= "From: Website Request Form <" . $to . ">\r\n";
	$headers .= "Reply-To: ".$email."\r\n";
	$headers .= "Bcc: ".$bcc."\r\n";

    $subject = 'Website Car Request';	
    
	if(@mail($to,$subject,$emailBody,$headers,'-f'.$email_from)){
    //if (mail($to, $subject, $emailBody, $headers)) {
      $sent = true;	
    }
  }
}
?>

  <?php if (!empty($errors)) : ?> 

            {
  "status": "fail",
  "error":  <?php echo json_encode($errors) ?>
}
  <?php endif; ?>
  
  
  <?php if (isset($sent) && $sent === true) : ?> 

{
  "status": "success",
  "message": "Your data was successfully submitted"
}
  <?php endif; ?>