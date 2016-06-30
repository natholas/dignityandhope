<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to remove a product
// Lets check if the user is allowed to do this
$permissions_needed = array("remove_product");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['product_id'])) {

        // The client has provided the correct data.
        // Lets check if this product actually exisits
        $stmt = $mysqli->prepare("SELECT creator_id FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $_POST['product_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();

        if ($result) {

            // We found the product
            // We need to now see what organization the creator of this product is in.
            $stmt = $mysqli->prepare("SELECT organization_id FROM investments WHERE investment_id = ?");
            $stmt->bind_param("i", $result->creator_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_object();

            if ($result) {

                // We should only allow a user to remove this product if they are in the same organization as the creator of the product (or if they are in dignity and hope)
                if ($result->organization_id == $_SESSION['organization_id'] || $_SESSION['organization_id'] == 0) {

                    // This user is allowed to remove this product
                    // We dont actually remove the product from the database.
                    // We just se the status to "REMOVED"
                    $stmt = $mysqli->prepare("UPDATE products SET status = 'REMOVED' WHERE product_id = ?");
                    $stmt->bind_param("i", $_POST['product_id']);
                    $stmt->execute();

                    $data->status = "success";
                }
            }
        }
    }
} else {
    $data->status = "permission denied";
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "remove_product ".$data->status);

echo json_encode($data);
