<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants a spesific investment
// Lets see if they provided the needed parameters
if (!isset($_POST['investment_id'])
|| !is_numeric($_POST['investment_id'])
){
    echo json_encode($data);
    die();
}

$stmt = $mysqli->prepare("SELECT * FROM investments WHERE (status = 'LIVE' OR status = 'ENDED') AND investment_id = ?");
$stmt->bind_param("i", $_POST['investment_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    // And add them all to the products array
    $investment = mysqli_fetch_object($result);
    $investment->images = json_decode($investment->images);
    $investment->location_lat_lng = json_decode($investment->location_lat_lng);
    $investment->money_split = json_decode($investment->money_split);

    // We now need to see what the organization is called that this investment is part of
    $sql = "SELECT name FROM organizations WHERE organization_id = ".$investment->organization_id;
    $investment->organization = mysqli_fetch_object($mysqli->query($sql))->name;


    $data->status = "success";
    $data->investment = $investment;
}
echo json_encode($data);
