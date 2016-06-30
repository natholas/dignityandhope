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
    if ((isset($_POST['product_id']) || isset($_POST['investment_id'])) && isset($_POST['subject']) && isset($_POST['message']) && isset($_POST['status'])
    && ($_POST['status'] == "DRAFT" || $_POST['status'] == "PENDING" || $_POST['status'] == "SENT")) {

        // The client has provided the correct data.
        // We need to get the details for all of the users associated with this product or investment

        if (isset($_POST['product_id'])) {

            $stmt = $mysqli->prepare("SELECT order_id FROM order_items WHERE type = 'product' AND the_id = ?");
            $stmt->bind_param("i", $_POST['product_id']);
            $stmt->execute();
            $result = $stmt->get_result();

        } else {

            $stmt = $mysqli->prepare("SELECT order_id FROM order_items WHERE type = 'investment' AND the_id = ?");
            $stmt->bind_param("i", $_POST['investment_id']);
            $stmt->execute();
            $result = $stmt->get_result();

        }

        $orders = array();
        while($order = mysqli_fetch_object($result)) {
            $orders[] = $order->order_id;
        }

        // Now we have a list of orders.
        // Lets remove any duplicates
        $orders = array_unique($orders);

        // Now for each of these order_ids we can select the user_id
        $user_ids = array();
        for ($i=0;$i<count($orders);$i++) {

            $stmt = $mysqli->prepare("SELECT user_id FROM orders WHERE order_id = ?");
            $stmt->bind_param("i", $orders[$i]);
            $stmt->execute();
            $user_id = $stmt->get_result();
            if ($user_id) {
                $user_ids[]= mysqli_fetch_object($user_id)->user_id;
            }
        }

        // At this point we have an array of user_ids
        // Lets make sure there are no duplicates again
        $user_ids = array_unique($user_ids);
        // And finally we can select the details for each of these users.
        $users = array();
        for ($i=0;$i<count($user_ids);$i++) {

            $stmt = $mysqli->prepare("SELECT user_id, first_name, last_name, email FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_ids[$i]);
            $stmt->execute();
            $user = $stmt->get_result();
            if ($user) {
                $users[]= mysqli_fetch_object($user);
            }
        }

        // Now we have an array of user objects.
        // So we can now loop through them all and send them each an email

        $keywords = array("[FIRSTNAME]", "[LASTNAME]");

        // We need to check if the user has the permissions to send emails and they are in dignity and hope. If they dont then the email will have to be approved
        if ($_POST['status'] == "SENT" && check_permission("send_email") && $_SESSION['organization_id'] == 0) {
            for ($i=0;$i<count($users);$i++) {

                // Lets personalize the subject and messages
                $replace = array($users[$i]->first_name, $users[$i]->last_name);
                $subject = str_replace($keywords, $replace, $_POST['subject']);
                $message = str_replace($keywords, $replace, $_POST['message']);

                $to      = $users[$i]->email;
                $subject = htmlspecialchars($subject);
                $message =  htmlspecialchars($message);
                $headers = 'From: nathansecodary@gmail.com' . "\r\n" .
                    'Reply-To: nathansecodary@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);

                // Now that the email has been sent we should also add it to the database
                $status = "SENT";
                $stmt = $mysqli->prepare("INSERT INTO emails (user_id, subject, message, status, sent_time) VALUES (?,?,?,?,?)");
                $stmt->bind_param("isssi", $users[$i]->user_id, $subject, $message, $status, time());
                $stmt->execute();

            }
        } else {

            // The user only has the permissions to draft an email.
            // This means that we should not send it straight away but instead we should put it up for approval
            for ($i=0;$i<count($users);$i++) {
                $status = $_POST['status'];
                $stmt = $mysqli->prepare("INSERT INTO emails (user_id, subject, message, status) VALUES (?,?,?,?)");
                $stmt->bind_param("isss", $users[$i]->user_id, $subject, $message, $status);
                $stmt->execute();
            }

        }

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
log_activity($identifier, "send_group_email ".$data->status);

echo json_encode($data);
