<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../../vendor/autoload.php';
$mail = new PHPMailer(true);
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';
$email = $_POST['email'] ?? '';
$mail->isSMTP();// Set mailer to use SMTP
$mail->CharSet = "utf-8";// set charset to utf8
$mail->SMTPAuth = true;// Enable SMTP authentication
$mail->SMTPSecure = 'tls';// Enable TLS encryption, `ssl` also accepted

$mail->Host = 'smtp.gmail.com';// Specify main and backup SMTP servers
$mail->Port = 587;// TCP port to connect to
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->isHTML(true);// Set email format to HTML

$mail->Username = "aedasr25@gmail.com";// SMTP username
$mail->Password = "zbjriewlhbvzyqfn";// SMTP password

$mail->setFrom($email, 'John Smith');//Your application NAME and EMAIL
$mail->Subject = $subject;//Message subject
$mail->MsgHTML("<p>".$body."</p>");// Message body
$mail->addAddress('ahmashrafmans@gmail.com', 'User Name');// Target email


$mail->send();
?>