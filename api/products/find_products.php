<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to find all products from a string
if (isset($_POST['string']) && strlen($_POST['string']) > 2) {

    $string = "%".$_POST['string']."%";

    $stmt = $mysqli->prepare("SELECT product_id, name, images FROM products
        WHERE (name LIKE ? OR description LIKE ?) AND (status = 'LIVE')
        ORDER BY product_id LIMIT 10");
    $stmt->bind_param("ss", $string, $string);
    $stmt->execute();

    $result = $stmt->get_result();

    $data->products = array();

    while($product = mysqli_fetch_object($result)) {
        $data->products[]= $product;
    };

    $data->status = "success";
}

echo json_encode($data);
