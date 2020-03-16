<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 27.01.14
 * Time: 15:33
 */

namespace misc{

class Utils {

    static function waitAnyKey(){
        $handle = fopen ("php://stdin","r");
        $line = fgetc($handle);
        fclose($handle);
    }

    /**
     * Camel case of string
     * @param string $name
     * @return string
     */
    static function camelCase($name){
        $ccName = '';
        $words = explode('_', $name);
        foreach($words as $word){
            $ccName .= ucfirst(strtolower($word));
        }
        return $ccName;
    }

    /**
     * Check is the string seems to be email
     * @param string $email
     * @return bool
     */
	static function checkEmail($email){
		return boolval(preg_match('/^[\.\-_\w\d+]+?@[\-\w\d]+?\.[\w\d]{2,6}$/si', $email));
	}

    /**
     * Check is password strength
     * @param string $password
     * @return bool
     */
	public static function checkPassword($password) {
		return boolval(preg_match('/[\w\d!_#$=*+\/-]{7,}/si', $password));
	}

    /**
     * Send SIG_KILL to myself
     */
	static function hardExit(){
		posix_kill(getmypid(), SIGKILL);
		sleep(1);
		echo "i must don't be here ever\n";
		exit;
	}

    /**
     * Check if position in segment
     * @param int $position
     * @param [int, int] $segment
     * @param bool $left
     * @param bool $right
     * @return bool
     */
	static function isInSegment($position, $segment, $left = true, $right=true){
		return  ($left ? $position>=$segment[0] : $position>$segment[0]) &&
                ($right ? $position<=$segment[1] : $position<$segment[1]);
	}

    /**
     * Generet random string
     * @param int $length
     * @param bool $case
     * @param bool $onlyNumbers
     * @return string
     */
	static function genRandomString($length = 10, $case = true, $onlyNumbers = false) {
	    $symbols = range(0, 9);
	    if(!$onlyNumbers){
	        $symbols = array_merge($symbols, range('a', 'z'));
	        if($case) $symbols = array_merge($symbols, range('A', 'Z'));
        }
	    $symbols = implode('', $symbols);
        $symbolsCount = strlen($symbols);
        $randString = '';
        for($i=0; $i<$length; $i++){
            $randString .= $symbols[rand(0, $symbolsCount -1)];
        }
        return $randString;
	}

    /**
     * Encrypt or decrypt passed string with predefined secret key
     * @param $action
     * @param $string
     * @return bool|false|string
     */
    static function encrypt_decrypt($action, $string) {
        $output = false;

        $encrypt_method = 'AES-256-CBC';
        $secret_key = '87144c63-5046-4b99-a197-05d83088fb58';
        $secret_iv = 'd7d30f65-53e3-44fb-b1b6-bb31d1dd1da4';

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /**
     * Print array formatted as array variable in PHP file
     * @param $array
     * @param bool $onScreen
     * @param int $intend
     * @return string
     */
    static function printPHPArray($array, $onScreen = true, $intend = 0){
        $text  = '';
        foreach ($array as $var => $val) {
            switch (gettype($val)){
                case 'boolean':
                    $val = $val?'true':'false';
                    break;
                case 'integer':
                case 'double':
                    break;
                case 'string':
                    $val = '\''.addslashes($val).'\'';
                    break;
                case 'array':
                    $val = "[\n".self::printPHPArray($val, false, $intend+1).str_repeat("\t", $intend)."]";
                    break;
                case 'NULL':
                    $val = 'null';
                    break;
                default:
                    $val = '\'unknown type\'';
            }
            $text .= str_repeat("\t", $intend)."'$var'\t\t=> ".$val.",\n";
        }
        if($onScreen){
            echo $text;
        }else{
            return $text;
        }
    }

    static function SHcolor($text, $color){
        return "\e[38;5;{$color}m$text\e[0m";
    }

    /**
     * Convert named array to usual PHP-array, ex.:
     * [ ['name'=>'n1', 'value'=>'v1'], ['name'=>'n2', 'value'=>'v2'], ['name'=>'n3', 'value'=>'v3'], ] => ['n1'=>'v1', 'n2'=>'v2', 'n3'=>'v3', ]
     * @param $array
     * @param string $var
     * @param string $val
     * @return array
     */
    public static function namedArrayToPHPArray($array, $var = 'name', $val = 'value'){
        $arr = [];
        foreach($array as $item){
            if($arr[$item[$var]]??null){
                if( is_array($arr[$item[$var]]) ){
                    $arr[$item[$var]][] = $item[$val];
                }else{
                    $arr[$item[$var]] = [$arr[$item[$var]], $item[$val]];
                }
            }else {
                $arr[$item[$var]] = $item[$val];
            }
        }
        return $arr;
    }

    static function genUUID($upper = false){
        $randomString = openssl_random_pseudo_bytes(16);
        $time_low = bin2hex(substr($randomString, 0, 4));
        $time_mid = bin2hex(substr($randomString, 4, 2));
        $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
        $node = bin2hex(substr($randomString, 10, 6));

        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;

        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

        $hex = sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
        return $upper ? strtoupper($hex) : $hex;
    }

    static function transliterate($string){
        return transliterator_transliterate('Any-Latin; Latin-ASCII;', $string);
    }

    static function mb_count_chars($string){
        $unique = array();
        for($i=0, $l=mb_strlen($string, 'UTF-8'); $i<$l; $i++) {
            $char = mb_substr($string, $i, 1, 'UTF-8');
            $unique[$char] = isset($unique[$char]) ? $unique[$char]+1 : 1;
        }
        return $unique;
    }

    static function genKeywords($string, $cut = ' ()-'){
        $keywords = explode(' ', preg_replace('/\s+/si', ' ', preg_replace('/['.quotemeta($cut).']/si',' ', $string)));
        return $keywords;
    }

    static function W3CNow($time = null){
        $date = date('Y-m-d H:i:s', isset($time)?$time:time());
        return $date;
    }

    static function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        $r = '';
        $d = $dtF->diff($dtT)->format('%a');
        $h = $dtF->diff($dtT)->format('%h');
        $m = $dtF->diff($dtT)->format('%i');
        $s = $dtF->diff($dtT)->format('%s');
        if($d) $r .= $d.' д, ';
        if($r || $h) $r .= $h.' ч, ';
        if($r || $m) $r .= $m.' м, ';
        $r .= $s.' с';
        return trim($r, ', ');
    }

    /**
     * @return float|string
     */
    static function mt($time = null){
        return $time??microtime(true);
    }


    static function ri($n, $p, $float = false){
        $r = (rand(0, PHP_INT_MAX)/PHP_INT_MAX-0.5)*2;// -1..1
        $f = $n + $n * $p / 100 * $r;
        return $float?$f:round($f);
    }

    static function crawlDir($dir, callable $cb, $depth = 0){
        $dir = rtrim($dir, '/');
        if(!($dh = opendir($dir))){
            echo "Can not open dir: $dir\n";
            return;
        }

        while (($file = readdir($dh)) !== false){
            $filename = $dir . '/' . $file;
            $type = filetype($filename);
            $cb($filename, $type);
            if($type=='dir' && $file[0]!='.') self::crawlDir($filename, $cb, $depth+1);
        }
        closedir($dh);
    }

    static function exec($cmd, $outputfile, $pidfile){
        exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
    }

}
}

namespace {
	class Utils extends \misc\Utils{
    }
}