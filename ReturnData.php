<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 14/01/2020
 * Time: 14:37
 */

namespace misc {
    class ReturnData
    {
        const RD_OK = 'ok';
        const RD_ERR = 'error';

        const RETURN_FORMAT_TEMPLATE = 'template';
        const RETURN_FORMAT_JSON = 'json';

        private $status;
        private $info;
        /** @var ReturnData|null  */
        private $prevMessage;

        function __construct($status, $info = [])
        {
            if(is_string($info)){
                $info = ['message' => $info];
            }
            $this->status = $status;
            $this->info = $info;
            $this->prevMessage = $_SESSION['prev_message']??null;
            unset($_SESSION['prev_message']);
        }

        /**
         * @return array|object
         */
        public function getInfo()
        {
            return $this->info;
        }

        /**
         * @return string
         */
        public function getStatus()
        {
            return $this->status;
        }

        /**
         * @return ReturnData|null
         */
        public function getPrevMessage()
        {
            return $this->prevMessage;
        }

        /**
         * @param $prev
         */
        public static function setPrevMessage($prev){
            $_SESSION['prev_message'] = $prev;
        }
    }

}

namespace {

    function RetOk($info = []){
        return new \misc\ReturnData(\misc\ReturnData::RD_OK, $info);
    }

    function RetErr($info = []){
        return new \misc\ReturnData(\misc\ReturnData::RD_ERR, $info);
    }
}