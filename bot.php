<?php
require_once('./LineBotTiny.php');
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



if($type == 'memberJoined') 
{
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


else if($message['type']=='text')
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
	else if(strpos($incomingMsg,"ตารางสอบ") !== false)
		{
                if($source['groupId'] == 'Cb968a26fc8ed3eda142403d465d4b2a7')
                {
		$replyText = 'ตารางสอบวิชา 14215 คือ วันอาทิตย์ ที่ 27 มกราคม 2561 เวลา 13.30-16.30 น.';
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
	       else if($source['groupId'] == 'C465f0f90a6c2efa75434fe4afea31593')
	        {
		$replyText = 'ตารางสอบวิชา 14318 คือ วันอาทิตย์ ที่ 27 มกราคม 2561 เวลา 09.00-12.00 น.';
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
	else if(strpos($incomingMsg,"ทดสอบ") !== false)
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
			$replyText = "Hi ".$source['groupId'];
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
        
