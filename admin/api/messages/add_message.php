<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to add an admin message
// Lets check if the user is logged in and has the correct permissions
$permissions_needed = array("add_admin_message");
if (check_user($permissions_needed, false)) {

    // The user is logged in.
    // Lets see if the user sent all the needed data
    if (isset($_POST['title']) && isset($_POST['message'])) {

        // The user has sent all the needed data
        // Lets add it to the database
        $stmt = $mysqli->prepare("INSERT INTO admin_messages (title, message, poster_id, post_time) VALUES (?,?,?,?)");
        $stmt->bind_param("ssii", $_POST['title'], $_POST['message'], $_SESSION['admin_user_id'], time());
        $stmt->execute();
        $data->status = "success";

    }
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "add_message ".$data->status);

echo json_encode($data);
