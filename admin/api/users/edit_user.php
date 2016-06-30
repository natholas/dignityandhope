<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// Lets check if the client has provided all of the needed data
if (isset($_POST['user_id']) && isset($_POST['email']) && isset($_POST['first_name']) && isset($_POST['last_name'])) {

    // And if the user is allowed to do this
    $permissions_needed = array("edit_user");
    if (check_user($permissions_needed, false)) {

        // Lets see if the email address that the client provided is not used for another account
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $_POST['email'], $_POST['user_id']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 0) {

            // The client has provided all of the needed info and is allowed to edit this user.
            // Lets check if they want to change the password as well
            // Lets also see if they have the needed permissions or if it is their own profile
            if (isset($_POST['newpassword']) && isset($_POST['send_mail']) && $_POST['send_mail'] && (check_permission('change_user_password'))) {

                // Editor is allowed to edit this users password
                // Lets hash the new password
                $new_password = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);

                // and update it in the database
                $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_password, $_POST['user_id']);
                $stmt->execute();

                // Checking if the client wants us to send the new password to the user
                if ($_POST['send_mail']) {

                    // We need to send the new password to the user
                    // Obviously we need to take the new email address that they might have just changed
                    $to      = $_POST['email'];
                    $subject = 'New password for dignity & hope';
                    $message = "The password for your dignity and hope website has changed. \r\n";
                    $message.= 'Your new password is: '.$_POST['newpassword'];
                    $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                        'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    mail($to, $subject, $message, $headers);

                }
            }

            // Updating the user in the database
            $stmt = $mysqli->prepare("UPDATE users SET first_name = ?, Last_name = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", strip_tags($_POST['first_name']), strip_tags($_POST['last_name']), $_POST['email'], $_POST['user_id']);
            $stmt->execute();

            $data->status = "success";

        }
    } else {
        $data->status = "permission denied";
    }
}



// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "edit user ".$data->status);

echo json_encode($data);
