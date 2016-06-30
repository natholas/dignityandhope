<?php

function base64_to_jpeg($base64_string, $output_file) {
    $ifp = fopen($output_file, "wb");
    $data = explode(',', $base64_string);
    fwrite($ifp, base64_decode($data[1]));
    fclose($ifp);
}

function resize_image($path, $width) {
    $img = imagecreatefromjpeg($path);
    $img = imagescale($img, $width);
    imagejpeg($img, $path, 90);
}

function make_thumbnail($path, $saveto) {
    $img = imagecreatefromjpeg($path);
    $img = imagescale($img, 120);
    imagejpeg($img, $saveto, 90);
}
