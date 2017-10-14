<?php
class Zomato {
	public function __construct()
    {
        $this->urlPrefix = "https://developers.zomato.com/api/v2.1/search?";
        $this->APIKey = '29a0b741b9b6cbaa9c7e020493e6748b';
        $this->entityId = 74; //jkt
        $this->collectionIds = array(
                1,
                274852,
                29,
                126,
                71250
            );

    }

    function exec_get($fullurl)
    {
            
            $header = array(
                "Accept: application/json",
                "user-key: ".$this->APIKey,
            );
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT']); 
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

    public function getNearByPlaces($lat, $lon) {
        $count = rand(10,30);
    	$fullUrl = $this->urlPrefix.'count='.$count.'&lat='.$lat.'&lon='.$lon.'&sort=real_distance&order=asc';
    	$result = json_decode($this->exec_get($fullUrl), true);

        if($result != null && !empty($result)) {
            $restaurants = $result['restaurants'];
            $index = 0;
            if($result['results_found'] >= $count) {
                $index = $count - 1;
            }
            else {
                $index = $result['results_found'] - 1;
            }
            return $restaurants[rand(0, $index)];
        }
        
        return null;
        
    }

    public function getRandomPlaces() {
        $count = rand(10,30);
        $collId = $this->collectionIds[array_rand($this->collectionIds,1)];
        $entityType = 'city';
        $fullUrl = $this->urlPrefix.'entity_id='.$this->entityId.'&entity_type='.$entityType.'&count='.$count.'&collection_id='.$collId.'&sort=cost&order=desc';
        $result = json_decode($this->exec_get($fullUrl), true);

        if($result != null && !empty($result)) {
            $restaurants = $result['restaurants'];
            $index = 0;
            if($result['results_found'] >= $count) {
                $index = $count - 1;
            }
            else {
                $index = $result['results_found'] - 1;
            }
            return $restaurants[rand(0, $index)];
        }
        
        return null;
        
    }

    
}



?>