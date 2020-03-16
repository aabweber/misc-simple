<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 28.03.14
 * Time: 13:49
 */

namespace misc\Network;


class Network {
	static $interfaces      = [];

	static function getInterface(){
        return $ip = gethostbyname(gethostname());
    }

    /**
	 * Get IP addresses of interfaces on this computer
	 * @param string $networkMask
	 * @return string[]
	 */
	static function getInterfaces($networkMask){
        if(self::$interfaces) return self::$interfaces;
		$c = `ip addr show | grep inet | grep eth`;
		$lines = explode("\n", trim($c));
		$interfaces = [];
		list($network, $mask) = explode('/', $networkMask);
		$network = ip2long($network);
		$_ = ($network >> (32-$mask)) << (32-$mask);
		foreach($lines as $line){
		    echo $line."\n";
			preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)/si', $line, $ms);
			$ip = ip2long($ms[1]);
			$maskedIP = ($ip >> (32-$mask)) << (32-$mask);
			if($maskedIP == $_){
				$interfaces[] = long2ip($ip);
			}
		}
        self::$interfaces = $interfaces;
		return $interfaces;
	}

	/**
	 * @param string $ip
	 * @param string $networkMask
	 * @return bool
	 */
	public static function IPInNetwork($ip, $networkMask) {
		list($network, $mask) = explode('/', $networkMask);
		$network = ip2long($network);
		$ip = ip2long($ip);
		return (($ip >> (32-$mask)) << (32-$mask)) == (($network >> (32-$mask)) << (32-$mask));
	}

	public static function getDomainLevel($domain){
		if(!preg_match_all('/(\.)/si', $domain, $ms)){
			return false;
		}
		return count($ms[1])+1;
	}

	public static function ping($host){
		$c = `ping -c 1 $host`;
		return strpos($c, '1 packets transmitted, 1 received')!==false;
	}

	/**
	 * @param int $port
	 * @return bool
	 */
	public static function isPortFree($port){
		$c = `netstat -taupen`;
		$lines = explode("\n", trim($c));
		$ports = [];
		foreach($lines as $line){
			if(preg_match('/tcp.+?\d+\.\d+\.\d+\.\d+\:(\d+)/si', $line, $ms)){
				$ports[] = $ms[1];
			}
		}
		return !in_array($port, $ports);
	}
}




