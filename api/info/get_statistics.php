<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "success";

$data->completed_investments = $mysqli->query("SELECT COUNT(*) FROM investments WHERE status = 'ENDED'")->fetch_assoc()['COUNT(*)'];

echo json_encode($data);
