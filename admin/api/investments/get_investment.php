<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get an investment
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false)) {

    // We need to make sure that the client sent along the needed data
    if (isset($_POST['investment_id']) && is_numeric($_POST['investment_id'])) {

        // There are no special permissions needed to view investments
        // However there is for viewing investments that were previously removed so lets check for that first
        if (check_permission("view_removed_investments")) {

            // The user has the needed permissions to view removed investments
            $sql = "SELECT * FROM investments WHERE investment_id = ".$_POST['investment_id'];

        } else {

            // The user doesn't have permission to view removed investments
            $sql = "SELECT * FROM investments WHERE status != 'REMOVED' AND investment_id = ".$_POST['investment_id'];

        }
        $result = $mysqli->query($sql);

        if ($result) {



            $data->status = "success";
            $data->investment = mysqli_fetch_object($result);
            $data->investment->images = json_decode($data->investment->images);
            $data->investment->location_lat_lng = json_decode($data->investment->location_lat_lng);
            $data->investment->money_split = json_decode($data->investment->money_split);

            // We now need to see what the organization is called that this investment is part of
            $sql = "SELECT name FROM organizations WHERE organization_id = ".$data->investment->organization_id;
            $data->investment->organization = mysqli_fetch_object($mysqli->query($sql))->name;
        }
    }
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_investment ".$data->status);

echo json_encode($data);
