<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to update an email
// Lets check if the user is allowed to do this
$permissions_needed = array("draft_email");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['email_id']) && isset($_POST['subject']) && isset($_POST['message']) && isset($_POST['user']) && isset($_POST['user']['email']) && isset($_POST['status'])
    && ($_POST['status'] == "DRAFT" || $_POST['status'] == "PENDING" || $_POST['status'] == "SENT")) {

        // The client has provided the correct data.
        // We need to get the details for this user
        $stmt = $mysqli->prepare("SELECT user_id, first_name, last_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $_POST['user']['email']);
        $stmt->execute();
        $user_details = $stmt->get_result()->fetch_object();

        if ($user_details) {

            // Lets personalize the subject and messages
            $keywords = array("[FIRSTNAME]", "[LASTNAME]");
            $replace = array($user_details->first_name, $user_details->last_name);
            $subject = str_replace($keywords, $replace, $_POST['subject']);
            $message = str_replace($keywords, $replace, $_POST['message']);

            // We need to check if the user has the permissions to send emails and is in dignity and hope. If they dont then the email will have to be approved
            if ($_POST['status'] == "SENT" && check_permission("send_email") && $_SESSION['organization_id'] == 0) {
                // Lets send the email!
                $to      = $_POST['user']['email'];
                $subject = htmlspecialchars($subject);
                $message =  htmlspecialchars($message);
                $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                    'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);

                // Now that the email has been sent we should also add it to the database
                $stmt = $mysqli->prepare("UPDATE emails SET user_id = ?, subject = ?, message = ?, status = ?, sent_time = ? WHERE email_id = ?");
                $stmt->bind_param("isssii", $user_details->user_id, $_POST['subject'], $_POST['message'], $_POST['status'], time(), $_POST['email_id']);
                $stmt->execute();
            } else {

                // The user wants to save email
                $stmt = $mysqli->prepare("UPDATE emails SET user_id = ?, subject = ?, message = ?, status = ? WHERE email_id = ?");
                $stmt->bind_param("isssi", $user_details->user_id, $_POST['subject'], $_POST['message'], $_POST['status'], $_POST['email_id']);
                $stmt->execute();
            }

            $data->status = "success";

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
log_activity($identifier, "update_email ".$data->status);

echo json_encode($data);
