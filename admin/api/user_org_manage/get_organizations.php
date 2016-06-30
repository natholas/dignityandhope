<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of all the organizations
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false)) {

    // There are no special permissions needed to view organizations
    // So lets just get a list of all of the organizations
    $sql = "SELECT * FROM organizations WHERE active = 1";
    $result = $mysqli->query($sql);

    $organizations = array();

    while($org = mysqli_fetch_object($result)) {
        $organizations[] = $org;
    }

    $data->status = "success";
    $data->organizations = $organizations;

}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_organizations ".$data->status);

echo json_encode($data);
