<?php
require_once("funcs.php");
require_once("update.php");
require_once("run.php");
require_once("data.php");

class Pipeline
{
    public $params = null;
    /**
     * Use the query (often the requested URL) to define some settings.
     */
    public function parse_params($params = null)
    {
        // If a query has been passed to the function, turn it into an array.
        if (is_string($params)) {
            $params = $this->parse_args($params);
        }

        // If a query has not been passed to this function, just use the array of variables that
        // were passed in the URL.
        if (is_null($params)) {
            if (($_POST)) {
                $this->params = $_POST;
            } else if (($_GET)) {
                $this->params = $_GET;
            }
        }
        return get_object_vars($this);
    }

    /** runFuncs
     *
     * @return string Response
     */
    public function runFuncs($params)
    {
        if (isset($params['func'])) {
            $func = $params['func'];
            $myClass = new funcs();
            if ($func) {
                $result = $myClass->$func($params);
                return json_encode($result);
            }
        }
        if (isset($params['upd'])) {
            $updClass = new updates();
            if (isset($params['token'])) {
                $token = $params['token'];
                $verify = $updClass->verifyToken($token);
                if ($verify == "true") {
                    $upd = $params['upd'];
                    if ($upd) {
                        $result = $updClass->$upd($params);
                        return json_encode($result);
                    }
                }
            }
        }
        if (isset($params['data'])) {
            $ret = array();
            $headers = apache_request_headers();
            $Run = new run();
            $Data = new data();
            $user = $Run->verifyBearerToken($headers);
            if (!empty($user)) {
                $body = json_decode(file_get_contents('php://input'), true);
                $data = $params['data'];
                error_log(print_r($body, TRUE));
                error_log(print_r($params, TRUE));
                $ret["status"] = "success";
                $result = $Data->$data($body, $params, $user);
                $newData = array();
                $newData["data"] = $result;
                $ret["data"] = $newData;
                return json_encode($ret);
            }
            http_response_code(401);
            $ret["status"] = "error";
            $ret["log"] = "Token not found.";
            return json_encode($ret);
        }
        if (isset($params['run'])) {
            $ret = array();
            $headers = apache_request_headers();
            $Run = new run();
            $user = $Run->verifyBearerToken($headers);
            if (!empty($user)) {
                $body = json_decode(file_get_contents('php://input'), true);
                $run = $params['run'];


                $result = $Run->$run($body, $params, $user);
                error_log(print_r($result, TRUE));

                if (empty($result)) {
                    http_response_code(401);
                    $ret["status"] = "error";
                    $ret["log"] = "Run could not be started.";
                } else if (!empty($result["status"])) {
                    $ret["run_url"] = !empty($result["run_url"]) ? $result["run_url"] : "";
                    $ret["status"] = $result["status"];
                    $ret["log"] = $result["log"];
                } else {
                    http_response_code(401);
                    $ret["status"] = "error";
                    $ret["log"] = $result;
                }
                error_log(print_r($ret, TRUE));
                return json_encode($ret);
            }
            http_response_code(401);
            $ret["status"] = "error";
            $ret["log"] = "Token not found.";
            return json_encode($ret);
        }
    }
}

error_reporting(E_ALL);
ini_set('report_errors', 'on');
$myClass = new Pipeline();
$result = $myClass->parse_params();
$data = $myClass->runFuncs($result['params']);

if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
}
echo $data;
exit;
