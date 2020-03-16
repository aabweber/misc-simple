<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 16.05.14
 * Time: 14:02
 */

namespace misc;


class Template {

	/** @var string $directory */
	private static $directory;

	/**
	 * @param string $directory
	 */
	static function setDirectory($directory){
		self::$directory = $directory;
	}

    /**
     * @return string
     */
    static function getDirectory(){
        return self::$directory;
    }

	/**
	 * @param string $template
	 * @param bool $absolutePath
	 * @return string
	 */
	static function getFilename($template, $absolutePath = false){
        $path = rtrim($absolutePath ? $template : self::$directory, '/');
	    $name =  $path.'/'.$template;
	    if(is_file($fname = $name.'.'.Lang::get(true).'.php')) return $fname;
        if(is_file($fname = $name.'.php')) return $fname;
        return null;
	}

    /**
     * @param $template
     * @param bool $absolutePath
     * @return bool
     */
    static function has($template, $absolutePath = false){
        $filename = self::getFilename($template, $absolutePath);
        return boolval($filename);
    }

}
