<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require("checkout_functions/saferpay_call.php");
require("checkout_functions/initialize_transaction.php");

$data = new stdClass();
$data->status = "failed";

// This is the first part of the backend order process
// Lets make sure that the client is either logged in or has provided the extra info needed to creat their account
if ((!isset($_SESSION['user_id']) || !isset($_SESSION['email']))
&& (!isset($_POST['email']) || !isset($_POST['password']))) {
    echo json_encode($data);
    die("Not logged in");
}

// Lets check if all the needed parameters were sent
if (!isset($_POST['first_name'])
|| !isset($_POST['last_name'])
|| !isset($_POST['dob'])
|| !isset($_POST['address'])
|| !isset($_POST['post_code'])
|| !isset($_POST['city'])
|| !isset($_POST['country'])
|| !isset($_POST['currency'])
|| !isset($_POST['cart'])) {
    echo json_encode($data);
    die(" Missing params");
}

$data->status = "success";

// Lets update the user table with the billing details that were sent if the user is logged in
if (isset($_SESSION['user_id'])) {

	$stmt = $mysqli->prepare("UPDATE users SET first_name = ?, last_name = ?, dob = ?, address = ?, post_code = ?, city = ?, country = ? WHERE user_id = ?");
	$stmt->bind_param("sssssssi", $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['address'], $_POST['post_code'], $_POST['city'], $_POST['country'], $_SESSION['user_id']);
	$stmt->execute();

} else {

	// If the user is not logged in then we need to create a new account for this user
	// First we need to check if this email address is unique
	$stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
	$stmt->bind_param("s", $_POST['email']);
	$stmt->execute();
	$result = mysqli_fetch_assoc($stmt->get_result())['COUNT(*)'];

	if ($result == 0) {

		$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, dob, address, post_code, city, country, email, password_hash) VALUES (?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("sssssssss", $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['address'], $_POST['post_code'], $_POST['city'], $_POST['country'], $_POST['email'], $password_hash);
		$stmt->execute();

		$_SESSION['user_id'] = $stmt->insert_id;
		$_SESSION['email'] = $_POST['email'];
		$data->user_id = $_SESSION['user_id'];
		$data->email = $_SESSION['email'];

	} else {

		$data->status = "failed";
		$data->error = "email_not_unique";
		echo json_encode($data);
		die();

	}
}

// Lets make sure that the currency that was entered is valid
$stmt = $mysqli->prepare("SELECT * FROM conversion_rates WHERE currency_code = ?");
$stmt->bind_param("s", $_POST['currency']);
$stmt->execute();
$currency = mysqli_fetch_assoc($stmt->get_result());

if (!$currency) {
    $data->status = "failed";
    $data->error = "currency_not_found";
    echo json_encode($data);
    die();
}

// We need to make sure that each item in the cart exists and is allowed
// At the same time we can calculate the order total
$cart = $_POST['cart'];
$order_total = 0;

// Now that we know everything in the cart is allowed we can create the order.
// Note that at this time the order status is set to INITIAL. We will change it to completed when all of the order items have been created and the payment has been done
$time = time();
$stmt = $mysqli->prepare("INSERT INTO orders (user_id, order_time, order_status, currency_code, conversion_rate) VALUES (?,?,'FAILED',?, ?)");
$stmt->bind_param("iisd", $_SESSION['user_id'], $time, $currency['currency_code'], $currency['value']);
$stmt->execute();
$order_id = $stmt->insert_id;

// Now we can insert a new order item for each of the items in the cart and update each investment/product
for ($i=0; $i < count($cart); $i++) {

    if ($cart[$i]["type"] == "investment") {

        $stmt = $mysqli->prepare("SELECT amount_needed, amount_invested FROM investments WHERE investment_id = ? AND status = 'LIVE'");
        $stmt->bind_param("i", $cart[$i]["investment_id"]);
        $stmt->execute();
        $result = mysqli_fetch_object($stmt->get_result());
        if (!$result) {$data->status = "failed1";}

        // For investments we need to check if the amount that the client sent is less than the amount that the investment still needs
        if ($result->amount_needed - $result->amount_invested < $cart[$i]["amount"] - 0.02) $data->status = "failed2";
        $order_total += $cart[$i]["amount"];
        $cart[$i]["new_amount"] = $result->amount_invested + $cart[$i]["amount"];
        if ($result->amount_needed - $cart[$i]["new_amount"] < 0.01) $cart[$i]["completed"] = true;
        else $cart[$i]["completed"] = false;


    } else if ($cart[$i]["type"] == "product") {

        $stmt = $mysqli->prepare("SELECT price, stock FROM products WHERE product_id = ? AND status = 'LIVE'");
        $stmt->bind_param("i", $cart[$i]["product_id"]);
        $stmt->execute();
        $result = mysqli_fetch_object($stmt->get_result());
        if (!$result) {$data->status = "failed";}

        // For products we need to check if the client is not ordering more than the amount of stock we have
        if ($result->stock < $cart[$i]["count"] && $result->price == $cart[$i]["amount"]) {$data->status = "failed";}
        $order_total += $cart[$i]["amount"] * $cart[$i]["count"];
        $cart[$i]["new_stock"] = $result->stock - $cart[$i]["count"];

    } else {$data->status = "failed";}

    $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, type, the_id, amount_paid) VALUES (?,?,?,?)");
    $stmt->bind_param("isid", $order_id, $cart[$i]["type"], $cart[$i][$cart[$i]["type"]."_id"], $cart[$i]["amount"]);
    $stmt->execute();

}

if ($data->status == "success") {

	$request_id = generateRandomString(32);

    $result = initialize_transaction($request_id, $order_total, $currency['value'], $currency['currency_code'], $order_id);

	if (!$result) {
		$data->status == "failed";
	} else {

		$data->RedirectUrl = $result['RedirectUrl'];

		$time = time();
		$request_type = "Initialize";
		$stmt = $mysqli->prepare("INSERT INTO order_requests (order_id, request_type, RequestId, Token, request_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $order_id, $request_type, $request_id, $result['Token'], $time);
        $stmt->execute();

		// Now we also need to save the cart so that we can load it up again after the transaction is successful
		$cart = json_encode($cart);
		$stmt = $mysqli->prepare("INSERT INTO cart_data (order_id, cart_data) VALUES (?, ?)");
        $stmt->bind_param("is", $order_id, $cart);
        $stmt->execute();

	}
}

echo json_encode($data);


?>
