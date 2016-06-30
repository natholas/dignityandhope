<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get the details of a user
if (isset($_POST['user_id'])) {

    $stmt = $mysqli->prepare("SELECT user_id, email, first_name, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_object();

    if ($result) {
        $data->status = "success";
        $data->user = $result;
    }
}

echo json_encode($data);
