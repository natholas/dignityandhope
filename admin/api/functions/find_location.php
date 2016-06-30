<?php
function lat_lng_from_address($address) {
    $location = new stdClass();
    $location->lat = 0;
    $location->lng = 0;
    return $location;
}
