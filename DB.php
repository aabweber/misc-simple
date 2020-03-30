<?php

/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 14:12
 */
namespace misc;

use mysqli;

class DB{
    use Singleton;

    /**
     * Select styles
     */
    const SELECT_COL		= 1;
    const SELECT_ROW		= 2;
    const SELECT_ARR		= 3;
    const SELECT_ARR_COL	= 4;
    const SELECT_COUNT      = 5;

    /**
     * Reaction on duplicate insert
     */
    const INSERT_DEFAULT		= 1;
    const INSERT_IGNORE			= 2;
    const INSERT_UPDATE			= 3;

    /**
     * Select options
     */
    const OPTION_OFFSET 	= 'offset';
    const OPTION_LIMIT 		= 'limit';
    const OPTION_ORDER_BY	= 'order';

    /** @var mysqli $link */
    private $link = null;
    /** @var array */
    private $tableFields = [];


    function autoTest(){

        $t1 = 'atest1_'.microtime(true);
        $t2 = 'atest2_'.microtime(true);
        echo "Create tables\n";
        DB::get()->executeSQL('CREATE TABLE `'.$t1.'` (`id` int(11) NOT NULL, `id2` varchar(50) NOT NULL, `val` text NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        DB::get()->executeSQL('ALTER TABLE `'.$t1.'` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `un1` (`id2`)');
        DB::get()->executeSQL('ALTER TABLE `'.$t1.'` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');

        DB::get()->executeSQL('CREATE TABLE `'.$t2.'` (`id2` varchar(50) NOT NULL, `val2` text NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        DB::get()->executeSQL('ALTER TABLE `'.$t2.'` ADD UNIQUE KEY `un2` (`id2`)');
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();

        echo "INSERT rows\n";
        $d1 = [];
        for($i=0, $l=rand(10,20); $i<$l; $i++){
            $d1[] = ['id2' => rand(1000, 10000), 'val' => rand(10000, 20000)];
            $id = DB::get()->insert($t1, $d1[$i]);
            echo "ID:$id ";
        }
        $d2 = [];
        for($i=0, $l=rand(10,20); $i<$l; $i++){
            $d2[] = ['id2' => rand(1000, 10000), 'val2' => rand(10000, 20000)];
            DB::get()->insert($t2, $d2[$i]);
        }
        echo "\n";
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "INSERT IGNORE\n";
        for($i=0, $l=count($d1); $i<$l; $i++){
            $id = DB::get()->insert($t1, array_merge($d1[$i], ['val' => 'v_'.rand(1, 100)]), DB::INSERT_IGNORE);
            echo "ID:$id ";
        }
        echo "\n";
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "INSERT UPDATE\n";
        for($i=0, $l=count($d1); $i<$l; $i++){
            if(rand(0,1)) {
                $id = DB::get()->insert($t1, array_merge($d1[$i], ['val' => 'v_' . rand(1, 100)]), DB::INSERT_UPDATE);
                echo "ID:$id ";
            }
        }
        $id = DB::get()->insert($t1, ['id2' => 1, 'val' => 'val_1'], DB::INSERT_UPDATE);
        echo "ID:$id ";
        echo "\n";
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "INSERT duplicates\n";
        for($i=0, $l=count($d1); $i<$l; $i++){
            $id = DB::get()->insert($t1, array_merge($d1[$i], ['val' => 'vvv_' . rand(1, 100)]));
            echo "ID:".var_export($id, true)." ";
        }
        echo "\n";
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();

        echo "SELECT function\n";
        $rows = DB::get()->select($t1, ['id' => 1]);
        var_dump($rows);
        $row = DB::get()->select($t1, ['id' => 1], DB::SELECT_ROW);
        var_dump($row);
        $cnt = DB::get()->select($t1, [], DB::SELECT_COUNT);
        var_dump($cnt);
        $ids = DB::get()->select($t1, [], DB::SELECT_ARR_COL, [DB::OPTION_ORDER_BY => 'id DESC', DB::OPTION_LIMIT => 5, DB::OPTION_OFFSET => 3], 'id');
        var_dump($ids);
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "UPDATE\n";
        $rowsCnt = DB::get()->update($t1, ['val123' => '123'], [], [DB::OPTION_LIMIT => 5]);
        var_dump($rowsCnt);
        $rowsCnt = DB::get()->update($t1, ['val' => '123'], [], [DB::OPTION_LIMIT => 5]);
        var_dump($rowsCnt);
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "DELETE\n";
        DB::get()->delete($t1, ['id' => 1]);
        var_dump(DB::get()->selectBySQL('SELECT * FROM `'.$t1.'` WHERE id < 3'));
        echo "DONE!!! Press any key...\n";
        Utils::waitAnyKey();
        echo "Drop tables\n";
        DB::get()->drop($t1);
        DB::get()->drop($t2);
        echo "DONE!!!\n";
    }

    /**
     * Connect to MySQL host with login/password and use DB
     *
     * @param $host
     * @param $port
     * @param $user
     * @param $pass
     * @param $db
     * @param bool $debug
     * @return bool
     */
    function connect($host, $port, $user, $pass, $db){
        $this->link = @new mysqli($host, $user, $pass, $db, $port);
        if (mysqli_connect_errno()) {
            if(__DEBUG__) {
                echo "Host: " . $host . ", errorNo: " . mysqli_connect_errno() . "\nError:\n" . mysqli_connect_error() . "\n";
            }
            return false;
        }

        if(!$this->link->set_charset("utf8mb4")){
            if(__DEBUG__) {
                echo "Can not set utf8mb4 charset\n";
            }
            return false;
        }

        $this->tableFields = [];
        $rows = $this->selectBySQL('SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM `information_schema`.`COLUMNS` WHERE TABLE_SCHEMA="'.$db.'"');
        foreach($rows as $row){
            $this->tableFields[$row['TABLE_NAME']] = $this->tableFields[$row['TABLE_NAME']] ?? [];
            $this->tableFields[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row['DATA_TYPE'];
        }

        return true;
    }

    /**
     * Disconnect from MySQL
     */
    function disconnect(){
        $this->link->close();
        unset($this->link);
    }


    /**
     * Execute sql and return statement or null
     *
     * @param string $query
     * @param bool $closeStatement
     * @return \mysqli_stmt|bool
     */
    function executeSQL($query, $closeStatement = true){
        $stmt = @$this->link->prepare($query);
        if(!$stmt){
            if(__DEBUG__){
                echo "Can not prepare SQL, ErrorNo: ".$this->link->errno."\nError:\n".$this->link->error."\nSQL:\n".$query."\n";
            }
            return false;
        }
        $success = $stmt->execute();
        if(!$success){
            if(__DEBUG__) {
                echo "Can not execute SQL, ErrorNo: " . $this->link->errno . "\nError:\n" . $this->link->error."\nSQL:\n".$query."\n";
            }
            return false;
        }
        $ret = $stmt;
        if($closeStatement){
            $stmt->close();
            $ret = true;
        }
        return $ret;
    }

    /**
     * Select data by conditions
     *
     * @param string $tableName
     * @param array[string]scalar $conditions
     * @param int $fetchStyle
     * @param array $options
     * @param string $colName
     * @return array|mixed
     */
    function select($tableName, array $conditions, $fetchStyle = DB::SELECT_ARR, array $options = [], $colName = null){
        if($fetchStyle == DB::SELECT_COUNT){
            $select = 'COUNT(id) as cnt';
            $fetchStyle = DB::SELECT_COL;
            $colName = '`cnt`';
        }elseif($colName){
            $select = '`'.$colName.'`';
        }else{
            $select = '*';
        }
        $sql = 'SELECT '.$select.' FROM `'.$tableName.'` WHERE '.$this->genWhereClauseString($conditions).$this->genOptionsClause($options);
        return $this->selectBySQL($sql, $fetchStyle, trim($colName, '`'));
    }

    /**
     * Select by full SQL with given fetch style
     *
     * @param string $query
     * @param int $fetchStyle
     * @param null $colName
     * @return array|mixed
     */
    function selectBySQL($query, $fetchStyle = DB::SELECT_ARR, $colName = null){
        $stmt = $this->executeSql($query, false);
        if(!$stmt){
            if(__DEBUG__) {
                echo 'selectBySQL: Can not execute SQL: ' . $query;
            }
            return null;
        }
        $result = $stmt->get_result();
        $res = null;
        switch($fetchStyle){
            case DB::SELECT_ARR:
                $res = [];
                while ($row = $result->fetch_assoc()){
                    $res[] = $row;
                }
                break;
            case DB::SELECT_ARR_COL:
                $colName = $colName=='*' ? 0 : $colName;
                $res = [];
                while ($row = $result->fetch_array(MYSQLI_BOTH)){
                    $res[] = $row[$colName];
                }
                break;
            case DB::SELECT_ROW:
                $res = $row = $result->fetch_assoc();
                break;
            case DB::SELECT_COL:
                $colName = $colName=='*' ? 0 : $colName;
                $res = $result->fetch_array(MYSQLI_BOTH);
                if($res){
                    $res = $res[$colName];
                }
                break;
            default:
                error_log('DB: Unknown fetch style ('.$fetchStyle.')');
        }
        $result->close();
        $stmt->close();
        return $res;
    }

    /**
     * Insert row to table with action on duplicate
     * returns id of inserted(updated) row
     *
     * @param string $tableName
     * @param array[] scalar $data
     * @param int $onDuplicate
     * @return int
     */
    function insert($tableName, array $data, $onDuplicate = DB::INSERT_DEFAULT, $updateData = null){
        if($updateData===null){
            $updateData = $data;
        }
        if($onDuplicate==DB::INSERT_UPDATE && !$updateData) $onDuplicate = DB::INSERT_IGNORE;
        $fields = '';
        $rowId = null;
//        unset($data['id']);
        $tableFields = $this->tableFields[$tableName];

        foreach($data as $k => $v) {
            if (!isset($tableFields[$k])) {
                unset($data[$k]);
                unset($updateData[$k]);
            }
        }
        $keys = array_keys($data);
        foreach($keys as $f){
            $fields .= '`' . $f . '`,';
        }
        $fields = trim($fields, ',');
        $sql = 'INSERT '.($onDuplicate==DB::INSERT_IGNORE?'IGNORE ':'').'INTO `'.$tableName.'`('.$fields.') VALUES ('.$this->genInsertValuesString(array_values($data)).')';
        if($onDuplicate==DB::INSERT_UPDATE && $updateData){
            unset($data['id']);
            $sql .= ' ON DUPLICATE KEY UPDATE '.$this->genUpdateValuesString($updateData).(isset($tableFields['id'])?',id=LAST_INSERT_ID(id)':'');
        }
//        echo $sql;exit;
        if(!$this->executeSql($sql)) return null;
        if($onDuplicate == DB::INSERT_UPDATE){
            $rowId = isset($tableFields['id']) ? $this->select($tableName, $data, DB::SELECT_COL, [], 'id') : null;
        }
        return $rowId ? $rowId : $this->link->insert_id;
    }

    /**
     * Get native mysqli insert_id
     *
     * @return mixed
     */
    function getLastInsertId(){
        return $this->link->insert_id;
    }

    /**
     * Delete rows by conditions
     *
     * @param string $tableName
     * @param array $conditions
     */
    function delete($tableName, array $conditions){
        $sql = 'DELETE FROM `'.$tableName.'` WHERE '.$this->genWhereClauseString($conditions);
        $this->executeSql($sql);
    }

    /**
     * Update rows by conditions
     *
     * @param string $tableName
     * @param array $values
     * @param array $conditions
     * @param array $options
     * @return bool|int
     */
    function update($tableName, array $values, array $conditions, array $options=[]){
        $optionsString = '';
        if(isset($options[DB::OPTION_LIMIT])){
            $optionsString .= ' LIMIT '.(isset($options[DB::OPTION_OFFSET]) ? intval($options[DB::OPTION_OFFSET]).', ' : '').intval($options[DB::OPTION_LIMIT]);
        }

        $sql = 'UPDATE `'.$tableName.'` SET '.$this->genUpdateValuesString($values).' WHERE '.$this->genWhereClauseString($conditions).$optionsString;
        $stmt = $this->executeSql($sql, false);
        if(!$stmt) return $stmt;
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows;
    }

    /**
     * Truncate table
     *
     * @param string $tableName
     */
    function truncateTable($tableName){
        $this->executeSql('TRUNCATE TABLE `'.$tableName.'`');
    }

    /**
     * Drop table by name
     *
     * @param $tableName
     */
    function drop($tableName){
        $this->executeSQL('DROP TABLE `'.$tableName.'`');
    }

    /**
     * Get mysqli link
     *
     * @return mysqli
     */
    function getLink(){
        return $this->link;
    }










    /********************************************************
     *      PRIVATE SECTION
     ********************************************************/

    /**
     * Prepare value to use in mysql query
     *
     * @param Mixed $val
     * @return string
     */
    private function prepareValue($val){
        if( $val instanceof DBFunction ){
            $val = strval($val);
        }else {
            switch (gettype($val)) {
                case 'boolean':
                    $val = $val ? 'TRUE' : 'FALSE';
                    break;
                case 'integer':
                    $val = intval($val);
                    break;
                case 'double':
                    $val = '"' . $this->link->escape_string($val) . '"';
                    break;
                case 'string':
                    $val = '"' . $this->link->escape_string($val) . '"';
                    break;
                case 'NULL':
                    $val = 'NULL';
                    break;
                default:
                    $val = '';
            }
        }
        return $val;
    }

    /**
     * Generate WHERE CLAUSE for SELECT
     *
     * @param array $data
     * @return string
     */
    private function genWhereClauseString(array $data){
        $sql = '';
        $cnt = count($data);
        $i = 0;
        foreach($data as $var => $val){
            $operator = '=';

            if($val === NULL){
                $operator = 'IS';
            }
            if( $val instanceof DBFunction ){
                $operator = $val->getOperator();
            }
            $sql .= '`'.$var.'` '.$operator.' '.$this->prepareValue($val);

            if($i++ != $cnt-1) $sql .= ' AND ';
        }
        return $sql ? $sql : '1';
    }

    /**
     * Generate update value string
     *
     * @param array $data
     * @return string
     */
    private function genUpdateValuesString(array $data){
        $sql = '';
        foreach($data as $var => $val){
            $sql .= '`'.$var.'`='.$this->prepareValue($val).',';
        }
        return trim($sql, ', ');
    }

    /**
     * Generate string with values based on passed values array
     *
     * @param array $values
     * @return string
     */
    private function genInsertValuesString(array $values){
        $sql = '';
        foreach($values as $val){
            $sql .= $this->prepareValue($val).',';
        }
        return trim($sql, ', ');
    }


    /**
     * Generate options string for SELECT query
     *
     * @param mixed $options
     * @return string
     */
    private function genOptionsClause($options) {
        $optionsString = '';
        if(isset($options[DB::OPTION_ORDER_BY])){
            $optionsString .= ' ORDER BY '.$options[DB::OPTION_ORDER_BY];
        }
        if(isset($options[DB::OPTION_LIMIT])){
            $optionsString .= ' LIMIT '.(isset($options[DB::OPTION_OFFSET]) ? intval($options[DB::OPTION_OFFSET]).', ' : '').intval($options[DB::OPTION_LIMIT]);
        }
        return $optionsString;
    }

}













