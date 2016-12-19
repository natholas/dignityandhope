<?php

function initialize_transaction($request_id, $order_total, $conversion_rate, $currency, $order_id) {

    $object = array();
    $object['RequestHeader'] = array();
    $object['RequestHeader']['SpecVersion'] = "1.3";
    $object['RequestHeader']['CustomerId'] = "406798";
    $object['RequestHeader']['RequestId'] = $request_id;
    $object['RequestHeader']['RetryIndicator'] = 0;
    $object['TerminalId'] = "17829283";
    $object['Payment'] = array();
    $object['Payment']['Amount'] = array();
    $object['Payment']['Amount']['Value'] = floatval(number_format($order_total * 100));
    $object['Payment']['Amount']['CurrencyCode'] = "CHF";
    $object['Payment']['OrderId'] = $order_id;
    $object['Payment']['Description'] = "Payment to dignity and hope";
    $object['Payer'] = array();
    $object['Payer']['LanguageCode'] = "en";
    $object['ReturnUrls'] = array();
    $object['ReturnUrls']['Success'] = "http://".$_SERVER['HTTP_HOST']."/api/checkout/checkout_success.php?RequestId=".$request_id;
    $object['ReturnUrls']['Fail'] = "http://".$_SERVER['HTTP_HOST']."/api/checkout/checkout_failure.php";

    $url = "https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize";

    return do_post($url, $object);

}
