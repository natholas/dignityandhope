<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client forgot their password and would like us to send a reset it
// We need to generate a code that they will have to confirm in reset_password.php
// First though we have to check if the email address that they entered belongs to a real user

if (isset($_POST['email'])) {

    // We check the database to see if the email address is real.
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if ($result) {

        // The email address is real.

        // Now we neeed to generate a random code to send them
        // We also save it in the session along with the email
        $_SESSION['reset_code'] = generateRandomString(8);
        $_SESSION['reset_email'] = $_POST['email'];
        $_SESSION['reset_count'] = 0;

        // And send the code to the email address
        $to      = $_POST['email'];
        $subject = 'Password reset code';
        $message = 'Your reset code is: '.$_SESSION['reset_code'];
        $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
            'Reply-To: nathansecodary@gmail.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);

        $data->status = "success";
    }
}

echo json_encode($data);


function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
