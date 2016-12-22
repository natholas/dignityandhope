<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get their order history
// Lets see if they are logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {

    // The client has a valid session set up.
    // Lets get their orders.
    $stmt = $mysqli->prepare('SELECT * FROM order_history_view WHERE user_id = ? AND type = "investment"');
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = array();

    while ($order_item = mysqli_fetch_object($result)) {
        $order_item->images = json_decode($order_item->images);
        $order = new stdClass();
        $order->order_id = $order_item->order_id;
        $order->order_total = $order_item->order_total;
        $order->order_time = $order_item->order_time;
        $order->order_status = $order_item->order_status;
        $order->currency_code = $order_item->currency_code;
        $order->conversion_rate = $order_item->conversion_rate;
        unset($order_item->order_id);
        unset($order_item->order_total);
        unset($order_item->order_time);
        unset($order_item->order_status);
        unset($order_item->currency_code);
        unset($order_item->conversion_rate);
        $order->order_items = array();
        $order->order_items[] = $order_item;
        $orders[] = $order;
    }

    $data->status = "success";
    $data->orders = $orders;
}

echo json_encode($data);
