<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of all the emails
// Lets check if the user is logged in
$permissions_needed = array("view_emails");
if (check_user($permissions_needed, false)) {

    //  The user has the needed permissions
    $sql_start = "SELECT * FROM emails WHERE 1 = 1";
    $sql = "";

    // Lets see if the client wants to filter the results
    if (isset($_POST['filter'])) {

        $filter = json_decode($_POST['filter']);

        // The client wants to filter the results.
        // Lets see if the filter that they entered matches one of the allowed filters
        if (isset($filter->drafts) && !$filter->drafts) {
            $sql.= " AND status != 'DRAFT'";
        }
        if (isset($filter->pending) && !$filter->pending) {
            $sql.= " AND status != 'PENDING'";
        }
        if (isset($filter->sent) && !$filter->sent) {
            $sql.= " AND status != 'SENT'";
        }
        if (isset($filter->organization_id) && is_numeric($filter->organization_id)) {
            $sql.= " AND organization_id = ".$filter->organization_id;
        }
        if (isset($filter->search) && !preg_match('/[^A-Za-z0-9 ]/', $filter->search) && strlen($filter->search) > 0) {
            $sql.= " AND subject LIKE '%".$filter->search."%'";
        }
    }

    // Before we apply any limits. Lets get a quick count of the amount of rows that there are.
    // If the client asked for it
    if (isset($_POST['getcount'])) {
        $count_sql = "SELECT COUNT(*) FROM emails WHERE 1 = 1 ";
        $result = $mysqli->query($count_sql.$sql);
        if ($result) {
            $data->count = mysqli_fetch_assoc($result)['COUNT(*)'];
        }
    }

    // Lets check if they wanted to order and limit the results
    if (isset($_POST['limit']) && isset($_POST['offset']) && isset($_POST['order_by'])) {

        // The customer wants to limit and order the results.
        // There are only a few things that we should allow them to order by.
        // Lets see if they wanted one of these
        $order_by = $_POST['order_by'];
        if ($order_by == "email_id ASC" || $order_by == "subject ASC" ||
        $order_by == "message_read ASC" || $order_by == "email_id DESC" ||
        $order_by == "subject DESC" || $order_by == "message_read DESC") {

            // The order_by is allowed.
            // Lets check if the limit that they set is ok
            if (is_numeric($_POST['limit']) && $_POST['limit'] > 0 && is_numeric($_POST['offset']) && $_POST['offset'] >= 0) {

                // Everything looks valid. Lets add it to the SQL
                $sql.= " ORDER BY ".$order_by." LIMIT ".$_POST['limit']." OFFSET ".$_POST['offset'];

            }
        }
    } else {

        // The client has not specified how they would like to limit and order the results
        // This means that we will apply a default
        $sql.= " ORDER BY email_id LIMIT 50";

    }

    // Lets get the emails using the sql that was chosen based on the users permissions
    $result = $mysqli->query($sql_start.$sql);

    // And add them all to the products array
    $emails = array();
    while($email = mysqli_fetch_object($result)) {

        // We now need to see what the name of the customer that this email is sent to
        $sql = "SELECT first_name, last_name, email FROM users WHERE user_id = ".$email->user_id;
        $email->user = mysqli_fetch_object($mysqli->query($sql));
        $emails[] = $email;
    }

    $data->status = "success";
    $data->emails = $emails;

} else {
    $data->status = "permission denied";
}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_emails ".$data->status);

echo json_encode($data);
