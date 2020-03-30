<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 20.03.14
 * Time: 12:36
 */

namespace misc;


class DBFunction {
	private $sql = '';
	private $operator = '=';

	function __construct($sql, $operator='=') {
		$this->sql = $sql;
		$this->operator = $operator;
	}

	function __toString() {
		return strval($this->sql);
	}


	/**
	 * @param int|string $interval
	 * @param string $operation
	 * @param string $what
	 * @return string
	 */
	static function now($operation='+', $interval = 0, $what = 'SECOND'){
		$intervalString = '';
		if($interval){
			if(is_string($interval)){
				$intervalString .= $operation.'INTERVAL `'.$interval.'` '.$what;
			}elseif(is_numeric($interval)){
				$intervalString .= $operation.'INTERVAL '.$interval.' '.$what;
			}else{
				error_log('DBFunction: unknown interval type');
				exit;
			}
		}
		return new static('NOW()'.$intervalString);
	}

    /**
     * Like $str
     * @param $str
     * @return static
     */
	static function like($str){
	    return new static('"'.DB::get()->getLink()->escape_string($str).'"', 'LIKE');
    }

	/**
	 * @param string $password
	 * @return string
	 */
	static function password($password){
		return new static('PASSWORD("'.addslashes($password).'")');
	}

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }
} 