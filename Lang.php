<?php
namespace misc {

    class Lang implements \core\LangConst
    {
        const RU = 'RU';
        const EN = 'EN';
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

        static function _l($cv, ...$args)
        {
            $lang = self::get();
            $str = $cv[$lang];
            $argsCount = count($args);
            for ($i = 0, $l = substr_count($str, '%s') - $argsCount; $i < $l; $i++) {
                $args[] = '';
            }
            array_unshift($args, $str);
            return call_user_func_array('sprintf', $args);
        }
    }
}

namespace {
    class L extends misc\Lang{
    }

    function _l($cv, ...$args)
    {
        array_unshift($args, $cv);
        return call_user_func_array('L::_l', $args);
    }
}