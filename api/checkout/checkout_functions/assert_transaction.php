<?php

function assert_transaction($request_id, $token) {

    // Lets check with saferpay if the payment was successful
    $url = "https://test.saferpay.com/api/Payment/v1/PaymentPage/Assert";

    $object = array();
    $object['RequestHeader'] = array();
    $object['RequestHeader']['SpecVersion'] = "1.3";
    $object['RequestHeader']['CustomerId'] = "404298";
    $object['RequestHeader']['RequestId'] = $request_id;
    $object['RequestHeader']['RetryIndicator'] = 0;
    $object['Token'] = $token;

    return do_post($url, $object);

}
