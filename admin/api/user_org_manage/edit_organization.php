<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to edit an organization
// Lets check if the user is allowed to do this
$permissions_needed = array("edit_org");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['organization_id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['website_link'])) {

        // The client has provided the correct data.
        // We need to check if the name that the client has provided is not used by another organization
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE name = ? AND organization_id != ?");
        $stmt->bind_param("si", $_POST['name'], $_POST['organization_id']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 0) {

            // The new name is unique.
            // Lets quickly check to make sure that the description and website_link meet the requirements
            if (strlen($_POST['description']) > 10 && strlen($_POST['description']) < 255 && strlen($_POST['name']) > 3 && strlen($_POST['name']) < 64) {
                if (strlen($_POST['website_link']) > 5 && strlen($_POST['website_link']) < 64 && substr($_POST['website_link'], 0, 4) == "http") {

                    // We can now update the organization in the database
                    $stmt = $mysqli->prepare("UPDATE organizations SET name = ?, description = ?, website_link = ? WHERE organization_id = ?");
                    $stmt->bind_param("sssi", $_POST['name'], $_POST['description'], $_POST['website_link'], $_POST['organization_id']);
                    $stmt->execute();
                    
                    $data->status = "success";
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
log_activity($identifier, "edit_organization ".$data->status);

echo json_encode($data);
