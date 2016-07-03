<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client is requesting the count of the unread approvals
// Lets check if the user is allowed to do this
$permissions_needed = array("get_unprocessed_count");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // We need to get a count of the amount of products and investments that have a status of PENDING
    // The sql should also change slightly depending on if the user is part of dignity and hope
    // Emails can also only be approved if the user is in dignity and hope
    if ($_SESSION['organization_id'] == 0) {

        // The client is from dignity and hope so we can just get the total
        $inv_sql = "SELECT COUNT(*) FROM investments WHERE status = 'PENDING'";
        $prod_sql = "SELECT COUNT(*) FROM products WHERE status = 'PENDING'";
        $email_sql = "SELECT COUNT(*) FROM emails WHERE status = 'PENDING'";
        $order_sql = "SELECT COUNT(*) FROM orders WHERE status = 'COMPLETED'";
        $product_count = $mysqli->query($prod_sql)->fetch_assoc()['COUNT(*)'];
        $invest_count = $mysqli->query($inv_sql)->fetch_assoc()['COUNT(*)'];
        $email_count = $mysqli->query($email_sql)->fetch_assoc()['COUNT(*)'];
        $order_count = $mysqli->query($order_sql)->fetch_assoc()['COUNT(*)'];
        $data->investments = $invest_count;
        $data->products = $product_count;
        $data->emails = $email_count;
        $data->orders = $order_count;
        $data->status = "success";
    } else {

        // the client is not from dignity and hope. This mean that we have to only get the products and investments from their organization_id
        $product_count = 0;
        $invest_count = 0;
        $inv_sql = "SELECT investment_id FROM investments WHERE status = 'PENDING' AND organization_id = ".$_SESSION['organization_id'];
        $result = $mysqli->query($inv_sql);
        if ($result->num_rows > 0) {
            $invest_count = $result->num_rows();

            // For the products its a little more difficult because we have to look up what products belong to what investments
            // We have all of the relevant investments from the result above so we can use that
            while($investment_id = mysqli_fetch_object($result)) {
                $prod_sql = "SELECT COUNT(*) FROM products WHERE status = 'PENDING' AND creator_id = ".$investment_id;
                $result = $mysqli->query($prod_sql);
                $product_count += $result->num_rows;
            }
        }

        $data->status = "success";
        $data->investments = $invest_count;
        $data->products = $product_count;

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
log_activity($identifier, "get_unprocessed_count ".$data->status);

echo json_encode($data);
