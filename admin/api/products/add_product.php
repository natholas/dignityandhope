<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/random_string.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to add a new product
// Lets check if the user is allowed to do this
$permissions_needed = array("add_product");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['stock']) && isset($_POST['creator_id']) && isset($_POST['status']) && isset($_POST['images'])) {
        if ($_POST['status'] == "DRAFT" || $_POST['status'] == "LIVE" || $_POST['status'] == "PENDING") {
            if ($_POST['status'] == "LIVE" && !check_permission("publish_product")) {
                $_POST['status'] = "PENDING";
            }

            // The client has provided the correct data.
            // Lets check if the creator_id that the client has provided belongs to an investment that is fully invested and is in the same organization as the user (or in dignity and hope)
            if ($_POST['creator_id'] == 0) {

                $result = new stdClass();
                $result->organization_id = 0;

            } else {

                $stmt = $mysqli->prepare("SELECT organization_id FROM investments WHERE investment_id = ? AND status = 'ENDED'");
                $stmt->bind_param("i", $_POST['creator_id']);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_object();
            }
            if ($result || $_POST['creator_id'] == 0) {

                // The investment does exist.
                // Now lets see if the organization_id is the same as the user (unless the user is in dignity and hope (0))
                if ($result->organization_id == $_SESSION['organization_id'] || $_SESSION['organization_id'] == 0) {

                    // The user is allowed to add a product on behalf of this investment
                    // Lets check if all of the data that the client provided meets the requirements
                    if (strlen($_POST['name']) > 4 && strlen($_POST['description']) > 20 && $_POST['price'] > 0 && $_POST['price'] < 1000) {

                        // Everything looks ok so we can now add the new product to the database
                        $stmt = $mysqli->prepare("INSERT INTO products (name, description, price, stock, creator_id, status, added_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssdiisi", $_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['creator_id'], $_POST['status'], time());
                        $stmt->execute();

                        $new_product_id = $stmt->insert_id;

                        // We need to save the images that the client sent along
                        // First we need to json_decode the array of base64 images that was sent
                        $images = $_POST['new_images'];

                        $new_images = array();

                        // We create a directory for this investment_id
                        mkdir ($_SERVER["DOCUMENT_ROOT"]."/assets/images/products/prod_".$new_product_id);

                        // And then loop through them and save them to a file
                        for ($i=0;$i<count($images);$i++) {
                            $base64_string = $images[$i]['data'];
                            $image_name = generateRandomString(10);

                            $newimg = new stdClass();
                            $newimg->name = $image_name;
                            $newimg->settings = $images[$i]['settings'];
                            $new_images[] = $newimg;

                            // Create the image location
                            $output_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/products/prod_".$new_product_id."/img_".$image_name.".jpg";
                            // Save it to the file
                            base64_to_jpeg($base64_string, $output_file);
                            // Lets save a thumbnail
                            $thumbnail_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/products/prod_".$new_product_id."/img_".$image_name."_small.jpg";
                            make_thumbnail($output_file, $thumbnail_file);
                            // Now we should resize it to a width of 500px
                            resize_image($output_file, 500);
                        }

                        // And now we need to update this investment in the database with the image names
                        $stmt = $mysqli->prepare("UPDATE products SET images = ? WHERE product_id = ?");
                        $stmt->bind_param("si", json_encode($new_images), $new_product_id);
                        $stmt->execute();

                        $data->status = "success";
                        $data->product_id = $new_product_id;
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
log_activity($identifier, "add_product ".$data->status);

echo json_encode($data);
