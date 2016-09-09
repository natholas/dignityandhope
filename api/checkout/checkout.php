<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// This script is for doing a checkout.
// Im not sure yet how the payment method will be involved so for now this only handles adding the order to the db

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





// We need to make sure that each item in the cart exists and is allowed
// At the same time we can calculate the order total
$cart = $_POST['cart'];
$order_total = 0;

// Now that we know everything in the cart is allowed we can create the order.
// Note that at this time the order status is set to INITIAL. We will change it to completed when all of the order items have been created and the payment has been done
$time = time();
$stmt = $mysqli->prepare("INSERT INTO orders (user_id, order_time, order_status) VALUES (?,?,'FAILED')");
$stmt->bind_param("ii", $_SESSION['user_id'], $time);
$stmt->execute();
$order_id = $stmt->insert_id;

// Now we can insert a new order item for each of the items in the cart and update each investment/product
for ($i=0; $i < count($cart); $i++) {

    if ($cart[$i]["type"] == "investment") {

        $stmt = $mysqli->prepare("SELECT amount_needed, amount_invested FROM investments WHERE investment_id = ? AND status = 'LIVE'");
        $stmt->bind_param("i", $cart[$i]["investment_id"]);
        $stmt->execute();
        $result = mysqli_fetch_object($stmt->get_result());
        if (!$result) {$data->status = "failed";}

        // For investments we need to check if the amount that the client sent is less than the amount that the investment still needs
        if ($result->amount_needed - $result->amount_invested < $cart[$i]["amount"] - 0.02) {$data->status = "failed";}
        $order_total += $cart[$i]["amount"];
        $cart[$i]["new_amount"] = $result->amount_invested + $cart[$i]["amount"];
        if ($result->amount_needed - $cart[$i]["new_amount"] < 0.01) {
            $cart[$i]["completed"] = true;
        } else {
            $cart[$i]["completed"] = false;
        }


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

    // Now we can update the investments/products with the new value
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


    // Somewhere here we need to confirm the payment


    // Now we can set the order status to completed and add the order_total

    $stmt = $mysqli->prepare("UPDATE orders SET order_status = 'COMPLETED', order_total = ? WHERE order_id = ?");
    $stmt->bind_param("di", $order_total, $order_id);
    $stmt->execute();
    $data->order_id = $order_id;

} else {
    echo "Something went wrong";
}


// Now we can return the order_id or a failure message
echo json_encode($data);
