<?php

function sendEmail($to, $subject, $msg){
    try{
        $headers = 'From: Newsletter App <NewsletterApp@onebrick.org>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $msg, $headers);
    } catch (Exception $e){
        return "An error occurred sending your email: " . $e;
    }

    return "Email sent.";
}

?>
