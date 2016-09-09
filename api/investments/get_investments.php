<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants a list of investments.
// Lets see if they provided the needed parameters
if (!isset($_POST['limit'])
|| !isset($_POST['offset'])
|| !is_numeric($_POST['limit'])
|| !is_numeric($_POST['offset'])
|| $_POST['limit'] > 50
){
    echo json_encode($data);
    die();
}

$stmt = $mysqli->prepare("SELECT * FROM investments WHERE status = 'LIVE' LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $_POST['limit'], $_POST['offset']);
$stmt->execute();
$result = $stmt->get_result();

// And add them all to the products array
$investments = array();
while($investment = mysqli_fetch_object($result)) {

    $investment->images = json_decode($investment->images);
    $investment->location_lat_lng = json_decode($investment->location_lat_lng);
    $investment->money_split = json_decode($investment->money_split);

    // We now need to see what the organization is called that this investment is part of
    $sql = "SELECT name FROM organizations WHERE organization_id = ".$investment->organization_id;
    $investment->organization = mysqli_fetch_object($mysqli->query($sql))->name;
    $investments[] = $investment;
}

$data->status = "success";
$data->investments = $investments;

echo json_encode($data);
