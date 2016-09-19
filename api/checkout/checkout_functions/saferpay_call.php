<?php
//JSONObj is a multidimensional Array, that assembles the JSON structure
//$username and $password for the http-Basic Authentication
//$url is the SaferpayURL eg. https://www.saferpay.com/api/Payment/v1/Transaction/Initialize

function do_post($url, $JSONObj) {

	$username = "API_406798_69320839";
	$password = "JsonApiPwd1_3S5E6uL5";

    //Set Options for CURL
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    //Return Response to Application
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //Set Content-Headers to JSON
    curl_setopt($curl, CURLOPT_HTTPHEADER,array("Content-type: application/json"));
    //Execute call via http-POST
    curl_setopt($curl, CURLOPT_POST, true);
    //Set POST-Body
        //convert DATA-Array into a JSON-Object
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($JSONObj));
    //WARNING!!!!!
    //This option should NOT be "false"
    //Otherwise the connection is not secured
    //You can turn it of if you're working on the test-system with no vital data
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //HTTP-Basic Authentication for the Saferpay JSON-API
    curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
    //CURL-Execute & catch response
    $jsonResponse = curl_exec($curl);
    //Get HTTP-Status
    //Abort if Status != 200
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ( $status != 200 ) {
        die("Error: call to URL $url failed with status $status, response $jsonResponse, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl) . "HTTP-Status: " . $status . "<||||> DUMP: URL: ". $url . " <|||> JSON: " . var_dump($JSONObj));
    }
    //IMPORTANT!!!
    //Close connection!
    curl_close($curl);
    //Convert response into an Array
    $response = json_decode($jsonResponse, true);
    return $response;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
