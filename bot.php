<?php
require_once('./Crypto.php');
require_once('./CurrencyExchange.php');
require_once('./Zomato.php');
require_once('./GooglePlace.php');
require __DIR__.'/vendor/autoload.php';

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\DriverManager;

$config = [
    'channelAccessToken' => 'LYuQSwsxigKYSql4Ad3VdLsoCjmPbplcKJiT5XIDTeCdFxVGO0lC9uvGFcnmOEpc+Ams035JZ/+PfReMjlTYSidnX+GvrLH3T1QqRx/R4CxNU4EBw3uD+0iR98IvmyU9Udl8hge9HAPn/UpeYumQwwdB04t89/1O/w1cDnyilFU=',
	'channelSecret' => '03371616390b4cb96139412c5ce45d53'
];
date_default_timezone_set('Asia/Jakarta');
$yesNoList = array("Iya", "Nggak");

DriverManager::loadDriver(LINEDriver::class);
// create an instance
$botman = BotManFactory::create($config);

$botman->on('join',  function($payload, $bot) {
    $replyText = 'Halo, kenalin gw Petrik, teman nya Kerang Ajaib'.chr(10);
	$replyText .= chr(10).'Gw bisa bantu kalian nentuin tempat makan jika kalian bingung, maupun memberitahu nilai tuker mata uang (baik valas maupun crypto currency) loh..'.chr(10);
	$replyText .= chr(10).'Untuk lebih jelas, bisa ketik -help';

	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);
	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/kids jaman now/)',  function($bot) {
    $replyText = 'Wahh, gw ketahuan';
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);
	$bot->leaveChat();

})->driver(LINEDriver::class);

$botman->hear('(/^(hi|hai|hei|hey|helo|hello|halo|hallo) (pet|petrik)/)',  function($bot) {
    $userData = $bot->getUser();

    $replyText = "Hi ".$userData->getFirstName();
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/nilai tukar {from} ke {to}/)',  function($bot, $from, $to) {
    $currency   = new CurrencyExchange();
	if($currency->checkCurrencyID($from) && $currency->checkCurrencyID($to)){
		$currencyPrice = $currency->getCurrencyInfo($from, $to);
		$replyText = '1 '.$from.' = '.$currency->generateCurrencyValueString($currencyPrice, $to);
	}
	else {
		$replyText = 'maaf, petrik tidak mengenai currency itu :(';
	}
    
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/info harga crypto {crypto}/)',  function($bot, $crypto) {
    $crypto = new Crypto();

    if($crypto->checkCryptoId($cryptoId)){
		$cryptoData = $crypto->getCryptoInfo($cryptoId);
		$cryptoPrice = $cryptoData['ticker']['last'];
		$replyText = '1 '.strtoupper($cryptoId).' = '.$crypto->generateIDRString($cryptoPrice);
	}
	else {
		$replyText = 'maaf, petrik tidak mengenai crypto currency itu :(';
	}

	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/^apakah {string}/)',  function($bot, $string) {
    
    if(strpos($string, "erwin") !== false || strpos($string, "erwinwnz") !== false 
    	|| strpos($string, "wnz") !== false || strpos($string, "winz") !== false 
    	|| strpos($string, "winzz") !== false){
		
		$replyText = 'All hail @erwinwnz';
	}
	else {
		$replyText = $yesNoList[array_rand($yesNoList,1)];
	}

	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/(makan dimana|makan di mana)/)',  function($bot, $string) {
    
    $zomato = new Zomato();
	$result = $zomato->getRandomPlaces();

	if($result != null) {
		$replyText = 'Mungkin bisa coba '.$result['restaurant']['name'];
		$restaurantName = strtolower(trim($result['restaurant']['name']));

		$urlPlaces = $googleMapUrl.str_replace(" ","+",$restaurantName);
		$reply = array(
					array(
							'type' => 'text',					
							'text' => $replyText
						),
					array(
							'type' => 'text',					
							'text' => $urlPlaces
						)
					
				);
			
				
		
	}
	else {
		$replyText = "Wahh, Petrik lagi ga ada ide nih :(";
		$reply = array(
					array(
							'type' => 'text',					
							'text' => $replyText
						)
				);
		
	}

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/rekomendasi tempat/)',  function($bot) {
    
    $query = substr($incomingMsg, strrpos($incomingMsg, "rekomendasi ")+12);
	if(strpos($query,",") !== false) {
		$query = substr($query, strrpos($query, ",")+1);
	}
	$query = trim($query);
	$query = str_replace(" ", "+", $query);

	$googlePlace = new GooglePlace();
	$result = $googlePlace->getRandomPlacesByQuery($query);

	if($result != null) {
		$replyText = 'Mungkin bisa coba '.$result['name'];
		$restaurantPlaceId = $result['place_id'];
		$restaurantLoc = $result['geometry']['location'];
		$urlPlaces = $googleMapUrl.$restaurantLoc['lat'].",".$restaurantLoc['lng']."&query_place_id=".$restaurantPlaceId;
		$reply = array(
					array(
							'type' => 'text',					
							'text' => $replyText
						),
					array(
							'type' => 'text',					
							'text' => $urlPlaces
						)
					
				);

				
		
	}
	else {
		$replyText = "Wahh, Petrik lagi ga ada ide nih :(";
		$reply = array(
					array(
							'type' => 'text',					
							'text' => $replyText
						)
				);		
	}

	$bot->reply($reply);

})->driver(LINEDriver::class);


$botman->hear('(/^(selamat)? (pagi|siang|sore|malam) (pet|petrik)/)',  function($bot) {
    $userData = $bot->getUser();
    $currentHour = date('H');
	
	if($currentHour > 3 && $currentHour <= 11) {
		$replyText = "selamat pagi ";
	}
	else if($currentHour > 12 && $currentHour <= 15) {
		$replyText = "selamat siang ";
	}
	else if($currentHour > 15 && $currentHour <= 17) {
		$replyText = "selamat sore ";
	}
	else {
		$replyText = "selamat malam ";
	}

    $replyText .=$userData->getFirstName();
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/^(good)? (morning|afternoon|evening|night) (pet|petrik)/)',  function($bot) {
    $userData = $bot->getUser();
    $currentHour = date('H');
	
	if($currentHour > 3 && $currentHour <= 11) {
		$replyText = "good morning ";
	}
	else if($currentHour > 12 && $currentHour <= 15) {
		$replyText = "good afternoon ";
	}
	else if($currentHour > 15 && $currentHour <= 17) {
		$replyText = "good evening ";
	}
	else {
		$replyText = "good night ";
	}

    $replyText .=$userData->getFirstName();
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);

$botman->hear('(/^(siapa)? (..)?(nyipta|cipta|buat|develop|creator)(..)? (pet|petrik)/)',  function($bot) {
   
    $replyText = "@erwinwnz";
	$reply = array(
				array(
					'type' => 'text',					
					'text' => $replyText
				)
		);

	$bot->reply($reply);

})->driver(LINEDriver::class);


$bot->receivesLocation(function($bot, Location $location) {
    $lat = $location->getLatitude();
    $lng = $location->getLongitude();

    $googlePlace = new GooglePlace();
	$result = $googlePlace->getNearByPlaces($lat,$lon);

	if($result != null) {

		$replyText = 'Mungkin bisa coba '.$result['name'];
		$restaurantPlaceId = $result['place_id'];
		$urlPlaces = $googleMapUrl.$lat.",".$lon."&query_place_id=".$restaurantPlaceId;
		$reply = array(
						array(
								'type' => 'text',					
								'text' => $replyText
							),
						
						array(
								'type' => 'text',					
								'text' => $urlPlaces
							)
					);

	}
	else {
			$replyText = "Hmm, sepertinya Petrik tidak mengenali tempat itu";
			$reply = array(
							array(
									'type' => 'text',					
									'text' => $replyText
								)

					);
					
			
	}

	$bot->reply($reply);
});

$botman->listen();



?>