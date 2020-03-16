<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 14:01
 */

//use misc\ReturnData;
use misc\ReturnData;

include BASE_DIR.'/misc/ReturnData.php';
include BASE_DIR.'/misc/Utils.php';
include 'Mail.php';

define('SESS_LIFETIME', 30*24*3600);
$BASE_DOMAIN = preg_match('/([\w\d-]+\.[\w\d-]+)$/si', $_SERVER['HTTP_HOST']??'', $ms)?$ms[1]:'';
session_name('s');
//session_set_cookie_params(SESS_LIFETIME, '/', '.'. $BASE_DOMAIN);
session_set_cookie_params(SESS_LIFETIME, '/');
session_start();

//ini_set("session.gc_maxlifetime", SESS_LIFETIME);
//ini_set("session.cookie_lifetime", SESS_LIFETIME);
//setcookie(session_name(), session_id(), time()+SESS_LIFETIME, '/', '.'.$BASE_DOMAIN);


//$some_name = session_name("some_name");
//session_set_cookie_params(0, '/', '.example.com');
//session_start();


spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', '/', $class_name);
    $fname = BASE_DIR.'/'.$class_name.'.php';
    if(!is_file($fname)){
//        print_r(debug_backtrace());
//	    echo "Can't include file $fname\n";
//        exit;
//	    return null;
    }else{
        include $fname;
    }
});

$INFO = $INFO??[];
$INFO['BASE_DOMAIN'] = $BASE_DOMAIN;
$INFO['DOMAIN'] = $_SERVER['HTTP_HOST']??'';
$INFO['PAGE']   = parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_PATH);
parse_str(urldecode(parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_QUERY)), $args);
$format = $args['format'] ?? ReturnData::RETURN_FORMAT_TEMPLATE;
unset($args['format']);
$INFO['FORMAT'] = $format;
$INFO['ARGS'] = $args;

