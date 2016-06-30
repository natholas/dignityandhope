<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a the details for a product
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false) && isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {

    // There are no special permissions needed to view products
    // However there is for viewing products that were previously removed so lets check for that first
    if (check_permission("view_removed_products")) {

        // The user has the needed permissions to view removed products
        $sql = "SELECT * FROM products WHERE product_id = ".$_POST['product_id'];

    } else {

        // The user doesn't have permission to view removed products
        $sql = "SELECT * FROM products WHERE status != 'REMOVED' AND product_id = ".$_POST['product_id'];
    }

    // Lets get the products using the sql that was chosen based on the users permissions
    $result = $mysqli->query($sql);

    if ($result) {
        // And add them all to the products array

        $data->status = "success";
        $data->product = mysqli_fetch_object($result);
        $data->product->images = json_decode($data->product->images);

    }
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_product ".$data->status);

echo json_encode($data);
