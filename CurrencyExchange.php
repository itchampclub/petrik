<?php
class CurrencyExchange {
	public function __construct()
    {
        $this->urlPrefix = "https://finance.google.com/finance/converter?a=1";
        
        $this->currencyList = array(
        	"AED",
            "AFN",
            "ALL",
            "AMD",
            "ANG",
            "AOA",
            "ARS",
            "AUD",
            "AWG",
            "AZN",
            "BAM",
            "BBD",
            "BDT",
            "BGN",
            "BHD",
            "BIF",
            "BMD",
            "BND",
            "BOB",
            "BRL",
            "BSD",
            "BTC",
            "BTN",
            "BWP",
            "BYN",
            "BYR",
            "BZD",
            "CAD",
            "CDF",
            "CHF",
            "CLF",
            "CLP",
            "CNH",
            "CNY",
            "COP",
            "CRC",
            "CUP",
            "CVE",
            "CZK",
            "DEM",
            "DJF",
            "DKK",
            "DOP",
            "DZD",
            "EGP",
            "ERN",
            "ETB",
            "EUR",
            "FIM",
            "FJD",
            "FKP",
            "FRF",
            "GBP",
            "GEL",
            "GHS",
            "GIP",
            "GMD",
            "GNF",
            "GTQ",
            "GYD",
            "HKD",
            "HNL",
            "HRK",
            "HTG",
            "HUF",
            "IDR",
            "IEP",
            "ILS",
            "INR",
            "IQD",
            "IRR",
            "ISK",
            "ITL",
            "JMD",
            "JOD",
            "JPY",
            "KES",
            "KGS",
            "KHR",
            "KMF",
            "KPW",
            "KRW",
            "KWD",
            "KYD",
            "KZT",
            "LAK",
            "LBP",
            "LKR",
            "LRD",
            "LSL",
            "LTL",
            "LVL",
            "LYD",
            "MAD",
            "MDL",
            "MGA",
            "MKD",
            "MMK",
            "MNT",
            "MOP",
            "MRO",
            "MUR",
            "MVR",
            "MWK",
            "MXN",
            "MYR",
            "MZN",
            "NAD",
            "NGN",
            "NIO",
            "NOK",
            "NPR",
            "NZD",
            "OMR",
            "PAB",
            "PEN",
            "PGK",
            "PHP",
            "PKG",
            "PKR",
            "PLN",
            "PYG",
            "QAR",
            "RON",
            "RSD",
            "RUB",
            "RWF",
            "SAR",
            "SBD",
            "SCR",
            "SDG",
            "SEK",
            "SGD",
            "SHP",
            "SKK",
            "SLL",
            "SOS",
            "SRD",
            "STD",
            "SVC",
            "SYP",
            "SZL",
            "THB",
            "TJS",
            "TMT",
            "TND",
            "TOP",
            "TRY",
            "TTD",
            "TWD",
            "TZS",
            "UAH",
            "UGX",
            "USD",
            "UYU",
            "UZS",
            "VEF",
            "VND",
            "VUV",
            "WST",
            "XAF",
            "XCD",
            "XDR",
            "XOF",
            "XPF",
            "YER",
            "ZAR",
            "ZMK",
            "ZMW",
            "ZWL"
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

    public function getCurrencyInfo($from, $to) {
    	$fullUrl = $this->urlPrefix.'&from='.$from.'&to='.$to;
        $source = $this->exec_get($fullUrl);
        $res = explode("<span class=bld>",$source);
        return explode(" ",$res[1])[0];
    }

    public function checkCurrencyID($currencyID) {
    	error_log($currencyID);
    	if(in_array($currencyID, $this->currencyList)){
    		return true;
    	}

    	return false;
    }

    public function generateCurrencyValueString($value, $currencyID) {

        $separator = ',';

        if(strpos($value, ".") !== false)
        {
            $explodedValue = explode(".", $value);
            $value = $explodedValue[0];
            if(strlen($explodedValue[1]) > 2) {
                $result = '.'.$explodedValue[1][0].$explodedValue[1][1];
            }
            else
                $result = '.'.$explodedValue[1];
            
        }
        else
            $result = '';

    	
    	$count = 0;
    	$numLength = strlen((string)$value);
    	for($i = $numLength - 1; $i >= 0; $i--) {
    		if($count !=0 && $count % 3 == 0) {
    			$result = $value[$i].$separator.$result;
    		}
    		else {
    			$result = $value[$i].$result;
    		}
    		$count++;
    	}

    	return $result.' '.$currencyID;
    }
}



?>