<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to send an email to a front end user
// Lets check if the user is allowed to do this
$permissions_needed = array("draft_email");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['user_id']) && isset($_POST['subject']) && isset($_POST['message']) && isset($_POST['status'])
    && ($_POST['status'] == "DRAFT" || $_POST['status'] == "PENDING" || $_POST['status'] == "SENT")) {

        // The client has provided the correct data.
        // We need to get the details for this user
        $stmt = $mysqli->prepare("SELECT email, first_name, last_name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_POST['user_id']);
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
                $to      = $user_details->email;
                $subject = htmlspecialchars($subject);
                $message =  htmlspecialchars($message);
                $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                    'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);

                // Now that the email has been sent we should also add it to the database
                $status = "SENT";
                $stmt = $mysqli->prepare("INSERT INTO emails (user_id, subject, message, status, sent_time) VALUES (?,?,?,?,?)");
                $stmt->bind_param("isssi", $_POST['user_id'], $_POST['subject'], $_POST['message'], $status, time());
                $stmt->execute();
            } else {

                // The user only has the permissions to draft an email.
                // This means that we should not send it straight away but instead we should put it up for approval
                $status = $_POST['status'];
                $stmt = $mysqli->prepare("INSERT INTO emails (user_id, subject, message, status) VALUES (?,?,?,?)");
                $stmt->bind_param("isss", $_POST['user_id'], $_POST['subject'], $_POST['message'], $status);
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
log_activity($identifier, "send_email ".$data->status);

echo json_encode($data);
