<?php
class sign
{
//    Developer:MD Zahid Hasan
//    Email:jahid1234.jh@gmail.com
//    API USED FOR: Daraz Open API Used For Seller Data(cURL Generate)
//      Steps: 1.Seller Authentication
//             2.Signature Algorithm Apply
//             3.cURL Generate


    public $appkey = "Your App key";
    public $secretKey = "Your App Secret";
    public $gatewayUrl = "https://api.daraz.com.bd/rest"; //Daraz Bd Base URL
    protected $signMethod = "sha256"; //Static Signature Algorithm
    public $apiName = "/seller/get"; // Your Desired API
    public $access_token="Your Access Token"; //Generated From  Base URL+/auth/token/create
    public $headerParams = array();
    public $udfParams = array();
    public $fileParams = array();
    public $httpMethod = 'POST';


//Generate Final API Data
    public function generateResult(){
        $request = $this->requestSeller('/seller/get','GET');
        $response_total = $this->execute();
    }

    //Generate Signature by Using Signature Algorithm
    protected function generateSign($apiName, $params)
    {
        ksort($params);
        $stringToBeSigned = '';
        $stringToBeSigned .= $apiName;
        foreach ($params as $k => $v) {
            $stringToBeSigned .= "$k$v";
        }
        return strtoupper($this->hmac_sha256($stringToBeSigned, $this->secretKey));
    }

    //Making of 64 Bit Encode Value
    function hmac_sha256($data, $key){
        return hash_hmac('sha256', $data, $key);
    }

    //Get The cUrl Value by GET Method
    public function curl_get($url,$apiFields = null,$headerFields = null)
    {
        $ch = curl_init();

        foreach ($apiFields as $key => $value)
        {
            $url .= "&" ."$key=" . urlencode($value);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if($headerFields)
        {
            $headers = array();
            foreach ($headerFields as $key => $value)
            {
                $headers[] = "$key: $value";
                var_dump( $headers);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            unset($headers);
        }
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" )
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $output = curl_exec($ch);
        $errno = curl_errno($ch);

        if ($errno)
        {
            curl_close($ch);
            throw new Exception($errno,0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 !== $httpStatusCode)
            {
                //throw new Exception($reponse,$httpStatusCode);
            }
        }
        return $output;
    }

    //Get The cUrl Value by POST Method
    public function curl_post($url, $postFields = null, $fileFields = null,$headerFields = null)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        if ($this->readTimeout)
        {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }

        if ($this->connectTimeout)
        {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }

        if($headerFields)
        {
            $headers = array();
            foreach ($headerFields as $key => $value)
            {
                $headers[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            unset($headers);
        }

        curl_setopt ( $ch, CURLOPT_USERAGENT, $this->sdkVersion );

        //https ignore ssl check ?
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" )
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $delimiter = '-------------' . uniqid();
        $data = '';
        if($postFields != null)
        {
            foreach ($postFields as $name => $content)
            {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"';
                $data .= "\r\n\r\n" . $content . "\r\n";
            }
            unset($name,$content);
        }

        if($fileFields != null)
        {
            foreach ($fileFields as $name => $file)
            {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $file['name'] . "\" \r\n";
                $data .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
                $data .= $file['content'] . "\r\n";
            }
            unset($name,$file);
        }
        $data .= "--" . $delimiter . "--";

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER ,
            array(
                'Content-Type: multipart/form-data; boundary=' . $delimiter,
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        unset($data);
        $errno = curl_errno($ch);
        if ($errno)
        {
            curl_close($ch);
            throw new Exception($errno,0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($response,$httpStatusCode);
            }
        }

        return $response;
    }
    //Execute The Main Output
    public function execute()
    {
        $accessToken = $this->access_token;
        $sysParams["app_key"] = $this->appkey;
        $sysParams["sign_method"] = $this->signMethod;
        $sysParams["timestamp"] = $this->msectime();
        $sysParams["access_token"] = $accessToken;
        $apiParams = $this->udfParams;
        $requestUrl = $this->gatewayUrl;
        $requestUrl .= $this->apiName;
        $requestUrl .= '?';
        //Taking Input to Generate Sign Value for signature generate Function
        $sysParams["sign"] = $this->generateSign($this->apiName,array_merge($apiParams, $sysParams));
        echo " FInal URL: <br>";
        echo $requestUrl .'app_key'.'='.$this->appkey.'&sign_method'.'='.'sha256'.'&sign='.$sysParams["sign"].'&access_token'.'='.$accessToken.'&amp'.'timestamp'.'='.$sysParams["timestamp"]."<br>"."<br>";
        foreach ($sysParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        echo "<br>";
        $resp = '';
        try
        {
            if($this->httpMethod == 'POST')
            {
                $resp = $this->curl_post($requestUrl, $apiParams, $this->fileParams,$this->headerParams);
                //Printing The Data
                echo $resp;
            }
            else
            {
                $resp = $this->curl_get($requestUrl, $apiParams,$this->headerParams);
                echo 'DATA:<br>'.$resp;
            }
        }
        catch (Exception $e)
        {
        }

        unset($apiParams);

        $respObject = json_decode($resp);

        if(isset($respObject->code) && $respObject->code != "0")
        {
        } else
        {

        }
        return $resp;
    }

  //Set Api Name
    public function requestSeller($apiName,$httpMethod = 'POST')
    {
        $this->apiName = $apiName;
        $this->httpMethod = $httpMethod;

        if($this->startWith($apiName,"//"))
        {
            throw new Exception("api name is invalid. It should be start with /");
        }
    }

 //Store Parameter value of API Calling
    function addApiParam($key,$value)
    {

        if(!is_string($key))
        {
            throw new Exception("api param key should be string");
        }

        if(is_object($value))
        {
            $this->udfParams[$key] = json_decode($value);
            // $this->payload= $this->udfParams[$key];;

        }
        else
        {
            $this->udfParams[$key] = $value;
            echo $this->udfParams[$key].' lReq2<br>';
        }
    }
    function startWith($str, $needle) {
        return strpos($str, $needle) === 0;
    }

    //Convert Time to millisecond according API Format
    function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        return $sec . '000';
    }
}

//creating The OOP Reference
   $sign= new sign();
   $sign->generateResult();
?>
