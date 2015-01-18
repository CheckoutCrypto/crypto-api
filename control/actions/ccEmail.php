<?php

class ccEmail{
function get_include_contents($filename, $variablesToMakeLocal) {
    extract($variablesToMakeLocal);
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}

function sendEmail($args){
    $data = array();

    if(isset($args['action'])) {
        $data['link'] = $args['link'];
        $data['title'] = 'CheckoutCrypto '.$args['action'].' request is pending confirmation';
        $data['coin_name'] = $args['coin_code'];
        $data['address'] = $args['address'];
        $data['email'] = $args['email'];
        if($args['action'] == 'withdraw') {
            $data['coin_amount'] = $args['coin_amount'];
        }
    } else {
        return FALSE;
    }

    require '../2fa/PHPMailer/PHPMailerAutoload.php';
    //Create a new PHPMailer instance
    $mail = new PHPMailer();

    //Tell PHPMailer to use SMTP
    $mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 2;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    //Set the hostname of the mail server
    $mail->Host = 'mail.coingateway.net';

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 993;

    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'tls';

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = "";

    //Password to use for SMTP authentication
    $mail->Password = "";

    //Set who the message is to be sent from
    $mail->setFrom('noreply@coingateway.net', 'coingateway.net');

    //Set an alternative reply-to address
    $mail->addReplyTo('noreply@coingateway.net', 'coingateway.net');

    //Set who the message is to be sent to
    $mail->addAddress($data['email']);

    //Set the subject line
    $mail->Subject = 'Pending Withdraw Notice';

	/// Static email content
    $data['content'] = 'A request which needs your confirmation has been created. To confirm the request please click this link or copy the address and visit the it in your browser. The details of the request are listed below';
    $data['footer'] = 'If you did not request this, please change your password immediately as your account may be compromised. For further assistance contact';
    $data['footer_link'] = 'mailto:support@yoursite.net';
    $data['footer_link_text'] = 'support@yoursite.net'; 
    //logo
    $data['logo'] = '<img style="width: 200px;display: block;  margin-left: auto;  margin-right: auto;" src="https://www.yoursite.net/yourlogo.jpg">';

    $body = $this->get_include_contents('../2fa/templates/email_content_ob.php', $data);

$mail->msgHTML($body);

//Replace the plain text body with one created manually
$mail->AltBody = 'CoinGateway is awaiting your confirmation for a pending transaction.';

//Attach an image file
$mail->addAttachment('../2fa/images/coingateway_logo.jpg');

//send the message, check for errors
if (!$mail->send()) {
    //echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    //echo "Message sent!";
}
} /// end sendEmail()

}

?>
