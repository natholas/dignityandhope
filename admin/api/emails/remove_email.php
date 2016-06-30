<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to delete an email
// Lets check if the user is allowed to do this
$permissions_needed = array("draft_email");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['email_id'])) {

        // Now that the email has been sent we should also add it to the database
        $status = "SENT";
        $stmt = $mysqli->prepare("DELETE FROM emails WHERE email_id = ?");
        $stmt->bind_param("i", $_POST['email_id']);
        $stmt->execute();

        $data->status = "success";

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
log_activity($identifier, "remove_email ".$data->status);

echo json_encode($data);
