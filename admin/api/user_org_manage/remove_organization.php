<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to remove an organization
// Lets check if the user is logged in and has the correct permissions
$requested_permissions = array("remove_org");
if (check_user($requested_permissions, true)) {

    // And if they have provided all of the needed data
    if (isset($_POST['organization_id']) && isset($_POST['remove_users']) && isset($_POST['move_active_investments']) && isset($_POST['move_inactive_investments'])) {

        // Lets do a quick check to make sure that this organization_id is real
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE organization_id = ?");
        $stmt->bind_param("i", $_POST['organization_id']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()['COUNT(*)'] == 1) {

            // The organization exists
            // Lets check if they are not trying to remove dignity and hope (0)
            if ($_POST['organization_id'] != 0) {

                // Before we remove the organization we need to do something with all of the investments and users that are a part of this organization
                // The client has provided us with some values to determine what to do
                if ($_POST['remove_users'] && check_permission("remove_admin_user")) {

                    // The client wants to remove all of the users in this organization.
                    // The user also has the permissions to remove users
                    $stmt = $mysqli->prepare("DELETE FROM admin_users WHERE organization_id = ?");
                    $stmt->bind_param("i", $_POST['organization_id']);
                    $stmt->execute();

                } else {
                    // The user doesnt want to remove all of the admin users that were in this organization
                    // This means that we have to disable the login access for all of the users belonging to this organization

                    // First lets select them all
                    $stmt = $mysqli->prepare("SELECT user_id, permissions FROM admin_users WHERE organization_id = ?");
                    $stmt->bind_param("i", $_POST['organization_id']);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $users = array();

                    // We can now loop through all of the users
                    while($user = mysqli_fetch_object($result)) {

                        // decode their permissions
                        $user->permissions = json_decode($user->permissions);

                        // Switch login to false
                        $user->permissions->login = false;

                        // Encode them again
                        $user->permissions = json_encode($user->permissions);

                        // And add them to the users array
                        $users[] = $user;
                    }

                    // Now we can go through all of the users and update them in the database
                    for ($i=0;$i<count($users);$i++) {
                        $permissions = $users[$i]->permissions;
                        $user_id = $users[$i]->user_id;
                        $sql = "UPDATE admin_users SET permissions = '$permissions' WHERE user_id = $user_id";
                        $mysqli->query($sql);
                    }
                }

                // Now we need to deal with the investments
                // Lets see if the client wants to move the active investments to another organization.
                if ($_POST['move_active_investments'] != false) {

                    // The client wants to move the active investments that belong to this organization to the other one
                    $stmt = $mysqli->prepare("UPDATE investments SET organization_id = ? WHERE organization_id = ? AND completion_time = null");
                    $stmt->bind_param("ii", $_POST['move_active_investments'], $_POST['organization_id']);
                    $stmt->execute();

                }

                // Lets see if the client wants to move the completed investments to another organization.
                if ($_POST['move_inactive_investments'] != false) {

                    // The client wants to move the inactive investments that belong to this organization to the other one
                    $stmt = $mysqli->prepare("UPDATE investments SET organization_id = ? WHERE organization_id = ? AND completion_time != null");
                    $stmt->bind_param("ii", $_POST['move_active_investments'], $_POST['organization_id']);
                    $stmt->execute();

                }

                // The user has the needed permissions
                // This means that we can se this organizations active status to 0
                $stmt = $mysqli->prepare("UPDATE organizations SET active = 0 WHERE organization_id = ?");
                $stmt->bind_param("i", $_POST['organization_id']);
                $stmt->execute();

                $data->status = "success";

            } else {
                $data->status = "permission denied";
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
log_activity($identifier, "remove organization ".$data->status);

echo json_encode($data);
