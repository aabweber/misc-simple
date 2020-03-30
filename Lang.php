<?php
namespace misc {

    class Lang implements \core\LangConst
    {
        const RU = 'RU';
        const EN = 'EN';
        const _js       = '_js';

        private static $constants = [];

        static private function detect()
        {
            return self::RU;
        }

        private static function check($lang)
        {
            return $lang == self::RU || $lang == self::EN;
        }


        function __construct()
        {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            self::$constants = $constants;
            self::init();
        }

        static function getJSConsts(){
            $cs = [];
            foreach(self::$constants as $var => $val){
                $jsConstant = ($c = constant('self::'.$var)) && isset($c[Lang::_js]) && $c[Lang::_js];
                if(preg_match('#^main#si', $var, $ms) || $jsConstant) {
                    $cs[$var] = $val[self::get()];
                }
            }
            return $cs;
        }

        static function init()
        {
            if (!isset($_SESSION['lang']) || !self::check($_SESSION['lang'])) self::set(self::detect());
        }

        static function set($lang)
        {
            $lang = strtoupper($lang);
            if (self::check($lang)) $_SESSION['lang'] = $lang;
        }

        static function get($lower = false)
        {
            if (!isset($_SESSION['lang'])) self::init();
            return $lower ? strtolower($_SESSION['lang']) : $_SESSION['lang'];
        }

        static function sprintf($str, ...$args){
            $argsCount = count($args);
            for ($i = 0, $l = substr_count($str, '%s') - $argsCount; $i < $l; $i++) {
                $args[] = '';
            }
            array_unshift($args, $str);
            return call_user_func_array('sprintf', $args);
        }

        static function _l($cv, ...$args)
        {
            $lang = self::get();
            $str = $cv[$lang];
            array_unshift($args, $str);
            return call_user_func_array('self::sprintf', $args);
        }
    }
}

namespace {
    class L extends misc\Lang{
    }

    function _l($cv, ...$args){
        array_unshift($args, $cv);
        return call_user_func_array('L::_l', $args);
    }
    function _re($ruText, $enText=null, ...$args){
        $enText ??= $ruText;
        array_unshift($args, L::get()==L::RU ? $ruText : $enText);
        return call_user_func_array('L::sprintf', $args);
    }
}