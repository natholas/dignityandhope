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

    $sql = "SELECT orders.*, users.first_name, users.last_name, order_requests.payment_method, order_requests.masked_cc FROM orders";

    $sql .= " INNER JOIN order_items
	ON order_items.order_id = orders.order_id

	INNER JOIN order_requests
	ON order_requests.order_id = orders.order_id


	INNER JOIN investments
	ON investments.investment_id = order_items.the_id

	INNER JOIN users
	ON orders.user_id = users.user_id WHERE order_requests.request_type = 'Assert'";

    if (isset($_POST['filter'])) {

        // The client wants to filter the results.
        // Lets see if the filter that they entered matches one of the allowed filters

        $filter = json_decode($_POST['filter']);

        // The client wants to filter the results.
        // Lets see if the filter that they entered matches one of the allowed filters
        if (isset($filter->canceled) && !$filter->canceled) {
            $sql.= " AND orders.order_status != 'CANCELED'";
        }
        if (isset($filter->pending) && !$filter->pending) {
            $sql.= " AND orders.order_status != 'PENDING'";
        }
        if (isset($filter->completed) && !$filter->completed) {
            $sql.= " AND orders.order_status != 'COMPLETED'";
        }
        if (isset($filter->processed) && !$filter->processed) {
            $sql.= " AND orders.order_status != 'PROCESSED'";
        }
        if (isset($filter->organization_id) && is_numeric($filter->organization_id)) {
            $sql.= " AND organizations.organization_id = ".$filter->organization_id;
        }
    }

    // If the user is not in dignity and hope then we should only get the results for that organization
    if ($_SESSION['organization_id'] != 0) {
        $sql.= " AND organizations.organization_id = ".$_SESSION['organization_id'];
    }


    // Before we apply any limits. Lets get a quick count of the amount of rows that there are.
    // If the client asked for it
    if (isset($_POST['getcount'])) {
        $result = $mysqli->query($sql);
        if ($result) {
            $data->count = $result->num_rows;
        }
    }

    // Lets check if they wanted to order and limit the results
    if (isset($_POST['limit']) && isset($_POST['order_by']) && isset($_POST['offset'])) {

        // The customer wants to limit and order the results.
        // There are only a few things that we should allow them to order by.
        // Lets see if they wanted one of these
        $order_by = $_POST['order_by'];
        if ($order_by == "order_id ASC" || $order_by == "order_time ASC" || $order_by == "order_total ASC" || $order_by == "order_id DESC" ||  $order_by == "order_time DESC" || $order_by == "order_total DESC") {

            // The order_by is allowed.
            // Lets check if the limit that they set is ok
            if (is_numeric($_POST['limit']) && $_POST['limit'] > 0 && is_numeric($_POST['offset'])) {

                // Everything looks valid. Lets add it to the SQL
                $sql.= " ORDER BY ".$order_by." LIMIT ".$_POST['limit']." OFFSET ".$_POST['offset'];

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
