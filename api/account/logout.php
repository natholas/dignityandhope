<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "success";

// The client wishes to logout.
// All we need to do is destroy the session
session_destroy();

echo json_encode($data);
