<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the details of a user
if (isset($_POST['user_id'])) {

    $stmt = $mysqli->prepare("SELECT users.user_id, users.email, users.first_name, users.last_name, membership_types.membership_name, users.membership_expiry FROM users
        INNER JOIN membership_types
        ON users.membership_type = membership_types.membership_type_id
        WHERE user_id = ?");
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_object();

    if ($result) {

        $data->status = "success";
        $data->user = $result;

        $stmt = $mysqli->prepare("SELECT * FROM emails WHERE user_id = ? AND status = 'SENT' ORDER BY email_id DESC LIMIT 10");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data->user->emails = array();

        while($email = $result->fetch_object()) {
            $data->user->emails[]= $email;
        }

        $stmt = $mysqli->prepare("SELECT * FROM orders WHERE user_id = ? AND (status = 'COMPLETED' OR status = 'PROCESSED') ORDER BY order_id DESC LIMIT 10");
        $stmt->bind_param("i", $_POST['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data->user->orders = array();

        while($order = $result->fetch_object()) {
            $data->user->orders[]= $order;
        }
    }
}

echo json_encode($data);
