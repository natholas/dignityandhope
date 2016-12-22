<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants the list of organizations
// Lets see if they provided the needed parameters

$sql = "SELECT * FROM organizations";
$result = $mysqli->query($sql);

$data->organizations = [];

while ($row = $result->fetch_assoc()) {
	$data->organizations[] = $row;
}

$data->status = "success";

echo json_encode($data);
