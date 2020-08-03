<?php
namespace Entrego;

class Rest
{
    private $token;
    public $sandbox;

    public $email;
    public $password;

	private $endpoint = "http://staging.entregoya.com/mobile-gateway/api/";
	private $uri;
	private $query;

    final private function getEndPoint()
    {
    	return "{$this->endpoint}{$this->uri}{$this->query}";
    }

    final public function request(string $uri, array $options = array())
    {
        if(!$this->sandbox) $this->endpoint = "https://entregoya.com/mobile-gateway/api/";

        $this->token = base64_encode("{$this->email}:{$this->password}");

    	return $this->_request($uri, $options);
    }

    final private function _request(string $uri, array $options = array())
    {
    	$curl = null;

    	try{
    		$this->uri = $uri;
    		
    		$curl = curl_init();
    		curl_setopt_array($curl, $this->optionsMerge($options));
    		$response = curl_exec($curl);
            $responseObject = json_decode($response);

            if(!empty($responseObject)) {
                $response = $responseObject;
                unset($responseObject);
            }

    		if(curl_error($curl)) throw new \Exception("No se pudo conectar con la api rest.", 500);

            $info = (object) curl_getinfo($curl);

            $errorsList = array(
                "1" => "Required data not present",
                "2" => "Authentication failed",
                "6" => "Error calculating route, mostly because of wrong addresses",
                "500" => "Internal service error"
            );

            if(!preg_match("/200|201|204|304/", $info->http_code) || isset($errorsList[$response->code])) {
                $errorMessages = array();

                $errorMessages[] = $errorsList[$response->code];

                throw new \Exception(!empty($errorMessages) ? implode(",", $errorMessages) : '', $info->http_code);
            }

            return $response;
    	}catch(\Exception $e) {
    		throw new \Exception("Entrego {$e->getCode()} : {$e->getMessage()}", $e->getCode());
    	}finally {
    		if(!is_null($curl)) curl_close($curl);
    	}
    }

    final private function optionsMerge(array $options = array())
    {
    	$options_merge = $this->optionsDefault();

    	if(count($options) > 0) {
    		foreach ($options as $key => $value) {
    			switch ($key) {
    				case "method":
    					$key = CURLOPT_CUSTOMREQUEST;
    					break;
    				case "data":
    					$key = CURLOPT_POSTFIELDS;
    					$value = json_encode($value, JSON_UNESCAPED_UNICODE);
    					break;
    				case "query":
    					if(count($value) > 0) {
	    					$value = array_build_query($value);
	    					$this->query = "?{$value}";
	    					$key = CURLOPT_URL;
                            $value = $this->getEndPoint();
    					}
    					break;
    				default:
    					$key = null;
    					break;
    			}

    			if(!is_null($key)) $options_merge[$key] = $value;
    		}
    	}

    	return $options_merge;

    }

    final private function optionsDefault()
    {
    	return array(
    		CURLOPT_URL => $this->getEndPoint(),
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_SSL_VERIFYHOST => false,
    		CURLOPT_SSL_VERIFYPEER => false,
    		CURLOPT_ENCODING => "",
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 30,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => "GET",
    		CURLOPT_HTTPHEADER => array(
    			"Accept: application/json",
    			"Content-Type: application/json",
    			"Authorization: Basic {$this->token}"
    		)
    	);
    }
}
