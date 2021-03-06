<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");

$data = new stdClass();
$data->status = "failed";

// The client wishes to logout.
// All we need to do is destroy the session
session_destroy();


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $data->status = "success";
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "logout ".$data->status);


echo json_encode($data);
