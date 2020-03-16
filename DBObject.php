<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 14:02
 */

namespace misc;


class DBObject extends AObject{
    const EVT_GEN           = 'dbo_gen';
    const EVT_PRE_COMMIT    = 'dbo_pre_commit';
    const EVT_POST_COMMIT   = 'dbo_post_commit';

    static $TABLE           = null;

    /**
     * @param $data
     * @return static
     */
    static function generate($data){
        /** @var static $dbo */
        $dbo = parent::generate($data);
        $data['id'] = $data['id']??null;
        $dbo->emitEvent(self::EVT_GEN, $dbo->adata);
        return $dbo;
    }

    /**
     * @return int
     */
    function getId(){
        return $this->adata['id'];
    }

    function commit($onDuplicate = DB::INSERT_UPDATE){
        $touchedFields = $this->touchedFields;
//        print_r($touchedFields);
        parent::commit();
        $tmpData = array_merge($this->adata, []);
        $this->emitEvent(self::EVT_PRE_COMMIT, $tmpData);
        $updateData = [];//$tmpData;
//        print_r($touchedFields);
//        var_dump($tmpData);
        foreach($touchedFields as $var => $_){
//            echo "$var - ".isset($tmpData[$var])."<br>\n";
            if(array_key_exists($var, $tmpData)) {
                $updateData[$var] = $tmpData[$var];
            }
        }
//        print_r($updateData);
        $id = DB::get()->insert(static::$TABLE, $tmpData, $onDuplicate, $updateData);
        if($id) {
            $this->adata['id'] = $id;
        }
        $this->emitEvent(self::EVT_POST_COMMIT);
        return boolval($id);
    }

    function forceCommit($onDuplicate = DB::INSERT_UPDATE){
        $this->touchedFields = array_merge($this->adata, []);
        $this->commit($onDuplicate);
    }

    /**
     * @param int $id
     * @return null|static
     */
    static public function getById($id){
        $data = DB::get()->select(static::$TABLE, ['id' => $id], DB::SELECT_ROW);
        if(!$data) return null;
        return static::generate($data);
    }

    /**
     * @param array $conds
     * @param string|null $order
     * @return null|static
     */
    static public function getByConds($conds, $order=null){
        $opts = [];
        if($order){
            $opts[DB::OPTION_ORDER_BY] = $order;
        }
        $data = DB::get()->select(static::$TABLE, $conds, DB::SELECT_ROW, $opts);
        if(!$data) return null;
        return static::generate($data);
    }

    /**
     * Get list of objects by conditions
     * @param array $conds
     * @param $order
     * @param null $limit
     * @return static[]
     */
    static function getList(array $conds, $order = null, $limit = null){
        $accounts = [];
        $opts = [];
        if($order) $opts[DB::OPTION_ORDER_BY] = $order;
        if($limit) $opts[DB::OPTION_LIMIT] = $limit;
        $rows = DB::get()->select(static::$TABLE, $conds, DB::SELECT_ARR, $opts);
        foreach($rows as $row){
            $accounts[] = static::generate($row);
        }
        return $accounts;
    }



    /**
     * Get one row by conditions for processing the row and set newValues.
     * @param array $condition
     * @param array $newValues
     * @param array $options
     * @return static|null
     */
    static function getOneForProcessing($condition, $newValues, $options = []){
        $newValues['id'] = new DBFunction('LAST_INSERT_ID(id)');
        $options[DB::OPTION_LIMIT] = 1;
        DB::get()->update(static::$TABLE, $newValues, $condition, $options);
        $instance = static::getById(DB::get()->getLastInsertId());
        return $instance;
    }

    function delete(){
        DB::get()->delete(static::$TABLE, ['id' => $this->id]);
    }
}