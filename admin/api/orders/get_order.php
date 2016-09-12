<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get an order
// Lets check if the user is logged in and has the correct permissions
$permissions_needed = array("get_orders");
if (check_user($permissions_needed, false)) {

    // We need to make sure that the client sent along the needed data
    if (isset($_POST['order_id']) && is_numeric($_POST['order_id'])) {

        // The client is allowed to view orders
        // Lets first get the info about this order.
        $sql = "SELECT orders.*, users.first_name, users.last_name, users.email, order_requests.payment_method, order_requests.masked_cc
		FROM orders

		INNER JOIN users
		ON orders.user_id = users.user_id

		INNER JOIN order_requests
		ON order_requests.order_id = orders.order_id

		WHERE order_requests.request_type = 'Assert' AND orders.order_id = ".$_POST['order_id'];

        $result_order = mysqli_fetch_object($mysqli->query($sql));

        if ($result_order) {

            $data->order = $result_order;

            $sql = "SELECT investments.name, investments.investment_id, order_items.quantity, order_items.amount_paid
            FROM order_items

            INNER JOIN orders
            ON order_items.order_id = orders.order_id

            INNER JOIN investments
            ON order_items.the_id = investments.investment_id
            WHERE order_items.type = 'investment' AND orders.order_id = ".$_POST['order_id'];

            $result_inv = $mysqli->query($sql);

            $sql = "SELECT products.name, products.product_id, order_items.quantity, order_items.amount_paid
            FROM order_items

            INNER JOIN orders
            ON order_items.order_id = orders.order_id

			INNER JOIN order_requests
            ON order_requests.order_id = orders.order_id

            INNER JOIN products
            ON order_items.the_id = products.product_id
            WHERE order_items.type = 'product' AND orders.order_id = ".$_POST['order_id'];

            $result_prod = $mysqli->query($sql);

            $data->order->investments = array();
            $data->order->products = array();

            while($result = mysqli_fetch_object($result_inv)) {
                $data->order->investments[] = $result;
            }

            while($result = mysqli_fetch_object($result_prod)) {
                $data->order->products[] = $result;
            }


            $data->status = "success";
        }
    }
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_order ".$data->status);

echo json_encode($data);
