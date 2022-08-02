<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors', 'on');

require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../ajax/dbfuncs.php");


//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
// require 'vendor/autoload.php';
require_once(__DIR__ . "/../../vendor/autoload.php");


class emailer
{
    private $EMAIL_HOST = EMAIL_HOST;
    private $EMAIL_USERNAME = EMAIL_USERNAME;
    private $EMAIL_PASSWORD = EMAIL_PASSWORD;
    private $EMAIL_PORT = EMAIL_PORT;

    function sendSimpleEmail($from, $from_name, $to, $subject, $message)
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $ret = array();
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->EMAIL_HOST;                      //Set the SMTP server to send through
            $mail->SMTPDebug = 0;                                       //disable Sending debug as php response
            $mail->Debugoutput = 'html';
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->EMAIL_USERNAME;                  //SMTP username
            $mail->Password   = $this->EMAIL_PASSWORD;                  //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $this->EMAIL_PORT;                      //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($from, 'Mailer');
            // $mail->addCC('cc@example.com');
            $to = preg_replace('/,/', ';', $to);
            if (str_contains($to, ';')) {
                $toArr = explode(";", $to);
                foreach ($toArr as $eachEmail) {
                    error_log($eachEmail);
                    $mail->AddAddress($eachEmail);
                }
            } else {
                $mail->AddAddress($to);
            }
            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    =  $message;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            $ret['status'] = "sent";
        } catch (Exception $e) {
            $ret['status'] = "failed";
            $ret['log'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo} {$e}";
        }
        error_log(print_r($ret, TRUE));
        return $ret;
    }
}
