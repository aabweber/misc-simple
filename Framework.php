<?php
/**
 * Created by PhpStorm.
 * User: aabweber
 * Date: 13/01/2020
 * Time: 15:26
 */

namespace misc;
include_once 'ReturnData.php';

abstract class Framework extends AObject{
    const EVT_RULE_FOUND    = 'rule_found';
    use Singleton;

    static $URL_RULES = [];

    private $ruleInfo       = null;
    protected $args         = null;

    /** @var ReturnData|null  */
    private $result             = null;
    protected $returnFormat     = ReturnData::RETURN_FORMAT_TEMPLATE;

    private $hTitle         = '';
    private $hKeywords      = '';
    private $hDescription   = '';

    /** @var bool */
    private $is404  = false;

    /**
     * Init site rule
     * @return bool
     */
    function init(){
        $this->hTitle = INFO['DEFAULT_TITLE'];
        $this->hKeywords = INFO['DEFAULT_KEYWORDS'];
        $this->hDescription = INFO['DEFAULT_DESCRIPTION'];
        $this->returnFormat = INFO['FORMAT'];
        $this->args = $_REQUEST;//INFO['ARGS'];
        foreach(static::$URL_RULES as $rule => $info){
//            echo INFO['PAGE']." - ".$rule."<br>";
            if(preg_match('#^'.rtrim($rule, '/').'$#si', rtrim(INFO['PAGE'], '/'), $ms)){
                foreach($info['params'] as &$param){
                    if(preg_match('/\$(\d+)/', $param, $paramIndMs)){
                        $paramInd = $paramIndMs[1];
                        $param = str_replace('$'. $paramInd, $ms[$paramInd], $param);
                    }elseif(preg_match('/:([\w\d_-]+)/si', $param, $ms)){
                        $param = $this->getArgs()[$ms[1]]??null;
//                        var_dump($param);
                    }
                }
                $this->ruleInfo = $info;
                $this->emitEvent(self::EVT_RULE_FOUND, $info);
//                var_dump($this->returnFormat);exit;
                if($this->returnFormat==ReturnData::RETURN_FORMAT_TEMPLATE) {
                    VisitHistory::init();
                }
                return true;
            }
        }
        if(__DEBUG__){
            echo "Can not find rule in Framewok for URL: '".INFO['PAGE']."'\n";
        }
        return false;
    }

    /**
     * Run site logic
     * @return bool|null
     */
    function run(){
        if(!$this->ruleInfo){
            return null;
        }
        if(!method_exists($this, $this->ruleInfo['dispatcher'])){
            if(__DEBUG__){
                echo "Method '".$this->ruleInfo['dispatcher']."' does not exists in Framework!\n";
            }
            return null;
        }

        $this->result = $res = call_user_func_array([$this, $this->ruleInfo['dispatcher']], $this->ruleInfo['params']);
        return $res;
    }

    /**
     * @param string $template
     * @param Mixed[string] $args
     * @param bool $absolutePath
     */
    private function applyTemplate($template, $args=[], $absolutePath = false){
        $file = Template::getFilename($template, $absolutePath);
        extract((array)$args);
        if($this->result) {
            extract((array)$this->result->getInfo());
        }
        ob_start();
        include $file;
        return ob_get_clean();
    }


    function render(){
        echo $this->applyTemplate('index');
    }


    /**
     * Get static page
     * @param $page
     * @return bool|ReturnData
     */
    function getStatic($page){
        $tpl = 'static/' . $page;
        if(!Template::has($tpl)) return false;
        return RetOk();
    }

    /**
     * Process 404 error
     */
    function process404(){
        header("HTTP/1.0 404 Not Found");
        if(!Template::has('404')) exit;
        $this->is404 = true;
    }

    /**
     * @return bool
     */
    function is404(): bool{
        return $this->is404;
    }

    function getObject(){
        return $this->ruleInfo['object']??null;
    }

    function getAction(){
        return $this->ruleInfo['action']??null;
    }

    function getParams(){
        return $this->ruleInfo['params']??null;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->hTitle;
    }

    /**
     * @param mixed $hTitle
     */
    public function setTitle($hTitle): void
    {
        $this->hTitle = $hTitle;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->hKeywords;
    }

    /**
     * @param mixed $hKeywords
     */
    public function setKeywords($hKeywords): void
    {
        $this->hKeywords = $hKeywords;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->hDescription;
    }

    /**
     * @param mixed $hDescription
     */
    public function setDescription($hDescription): void
    {
        $this->hDescription = $hDescription;
    }

    /**
     * @return string
     */
    public function getReturnFormat(): string
    {
        return $this->returnFormat;
    }

    /**
     * @return null|ReturnData
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return null|Mixed
     */
    public function getArgs(){
        return $this->args;
    }

    /**
     * @param string name
     * @return null|Mixed
     */
    public function getArg($name){
        return $this->args[$name]??null;
    }

}