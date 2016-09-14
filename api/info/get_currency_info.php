<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "success";

$result = $mysqli->query("SELECT * FROM conversion_rates");

$data->currencies = array();
while ($currency = mysqli_fetch_assoc($result)) {
    $data->currencies[] = $currency;
}


echo json_encode($data);
