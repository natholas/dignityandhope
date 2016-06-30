<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of all the emails
// Lets check if the user is logged in
$permissions_needed = array("view_emails");
if (check_user($permissions_needed, false)) {

    // The user has the needed permissions
    // Lets see if they provided all the needed data
    if (isset($_POST['email_id'])) {


        $stmt = $mysqli->prepare("SELECT * FROM emails WHERE email_id = ?");
        $stmt->bind_param("i", $_POST['email_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();

        if ($result) {

            $data->email = $result;

            // We now need to see what the name of the customer that this email is sent to
            $sql = "SELECT first_name, last_name, email FROM users WHERE user_id = ".$result->user_id;
            $data->email->user = mysqli_fetch_object($mysqli->query($sql));

            $data->status = "success";
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
log_activity($identifier, "get_email ".$data->status);

echo json_encode($data);
