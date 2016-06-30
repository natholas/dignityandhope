<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to view all of the admin users
// Lets check if the user has the correct permissions to do this
$requested_permissions = array("view_admin_users");
if (check_user($requested_permissions, false)) {

    // The user has the correct permissions.
    // Lets get the list of usernames and email addresses
    // The sql excludes users without the same organization_id unless you have organization_id set to 0 (dignity and hope)
    $org = $_SESSION['organization_id'];
    if ($org != 0) {
        $sql = "SELECT user_id, username, email, organization_id FROM admin_users WHERE organization_id = $org";
    } else {
        $sql = "SELECT user_id, username, email, organization_id FROM admin_users";
    }

    $result = $mysqli->query($sql);
    $users = array();

    while($user = mysqli_fetch_object($result)) {
        $users[] = $user;
    }

    $data->status = "success";
    $data->users = $users;

} else {
    $data->status = "permission denied";
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "view_users ".$data->status);

echo json_encode($data);
