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
