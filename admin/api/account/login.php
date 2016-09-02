<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");


$data = new stdClass();
$data->status = "failed";

// Checking if the client provided the needed information
if (isset($_POST['username']) && isset($_POST['password'])) {
    // Looking up the username in the admin_users database
    $stmt = $mysqli->prepare("SELECT username, password_hash, user_id, permissions, organization_id FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    // Checking if we found them
    if ($result) {

        // We found the user
        $permissions = json_decode($result->permissions);

        // Before we let the client login, lets check if this user has the correct permissions to login
        if ($permissions && $permissions->login) {

            // Now we check if the password entered is correct
            if (password_verify($_POST['password'], $result->password_hash)) {

                // The password that the user entered was correct.
                $data->status = "success";

                // Now we can set the session values
                $_SESSION['admin_user_id'] = $result->user_id;
                $_SESSION['admin_username'] = $_POST['username'];
                $_SESSION['organization_id'] = $result->organization_id;

                // Lets fix the users permissions (incase it needs it)
                $permissions = check_and_fix_permissions();
                $data->username = $result->username;
                $data->organization_id = $result->organization_id;
                $data->permissions = $permissions;
                $data->user_id = $_SESSION['admin_user_id'];
            }
        } else {
            $data->status = "permission denied";
        }
    }
}

// Logging
if (isset($result) && $result) {
    $identifier = $result->user_id;
} else if (isset($_POST['username'])) {
    $identifier = $_POST['username'];
} else {
    $identifier = "";
}
log_activity($identifier, "login ".$data->status);

echo json_encode($data);
