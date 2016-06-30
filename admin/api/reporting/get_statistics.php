<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the statistics
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false)) {

    // The user is logged in and doesn't need any special permissions to view the statistics

    // Lets get all of the investments
    $sql = "SELECT status FROM investments WHERE status != 'REMOVED'";
    $result = $mysqli->query($sql);

    $data->investments = new stdClass();
    $data->investments->draft = 0;
    $data->investments->pending = 0;
    $data->investments->live = 0;
    $data->investments->ended = 0;

    while($item = mysqli_fetch_object($result)) {
        if ($item->status == "DRAFT") {
            $data->investments->draft += 1;
        } else if ($item->status == "PENDING") {
            $data->investments->pending += 1;
        } else if ($item->status == "LIVE") {
            $data->investments->live += 1;
        } else if ($item->status == "ENDED") {
            $data->investments->ended += 1;
        }
    }

    // Lets get all of the products
    $sql = "SELECT status FROM products WHERE status != 'REMOVED'";
    $result = $mysqli->query($sql);

    $data->products = new stdClass();
    $data->products->draft = 0;
    $data->products->pending = 0;
    $data->products->live = 0;
    $data->products->ended = 0;

    while($item = mysqli_fetch_object($result)) {
        if ($item->status == "DRAFT") {
            $data->products->draft += 1;
        } else if ($item->status == "PENDING") {
            $data->products->pending += 1;
        } else if ($item->status == "LIVE") {
            $data->products->live += 1;
        }
    }

    $data->status = "success";

}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_statistics  ".$data->status);

echo json_encode($data);
