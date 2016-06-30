<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/find_location.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/random_string.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to edit an investment
// Lets check if the user is allowed to do this
$permissions_needed = array("edit_investment");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['investment_id']) && isset($_POST['name']) && isset($_POST['description'])
    && isset($_POST['address'])  && isset($_POST['city'])  && isset($_POST['country'])
    && isset($_POST['amount_needed']) && isset($_POST['organization_id'])
    && isset($_POST['status']) && isset($_POST['new_images']) && isset($_POST['dob'])
    && isset($_POST['remove_images']) && isset($_POST['money_split'])) {

        if ($_POST['status'] == "DRAFT" || $_POST['status'] == "PENDING" || $_POST['status'] == "LIVE" || $_POST['status'] == "ENDED" || ($_POST['status'] == "REMOVED" && check_permission("view_removed_investments"))) {

            // The client has provided the correct data.
            // Lets select the old investment
            if (check_permission("view_removed_investments")) {
                $stmt = $mysqli->prepare("SELECT * FROM investments WHERE investment_id = ?");
            } else {
                $stmt = $mysqli->prepare("SELECT * FROM investments WHERE investment_id = ? AND status != 'REMOVED'");
            }
            $stmt->bind_param("i", $_POST['investment_id']);
            $stmt->execute();
            $old_investment = $stmt->get_result()->fetch_object();

            if ($old_investment) {

                if ($old_investment->status != "LIVE" || check_permission("edit_live_investment")) {

                    // The investment does exist.
                    // If the client wants to change the organization_id of the investment then they must be part of dignity and hope
                    if (($_SESSION['organization_id'] == $old_investment->organization_id && $_POST['organization_id'] == $old_investment->organization_id) || $_SESSION['organization_id'] == 0) {

                        // We need to check to make sure that the new organization_id actually exists
                        $stmt = $mysqli->prepare("SELECT * FROM organizations WHERE organization_id = ?");
                        $stmt->bind_param("i", $_POST['organization_id']);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_object();

                        if ($result) {

                            // The user is allowed to edit this investment and the new organization_id exists
                            // Lets check if all of the data that the client provided meets the requirements
                            if (strlen($_POST['name']) > 4 && strlen($_POST['description']) > 20 && $_POST['amount_needed'] > 0 && $_POST['amount_needed'] < 10000
                            && (($old_investment->status == "ENDED" && $_POST['status'] == "ENDED") || $old_investment->status != "ENDED")) {

                                // Lets make sure that the clients change to the amount needed wont make it lower than the amount invested
                                if ($_POST['amount_needed'] - $old_investment->amount_invested > 0) {

                                    // Before we make the changes in the database we first need to remove the images from the image list that the client wants to remove
                                    $images = json_decode($old_investment->images);
                                    $images_to_remove = $_POST['remove_images'];

                                    // Lets go through a list of all the images that the user wants to remove and take them out of the old_images array
                                    for ($i=0;$i<count($images_to_remove);$i++) {
                                        for ($ii=0;$ii<count($images);$ii++) {
                                            if ($images[$ii]->name == $images_to_remove[$i]['name']) {
                                                unset($images[$ii]);
                                                $images = array_values($images);
                                                break;
                                            }
                                        }
                                    }

                                    // Lets go through the list of existing images that the client has provided to see if we need to change any settings
                                    if (isset($_POST['images'])) {
                                        for ($i=0;$i<count($_POST['images']);$i++) {

                                            // For each of these images we have to find the corrosponding image from the old investment data
                                            for ($ii=0;$ii<count($images);$ii++) {

                                                // And see if they match
                                                if ($images[$ii]->name == $_POST['images'][$i]['name']) {

                                                    // We found it. Lets update the settings
                                                    $images[$ii]->settings = $_POST['images'][$i]['settings'];
                                                    break;
                                                }
                                            }

                                        }
                                    }

                                    // We now need to add and save the images that the client wants to add
                                    // Lets loop through them and save them to a file
                                    $new_images = $_POST['new_images'];
                                    for ($i=0;$i<count($new_images);$i++) {
                                        $base64_string = $new_images[$i]["data"];
                                        $image_name = generateRandomString(10);
                                        $newimg = new stdClass();
                                        $newimg->name = $image_name;
                                        $newimg->settings = $new_images[$i]['settings'];
                                        $images[] = $newimg;

                                        // Create the image location
                                        $output_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/investments/inv_".$old_investment->investment_id."/img_".$image_name.".jpg";
                                        // Save it to the file
                                        base64_to_jpeg($base64_string, $output_file);
                                        // Lets save a thumbnail
                                        $thumbnail_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/investments/inv_".$old_investment->investment_id."/img_".$image_name."_small.jpg";
                                        make_thumbnail($output_file, $thumbnail_file);
                                        // Now we should resize it to a width of 500px
                                        resize_image($output_file, 500);
                                    }

                                    // We need to look up the location incase the user changed it
                                    $location = json_encode(lat_lng_from_address($_POST['address']. ", " .$_POST['city']. ", " .$_POST['country']));

                                    // Everything looks ok so we can now edit the investment in the database
                                    $stmt = $mysqli->prepare("UPDATE investments SET name = ?, dob = ?, description = ?, address = ?,  city = ?,  country = ?, location_lat_lng = ?, amount_needed = ?, organization_id = ?, status = ?, images = ?, money_split = ? WHERE investment_id = ?");
                                    $stmt->bind_param("sisssssdisssi", $_POST['name'], $_POST['dob'], $_POST['description'], $_POST['address'], $_POST['city'], $_POST['country'], $location, $_POST['amount_needed'], $_POST['organization_id'], $_POST['status'], json_encode($images), json_encode($_POST['money_split']), $_POST['investment_id']);
                                    $stmt->execute();

                                    $data->status = "success";
                                    $data->images = $images;
                                }
                            }
                        }
                    }
                }
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
log_activity($identifier, "edit_investment ".$data->status);

echo json_encode($data);
