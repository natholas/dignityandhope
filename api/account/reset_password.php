<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client forgot their password and would like us to send a reset it
// A code should have been generated from send_password_reset.php
// The client should also have sent through a code and a new password.

if (isset($_SESSION['reset_code']) && isset($_SESSION['reset_email']) && isset($_SESSION['reset_count']) && isset($_POST['code']) && isset($_POST['newpassword'])) {

    // The client has all the required data.
    // Now lets see if the code that they entered matched the one that we sent.
    // We also check if the client has not already tried to enter the code 5 times
    if ($_SESSION['reset_code'] == $_POST['code'] && $_SESSION['reset_count'] < 5) {

        // The code matched.
        // Lets hash the password
        $password_hash = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);

        // We can now update the password
        $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->bind_param("ss", $password_hash, $_SESSION['reset_email']);
        $stmt->execute();

        $data->status = "success";

        // Now we need to unset the reset the session values
        unset($_SESSION['reset_code']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_count']);
    }

    else if ($_SESSION['reset_count'] >= 5) {

        // If the client has already tried to enter the code 5 times.
        // We remove the session values for the reset without telling the client
        unset($_SESSION['reset_code']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_count']);

    } else {

        // We increment the admin_reset_count.
        $_SESSION['reset_count'] += 1;

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
