<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// Lets check if the client has provided all of the needed data
if (isset($_POST['user_id']) && isset($_POST['email']) && isset($_POST['permissions']) && isset($_POST['organization_id'])) {

    // And if the user is allowed to do this
    $permissions_needed = array();
    if (check_user($permissions_needed, false)) {

        // Lets see if the email address that the client provided is not used for another account
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $_POST['email'], $_POST['user_id']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 0) {

            // The client has provided all of the needed info and is allowed to edit this user.
            // We can now select the permission data from the db to compare
            $stmt = $mysqli->prepare("SELECT permissions, organization_id FROM admin_users WHERE user_id = ?");
            $stmt->bind_param("i", $_POST['user_id']);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_object();

            if ($result && ($_POST['user_id'] == $_SESSION['admin_user_id']) || check_permission("edit_admin_user")) {

                // We found the user.
                // Now lets check that the organization_id that the client sent actually exists
                $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE organization_id = ? AND active = 1");
                $stmt->bind_param("i", $_POST['organization_id']);
                $stmt->execute();

                if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 1) {

                    // The organization_id exists
                    // Lets just decode the old permissions that we got from the database
                    $old_permissions = json_decode($result->permissions, true);
                    // And decode the permissions that the client wants to set for this user
                    $requested_permissions = $_POST['permissions'];
                    // And get an up to date list of the default permissions
                    // We us the default permisions instead of just the old ones to avoid any missing permissions
                    $new_permissions = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/admin/api/defaults/permissions.json"), true);
                    // And finally we can convert the current users permissions into an array so that we can use it below
                    $user_permissions = get_permissions();
                    $user_permissions_array = (array)$user_permissions;

                    // Before we do anything lets make sure that the editor has a higher rank than the user he wishes to edit
                    if ($user_permissions->rank > $old_permissions['rank'] || $_SESSION['admin_user_id'] == $_POST['user_id']) {

                        // And that the editor is in organization_id 0 (dignity and hope) or the same as the sender
                        if ($result->organization_id == $_SESSION['organization_id'] || $_SESSION['organization_id'] == 0) {

                            // And lets ignore the clients request for a organization_id change if the client is not organization_id 0
                            if ($_SESSION['organization_id'] != 0) {
                                $_POST['organization_id'] = $result->organization_id;
                            }

                            // We run through all of the default permissions
                            foreach($new_permissions as $key => $value) {

                                // Then, if is not the rank
                                if ($key != "rank") {

                                    // We see if it is enabled for the creator (allowed to edit this permission)
                                    if (isset($user_permissions_array[$key]) && $user_permissions_array[$key] && isset($requested_permissions[$key])) {

                                        // Then we switch it in the requested value
                                        $new_permissions[$key] = $requested_permissions[$key];

                                    }

                                    // If this permission is disabled for the creator then we add the old permission back in
                                    // unless it was not set before (added a new permission) in which case we leave it as the default
                                    else if (isset($old_permissions[$key])) {
                                        $new_permissions[$key] = $old_permissions[$key];
                                    }

                                } else {

                                    // If this key is the rank then we make sure that it is lower than the editors rank
                                    if (isset($requested_permissions['rank']) && $requested_permissions['rank'] < $user_permissions_array["rank"]) {
                                        $new_permissions['rank'] = $requested_permissions['rank'];
                                    } else {
                                        // Otherwise we keep the old rank
                                        $new_permissions['rank'] = $old_permissions['rank'];
                                    }
                                }
                            }

                            // Lets check if they want to change the password as well
                            // Lets also see if they have the needed permissions or if it is their own profile
                            if (isset($_POST['newpassword']) && isset($_POST['send_mail']) && (check_permission('change_admin_password'))) {

                                // Editor is allowed to edit this users password
                                // Lets hash the new password
                                $new_password = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);

                                // and update it in the database
                                $stmt = $mysqli->prepare("UPDATE admin_users SET password_hash = ? WHERE user_id = ?");
                                $stmt->bind_param("si", $new_password, $_POST['user_id']);
                                $stmt->execute();

                                // Checking if the client wants us to send the new password to the user
                                if ($_POST['send_mail']) {

                                    // We need to send the new password to the user
                                    // Obviously we need to take the new email address that they might have just changed
                                    $to      = $_POST['email'];
                                    $subject = 'New password for dignity & hope';
                                    $message = "Your password for the admin section of the dignity and hope website has changed. \r\n";
                                    $message.= 'Your new password is: '.$_POST['newpassword'];
                                    $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                                        'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                                        'X-Mailer: PHP/' . phpversion();

                                    mail($to, $subject, $message, $headers);

                                }
                            }

                            // Updating the user in the database
                            $stmt = $mysqli->prepare("UPDATE admin_users SET username = ?, email = ?, organization_id = ?, permissions = ? WHERE user_id = ?");
                            $stmt->bind_param("ssisi", strip_tags($_POST['username']), strip_tags($_POST['email']), $_POST['organization_id'], json_encode($new_permissions), $_POST['user_id']);
                            $stmt->execute();

                            $data->status = "success";

                        } else {
                            $data->status = "permission denied3";
                        }
                    } else {
                        $data->status = "permission denied2";
                    }
                }
            }
        }
    } else {
        $data->status = "permission denied1";
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
