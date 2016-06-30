<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to find all investments from a string
if (isset($_POST['string']) && strlen($_POST['string']) > 2) {

    $string = "%".$_POST['string']."%";

    $stmt = $mysqli->prepare("SELECT investment_id, name, images FROM investments
        WHERE (name LIKE ? OR address LIKE ? OR city LIKE ? OR country LIKE ?) AND (status = 'LIVE' OR status = 'ENDED')
        ORDER BY investment_id LIMIT 10");
    $stmt->bind_param("ssss", $string, $string, $string, $string);
    $stmt->execute();

    $result = $stmt->get_result();

    $data->investments = array();

    while($investment = mysqli_fetch_object($result)) {
        $data->investments[]= $investment;
    };

    $data->status = "success";
}

echo json_encode($data);
