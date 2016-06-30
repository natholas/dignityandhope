<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the totals
// Lets check if the user is logged in and has the needed permissions
$permissions_needed = array("get_totals");
if (check_user($permissions_needed, false)) {

    // The user is logged in and is allowed to get the totals
    // Lets see if they have specified a time frame to get the orders from
    if (isset($_POST['from']) && isset($_POST['to']) && is_numeric($_POST['from']) && is_numeric($_POST['to'])) {

        // The user has defined a timeframe
        $from = $_POST['from'];
        $to = $_POST['to'];

        // Lets get the order totals
        $stmt = $mysqli->prepare("SELECT order_total, order_time FROM orders WHERE order_time > ? AND order_time < ?");
        $stmt->bind_param("ii", $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();

        // We now need to add up the totals per day.
        $days = [];

        $datediff = $to - $from;
        $datediff = floor($datediff/(60*60*24));
        for ($i=0; $i <= $datediff; $i++) {
            $days[]= 0;
        }


        while($order = mysqli_fetch_object($result)) {
            $time = date($order->order_time);
            $datediff = $time - $from;
            $datediff = floor($datediff/(60*60*24));
            $days[$datediff] += $order->order_total;
        }

        $data->status = "success";
        $data->days = $days;

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
log_activity($identifier, "get_total ".$data->status);

echo json_encode($data);
