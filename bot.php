<?php

require_once('./LineBotTiny.php');
require_once('./Crypto.php');
require_once('./CurrencyExchange.php');
require_once('./Zomato.php');

$channelAccessToken = '1B8pMUtZdLgdebgTuRrxV3YirCQv91mbXGnxvlTbX7Cxn471Fs0bBgwGVpedxnPKm7tZUWxnMrT2NqCBCLAG8L7r6vtYoZwb3iqRvYr3BZGrZX/mRNFG8lzNbLr5CHO4PWfTicerD5PVHYjC8mpQ4wdB04t89/1O/w1cDnyilFU=';
$channelSecret = '69b2d81ee6e8ff48d2cacdc8c7d8c337';
date_default_timezone_set('Asia/Jakarta');

$client 		= new LINEBotTiny($channelAccessToken, $channelSecret);
$reply 			= '';
$leave 			= false;

$event 			= $client->parseEvents()[0];
$type 			= $event['type']; 
$source     	= $event['source'];
$userId 		= $source['userId'];
$replyToken 	= $event['replyToken'];
$timestamp		= $event['timestamp'];
$message 		= $event['message'];
$messageid 		= $message['id'];


$yesNoList = array("Iya", "Nggak");


$helloPattern = '/'.'^(hi|hai|hei|hey|helo|hello|halo|hallo) (pet|petrik)'.'/';
$currencyPattern = '/'.'nilai tukar (...)+ ke (...)+'.'/';
$cryptoPattern = '/'.'info harga crypto (...)+'.'/';
$wnzPattern = '/'.'^apakah (erwin|erwinwnz|win|winz|winzz|wnz)'.'/';
$eatPlacePattern = '/'.'(makan dimana|makan di mana)'.'/';
$placeRecommendationPattern = '/'.'rekomendasi tempat'.'/';
$salamPattern ='/'.'(selamat)?( )?(pagi|siang|sore|malam) (pet|petrik)'.'/';
$greetingPattern = '/'.'(good)?( )?(morning|afternoon|evening|night) (pet|petrik)'.'/';

if($type == 'memberJoined') {
	$replyText = 'สวัสดี'.chr(10);
	$replyText .= chr(10).'นี่คือข้อความต้อนรับจากบอท'.chr(10);
	$replyText .= chr(10).'นะจ้ะ';
	
	$reply = array(
								'replyToken' => $replyToken,														
								'messages' => array(
									array(
											'type' => 'text',					
											'text' => $replyText
										)
								)
							);

}
else if(preg_match($incomingMsg))
{
	$incomingMsg = strtolower($message['text']);

	if(strpos($incomingMsg,"ไล่บอท") !== false)
	{
		$replyText = 'บายจ้า';
		$reply = array(
								'replyToken' => $replyToken,														
								'messages' => array(
									array(
											'type' => 'text',					
											'text' => $replyText
										)
								)
							);
		$leave = true;

	}
	
	
	else if(preg_match($helloPattern, $incomingMsg))
	{

		$userData = null;
		if($source['type'] == "group") {
			$userData = $client->getProfilFromGroup($userId, $source['groupId']);
		}
		else if($source['type'] == "room") {
			$userData = $client->getProfilFromRoom($userId, $source['roomId']);
		}
		else if($source['type'] == "user") {
			$userData = $client->profil($userId);
		}
		
		if($userData != null) {
			$replyText = "Hi ".$userData['displayName'];
			$reply = array(
							'replyToken' => $replyToken,														
							'messages' => array(
								array(
										'type' => 'text',					
										'text' => $replyText
									)
							)
						);
		}
			
		
	}
	else if(preg_match($currencyPattern, $incomingMsg))
	{
		$currency   = new CurrencyExchange();
		$stringAfterCommand = substr($incomingMsg, strrpos($incomingMsg, "nilai tukar ")+12);
		if($stringAfterCommand != "" ) 
		{
		 	if(strpos($stringAfterCommand, " ke ") !== false) {
				$currencyString = explode(" ke ",$stringAfterCommand);
				$from = trim(strtoupper($currencyString[0]));
				$to = trim(strtoupper(explode(" ",$currencyString[1])[0]));
				
			}
			else {
				$from = "IDR";
				$to = trim(strtoupper($stringAfterCommand));
			}

			if($currency->checkCurrencyID($from) && $currency->checkCurrencyID($to)){
				$currencyPrice = $currency->getCurrencyInfo($from, $to);
				$replyText = '1 '.$from.' = '.$currency->generateCurrencyValueString($currencyPrice, $to);
			}
			else {
				$replyText = 'maaf, petrik tidak mengenai currency itu :(';
			}
		}
		else {
			$replyText = 'kurang currencyID nya bosque';
		}

		$reply = array(
								'replyToken' => $replyToken,														
								'messages' => array(
									array(
											'type' => 'text',					
											'text' => $replyText
										)
								)
							);

	}
	else if(preg_match($cryptoPattern, $incomingMsg))
	{
		$crypto 	= new Crypto();
		$stringAfterCommand = substr($incomingMsg, strrpos($incomingMsg, "info harga crypto ")+18);
		if($stringAfterCommand != "" ) 
		{
		 	if(strpos($stringAfterCommand, " ") === false) {
				$cryptoId = trim($stringAfterCommand);
			}
			else {
				$cryptoId = trim(substr($stringAfterCommand, 0 , strpos($stringAfterCommand, " ")));
			}

			if($crypto->checkCryptoId($cryptoId)){
				$cryptoData = $crypto->getCryptoInfo($cryptoId);
				$cryptoPrice = $cryptoData['ticker']['last'];
				$replyText = '1 '.strtoupper($cryptoId).' = '.$crypto->generateIDRString($cryptoPrice);
			}
			else {
				$replyText = 'maaf, petrik tidak mengenai crypto currency itu :(';
			}
		}
		else {
			$replyText = 'kurang cryptoId nya bosque';
		}

		$reply = array(
								'replyToken' => $replyToken,														
								'messages' => array(
									array(
											'type' => 'text',					
											'text' => $replyText
										)
								)
							);
	
	}
	else if(strpos($incomingMsg,"apakah ") !== false)
	{
		
		if(preg_match($wnzPattern,$incomingMsg)) {
			$replyText = 'All hail @erwinwnz';
		}
		else {
			$replyText = $yesNoList[array_rand($yesNoList,1)];
		}
		
		$reply = array(
								'replyToken' => $replyToken,														
								'messages' => array(
									array(
											'type' => 'text',					
											'text' => $replyText
										)
								)
							);

	}
	else if(preg_match($eatPlacePattern, $incomingMsg))
	{
		
		$zomato = new Zomato();
		$result = $zomato->getRandomPlaces();

		if($result != null) {
			$replyText = 'Mungkin bisa coba '.$result['restaurant']['name'];
			$restaurantName = strtolower(trim($result['restaurant']['name']));

			$urlPlaces = $googleMapUrl.str_replace(" ","+",$restaurantName);
			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											),
										array(
												'type' => 'text',					
												'text' => $urlPlaces
											)
										
									)
								);
					
			
		}
		else {
			$replyText = "Wahh, Petrik lagi ga ada ide nih :(";
			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											)
									)
								);
					
			
		}
	}
	else if(preg_match($placeRecommendationPattern, $incomingMsg))
	{
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
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											),
										array(
												'type' => 'text',					
												'text' => $urlPlaces
											)
										
									)
								);
					
			
		}
		else {
			$replyText = "Wahh, Petrik lagi ga ada ide nih :(";
			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											)
									)
								);
					
			
		}
				
	}
	else if(strpos($incomingMsg,"cipta") !== false || strpos($incomingMsg,"nyipta") !== false || strpos($incomingMsg,"buat") !== false || strpos($incomingMsg,"creator") !== false || strpos($incomingMsg,"develop") !== false) {
		if(strpos($incomingMsg,"petrik") !== false) {
			$replyText = '@erwinwnz';
			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											)
									)
								);
		}
	}
	else if(preg_match($salamPattern, $incomingMsg)) {
			
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

			$userData = null;
			if($source['type'] == "group") {
				$userData = $client->getProfilFromGroup($userId, $source['groupId']);
			}
			else if($source['type'] == "room") {
				$userData = $client->getProfilFromRoom($userId, $source['roomId']);
			}
			else if($source['type'] == "user") {
				$userData = $client->profil($userId);
			}
			
			if($userData != null) {
				$replyText .= $userData['displayName'];
			}

			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											)
									)
								);
		
		
	}
	else if(preg_match($greetingPattern, $incomingMsg)) {
				
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

			$userData = null;
			if($source['type'] == "group") {
				$userData = $client->getProfilFromGroup($userId, $source['groupId']);
			}
			else if($source['type'] == "room") {
				$userData = $client->getProfilFromRoom($userId, $source['roomId']);
			}
			else if($source['type'] == "user") {
				$userData = $client->profil($userId);
			}
			
			if($userData != null) {
				$replyText .= $userData['displayName'];
			}
			
			$reply = array(
									'replyToken' => $replyToken,														
									'messages' => array(
										array(
												'type' => 'text',					
												'text' => $replyText
											)
									)
								);
		}
		
		
}



if($reply != "") {
				
		$client->replyMessage($reply);
	 
	 	if($leave) {

	 		if($source['type'] == "group") {
				$client->leaveGroup($source['groupId']);
			}
			else if($source['type'] == "room") {
				$client->leaveRoom($source['roomId']);
			} 

	 	}	
}


?>
