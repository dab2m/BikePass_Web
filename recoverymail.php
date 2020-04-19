<?php
include('db.php');

$status = "";
$message = "";
$json = array();
$post_json = json_decode(file_get_contents("php://input"), true);

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

//require 'PHPMailer\src\Exception.php';
//require 'PHPMailer\src\PHPMailer.php';
//require 'PHPMailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';


//Recover Email
if (isset($post_json["recovery_email"]) && isset($post_json["recovery_username"]) && isset($post_json["recovery_code"])) {

	$email = $post_json["recovery_email"];
	$username = $post_json["recovery_username"];
	$code = $post_json["recovery_code"];
	$mail = new PHPMailer(true);
	try {
		//Server settings
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = "smtp.gmail.com";                    // Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = "bikepass496@gmail.com";                     // SMTP username
		$mail->Password   = "Abc123Cde456";                               // SMTP password
		$mail->SMTPSecure = "tls";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		//Recipients
		$mail->setFrom("recovery@bikepass.com", "BikePass");
		$mail->addAddress($email, " User " . $username);     // Add a recipient
		$mail->addReplyTo('no-reply@bikepass.com', 'No reply');


		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Recovery mail from BikePass';
		$mail->Body    = "Please use code <b> $code </b> to reset your password!";

		$mail->send();

		$status = 1;
		$message = "Message has been sent";
		// echo 'Message has been sent';
	} catch (Exception $e) {
		$status = 0;
		$message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		// echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}

	$json = array("status" => $status, "message" => $message);
	echo json_encode($json, JSON_FORCE_OBJECT);
}

//Report Error
if (isset($post_json["image_data"]) && isset($post_json["message"]) && isset($post_json["username"])) {
	$image_data = $post_json["image_data"];
	$username = $post_json["username"];
	date_default_timezone_set('Europe/Istanbul');
	$timestamp = date("Y-m-d-H-i");
	$image_name = $username . $timestamp;
	$image_path = "images/temporary/$image_name.png";
	$image_src = "data:image/jpg;base64," . $image_data;
	file_put_contents($image_path, base64_decode($image_data));

	$mail = new PHPMailer(true);

	try {
		//Server settings
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = "smtp.gmail.com";                    // Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = "bikepass496@gmail.com";                     // SMTP username
		$mail->Password   = "Abc123Cde456";                               // SMTP password
		$mail->SMTPSecure = "tls";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//Recipients
		$mail->setFrom("bikepass496@gmail.com", "BikePass Auto Error Report");
		$mail->addAddress("bikepass496@gmail.com", "BikePass");     // Add a recipient
		$mail->addReplyTo('no-reply@bikepass.com', 'No reply');

		// Attachments
		$mail->addAttachment($image_path);         // Add attachments

		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Report from ' . $username;
		$mail->Body    = $post_json["message"];

		$mail->send();
		$status = 1;
		$message = "Message has been sent";
	} catch (Exception $e) {
		$status = 0;
		$message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}

	$json = array("status" => $status, "message" => $message);
	echo json_encode($json, JSON_FORCE_OBJECT);
}
