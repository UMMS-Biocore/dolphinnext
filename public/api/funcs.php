<?php
require_once(__DIR__."/../../config/config.php");
require_once(__DIR__."/../ajax/dbfuncs.php");
require_once("run.php");

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();


class funcs
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
        sleep(1);
        $this->readINI();
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: ' . mysqli_connect_error());
        }
        $i = 0;
        while ($i < 3) {
            $result = $link->query($sql);

            if ($result) {
                $link->close();
                return $result;
            }
            sleep(5 * ($i + 1));
            $i++;
        }
        $link->close();
        return $sql;
    }
    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            while (($row = $res->fetch_assoc())) {
                $data[] = $row;
            }
            $res->close();
        }
        return $data;
    }


    //http://localhost:8080/dolphinnext/api/service.php?func=getUUID&type=process
    function getUUID($params){
        $type = $params['type'];
        $result = [];
        if ($type == "process" || $type == "pipeline"){
            $uuid = $this->getKey($type);
            $rev_uuid = $this->getKey($type."_rev");
            $result['uuid'] = $uuid;
            $result['rev_uuid'] = $rev_uuid;
            $wkey = "'$uuid'".", "."'$rev_uuid'";
            $this->insertUUID($wkey, $type);
        } else if ($type == "pipeline_rev" || $type == "process_rev"){
            $result['rev_uuid'] = $this->getKey($type);
            $this->insertUUID($result['rev_uuid'], $type);
        } else if ($type == "run_log"){
            $result['rev_uuid'] = $this->getKey($type);
            $this->insertUUID($result['rev_uuid'], $type);
        }
        return $result;
    }
    function insertUUID($wkey,$type){
        if ($type == "process"){
            $sql = "INSERT INTO `uuid` ( `process_uuid`, `process_rev_uuid`, `type`, `date_created`) VALUES ($wkey, '$type', now())";
        } else if ($type == "pipeline"){
            $sql = "INSERT INTO `uuid` ( `pipeline_uuid`, `pipeline_rev_uuid`, `type`, `date_created`) VALUES ($wkey, '$type', now())";
        } else if ($type == "pipeline_rev"){
            $sql = "INSERT INTO `uuid` ( `pipeline_rev_uuid`, `type`, `date_created`) VALUES ('$wkey', '$type', now())";
        } else if ($type == "process_rev"){
            $sql = "INSERT INTO `uuid` ( `process_rev_uuid`, `type`, `date_created`) VALUES ('$wkey', '$type', now())";
        } else if ($type == "run_log"){
            $sql = "INSERT INTO `uuid` ( `run_log_uuid`, `type`, `date_created`) VALUES ('$wkey', '$type', now())";
        }
        if ($result = $this->runSQL($sql)) {
            $ret = $result;
        }
        return $ret;
    }

    function getKey($type)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $wkey       = "";
        $ret        = "";
        for ($i = 0; $i < 30; $i++) {
            $wkey .= $characters[rand(0, strlen($characters) - 1)];
        }
        # If this random key exist it randomize another key
        if ($this->checkUUID($wkey,$type))
            $ret = $this->getKey($type);
        else
            $ret = $wkey;
        return $ret;
    }
    function checkUUID($wkey,$type)
    {   
	$this->readINI();
        if ($type == "process"){
            $sql    = "SELECT id FROM $this->db.uuid WHERE process_uuid='$wkey'";
        } else if ($type == "pipeline"){
            $sql    = "SELECT id FROM $this->db.uuid WHERE pipeline_uuid='$wkey'";
        } else if ($type == "pipeline_rev"){
            $sql    = "SELECT id FROM $this->db.uuid WHERE pipeline_rev_uuid='$wkey'";
        } else if ($type == "process_rev"){
            $sql    = "SELECT id FROM $this->db.uuid WHERE process_rev_uuid='$wkey'";
        } else if ($type == "run_log"){
            $sql    = "SELECT id FROM $this->db.uuid WHERE run_log_uuid='$wkey'";
        }
        $result = $this->runSQL($sql);
        if (is_object($result) && $row = $result->fetch_row()) {
            $id = $row[0];
        } else {
            return 0;
        }
        return $id;
    }

}
?>
