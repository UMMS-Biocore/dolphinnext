<?php
require_once("funcs.php");
require_once("update.php");

class Pipeline{
    public $params = null;
    /**
    * Use the query (often the requested URL) to define some settings.
    */
    public function parse_params( $params = null ) {
        // If a query has been passed to the function, turn it into an array.
        if ( is_string( $params) ) {
            $params = $this->parse_args( $params );
        }

        // If a query has not been passed to this function, just use the array of variables that
        // were passed in the URL.
        if (is_null($params)) {
            if(($_POST)){
                $this->params = $_POST;
            } else if(($_GET)){
                $this->params = $_GET;
            }
        }
        return get_object_vars( $this );
    }

    /** runFuncs
    *
    * @return string Response
    */
    public function runFuncs($params){
        if (isset($params['func'])){
            $func=$params['func'];
            $myClass = new funcs();
            if ($func){
                $result=$myClass->$func($params);
                return json_encode($result);
            }
        }
        if (isset($params['upd'])){
            $updClass = new updates();
            if (isset($params['token'])){
                $token=$params['token'];
                $verify=$updClass->verifyToken($token);
                if ($verify == "true"){
                    $upd=$params['upd'];
                    if ($upd){
                        $result=$updClass->$upd($params);
                        return json_encode($result);
                    }
                }

            }
        }
    }
}

error_reporting(E_ALL);
ini_set('report_errors','on');
$myClass = new Pipeline();
$result=$myClass->parse_params();
$data=$myClass->runFuncs($result['params']);

if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
}
echo $data;
exit;
