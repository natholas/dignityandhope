<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/logging/log_activity.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/admin_user_validate.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/save_image.php");
require($_SERVER["DOCUMENT_ROOT"]."/admin/api/functions/random_string.php");

$data = new stdClass();
$data->status = "failed";

// The client wants to edit a product
// Lets check if the user is allowed to do this
$permissions_needed = array("edit_product");
if (check_user($permissions_needed, false)) {

    // The user has the correct permissions.
    // Lets check if the client provided all the correct data
    if (isset($_POST['product_id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['stock']) && isset($_POST['creator_id']) && isset($_POST['status']) && isset($_POST['new_images']) && isset($_POST['remove_images'])) {
        if ($_POST['status'] == "DRAFT" || $_POST['status'] == "LIVE" || $_POST['status'] == "PENDING") {

            // The client has provided the correct data.
            // Lets select the old product
            $stmt = $mysqli->prepare("SELECT * FROM products WHERE product_id = ? AND status != 'REMOVED'");
            $stmt->bind_param("i", $_POST['product_id']);
            $stmt->execute();
            $old_product = $stmt->get_result()->fetch_object();

            if ($old_product) {

                // The product does exist.
                // We need to make sure that the user is allowed to edit live products if this product is already live
                if ($old_product->status != "LIVE" || check_permission("edit_live_product")) {

                    // If the client wants to change the creator_id of the product
                    $failed = false;
                    if ($old_product->creator_id != $_POST['creator_id']) {

                        // Then we need to make sure that the old creator and the new one are in the same organization as the user. (unless the user is from dignity and hope)
                        if ($_SESSION['organization_id'] != 0) {

                            $stmt = $mysqli->prepare("SELECT organization_id FROM investments WHERE investment_id = ?");
                            $stmt->bind_param("i", $_POST['creator_id']);
                            $stmt->execute();
                            $new_creator = $stmt->get_result()->fetch_object();

                            $stmt = $mysqli->prepare("SELECT organization_id FROM investments WHERE investment_id = ?");
                            $stmt->bind_param("i", $old_product->creator_id);
                            $stmt->execute();
                            $old_creator = $stmt->get_result()->fetch_object();

                            if ($new_creator) {
                                if ($old_creator->organization_id != $new_creator->organization_id) {

                                    // The old creator is not in the same orgnizaion as the new one and the user is not in dignity and hope.
                                    // This means that they should not be allowed to change the creator
                                    $failed = true;
                                }
                            } else {
                                $failed = true;
                            }
                        }
                    }

                    if (!$failed) {
                        // The user is allowed to edit this product on behalf of this investment
                        // Lets check if all of the data that the client provided meets the requirements
                        if (strlen($_POST['name']) > 4 && strlen($_POST['description']) > 20 && $_POST['price'] > 0 && $_POST['price'] < 1000) {

                            // Before we make the changes in the database we first need to remove the images from the image list that the client wants to remove
                            $images = json_decode($old_product->images);
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
                                $base64_string = $new_images[$i]['data'];
                                $newimg = new stdClass();
                                $image_name = generateRandomString(10);
                                $newimg->name = $image_name;
                                $newimg->settings = $new_images[$i]['settings'];
                                $images[] = $newimg;

                                // Create the image location
                                $output_file = $_SERVER["DOCUMENT_ROOT"]."/assets/images/products/prod_".$old_product->product_id."/img_".$image_name.".jpg";
                                // Save it to the file
                                base64_to_jpeg($base64_string, $output_file);
                                // Now we should resize it to a width of 500px
                                resize_image($output_file, 500);
                            }

                            // Everything looks ok so we can now edit the product in the database
                            $stmt = $mysqli->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, creator_id = ?, status = ?, images = ? WHERE product_id = ?");
                            $stmt->bind_param("ssdiissi", $_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['creator_id'], $_POST['status'], json_encode($images), $_POST['product_id']);
                            $stmt->execute();

                            $data->status = "success";
                            $data->images = $images;
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
log_activity($identifier, "edit_product ".$data->status);

echo json_encode($data);
