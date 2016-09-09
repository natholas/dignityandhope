<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get their order history
// Lets see if they are logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {

    // The client has a valid session set up.
    // Lets get their orders.
    $stmt = $mysqli->prepare('SELECT first_name, last_name, dob, membership_type, membership_expiry, address, post_code, city, country FROM users WHERE user_id = ?;');
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = mysqli_fetch_object($stmt->get_result());
    if ($result) {
        $data->status = "success";
        $data->personal_info = $result;
    }
}

echo json_encode($data);
