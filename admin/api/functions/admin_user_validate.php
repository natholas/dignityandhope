<?php

function check_user($needed_permissions, $password) {
    global $mysqli;

    // This function checks whether a user is allowed to interact with certain parts of the admin api
    // We need to check to see if the user is properly logged in
    if (!isset($_SESSION['admin_user_id']) || !isset($_SESSION['organization_id'])) {
        return false;
    }

    // The user is logged in
    // Lets get an updated version of this users permissions from the database.
    $stmt = $mysqli->prepare("SELECT permissions FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if (!$result) {
        return false;
    }

    $user_permissions = json_decode($result->permissions, true);

    // Lets now check to see if the user has all of the requested permissions
    for ($i=0;$i<count($needed_permissions);$i++) {

        // There are a few special cases where permission is not needed.
        // We check for those first before we check the permission
        // The client can view edit their own user and change their own password without permission to edit user details
        if ($needed_permissions[$i] == "edit_user_details" || $needed_permissions[$i] == "edit_user_details" || $needed_permissions[$i] == "change_admin_password") {
            if ($_SESSION['admin_user_id'] == $_POST['user_id'])  {
                continue;
            }
        }

        // The client can't delete their own account
        if ($needed_permissions[$i] == "remove_admin_user" && $_SESSION['admin_user_id'] == $_POST['user_id']) {
            return false;
        }

        // Now if we are still here we check the permission
        if (!isset($user_permissions[$needed_permissions[$i]]) || !$user_permissions[$needed_permissions[$i]]) {
            return false;
        }
    }

    // The user has all of the requested permissions
    // $password is a boolean
    // If it is true then the user is trying to do an action that requires them to re-enter their password.
    if ($password) {

        // Lets get their password from the database
        $stmt = $mysqli->prepare("SELECT password_hash FROM admin_users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['admin_user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();

        // And check if the client sent along their password and it is correct
        if (!$result || !$_POST['password'] || !password_verify($_POST['password'], $result->password_hash)) {
            return false;
        }
    }

    // Everything looks ok so lets return true
    return true;
}

function check_permission($permission) {
    global $mysqli;

    // This function checks a single permission and its edge cases
    // Lets get an updated version of this users permissions from the database.
    $stmt = $mysqli->prepare("SELECT permissions FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if (!$result) {
        return false;
    }

    $user_permissions = json_decode($result->permissions, true);

    if ($permission == "edit_user_details" || $permission == "edit_user_details" || $permission == "change_admin_password") {
        if ($_SESSION['admin_user_id'] == $_POST['user_id'])  {
            return true;
        }
    }

    // The client can't delete their own account
    if ($permission == "remove_admin_user" && $_SESSION['admin_user_id'] == $_POST['user_id']) {
        return false;
    }

    // Checking if the permission is there
    if (!isset($user_permissions[$permission]) || !$user_permissions[$permission]) {
        return false;
    }

    return true;
}

function get_rank() {
    global $mysqli;

    // This function checks the rank of an admin user
    $stmt = $mysqli->prepare("SELECT permissions FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if (!$result) {
        return 0;
    }

    return json_decode($result->permissions, true)['rank'];
}

function get_permissions() {
    global $mysqli;

    // This function checks the rank of an admin user
    $stmt = $mysqli->prepare("SELECT permissions FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if (!$result) {
        return 0;
    }

    return json_decode($result->permissions);
}

function check_and_fix_permissions() {
    global $mysqli;

    // To avoid any errors lets make sure that the user is actually logged in
    if (!isset($_SESSION['admin_user_id'])) {
        return false;
    }

    // Lets get the default permissions
    $new_permissions = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/admin/api/defaults/permissions.json"), true);

    // Then we get the users permissions from the database
    $stmt = $mysqli->prepare("SELECT permissions FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    if ($result) {
        $permissions = json_decode($result->permissions, true);
    } else {
        $permissions = array();
    }
    $missing = false;

    // Then we go through all of the default permissions
    foreach($new_permissions as $key => $value) {

        // If this permission is present in the provided permissions then we change the value in new_permissions
        if (isset($permissions[$key])) {
            $new_permissions[$key] = $permissions[$key];
        } else {
            $missing = true;
        }
    }

    // If there were any values missing we update the users permissions in the database
    // Then we get the users permissions from the database
	$x = json_encode($new_permissions);
    $stmt = $mysqli->prepare("UPDATE admin_users SET permissions = ? WHERE user_id = ?");
    $stmt->bind_param("si", $x, $_SESSION['admin_user_id']);
    $stmt->execute();

    return (object)$new_permissions;
}
