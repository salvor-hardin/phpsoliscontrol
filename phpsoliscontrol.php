<?php

// phpsoliscontrol 
// version 0.0.1
// standalone php script to set charge/discharge settings for Solis Inverters via SolisCloud API

/*
Commands based on details from
https://github.com/stevegal/solis_control
http://www.adsar.co.uk/soliscloud-php
*/

// error reporting
error_reporting(1); error_reporting(E_ERROR | E_WARNING | E_PARSE);

//-------------------- SETTINGS TO CONNECT TO SOLISCLOUD --------------------//

$keyID = '<your API Key provided by SolisAPI>'; //i.e. '13000000000000000'
$keySecret = '<your API SECRET provided by SolisAPI>'; //i.e. 'aabbccddeff001122334455'

$keyAccount = '<SolisCloud Web Account>'; //i.e. 'blahblah@hotmail.com';
$keyPass = '<SolisCloud Web Pass>'; // i.e.'sldkfjslkdfjslkdfjlsk';

//-------------------- SETTINGS TO CONNECT TO SOLISCLOUD --------------------//



//Overwrites settings from external file, if present
if (file_exists("config.php"))
require "config.php";



// if RESET is set AND TRUE, it will setup the charge-discharge to 00:00 all over so nothing will charge or discharge
//$RESET=TRUE;

// php phpsoliscontrol.php reset
// will force RESET and clear all values to no operation
if ((isset($argv)) && (in_array("reset",$argv))) $RESET=TRUE;





//Some inverter models ONLY use the FIRST charge/discharge values, 
//Repeating the setting in the three chargeSettings

//Setup here the values you want to push to the Inverter

$chargeCurrent=10;
$DISchargeCurrent=10;

$chargeSettings = [
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "06:00",
        'chargeEndTime' => "07:58",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ],
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "00:00",
        'chargeEndTime' => "00:00",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ],
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "00:00",
        'chargeEndTime' => "00:00",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ]
];






// RESET
if (
	(isset($RESET))
	&&
	($RESET===TRUE)
	)
{

$chargeCurrent=10;
$DISchargeCurrent=10;

$chargeSettings = [
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "00:00",
        'chargeEndTime' => "00:00",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ],
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "00:00",
        'chargeEndTime' => "00:00",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ],
    [
        'chargeCurrent' => $chargeCurrent,
        'dischargeCurrent' => $DISchargeCurrent,
        'chargeStartTime' => "00:00",
        'chargeEndTime' => "00:00",
        'dischargeStartTime' => "00:00",
        'dischargeEndTime' => "00:00"
    ]
];

}




//Generic function to API call SolisCloud endpoints

function soliscurl($keyAccount,$keyPass,$keyID,$keySecret,$endPoint,$body,$extraHeaders = array()) {

	$contentMD5 = base64_encode(md5($body, true));
	$contentType = "application/json";
	$gmdate = gmdate("D, j M Y H:i:s") . " GMT";
	$chain = "POST\n{$contentMD5}\n{$contentType}\n{$gmdate}\n{$endPoint}";
	
	$signature = base64_encode(pack('H*',hash_hmac('sha1', $chain , $keySecret, false)));
	$authorization = "API_" . $keyID . ":" . $signature;
	
	$headers = array(
		'Content-MD5: ' . $contentMD5,
		'Authorization: ' . $authorization,
		'Content-Type: ' . $contentType,
		'Date: ' . $gmdate
	  );
	
	//Add extra headers if any
	if (is_array($extraHeaders))
	foreach ($extraHeaders as $hkey => $hvalue)
		$headers[$hkey]=$headers[$hvalue];
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://www.soliscloud.com:13333'.$endPoint,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => $body,
	  CURLOPT_HTTPHEADER => $headers
	));
	
	$response = json_decode(curl_exec($curl),TRUE);
	
	curl_close($curl);

	if (
		(!empty($response['status']))
		&&
		($response['status']==403)
		)
		die("\n\nSomething went wrong, please check your configuration settings!\n\n");
	
	return $response;
	
}





$jouts=array();






// Get Battery percentage and Inverter ID (needed to set the new timings)
$endPoint = "/v1/api/inverterList";

$body = '{"pageNo":1,"pageSize":10}';

$response=soliscurl($keyAccount,$keyPass,$keyID,$keySecret,$endPoint,$body);

// Confirm inverter id has returned values and batteryCapacitySoc is integer from 1 to 100
if (
	(empty($response['data']['page']['records'][0]['id']))
	||
	(!ctype_digit((string) $response['data']['page']['records'][0]['id']))
	||
	(empty($response['data']['page']['records'][0]['batteryCapacitySoc']))
	||
	(!ctype_digit((string) $response['data']['page']['records'][0]['batteryCapacitySoc']))
	||
		(
		!
			(
				($response['data']['page']['records'][0]['batteryCapacitySoc']>0)
				&&
				($response['data']['page']['records'][0]['batteryCapacitySoc']<=100)
			)
		)
	)
	die("\n\nInverter id and batteryCapacitySoc retrieved must be an integer to confirm we can connect to SolisCloud. Review your settings!\n\n");
	
	
//Battery percentage (just the integer)
$jouts['battery']=$response['data']['page']['records'][0]['batteryCapacitySoc'];

//inverter ID
$jouts['inverter']=$response['data']['page']['records'][0]['id'];





	
//Get Token
$endPoint = "/v2/api/login";

$body = '{"userInfo":"' . $keyAccount . '","passWord":"' . md5($keyPass) . '"}';

$response=soliscurl($keyAccount,$keyPass,$keyID,$keySecret,$endPoint,$body);


if (
	(empty($response['csrfToken']))
	||
	(strpos($response['csrfToken'],"token_")!==0) //does not start with "token_"
	)
	die("\n\nUnable to retrieve a token for Inverter Control. Are your username and password correctly set?\n\n");

$jouts['token']=$response['csrfToken'];






//Send Control Command with extra token header and values from $chargeSettings

$endPoint = "/v2/api/control";

$body = '{"inverterId":"' . $jouts['inverter'] . '", "cid":"103",';
$body .= '"value":"' . implode(",",$chargeSettings[0]) . "," . implode(",",$chargeSettings[1]) . "," . implode(",",$chargeSettings[2]);
$body .= '"}';

$response=soliscurl($keyAccount,$keyPass,$keyID,$keySecret,$endPoint,$body,array("token" => $jouts['token']));


print_R($response);



?>