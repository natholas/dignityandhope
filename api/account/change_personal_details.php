<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// This script is for updating personal details

// Lets make sure that the client is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
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
|| !isset($_POST['country'])) {
    echo json_encode($data);
    die(" Missing params");
}

$data->status = "success";

// Lets update the user table with the billing details that were sent
$stmt = $mysqli->prepare("UPDATE users SET first_name = ?, last_name = ?, dob = ?, address = ?, post_code = ?, city = ?, country = ? WHERE user_id = ?");
$stmt->bind_param("sssssssi", $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['address'], $_POST['post_code'], $_POST['city'], $_POST['country'], $_SESSION['user_id']);
$stmt->execute();


// Now we can return the order_id or a failure message
echo json_encode($data);
