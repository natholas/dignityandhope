<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to find all users from a string
if (isset($_POST['string']) && strlen($_POST['string']) > 2) {

    $string = "%".$_POST['string']."%";

    //$stmt = $mysqli->prepare("SELECT user_id, first_name, last_name FROM users WHERE first_name LIKE ?");
    $stmt = $mysqli->prepare("SELECT user_id, first_name, last_name, email FROM users
        WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?
        ORDER BY user_id LIMIT 10");
    $stmt->bind_param("sss", $string,$string,$string);
    $stmt->execute();

    $result = $stmt->get_result();

    $data->users = array();

    while($user = mysqli_fetch_object($result)) {
        $data->users[]= $user;
    };

    $data->status = "success";
}

echo json_encode($data);
