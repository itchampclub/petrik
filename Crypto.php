<?php
class Crypto {
	public function __construct()
    {
        $this->urlPrefix = "https://vip.bitcoin.co.id/api/";
        $this->urlSuffix = "ticker";
        $this->cryptoList = array(
        	"btc",
        	"bch",
            "btg",
        	"eth",
        	"etc",
        	"ltc",
        	"waves",
        	"xrp",
        	"xzc",
            "nxt",
            "xlm"
        	);
    }

    function exec_get($fullurl)
    {
            
            $header = array(
                "Content-Type: application/json"
            );
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);        
            curl_setopt($ch, CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_URL, $fullurl);
            
            $returned =  curl_exec($ch);
        
            return($returned);
    }

    public function getCryptoInfo($cryptoId) {
    	$fullUrl = $this->urlPrefix.$cryptoId.'_idr/'.$this->urlSuffix;
    	 return json_decode($this->exec_get($fullUrl), true);
    }

    public function checkCryptoId($cryptoId) {
    	error_log($cryptoId);
    	if(in_array($cryptoId, $this->cryptoList)){
    		return true;
    	}

    	return false;
    }

    public function generateIDRString($price) {
    	$separator = '.';
    	$result = '';
    	$count = 0;
    	$numLength = strlen((string)$price);
    	for($i = $numLength - 1; $i >= 0; $i--) {
    		if($count !=0 && $count % 3 == 0) {
    			$result = $price[$i].$separator.$result;
    		}
    		else {
    			$result = $price[$i].$result;
    		}
    		$count++;
    	}

    	return 'Rp '.$result;
    }
}



?>