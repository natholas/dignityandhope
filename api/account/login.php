<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");


$data = new stdClass();
$data->status = "failed";

// Checking if the client provided the needed information
if (isset($_POST['email']) && isset($_POST['password'])) {

    // Looking up the username in the users database
    $stmt = $mysqli->prepare("SELECT email, password_hash, user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();

    // Checking if we found them
    if ($result) {

        // We found the user
        // Now we check if the password entered is correct
        if (password_verify($_POST['password'], $result->password_hash)) {

            // The password that the user entered was correct.
            $data->status = "success";

            // Now we can set the session values
            $_SESSION['user_id'] = $result->user_id;
            $_SESSION['email'] = $result->email;
            $data->email = $result->email;
            $data->user_id = $_SESSION['user_id'];
        }
    }

}

echo json_encode($data);
