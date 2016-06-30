<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to know if they are currently logged in.
// Lets see what we have to work with
if (isset($_SESSION['admin_user_id']) && isset($_SESSION['admin_username'])) {

    // Lets fix the users permissions (incase it needs it)
    check_and_fix_permissions();

    // The client has a valid session set up.
    // This means that all we have to do is return them their username and permissions
    $stmt = $mysqli->prepare("SELECT permissions, username, organization_id FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if ($result) {
        $data->status = "success";
        $data->username = $result->username;
        $data->organization_id = $result->organization_id;
        $data->permissions = json_decode($result->permissions);
        $data->user_id = $_SESSION['admin_user_id'];
    }
}

// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "check_login ".$data->status);

echo json_encode($data);
