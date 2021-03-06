<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require("checkout_functions/saferpay_call.php");
require("checkout_functions/assert_transaction.php");
require("checkout_functions/capture_transaction.php");

$data = new stdClass();
$data->status = "failed";

// This script should run when the client returns from the saferpay payment page and has sucessfully made the payment
// The request ID should be in the header.
// If this is not the case then we can redirect to the failed page
if (!isset($_GET['RequestId'])) {
	header("Location: http://".$_SERVER['HTTP_HOST']."api/checkout/checkout_failure.php");
	die("No request_id");
}

$request_id = $_GET['RequestId'];

// Of course we cant trust that a payment was successfully made just because someone visits this url.
// Lets get the token for the request that this RequestId relates to from the db
$time = time() - 60 * 20;
$stmt = $mysqli->prepare("SELECT token, order_id FROM order_requests WHERE RequestId = ?");
$stmt->bind_param("s", $request_id);
$stmt->execute();

$token = mysqli_fetch_object($stmt->get_result());

if ($token) {

	// Lets check with saferpay if the payment was successful
	$request_id = generateRandomString(32);
	$result = assert_transaction($request_id, $token->token);

	// If the transaction was successful then we can save the request in the db
	if ($result && $result['Transaction']['Status'] == "AUTHORIZED") {

		$time = time();
		$order_total = $result['Transaction']['Amount']['Value'] / 100;
		$order_id = $token->order_id;

		$request_type = "Assert";
		$stmt = $mysqli->prepare("INSERT INTO order_requests (order_id, request_type, RequestId, request_time, transaction_id, payment_method, masked_cc) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $order_id, $request_type, $result['RequestHeader']['RequestId'], $time, $result['Transaction']['Id'], $result['PaymentMeans']['Brand']['PaymentMethod'], $result['PaymentMeans']['Card']['MaskedNumber']);
        $stmt->execute();

		// If the transaction was authorized then we can capture the transaction
		$request_id = generateRandomString(32);
		$capture = capture_transaction($request_id, $result['Transaction']['Id']);

		$request_type = "Capture";
		$stmt = $mysqli->prepare("INSERT INTO order_requests (order_id, request_type, RequestId, request_time, transaction_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $order_id, $request_type, $capture['RequestHeader']['RequestId'], $time, $capture['TransactionId']);
        $stmt->execute();

		$data->status = "success";

	}
}

if ($data->status == "success") {

    // Now we can update the investments/products with the new value
	// First we need to load the cart again from the db
	$stmt = $mysqli->prepare("SELECT cart_data FROM cart_data WHERE order_id = ?");
	$stmt->bind_param("i", $order_id);
	$stmt->execute();
	$cart = mysqli_fetch_assoc($stmt->get_result())['cart_data'];

	if ($cart) {

		$cart = json_decode($cart, true);

	    for ($i=0; $i < count($cart); $i++) {

	        if ($cart[$i]["type"] == "investment") {

	            if ($cart[$i]["completed"]) {
	                $stmt = $mysqli->prepare("UPDATE investments SET amount_invested = ?, completion_time = ?, status = 'ENDED' WHERE investment_id = ?");
	                $stmt->bind_param("dii", $cart[$i]["new_amount"], $time, $cart[$i]["investment_id"]);
	                $stmt->execute();
	            } else {
	                $stmt = $mysqli->prepare("UPDATE investments SET amount_invested = ? WHERE investment_id = ?");
	                $stmt->bind_param("di", $cart[$i]["new_amount"], $cart[$i]["investment_id"]);
	                $stmt->execute();
	            }

	        } else if ($cart[$i]["type"] == "product") {

	            $stmt = $mysqli->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
	            $stmt->bind_param("di", $cart[$i]["new_stock"], $cart[$i]["product_id"]);
	            $stmt->execute();

	        }
	    }
	}

    // Now we can set the order status to completed and add the order_total
    $stmt = $mysqli->prepare("UPDATE orders SET order_status = 'COMPLETED', order_total = ? WHERE order_id = ?");
    $stmt->bind_param("di", $order_total, $order_id);
    $stmt->execute();
    $data->order_id = $order_id;

	// Now we can redirect the user to the confirmation page
	header("Location: http://".$_SERVER['HTTP_HOST']."/#/confirmation/$order_id");

} else if ($result && $result['Transaction']['Status'] != "CAPTURED") {

	// If the transaction has failed for whatever reason we need to cancel the transaction
	$time = time();
	require("checkout_functions/cancel_transaction.php");
	$request_id = generateRandomString(32);
	$result = cancel_transaction($request_id, $result['Transaction']['Id']);
	$request_type = "Cancel";
	$stmt = $mysqli->prepare("INSERT INTO order_requests (order_id, request_type, RequestId, request_time, transaction_id) VALUES (?, ?, ?, ?, ?)");
	$stmt->bind_param("issis", $order_id, $request_type, $result['RequestHeader']['RequestId'], $time, $result['TransactionId']);
	$stmt->execute();

}
