<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of orders
// Lets check if the user is logged in and has the needed permissions
$permissions_needed = array("get_orders");
if (check_user($permissions_needed, false)) {

    // The user is logged in and is allowed to get the orders
    // Lets see if the client wants to filter the results

    $sql = "SELECT * FROM orders";

    if (isset($_POST['filter'])) {

        // The client wants to filter the results.
        // Lets see if the filter that they entered matches one of the allowed filters
        if ($_POST['filter'] == "done") {
            $sql.= " WHERE status = 'DONE'";
        } else if ($_POST['filter'] == "cancelled") {
            $sql.= " WHERE status = 'CANCELLED'";
        } else if (is_numeric($_POST['filter'])) {
            $sql.= " WHERE user_id = ".$_POST['filter'];
        }
    }


    // Lets check if they wanted to order and limit the results
    if (isset($_POST['limit']) && isset($_POST['order_by'])) {

        // The customer wants to limit and order the results.
        // There are only a few things that we should allow them to order by.
        // Lets see if they wanted one of these
        $order_by = $_POST['order_by'];
        if ($order_by == "order_id ASC" || $order_by == "order_time ASC" || $order_by == "order_total ASC" || $order_by == "order_id DESC" ||  $order_by == "order_time DESC" || $order_by == "order_total DESC") {

            // The order_by is allowed.
            // Lets check if the limit that they set is ok
            if (is_numeric($_POST['limit']) && $_POST['limit'] > 0) {

                // Everything looks valid. Lets add it to the SQL
                $sql.= " ORDER BY ".$order_by." LIMIT ".$_POST['limit'];

            }
        }
    } else {

        // The client has not specified how they would like to limit and order the results
        // This means that we will apply a default
        $sql.= " ORDER BY order_id LIMIT 50";

    }

    // Lets get the orders using the sql that was chosen based on the users permissions
    $result = $mysqli->query($sql);

    // And add them all to the products array
    $orders = array();
    while($order = mysqli_fetch_object($result)) {
        $orders[] = $order;
    }

    $data->status = "success";
    $data->orders = $orders;

} else {
    $data->status = "permission denied";
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_orders ".$data->status);

echo json_encode($data);
