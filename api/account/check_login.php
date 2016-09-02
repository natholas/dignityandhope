<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to know if they are currently logged in.
// Lets see what we have to work with
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {

    // The client has a valid session set up.
    // This means that all we have to do is return them their username and permissions
    $data->status = "success";
    $data->username = $_SESSION['email'];
    $data->user_id = $_SESSION['user_id'];
}

echo json_encode($data);
