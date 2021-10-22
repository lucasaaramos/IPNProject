<?php

/** including PHPMailer downloaded from internet */
use PHPMailer\PHPMailer\PHPMailer;
require "PHPMailer/PHPMailer.php";
require "PHPMailer/Exception.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}


/** we need to verify all information we have received directly on PayPal
 *  including the URL available on PP website
 *  Posting the data back to PayPal, using curl.
 *  Setting URL for curl
 *  returning transfer to PayPal --> CURLOPT_RETURNTRANSFER, 1);
 *  Making a post request --> CURLOPT_POST, 1);
 */
$ch = curl_init();
// including the URL available on PP website
curl_setopt($ch, CURLOPT_URL, 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "cmd=_notify-validate&" . http_build_query($_POST));

/** executing and closing*/
$response = curl_exec($ch);
curl_close($ch);

/** verifying the response and setting conditions
 *  checking if it is verified and the payment has been done on PayPal side
 *  if $response is equal to "VERIFIED", it means that everything looks good
 *  if verified, set informations about the custumer and product (values related to the IPN simulator)
 *
 */

if ($response == "VERIFIED") {
    /** Information about the customer */
    $cEmail = $_POST['payer_email'];
    $name = $_POST['first_name'] . " " . $_POST['last_name'];
    $payer = $_POST['payer_id'];

    /** information about the product */

    $price = $_POST['mc_gross'];
    $currency = $_POST['mc_currency'];
    $item = $_POST['item_number'];
    $paymentStatus = $_POST['payment_status'];
    $qtd = $_POST['quantity'];


    /**
     * check if item/currency/paymentStatus/price is the same as set in the PP IPN simulator
     * sending the email to the customer
     * downloaded phpMailer to send the email
     * added phpMailer in this project folder
     * set --> use PHPMailer at the beggining of this program
     */
    if ($item == "wordpressPlugin" && $currency == "USD" && $paymentStatus == "Completed" && $price == 100) {

        /** creating a new object */
        $mail = new PHPMailer();

        /** determining who is sending the email - choose one! */
        $mail->setFrom("sales@gmail.com", "Sales");

        /** address to who you are sending the email --> customer email */
        $mail->addAddress($cEmail, $name);
        $mail->isHTML(true);

        /** sending the email to the customer */
        $mail->Subject = "Your Purchase Details";
        $mail->Body = "
				Hi, <br><br>
				Thank you for shopping. You payment has been completed!<br><br>
				
				<b>Payment status = <b> $paymentStatus <br><br>
				<b>Item number = <b> $item <br><br>
				<b>Quantity = <b> $qtd <br><br>
				<b>Customer ID = <b> $payer <br><br>
				
				Kind regards,
				Lucas A.
			";

        $mail->send();
    }
}

?>