<?php

/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 14:02
 */
namespace misc;

use ArrayAccess;

class AObject implements \JsonSerializable, ArrayAccess{
    use EventTrait;

    const AE_GEN        = 'ae_gen';
    const AE_COMMIT     = 'ae_commit';

    protected $adata     = [];
    protected $touchedFields   = [];

    public function jsonSerialize(){
        $data = $this->adata;
        return $data;
    }

    function &__get($var){
        return $this->adata[$var];
    }

    function &getDataValue(...$args){
        $val =& $this->adata;
        $null = null;
        foreach($args as $varName){
            if(!isset($val[$varName])) return $null;
            $val =& $val[$varName];
        }
        return $val;
    }

    function __set($var, $val){
        $this->adata[$var] = $val;
        $this->touchedFields[$var] = true;
    }

    static function generate($data){
        $ao = new static();
        $ao->emitEvent(self::AE_GEN, $data);
        $ao->adata = $data;
        return $ao;
    }

    function commit(){
        $this->emitEvent(self::AE_COMMIT, $this->adata);
        $this->touchedFields = [];
    }

    /**
     * @return array
     */
    public function getAdata(): array{
        return $this->adata;
    }

    /**
     * Array Access implementation
     */


//    public function __construct() {
//    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->adata[] = $value;
        } else {
            $this->adata[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->adata[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->adata[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->adata[$offset]) ? $this->adata[$offset] : null;
    }

}