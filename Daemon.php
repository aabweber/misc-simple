<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13.01.2020
 * Time: 14:39
 */

namespace misc;

abstract class Daemon{

	/**
	 * @return bool | NULL (null|false - ничего не выполняли - спим)
	 */
	abstract protected function loop();

	protected static $sleepTime        = 100000;
	protected $exitFlag                = false;

	protected function stopDaemon(){
		$this->exitFlag = true;
	}

	function __construct(){
	    Timer::each(1, [$this, 'ping'], true);
    }

    function ping(){
	    global $argv;
        $scriptName = preg_match('/(.+)\.\w+$/si', $argv[0], $ms) ? $ms[1] : $argv[0];
//	    DB::get()->insert('scripts', ['name' => $scriptName, 'last_ping' => Utils::mt()], DB::INSERT_UPDATE);
    }

    /**
	 * Входная функция демона, вызывается внешним приложением после создания экземпляра класса наследника
	 */
	function run(){
        while(!$this->exitFlag){
            $r = $this->loop();
            $r |= Timer::check();
            if(!$r){
                usleep(static::$sleepTime);
            }
        }
	}


}
