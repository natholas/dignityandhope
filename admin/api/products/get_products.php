<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to get a list of all the products
// Lets check if the user is logged in
$permissions_needed = array();
if (check_user($permissions_needed, false)) {

    // There are no special permissions needed to view products
    // However there is for viewing products that were previously removed so lets check for that first

    $sql_start = "SELECT * FROM products";
    if (check_permission("view_removed_products")) {
        $sql_allowed = " WHERE 1 = 1";
    } else {
        $sql_allowed = " WHERE status != 'REMOVED'";
    }
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
        if (isset($filter->live) && !$filter->live) {
            $sql.= " AND status != 'LIVE'";
        }
        if ((isset($filter->removed) && !$filter->removed) || !check_permission("view_removed_investments")) {
            $sql.= " AND status != 'REMOVED'";
        }
        if (isset($filter->organization_id) && is_numeric($filter->organization_id)) {
            $sql.= " AND organization_id = ".$filter->organization_id;
        }
        if (isset($filter->search) && !preg_match('/[^A-Za-z0-9 ]/', $filter->search) && strlen($filter->search) > 0) {
            $sql.= " AND name LIKE '%".$filter->search."%'";
        }
    }

    // Before we apply any limits. Lets get a quick count of the amount of rows that there are.
    // If the client asked for it
    if (isset($_POST['getcount'])) {
        $count_sql = "SELECT COUNT(*) FROM products";
        $result = $mysqli->query($count_sql.$sql_allowed.$sql);
        if ($result) {
            $data->count = mysqli_fetch_assoc($result)['COUNT(*)'];
        }
    }

    // Lets check if they wanted to order and limit the results
    if (isset($_POST['limit']) && isset($_POST['order_by'])) {

        // The customer wants to limit and order the results.
        // There are only a few things that we should allow them to order by.
        // Lets see if they wanted one of these
        $order_by = $_POST['order_by'];
        if ($order_by == "product_id ASC" || $order_by == "name ASC" || $order_by == "price ASC" || $order_by == "stock ASC" ||$order_by == "product_id DESC" ||  $order_by == "name DESC" || $order_by == "price DESC" || $order_by == "stock DESC") {

            // The order_by is allowed.
            // Lets check if the limit that they set is ok
            if (is_numeric($_POST['limit']) && $_POST['limit'] > 0) {

                // Everything looks valid. Lets add it to the SQL
                $sql.= " ORDER BY ".$order_by." LIMIT ".$_POST['limit'];

            }
        }
    } else {

        // The client has not specified how they would like to limit and order the results
        // This means that we will apply a default
        $sql.= " ORDER BY product_id LIMIT 50";

    }

    // Lets get the products using the sql that was chosen based on the users permissions
    $result = $mysqli->query($sql_start.$sql_allowed.$sql);

    // And add them all to the products array
    $products = array();
    while($product = mysqli_fetch_object($result)) {
        $product->images = json_decode($product->images);
        $products[] = $product;
    }

    $data->status = "success";
    $data->products = $products;

}


// Logging
if (isset($_SESSION['admin_user_id'])) {
    $identifier = $_SESSION['admin_user_id'];
} else {
    $identifier = "";
}
log_activity($identifier, "get_products ".$data->status);

echo json_encode($data);
