<?php

function capture_transaction($request_id, $transaction_id) {

    // Lets check with saferpay if the payment was successful
    $url = "https://test.saferpay.com/api/Payment/v1/Transaction/Capture";

    $object = array();
    $object['RequestHeader'] = array();
    $object['RequestHeader']['SpecVersion'] = "1.3";
    $object['RequestHeader']['CustomerId'] = "406798";
    $object['RequestHeader']['RequestId'] = $request_id;
    $object['RequestHeader']['RetryIndicator'] = 0;
    $object['TransactionReference'] = array();
    $object['TransactionReference']['TransactionId'] = $transaction_id;

    return do_post($url, $object);

}
