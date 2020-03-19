<?php
namespace misc\Browser;

class Browser{
    static function runPage($url, $template = 'xhr.js'){
        $command = 'node ' . __DIR__ . '/' . $template . ' ' . $url;
        exec($command, $output);
//        print_r($output);
//        echo $command."\n";exit;
        return $output;
    }
}