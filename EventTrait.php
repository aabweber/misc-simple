<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 15:28
 */
namespace misc;

trait EventTrait{
    private $events;

    /**
     * Emit event on object
     * @param string $evtName
     */
    function emitEvent($evtName, &...$argList){
        $this->events[$evtName] = $this->events[$evtName]??[];
        foreach($this->events[$evtName] as $listener){
            call_user_func_array($listener, $argList);
        }
    }

    /**
     * Add event listener
     * @param string $evtName
     * @param callable $listener
     */
    function addListener($evtName, $listener){
        $this->events[$evtName][] = $listener;
    }

    /**
     * Delete event listener
     * @param string $evtName
     * @param callable $listener
     */
    function dropListener($evtName, $listener){
        $this->events[$evtName] = array_diff($this->events[$evtName], [$listener]);
    }

}