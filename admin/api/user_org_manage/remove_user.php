<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to remove an admin user
// Lets check if the user is logged in and has the correct permissions
$requested_permissions = array("remove_admin_user");
if (check_user($requested_permissions, true)) {

    // And if they have provided all of the needed data
    if (isset($_POST['user_id'])) {

        // The user has the correct permissons and has provided all the needed data
        // We need to check is if the user has a higher rank than the user they is trying to remove
        // We also need to check the organization_id. A user should not be allowed to remove a user from another organization_id unless they are from 0 (dignity and hope)
        $stmt = $mysqli->prepare("SELECT permissions, organization_id FROM admin_users WHERE user_id = ?");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_object();

        if ($result) {
            // We found the user.
            // Lets get their rank and their organization_id
            $rank = json_decode($result->permissions)->rank;
            $organization_id = $result->organization_id;

            // And check them against the user that is trying to remove this user
            if ($rank < get_rank() && ($_SESSION['organization_id'] == 0 || $_SESSION['organization_id'] == $organization_id)) {

                // The current user has a higher rank and is in the same organization_id (or from dignity & hope)
                // This means that we can remove the user from the database
                $stmt = $mysqli->prepare("DELETE FROM admin_users WHERE user_id = ?");
                $stmt->bind_param("i", $_POST['user_id']);
                $stmt->execute();

                $data->status = "success";

            } else {
                $data->status = "permission denied";
            }
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
log_activity($identifier, "remove user ".$data->status);

echo json_encode($data);
