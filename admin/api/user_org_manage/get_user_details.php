<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the details of an admin_user_id
// Lets check if they sent all the needed data
if (isset($_POST['user_id'])) {

    // and if the user has the correct permissions to do this
    $requested_permissions = array("view_admin_users");
    if (check_user($requested_permissions, false)) {

        // The user has the correct permissions.
        // Lets get the details for this user_id
        $stmt = $mysqli->prepare("SELECT user_id, username, email, organization_id, permissions FROM admin_users WHERE user_id = ?");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_object();

        if ($result) {

            // We found the user.
            // Now we need to check if the client is allowed to see the details of this user
            $result->permissions = json_decode($result->permissions);
            
            if (($_SESSION['organization_id'] == 0 || $_SESSION['organization_id'] == $result->organization_id)
            && (get_rank() > $result->permissions->rank || $_SESSION['admin_user_id'] == $_POST['user_id'])) {

                // Everything looks ok.
                $data->status = "success";
                $data->user = $result;

            } else {
                $data->status = "permission denied";
            }
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
log_activity($identifier, "view_user_details ".$data->status);

echo json_encode($data);
