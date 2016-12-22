<?php

require($_SERVER["DOCUMENT_ROOT"]."/setup.php");

if (!isset($_POST['password']) || $_POST['password'] != "jgkhasdlnajawihcbajjj9823423sda££") {
	die();
}


$json = file_get_contents("http://api.fixer.io/latest?base=CHF");
$json = str_replace(' ', '', $json);
$json = preg_replace( "/\r|\n/", "", $json);

$json = json_decode($json, true);

$sql = "SELECT * FROM conversion_rates WHERE currency_code != 'CHF'";
$result = $mysqli->query($sql);

$currencies = [];

while ($row = $result->fetch_assoc()) {
	if (!isset($json['rates'][$row['currency_code']])) die($row['currency_code']);
	$row['value'] = $json['rates'][$row['currency_code']];
	$currencies[] = $row;
}

$stmt = $mysqli->prepare("UPDATE conversion_rates SET value = ?, last_updated = CURRENT_TIMESTAMP WHERE currency_code = ?");
$stmt->bind_param("ds", $value, $code);

foreach ($currencies as $currency) {
	$value = $currency['value'];
	$code = $currency['currency_code'];
	$stmt->execute();
}


echo "success!";
