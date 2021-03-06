<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/find_location.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/random_string.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to add a new investment
// Lets check if the user is allowed to do this
$permissions_needed = array("add_investment");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['address'])
    && isset($_POST['city'])  && isset($_POST['country']) && isset($_POST['amount_needed'])
    && isset($_POST['organization_id']) && isset($_POST['status']) && isset($_POST['new_images'])
    && isset($_POST['dob']) && isset($_POST['money_split'])) {

        // We need to now check if the organization_id that the client provided actually exists
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM organizations WHERE organization_id = ?");
        $stmt->bind_param("i", $_POST['organization_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc()['COUNT(*)'];

        if ($result > 0) {

            // The organization exists
            // Now lets see if the organization_id is the same as the user (unless the user is in dignity and hope (0))
            if ($_POST['organization_id'] == $_SESSION['organization_id'] || $_SESSION['organization_id'] == 0) {

                // The user is allowed to add an investment with this organization_id
                // Lets check if all of the data that the client provided meets the requirements
                if (strlen($_POST['name']) > 4 && strlen($_POST['description']) > 20 && $_POST['amount_needed'] > 0 && $_POST['amount_needed'] < 10000) {
                    if ($_POST['status'] == "DRAFT" || $_POST['status'] == "LIVE") {

                        if ($_POST['status'] == "LIVE" && !check_permission("publish_investment")) {
                            $_POST['status'] = "PENDING";
                        }

                        $location = json_encode(lat_lng_from_address($_POST['address']. ", " .$_POST['city']. ", " .$_POST['country']));

                        // Everything looks ok so we can now add the new investment to the database
						$time = time();
						$ms = json_encode($_POST['money_split']);
                        $stmt = $mysqli->prepare("INSERT INTO investments (name, dob, description, address, city, country, location_lat_lng, amount_needed, organization_id, status, creation_time, money_split) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                        $stmt->bind_param("sisssssdisis", $_POST['name'], $_POST['dob'], $_POST['description'], $_POST['address'], $_POST['city'], $_POST['country'], $location, $_POST['amount_needed'], $_POST['organization_id'], $_POST['status'], $time, $ms);
                        $stmt->execute();

                        $new_investment_id = $stmt->insert_id;

                        // We need to save the images that the client sent along
                        // First we need to json_decode the array of base64 images that was sent
                        $images = $_POST['new_images'];

                        $new_images = array();

                        // We create a directory for this investment_id
                        mkdir ($_SERVER["DOCUMENT_ROOT"]."/assets/images/investments/inv_".$new_investment_id);

                        // And then loop through them and save them to a file
                        for ($i=0;$i<count($images);$i++) {
                            $base64_string = $images[$i]["data"];
                            $image_name = generateRandomString(10);

                            $newimg = new stdClass();
                            $newimg->name = $image_name;
                            $newimg->settings = $images[$i]['settings'];
                            $new_images[] = $newimg;

                            // Create the image location
                            $output_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/investments/inv_".$new_investment_id."/img_".$image_name.".jpg";
                            // Save it to the file
                            base64_to_jpeg($base64_string, $output_file);
                            // Lets save a thumbnail
                            $thumbnail_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/investments/inv_".$new_investment_id."/img_".$image_name."_small.jpg";
                            make_thumbnail($output_file, $thumbnail_file);
                            // Now we should resize it to a width of 500px
                            resize_image($output_file, 500);
                        }

                        // And now we need to update this investment in the database with the image names
						$ni = json_encode($new_images);
                        $stmt = $mysqli->prepare("UPDATE investments SET images = ? WHERE investment_id = ?");
                        $stmt->bind_param("si", $ni, $new_investment_id);
                        $stmt->execute();

                        $data->status = "success";
                        $data->investment_id = $new_investment_id;
                        $data->images = $new_images;
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
log_activity($identifier, "add_investment ".$data->status);

echo json_encode($data);
