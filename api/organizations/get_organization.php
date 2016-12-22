<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants a spesific organization
// Lets see if they provided the needed parameters
if (!isset($_POST['organization_id'])
|| !is_numeric($_POST['organization_id'])
){
    echo json_encode($data);
    die();
}

$stmt = $mysqli->prepare("SELECT * FROM organizations WHERE organization_id = ?");
$stmt->bind_param("i", $_POST['organization_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
	$data->status = "success";
	$data->organization = $result;
}

echo json_encode($data);
