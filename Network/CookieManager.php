<?php


namespace misc\Network;


use misc\Utils;

class CookieManager implements \JsonSerializable{
    private $data   = [];

    public function jsonSerialize(){
        return $this->data;
    }

    function clear(){
        $this->data = [];
    }

    function printData(){
        print_r($this->data);
    }

    function get($url = null){
        $this->check();
        $cookie = [];
        $info = parse_url($url);
        $currentDomain = $info['host']??'';
        $currentPath = rtrim($info['path']??'', '/');
        foreach($this->data as $domain => $domainInfo) {
            if(
                !$currentDomain ||
                $domain=='.' ||
//                ($domain[0]=='.' && ($p=strpos($currentDomain, trim($domain, '.')))!==false && ($currentDomain[$p-1]=='.' || $p==0)) ||
                (($p=strpos($currentDomain, trim($domain, '.')))!==false && ($currentDomain[$p-1]=='.' || $p==0)) ||
                $domain==$currentDomain
            ) {
                foreach ($domainInfo as $path => $pathInfo) {
                    /** @todo: path check */
                    foreach ($pathInfo as $var => $varInfo) {
                        $cookie[$var] = $varInfo['val'];
//                        echo Utils::SHcolor("Cookie: $var = ".$varInfo['val'], 245)."\n";
                    }
                }
            }
        }
        return $cookie;
    }

    function set($cookieRow){
//        echo "cookieRow: $cookieRow\n";
        parse_str(strtr($cookieRow, array(/*'&' => '%26', '+' => '%2B', */';' => '&')), $info_);
//        parse_str(strtr($cookieRow, array('&' => '%26', '+' => '%2B', ';' => '&')), $info_);
        $var = array_key_first($info_);
        $val = array_shift($info_);
        $info = [];
        array_walk($info_, function($v,$k)use(&$info){
            $info[strtolower($k)] = $v;
        });
//        print_r($info);
//        exit;
        $expires = time();
        if(isset($info['expires'])){
            $expires = strtotime($info['expires']);
        }elseif(isset($info['max-age'])){
            $expires += $info['max-age']-1;
        }
        $this->setVar($var, $val, $info['domain']??'.', $info['path'], $expires);
    }

    /**
     * @param string $domain
     * @param string $path
     * @param string $var
     * @return array
     */
    function getRow($domain, $path, $var){
        return $this->data[$domain][$path][$var];
    }

    function setData($data){
        $this->data = $data;
    }

    function getData(){
        return $this->data;
    }

    function setVar($var, $val, $domain, $path, $expires){
        $this->data[$domain] = $this->data[$domain]??[];
        $this->data[$domain][$path] = $this->data[$domain][$path]??[];
        $this->data[$domain][$path][$var] = ['val' => $val, 'expires' => $expires];
    }

    function unset($domain, $path, $var){
        unset($this->data[$domain][$path][$var]);
    }
    private function check(){
        foreach($this->data as $domain => $domainInfo){
            foreach($domainInfo as $path => $pathInfo){
                foreach ($pathInfo as $var => $varInfo){
                    if($varInfo['expires']<=time()){
//                        $this->unset($domain, $path, $var);
                    }
                }
            }
        }
    }
}

