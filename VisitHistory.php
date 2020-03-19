<?php


namespace misc;


class VisitHistory{
    const MAX_DEPTH = 10;
    private static $history = [];

    static function init(){
        self::$history = $_SESSION['visit_history']??[];
        self::register();
    }
    static function go($left, $fragment = ''){
        header( 'Location: https://'.INFO['DOMAIN'].self::get($left).($fragment?'#'.$fragment:'') );
        exit;
    }
    private static function register(){
        $count = count(self::$history);
        if(!$count || $_SERVER['REQUEST_URI']!=self::$history[$count-1]) {
            self::$history[] = $_SERVER['REQUEST_URI'];
        }
        self::$history = array_slice(self::$history, -self::MAX_DEPTH);
        $_SESSION['visit_history'] = self::$history;
    }

    public static function get(int $left){
        return self::$history[count(self::$history)-1+$left]??null;
    }
}