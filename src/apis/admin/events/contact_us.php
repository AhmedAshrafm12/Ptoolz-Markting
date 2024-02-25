<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if($_SERVER['REQUEST_METHOD']  == "POST"){

    
    //Load Composer's autoloader
    require '../../vendor/autoload.php';

    $email = filter_var($_POST['email'], FILTER_SANITIZE_SPECIAL_CHARS);
    $Subject = filter_var($_POST['subject'], FILTER_SANITIZE_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_SPECIAL_CHARS);
    


    try {
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP

    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'ahmashrafmans@gmail.com';                     //SMTP username
    $mail->Password   = '####';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 587;
    
    
    $mail->setFrom($email, 'Mailer');
    $mail->addAddress('ahmedashrafdev99@gmail.com', 'Joe User');     //Add a recipient

    $mail->Subject = $Subject;
    $mail->Body    = $body;

     $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

}

?>