<?php

function log_activity($user_id, $message) {
    global $ipaddress, $the_time, $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO admin_usage_log (identifier, activity, ip, time_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $user_id, $message, $ipaddress, $the_time);
    $stmt->execute();
}
