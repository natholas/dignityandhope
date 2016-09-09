<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to update an order
// Lets check if the user is logged in and has the correct permissions
$permissions_needed = array("update_order");
if (check_user($permissions_needed, false)) {

    // We need to make sure that the client sent along the needed data
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        if ($_POST['status'] == "PROCESSED" || $_POST['status'] == "COMPLETED") {

            // The client has provided the correct data.
            // Lets select the old product
            $stmt = $mysqli->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmt->bind_param("i", $_POST['order_id']);
            $stmt->execute();
            $old_order = $stmt->get_result()->fetch_object();

            if ($old_order) {

                // We found the old order. Lets update it with the new status
                $stmt = $mysqli->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
                $stmt->bind_param("si", $_POST['status'], $_POST['order_id']);
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
log_activity($identifier, "update_order ".$data->status);

echo json_encode($data);
