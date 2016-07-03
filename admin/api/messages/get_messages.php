<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of all the admin_messages
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false)) {

    // The user is logged in.
    // Lets get the messages from the DB
    $sql = "SELECT admin_users.username, admin_messages.title, admin_messages.message, admin_messages.post_time FROM admin_messages
    INNER JOIN admin_users
    ON admin_messages.poster_id = admin_users.user_id
    ORDER BY admin_messages.message_id DESC LIMIT 10";
    $result = $mysqli->query($sql);

    $data->messages = array();
    while ($message = mysqli_fetch_object($result)) {
        $data->messages[]= $message;
    }

    $data->status = "success";

}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_messages ".$data->status);

echo json_encode($data);
