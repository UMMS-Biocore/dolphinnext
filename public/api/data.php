<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../ajax/dbfuncs.php");

class Data
{
    private $dbhost = "";
    private $db = "";
    private $dbuser = "";
    private $dbpass = "";
    private $BASE_PATH = "";
    private $SSO_URL = "";
    private $CLIENT_ID = "";
    private $CLIENT_SECRET = "";

    function readINI()
    {
        $this->dbhost     = DBHOST;
        $this->db         = DB;
        $this->dbpass     = DBPASS;
        $this->dbuser     = DBUSER;
        $this->BASE_PATH  = BASE_PATH;
        $this->SSO_URL    = SSO_URL;
        $this->CLIENT_ID  = CLIENT_ID;
        $this->CLIENT_SECRET  = CLIENT_SECRET;
    }

    function getINI()
    {
        $this->readINI();
        return $this;
    }

    function runSQL($sql)
    {
        $this->readINI();
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: ' . mysqli_connect_error());
        }
        $result = $link->query($sql);
        if ($result) {
            $link->close();
            return $result;
        }
        $link->close();
        return $sql;
    }


    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->close();
        }
        return $data;
    }

    function getRunEnv($body, $params, $user)
    {
        $ret = array();
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $profiles = $dbfuncs->getProfiles("run", $ownerID);
        $profiles = json_decode($profiles, true);
        for ($i = 0; $i < count($profiles); $i++) {
            $obj = array();
            $obj["name"] =  $profiles[$i]["name"];
            $id = $profiles[$i]["id"];
            if (isset($profiles[$i]["hostname"])) {
                $obj["_id"] = "cluster-$id";
            } else if (isset($profiles[$i]["amazon_cre_id"])) {
                $obj["_id"] = "amazon-$id";
            } else if (isset($profiles[$i]["google_cre_id"])) {
                $obj["_id"] = "google-$id";
            }
            $ret[] = $obj;
        }
        return $ret;
    }


    function getLsDir($body, $params, $user)
    {
        $ret = "";
        if (!empty($body['profileType']) && !empty($body['profileId']) && !empty($body['dir'])) {
            $ownerID = $user["id"];
            $project_pipeline_id = "";
            $dir = $body['dir'];
            $profileType = $body['profileType'];
            $profileId = $body['profileId'];
            $amazon_cre_id = $body['amazon_cre_id'];
            $google_cre_id = $body['google_cre_id'];
            $dbfuncs = new dbfuncs();
            $data = $dbfuncs->getLsDir($dir, $profileType, $profileId, $amazon_cre_id, $google_cre_id, $project_pipeline_id, $ownerID);
            $ret = json_decode($data);
        }
        return $ret;
    }

    function getCloudKeys($body, $params, $user)
    {
        $ret = array();
        $ret["amazon"] = array();
        $ret["google"] = array();
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $amzData = json_decode($dbfuncs->getAmz($ownerID), true);
        $googleData = json_decode($dbfuncs->getGoogle($ownerID), true);
        for ($i = 0; $i < count($amzData); $i++) {
            $obj = array();
            $obj["name"] =  $amzData[$i]["name"];
            $obj["_id"] = $amzData[$i]["id"];
            $ret["amazon"][] = $obj;
        }
        for ($i = 0; $i < count($googleData); $i++) {
            $obj = array();
            $obj["name"] =  $googleData[$i]["name"];
            $obj["_id"] = $googleData[$i]["id"];
            $ret["google"][] = $obj;
        }
        return $ret;
    }

    function getRuns($body, $params, $user)
    {
        $ret = array();
        $ownerID = $user["id"];
        $role = $user["role"];
        $dbfuncs = new dbfuncs();
        $proPipeAll = $dbfuncs->getProjectPipelines("", "", $ownerID, $role);
        $proPipeAll = json_decode($proPipeAll, true);
        for ($i = 0; $i < count($proPipeAll); $i++) {
            $obj = array();
            $obj["name"] =  htmlspecialchars_decode($proPipeAll[$i]["name"], ENT_QUOTES);
            $obj["_id"] = $proPipeAll[$i]["project_pipeline_id"];
            $ret[] = $obj;
        }
        return $ret;
    }

    function getProjects($body, $params, $user)
    {
        $ret = array();
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $projects = $dbfuncs->getProjects("", "", $ownerID);
        $projects = json_decode($projects, true);
        for ($i = 0; $i < count($projects); $i++) {
            $obj = array();
            $obj["name"] =  htmlspecialchars_decode($projects[$i]["name"], ENT_QUOTES);
            $obj["_id"] = $projects[$i]["id"];
            $ret[] = $obj;
        }
        return $ret;
    }

    function insertProject($body, $params, $user)
    {
        $ret = array();
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $name = $body["name"];

        $data = $dbfuncs->insertProject($name, "", $ownerID);
        $idArray = json_decode($data, true);
        $new_pro_id = $idArray["id"];
        error_log(print_r($data, TRUE));
        if (!empty($new_pro_id)) {
            $ret["id"] = $new_pro_id;
        }
        return $ret;
    }




    function getRun($body, $params, $user)
    {
        $ret = array();
        $id = !empty($params["id"]) ? $params["id"] : "";
        if (empty($id)) return $ret;
        $ownerID = $user["id"];
        $role = $user["role"];
        $dbfuncs = new dbfuncs();
        $proPipeAll = $dbfuncs->getProjectPipelines($id, "", $ownerID, $role);
        $proPipeAll = json_decode($proPipeAll, true);
        if (!empty($proPipeAll[0])) {
            $pipeline_id = $proPipeAll[0]["pipeline_id"];
            $ret["project_id"] = $proPipeAll[0]["project_id"];
            $project_pipeline_id = $proPipeAll[0]["id"];
            $processOptsRaw = $proPipeAll[0]["process_opt"];
            if (!empty($project_pipeline_id)) {
                $allinputs = json_decode($dbfuncs->getProjectPipelineInputs($project_pipeline_id, $ownerID));
                if (!empty($allinputs)) {
                    $inputs = array();
                    $inputsObj = array();
                    foreach ($allinputs as $inputitem) :
                        $collection_id = $inputitem->{'collection_id'};
                        $name = $inputitem->{'name'};
                        $given_name = $inputitem->{'given_name'};
                        $qualifier = $inputitem->{'qualifier'};
                        $inputsObj["name"] = $given_name;
                        if (!empty($collection_id)) {
                            $inputsObj["type"] = "collection";
                            $inputsObj["val"] = "";
                        } else {
                            $inputsObj["type"] = $qualifier;
                            $inputsObj["val"] = $name;
                        }
                        $inputs[] = $inputsObj;
                    endforeach;
                    $ret["inputs"] = $inputs;
                }
                $allprocessOpts = $dbfuncs->getProjectPipelineProcessOpts($project_pipeline_id, $ownerID);
                $ret["processOpts"] = $allprocessOpts;
                $ret["processOptsRaw"] = $processOptsRaw;
            }

            if (!empty($pipeline_id)) {
                $pipeData = $dbfuncs->loadPipeline($pipeline_id, $ownerID);
                $pipeData = json_decode($pipeData);
                $dMetaOutputs = $dbfuncs->getPubDmetaInfo($pipeData);
                $ret["dmetaOutput"] = $dMetaOutputs;
            }
        }
        return $ret;
    }
}
