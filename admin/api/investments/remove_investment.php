<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to remove an investment
// Lets check if the user is allowed to do this
$permissions_needed = array("remove_investment");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['investment_id'])) {

        // The client has provided the correct data.
        // Lets check if this investment actually exisits
        $stmt = $mysqli->prepare("SELECT organization_id FROM investments WHERE investment_id = ? AND status != 'LIVE'");
        $stmt->bind_param("i", $_POST['investment_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();

        if ($result) {

            // We found the investment
            // We should only allow a user to remove this investment if they are in the same organization as the investment (or if they are in dignity and hope)
            if ($result->organization_id == $_SESSION['organization_id'] || $_SESSION['organization_id'] == 0) {

                // This user is allowed to remove this investment
                // We dont actually remove the investment from the database.
                // We just set the status to "REMOVED"
                $stmt = $mysqli->prepare("UPDATE investments SET status = 'REMOVED' WHERE investment_id = ?");
                $stmt->bind_param("i", $_POST['investment_id']);
                $stmt->execute();

                $data->status = "success";
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
log_activity($identifier, "remove_investment ".$data->status);

echo json_encode($data);
