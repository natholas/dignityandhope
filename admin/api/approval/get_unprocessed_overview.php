<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client is requesting all of the unprocessed items that they have access to
// Lets check if the user is allowed to do this
$permissions_needed = array("get_unprocessed_overview");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // We need to get a count of the amount of products and investments that have a status of PENDING
    // The sql should also change slightly depending on if the user is part of dignity and hope
    // Emails are only allowed if the user is from dignity and hope
    if ($_SESSION['organization_id'] == 0) {

        // The client is from dignity and hope so we can just get the total
        $inv_sql = "SELECT * FROM investments WHERE status = 'PENDING'";
        $prod_sql = "SELECT * FROM products WHERE status = 'PENDING'";
        $email_sql = "SELECT * FROM emails WHERE status = 'PENDING'";

        $result = $mysqli->query($prod_sql);
        $products = array();
        while ($product = mysqli_fetch_object($result)) {
            $products[]= $product;
        }

        $result = $mysqli->query($inv_sql);
        $investments = array();
        while ($investment = mysqli_fetch_object($result)) {
            $investments[]= $investment;
        }

        $result = $mysqli->query($email_sql);
        $emails = array();
        while ($email = mysqli_fetch_object($result)) {
            $emails[]= $email;
        }

        $data->products = $products;
        $data->investments = $investments;
        $data->emails = $emails;
        $data->status = "success";

    } else {

        // The client is from dignity and hope so we can just get the total
        $inv_sql = "SELECT * FROM investments WHERE status = 'PENDING' AND organization_id = ".$_SESSION['organization_id'];

        $result = $mysqli->query($inv_sql);
        $investments = array();
        while ($investment = mysqli_fetch_object($result)) {
            $investments[]= $investment;
        }

        // For the products its a little more difficult because we have to look up what products belong to what investments
        // We have all of the relevant investments from the result above so we can use that
        $products = array();
        for ($i=0;$i<count($investments);$i++) {
            $prod_sql = "SELECT * FROM products WHERE status = 'PENDING' AND creator_id = ".$investments[$i]->investment_id;
            $result = $mysqli->query($prod_sql);
            while ($product = mysqli_fetch_object($result)) {
                $products[]= $product;
            }
        }

        $data->products = $products;
        $data->investments = $investments;
        $data->status = "success";

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
log_activity($identifier, "get_unprocessed_overview ".$data->status);

echo json_encode($data);
