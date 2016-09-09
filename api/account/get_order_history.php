<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get their order history
// Lets see if they are logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {

    // The client has a valid session set up.
    // Lets get their orders.
    $stmt = $mysqli->prepare('SELECT orders.order_id, orders.order_total, orders.order_time, orders.order_status,
        order_items.amount_paid, order_items.type, investments.investment_id, investments.name, investments.dob, investments.status, investments.images
        FROM orders
        INNER JOIN order_items
        ON orders.order_id = order_items.order_id
        INNER JOIN investments
        ON order_items.the_id = investments.investment_id
        WHERE orders.user_id = ? AND order_items.type = "investment";
    ');
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = array();

    while ($order_item = mysqli_fetch_object($result)) {
        $pos = findInOrderArray($orders, $order_item->order_id);
        $order_item->images = json_decode($order_item->images);
        if ($pos) {
            unset($order_item->order_id);
            unset($order_item->order_total);
            unset($order_item->order_time);
            unset($order_item->order_status);
            $orders[$pos]->order_items[] = $order_item;
        } else {
            $order = new stdClass();
            $order->order_id = $order_item->order_id;
            $order->order_total = $order_item->order_total;
            $order->order_time = $order_item->order_time;
            $order->order_status = $order_item->order_status;
            unset($order_item->order_id);
            unset($order_item->order_total);
            unset($order_item->order_time);
            unset($order_item->order_status);
            $order->order_items = array();
            $order->order_items[] = $order_item;
            $orders[] = $order;
        }
    }

    $data->status = "success";
    $data->orders = $orders;
}

function findInOrderArray($orders, $order_id)
{
    for ($i=0; $i < count($orders); $i++) {
        if ($orders[$i]->order_id == $order_id) {
            return $i;
        }
    }

    return false;
}

echo json_encode($data);
