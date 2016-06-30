<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to create a new user
// Lets check if the user is allowed to do this
$permissions_needed = array("add_admin_user");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['permissions']) && isset($_POST['organization_id']) && isset($_POST['send_mail'])) {
        // The client has provided the correct data.

        // We need to check if the username and email address that the client has provided are not used else where
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $_POST['username'], $_POST['email']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 0) {

            // The username and email are unique.
            // Now lets check that the organization_id that the client sent actually exists and is active
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE organization_id = ? AND active = 1");
            $stmt->bind_param("i", $_POST['organization_id']);
            $stmt->execute();

            if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] != 0) {

                // The organization_id exists
                // Lets hash the password that they provided.
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // We also need to make sure that the user entering the data is not giving the new user permissions that he himself doesn't have
                $requested_permissions = $_POST['permissions'];
                $user_permissions = (array)get_permissions();

                $new_user_permissions = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/admin/api//defaults/permissions.json");
                $new_user_permissions = json_decode($new_user_permissions, true);

                // We run through all of the default permissions
                foreach($new_user_permissions as $key => $value) {

                    // Then, if is not the rank
                    if ($key != "rank") {

                        // We see if it is enabled for the creator and enabled for the new user
                        if (isset($user_permissions[$key]) && isset($requested_permissions[$key]) && $user_permissions[$key] && $requested_permissions[$key]) {

                            // Then we enable it in the new users permissions
                            $new_user_permissions[$key] = true;

                        }

                    } else {

                        // If this key is the rank then we make sure that it is lower than the creators rank
                        if ($requested_permissions['rank'] >= $user_permissions["rank"]) {
                            $requested_permissions['rank'] = $user_permissions["rank"] - 1;
                        }
                        $new_user_permissions['rank'] = $requested_permissions['rank'];
                    }
                }

                // There was no issues with the permissions.
                // Now we need to see if the user is allowed to set the organization_id that it setup
                // a value of 0 means dignity and hope. If the clients organization_id is 0 they are allowed to add any organization_id
                if ($_SESSION['organization_id'] == 0 || $_SESSION['organization_id'] == $_POST['organization_id']) {

                    // We can now add the new user into the database
                    $stmt = $mysqli->prepare("INSERT INTO admin_users (username, email, password_hash, permissions, organization_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $_POST['username'], $_POST['email'], $password_hash, json_encode($new_user_permissions), $_POST['organization_id']);
                    $stmt->execute();

                    // Now we need to see if the client asked us to send the new user an email
                    if ($_POST['send_mail']) {

                        // We need to send an email to the new user
                        $to      = $_POST['email'];
                        $subject = 'Your new account for dignity & hope';
                        $message = "Your new account for the admin section of the dignity and hope website is ready. \r\n\r\n";
                        $message.= 'Your username is: '.$_POST['username']."\r\n";
                        $message.= 'And your password is: '.$_POST['password'];
                        $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                            'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                        mail($to, $subject, $message, $headers);
                    }

                    $data->status = "success";
                    $data->user_id = $stmt->insert_id;
                }
            }

        } else {
            $data->status = "not unique";
        }
    }
} else {
    $data->status = "permission denied";
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "create_user ".$data->status);

echo json_encode($data);
