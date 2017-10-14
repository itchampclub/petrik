<?php
class GooglePlace {
	public function __construct()
    {
        $this->textSearchUrlPrefix = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=";
        $this->nearByUrlPrefix = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=";
        $this->APIKey = array(
            'AIzaSyAsUovTstA1XHEq-PlnufR5Zb_zf6EaLi0',
            'AIzaSyAbq2CBqObQ1vnDM1B1vev2pDryQerNZQ4'
            );
        

    }

    function exec_get($fullurl)
    {
            
            $header = array(
                "Accept: application/json"
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
        $query = "tempat+makan+sekitar";
        $key = $this->APIKey[array_rand($this->APIKey, 1)];
    	$fullUrl = $this->nearByUrlPrefix.$lat.",".$lon."&language=id&opennow=true&type=restaurant&keyword=".$query."&radius=500&key=".$key;
    	$result = json_decode($this->exec_get($fullUrl), true);

        if($result != null && !empty($result['results'])) {
            $restaurants = $result['results'];
            $index = count($restaurants);
           
            return $restaurants[rand(0, $index)];
        }
        
        return null;
        
    }

    public function getRandomPlacesByQuery($query) {
        $type="";
        $useType = false;
        $key = $this->APIKey[array_rand($this->APIKey, 1)];
        if(strpos($query,"nongkrong") !== false || strpos($query,"ngopi") !== false || strpos($query,"hangout") !== false
            || strpos($query,"ngumpul") !== false || strpos($query,"kumpul") !== false || strpos($query,"kopi") !== false
            || strpos($query,"tongkrong") !== false){
            $type="cafe";
            $useType = true;
        }
        else if(strpos($query,"makan") !== false) {
            $type = "restaurant";
            $useType = true;
        }
        if($useType)
            $fullUrl = $this->textSearchUrlPrefix.$query."&language=id&opennow=true&type=".$type."&key=".$key;
        else
            $fullUrl = $this->textSearchUrlPrefix.$query."&language=id&opennow=true&key=".$key;

        $result = json_decode($this->exec_get($fullUrl), true);

        if($result != null && !empty($result['results'])) {
            $restaurants = $result['results'];
            $index = count($restaurants);
           
            return $restaurants[rand(0, $index)];
        }
        
        return null;
        
    }

    
}



?>