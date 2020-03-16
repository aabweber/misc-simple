<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 06.08.14
 * Time: 15:27
 */

namespace misc;


class Timer {
	private static $events  = [];

	static function after($seconds, callable $callback){
		self::$events[] = ['time' => time()+$seconds, 'callback' => $callback];
	}

	static function each($seconds, callable $callback, $doNow = false){
		self::$events[] = ['time' => time()+$doNow?0:$seconds, 'callback' => $callback, 'period' => $seconds];
	}

	static function check(){
		$events = self::$events;
		$time = time();
        $action = false;
		foreach($events as $i => &$event){
            if($event['time']<=$time){
				$action |= $event['callback']();
				if($event['period']??false){
					self::$events[$i]['time'] = time() + $event['period'];
				}else{
					unset(self::$events[$i]);
				}
			}
		}
        return $action;
	}
} 