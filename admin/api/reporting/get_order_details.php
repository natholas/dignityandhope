<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the details of an order
// Lets check if the user is logged in and has the needed permissions
$permissions_needed = array("get_order_details");
if (check_user($permissions_needed, false)) {

    // The user is logged in and is allowed to get the order details
    // Lets see if the client provided the needed data
    if (isset($_POST['order_id']) && is_numeric($_POST['order_id'])) {

        // The user has provided all the needed data
        // Lets get the order details
        $stmt = $mysqli->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $_POST['order_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $order_items = array();
        while($item = mysqli_fetch_object($result)) {
            $order_items[] = $item;
        }

        $data->status = "success";
        $data->order_items = $order_items;

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
log_activity($identifier, "get_order_details ".$data->status);

echo json_encode($data);
