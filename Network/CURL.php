<?php
namespace misc\Network;


use core\ProxyLog;
use misc\Utils;

class CURL{
    const METHOD_GET        = 'GET';
    const METHOD_POST       = 'POST';
    const METHOD_PUT        = 'PUT';

    private $method;

    private $url                = '';
    private $currentUrl         = '';
    private $headersToSend      = [];
    /** @var array|string  */
    private $postData           = [];

    private $receivedHeaders    = [];
    private $receivedCookies    = [];
    private $status;
    private $error              = false;
    private $reply;

    private $proxy              = null;

    /** @var CookieManager */
    private $cookies            = null;

    function __construct(){
        $this->cookies = new CookieManager();
    }

    function setProxy($addr, $port){
        $this->proxy = ['addr' => $addr, 'port' => $port];
    }

    function init($url, $method=CURL::METHOD_GET){
        $this->setUrl($url);
        $this->setMethod($method);

        $this->headersToSend = [];
        $this->postData = [];

        $this->receivedHeaders = [];
        $this->status = null;
        $this->error = false;
        $this->reply = null;
    }

    function setUrl($url){
        $components = parse_url($url);
        if(!isset($components['scheme']) || !isset($components['host'])) return;
        $url = ''.
            $components['scheme'].'://'.
            (
            isset($components['usename'])?(
                $components['usename'].(
                isset($components['password'])?':'.$components['password']:''
                ).'@'
            )
                :
                ''
            ).
            $components['host'].
            (isset($components['port'])?':'.$components['port']:'').
            ($components['path']??'')
        ;
        if(isset($components['query'])){
            parse_str($components['query'], $query);
//            print_r($query);
            $url .= '?'.http_build_query($query);
        }
        $this->url = $this->currentUrl = $url;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void{
        $this->method = $method;
    }

    /**
     * @param array|string $postData
     */
    public function setPostData($postData): void{
        $this->postData = $postData;
    }

    function setHeadersToSend($headersToSend){
        $headers = [];
        foreach($headersToSend as $k => $v){
            $headers[] = "$k: $v";
        }
        $this->headersToSend = $headers;
    }

    /**
     * @param $headerPart
     * @return array
     */
    private function parseHeaderPart($headerPart){
        $headers    = [];
        $cookies    = [];
        $status     = null;

        $headerLines = explode("\n", $headerPart);
        foreach($headerLines as $i => $headerLine){
            $headerLine = trim($headerLine);
            if($i!=0) {
                preg_match('/([^:]+):\s*(.*)/si', $headerLine, $ms);
                [$key, $value] = [$ms[1], $ms[2]];
                $value = trim($value);
                if($key=='Set-Cookie') {
                    $cookies[] = $value;
                }elseif($key=='Location'){
                    $headers[$key] = $value?$value: $this->url;
                }else {
                    $headers[$key] = $value;
                }
            }else{
                $status = preg_match('/HTTP\w*\/\S+\s+(\d+)/si', $headerLine, $ms) ? $ms[1]: null;
            }
        }
        return [$status, $headers, $cookies];
    }

    private function parseHeaders($headerData){
        if(preg_match('/(.+)\n\r?\n\r?(.+)/si', $headerData, $ms)) {
            $headerData = $ms[2];
            if(preg_match('/.*?(HTTP\S+\s+30\d+.+)/si', $ms[1], $ms)){
                [$status, $headers, $cookies] = self::parseHeaderPart($ms[1]);
                $this->currentUrl = $headers['Location'] ?? $this->url;
            };
        }
        [$this->status, $this->receivedHeaders, $this->receivedCookies] = self::parseHeaderPart($headerData);
        foreach($this->receivedCookies as $receivedCookieRow) {
            $this->cookies->set($receivedCookieRow);
        }
    }

    function run($verbose = false, $depth=0){
//        $verbose = true;
        if($depth>10) return null;
//        echo Utils::SHcolor($this->url, 82)."\n";
        $curl = curl_init($this->url);
        if($this->method==CURL::METHOD_POST){
            curl_setopt($curl, CURLOPT_POST, true);
            $postData = is_string($this->postData) ? $this->postData : http_build_query($this->postData);
            echo $postData."\n";
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }elseif($this->method == CURL::METHOD_PUT){
            $this->headersToSend = [];
            $this->headersToSend[] = 'Content-Length: '.intval(strlen($this->postData));
            $this->headersToSend[] = 'Content-Type: application/octet-stream';
            $this->headersToSend[] = 'Content-Transfer-Encoding: binary';
            $this->headersToSend[] = 'Expect:';
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
        }

        curl_setopt($curl, CURLOPT_VERBOSE, $verbose);
        if($verbose) {
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($curl, CURLOPT_STDERR, $verbose);
        }

//        if(isset($this->headersToSend['User-Agent'])){
//            curl_setopt($curl, CURLOPT_USERAGENT, $this->headersToSend['User-Agent']);
//        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headersToSend);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING , "");
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        if($this->proxy){
            $pStr = $this->proxy['addr'] . ':' . $this->proxy['port'];
//            $pStr = 'socks5://' . $this->proxy['addr'] . ':' . $this->proxy['port'];
//            echo $pStr."\n";
            curl_setopt($curl, CURLOPT_PROXY, $pStr);
        }

        $c = '';
        $cookies = $this->cookies->get($this->url);
//        var_dump($cookies);
        foreach($cookies as $k => $v){
            $c .= "$k=".$v."; ";
//            $c .= "$k=".urlencode($v)."; ";
        }
        $c = trim($c, '; ');
        if($c){
//            echo "$c\n";
            curl_setopt($curl, CURLOPT_COOKIE, $c);
        }

//        curl_setopt($curl,CURLOPT_CAINFO,'/etc/ssl/cert.pem');
//        curl_setopt($curl,CURLOPT_CAPATH,null);

        $response = curl_exec($curl);
//        echo $response;exit;

        if($verbose) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            echo Utils::SHcolor("Verbose information:\n<pre>". $verboseLog. "</pre>\n", 240);
            echo Utils::SHcolor(print_r(curl_getinfo($curl), true), 240);
        }

        $this->error = null;
        if(curl_errno($curl)){
            $this->error = curl_error($curl);
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);

        $this->parseHeaders(trim(substr($response, 0, $headerSize)));
        if($this->getStatus()>=300 && $this->getStatus()<=310){
            $location = $this->getReceivedHeaders()['Location'];
            $this->url = $location;
            $this->method = self::METHOD_GET;
            echo "Redir: $location\n";
            return $this->run($verbose, $depth+1);
        }
        $this->reply = substr($response, $headerSize);
        /*
        Log::create(null, null, Log::TYPE_REQUEST, [
            'request'   => [
                'url'       => $this->url,
                'method'    => $this->method,
                'headers'   => $this->headersToSend,
                'cookies'   => $c,
                'body'      => $this->postData,

            ],
            'reply'     => [
                'headers'   => $this->receivedHeaders,
                'cookies'   => $this->receivedCookies,
                'error'     => $this->error,
                'body'      => $this->reply
            ],
        ]);
        */
        return !boolval($this->error);
    }

    function getReceivedHeaders(){
        return $this->receivedHeaders;
    }

    function getReply($isJSON = true){
        return $isJSON ? json_decode($this->reply, true) : $this->reply;
    }

    /**
     * @return int
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getError(){
        return $this->error;
    }

    /**
     * @return string
     */
    public function getCurrentUrl(): string{
        return $this->currentUrl;
    }

    /**
     * @return CookieManager
     */
    public function getCookies(): CookieManager
    {
        return $this->cookies;
    }


}