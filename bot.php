<?php

require_once('./LineBotTiny.php');
require_once('./Crypto.php');
require_once('./CurrencyExchange.php');
require_once('./Zomato.php');
require_once('./GooglePlace.php');

$channelAccessToken = 'LYuQSwsxigKYSql4Ad3VdLsoCjmPbplcKJiT5XIDTeCdFxVGO0lC9uvGFcnmOEpc+Ams035JZ/+PfReMjlTYSidnX+GvrLH3T1QqRx/R4CxNU4EBw3uD+0iR98IvmyU9Udl8hge9HAPn/UpeYumQwwdB04t89/1O/w1cDnyilFU=';
$channelSecret = '03371616390b4cb96139412c5ce45d53';
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
$googleMapUrl	= "https://www.google.com/maps/search/?api=1&query=";


$yesNoList = array("Iya", "Nggak");

if($type == 'join') {
	$replyText = 'Halo, kenalin gw Petrik, teman nya Kerang Ajaib'.chr(10);
	$replyText .= chr(10).'Gw bisa bantu kalian nentuin tempat makan jika kalian bingung, maupun memberitahu nilai tuker mata uang (baik valas maupun crypto currency) loh..'.chr(10);
	$replyText .= chr(10).'Untuk lebih jelas, bisa ketik -help';
	
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
else if($message['type']=='text')
{
	$incomingMsg = strtolower($message['text']);

	if(strpos($incomingMsg,"kids jaman now") !== false)
	{
		$replyText = 'Wahh, gw ketahuan';
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
	$helloPattern = '/'.'^(hi|hai|hei|hey|helo|hello|halo|hallo) (pet|petrik)'.'/';
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
	else if(strpos($incomingMsg, "nilai tukar ") !== false)
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
	else if(strpos($incomingMsg, "info harga crypto ") !== false)
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
		$stringAfterCommand = substr($incomingMsg, strrpos($incomingMsg, "apakah ")+7);
		if(strpos($stringAfterCommand, "erwin") !== false || strpos($stringAfterCommand, "erwinwnz") !== false || strpos($stringAfterCommand, "wnz") !== false || strpos($stringAfterCommand, "winz") !== false || strpos($stringAfterCommand, "winzz") !== false) {
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
	else if(strpos($incomingMsg,"makan dimana") !== false || strpos($incomingMsg,"makan di mana") !== false)
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
	else if(strpos($incomingMsg,"rekomendasi tempat") !== false)
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
	else if(strpos($incomingMsg, "selamat")!== false) {
		$stringAfterCommand = substr($incomingMsg, strrpos($incomingMsg, "selamat")+7);
		if(strpos($stringAfterCommand, "pagi")!== false || strpos($stringAfterCommand, "siang")!== false || strpos($stringAfterCommand, "sore")!== false || strpos($stringAfterCommand, "malam")!== false){
			$petrik = substr($incomingMsg, strrpos($incomingMsg, "pet"));
			error_log($petrik);
			if($petrik == "pet" || $petrik == "petrik") {	
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
		}
	}
	else if(strpos($incomingMsg, "good")!== false) {
		$stringAfterCommand = substr($incomingMsg, strrpos($incomingMsg, "good")+4);
		if(strpos($stringAfterCommand, "morning")!== false || strpos($stringAfterCommand, "afternoon")!== false || strpos($stringAfterCommand, "evening")!== false || strpos($stringAfterCommand, "night")!== false){
			$petrik = substr($incomingMsg, strrpos($incomingMsg, "pet"));
			if($petrik == "pet" || $petrik == "petrik") {
				
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
	}
	

		
}
else if($message['type']=='location') {
	$lat = $message['latitude'];
	$lon = $message['longitude'];
	$googlePlace = new GooglePlace();
	$result = $googlePlace->getNearByPlaces($lat,$lon);

	if($result != null) {
		$replyText = 'Mungkin bisa coba '.$result['name'];
		$restaurantPlaceId = $result['place_id'];
		$urlPlaces = $googleMapUrl.$lat.",".$lon."&query_place_id=".$restaurantPlaceId;
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
		$replyText = "Hmm, sepertinya Petrik tidak mengenali tempat itu";
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
