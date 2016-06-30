<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to create a new organization
// Lets check if the user is allowed to do this
$permissions_needed = array("add_org");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['website_link'])) {

        // The client has provided the correct data.
        // We need to check if the name that the client has provided is not used else where
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE name = ?");
        $stmt->bind_param("s", $_POST['name']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 0) {

            // The name is unique.
            // Lets quickly check to make sure that the description and website_link meet the requirements
            if (strlen($_POST['description']) > 10 && strlen($_POST['description']) < 255 && strlen($_POST['name']) > 3 && strlen($_POST['name']) < 64) {
                if (strlen($_POST['website_link']) > 5 && strlen($_POST['website_link']) < 64 && substr($_POST['website_link'], 0, 4) == "http") {

                    // We can now add the new organization into the database
                    $stmt = $mysqli->prepare("INSERT INTO organizations (name, description, website_link) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $_POST['name'], $_POST['description'], $_POST['website_link']);
                    $stmt->execute();

                    $data->status = "success";
                    $data->organization_id = $stmt->insert_id;
                }
            }
        } else {
            $data->status = "not unique";
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
log_activity($identifier, "create_organization ".$data->status);

echo json_encode($data);
