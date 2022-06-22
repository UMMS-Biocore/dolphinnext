<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../ajax/dbfuncs.php");

class Run
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

    // use access&refresh tokens (which is delivered from SSO server) to get userINFO.
    // use userINFO to update user data in the database.
    // save tokens to database.
    // on success: return updated user Object.
    // on fail: return null
    function saveAccessRefreshToken($accessToken, $refreshToken, $expiresIn)
    {
        $expirationDate = !empty($expiresIn) ? date('Y-m-d H:i:s', strtotime("+$expiresIn seconds")) : null;
        $dbfuncs = new dbfuncs();
        $this->readINI();
        // GET USER INFO
        $url = "{$this->SSO_URL}/api/v1/users/info";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Bearer $accessToken"));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $body = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr = curl_errno($curl);
        curl_close($curl);
        if ($curlErr && $statusCode != 200) {
            return null;
        }
        $currentUser = json_decode($body, true);
        if (!empty($currentUser["_id"]) && !empty($currentUser["email"]) && !empty($currentUser["username"])) {
            $sso_user_id = $currentUser["_id"];
            $name = $currentUser["name"];
            $scope = $currentUser["scope"];
            $email = $currentUser["email"];
            $username = $currentUser["username"];

            $checkUserData = json_decode($dbfuncs->getUserByEmailorUsername($email));
            $db_id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            $db_role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";
            if (!empty($db_id)) {
                // User exist in table 
                // Update db with recent information
                $sql = "UPDATE users SET sso_id='$sso_user_id', name='$name', scope='$scope', username='$username',  date_modified= now(), last_modified_user ='$db_id'  WHERE id = '$db_id'";
                $this->runSQL($sql);
            } else if (!empty($email)) {
                // insert user
                // Update db with latest information
                $email_clean = str_replace("'", "''", $email);
                $role = "user";
                $active = 1;
                $sql = "INSERT INTO users(name, email, username, role, active, memberdate, date_created, date_modified, perms, sso_id, scope) VALUES ('$name', '$email_clean', '$username', '$role', $active, now(),now(), now(), '3', '$sso_user_id', '$scope')";
                $inUser = $dbfuncs->insTable($sql);
                $idArray = json_decode($inUser, true);
                $db_id = $idArray["id"];
                $dbfuncs->insertDefaultGroup($db_id);
                $dbfuncs->insertDefaultRunEnvironment($db_id);
            }
            if (!empty($db_id)) {
                $dbfuncs->insertAccessToken($accessToken, $expirationDate, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                if (!empty($refreshToken)) {
                    $dbfuncs->insertRefreshToken($refreshToken, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                }
                $currentUser["id"] = $db_id;
                $currentUser["role"] = $db_role;
                $currentUser["sso_id"] = $currentUser["_id"];
                $currentUser["accessToken"] = $accessToken;
                $currentUser["refreshToken"] = $refreshToken;
                return $currentUser;
            }
        }
        return null;
    }


    function verifyBearerToken($headers)
    {
        error_log("verifyBearerToken");
        $dbfuncs = new dbfuncs();
        $this->readINI();
        $token = null;
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $token = $matches[1];
            }
        }
        if (empty($token)) return null;
        $tokenInfo = json_decode($dbfuncs->getSSOAccessToken($token), true);
        if (!empty($tokenInfo[0]) &&  $tokenInfo[0]["expirationDate"] && date("Y-m-d H:i:s") > date($tokenInfo[0]["expirationDate"])) {
            $dbfuncs->removeSSOAccessToken($token);
            return null;
        }
        if (empty($tokenInfo[0])) {
            $tokeninfoURL = "{$this->SSO_URL}/api/v1/tokens/info?access_token={$token}";
            $curl = curl_init($tokeninfoURL);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            // secure it:
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $body = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlerr = curl_errno($curl);
            curl_close($curl);
            if ($curlerr && $statusCode != 200 && !empty($msg["user_id"])) {
                return null;
            }
            $msg = json_decode($body, true);
            $expiresIn = $msg["expires_in"];
            $user_id = $msg["user_id"];
            $currentUser = $this->saveAccessRefreshToken($token, null, $expiresIn);
        } else if (!empty($tokenInfo[0]["sso_user_id"])) {
            $currentUser = json_decode($dbfuncs->getUserBySSOId($tokenInfo[0]["sso_user_id"]), true);
            if (isset($currentUser[0])) $currentUser = $currentUser[0];
        }
        if (!empty($currentUser)) {
            error_log("BearerToken Verified");
            return $currentUser;
        }
        return null;
    }

    function existingRun($body, $params, $user)
    {
        $dbfuncs = new dbfuncs();
        $ret = array();
        $ownerID = $user["id"];
        $doc = $body["doc"];
        $info = $body["info"];
        $dmetaServer = $info["dmetaServer"];
        $run_url = !empty($doc["run_url"]) ? $doc["run_url"] : "";
        $dmeta = $this->getDmetaObj($doc, $dmetaServer, $info);
        if (!empty($doc["out"])) {
        }
        //http://localhost:8080/dolphinnext/index.php?np=3&id=586
        $proPipeId = substr($run_url, strpos($run_url, "id=") + 3);
        if (!empty($proPipeId)) {
            // if $doc["out"] empty there is no need to updateProjectPipelineDmeta 
            if (empty($doc["out"])) {
                $ret["status"] = "success";
                $ret["log"] = "Existing run found.";
                $ret["run_url"] = $run_url;
                return $ret;
            }
            //don't allow to update if user doesn't own the project_pipeline.
            $curr_ownerID = $dbfuncs->queryAVal("SELECT owner_id FROM $dbfuncs->db.project_pipeline WHERE id='$proPipeId'");
            $permCheck = $dbfuncs->checkUserOwnPerm($curr_ownerID, $ownerID);
            $userRole = $dbfuncs->getUserRoleVal($ownerID);
            if (!empty($permCheck) || $userRole == "admin") {
                $dbfuncs->updateProjectPipelineDmeta($proPipeId, $dmeta, $ownerID);
                $ret["status"] = "success";
                $ret["log"] = "Existing run found.";
                $ret["run_url"] = $run_url;
                return $ret;
            }
            $ret["status"] = "error";
            $ret["log"] = "This run doesn't belong to you. It is not allowed to enter as an existing run.";
            return $ret;
        }
        $ret["status"] = "error";
        $ret["log"] = "Run couldn't found.";
        return $ret;
    }

    function getDmetaObj($doc, $dmetaServer, $info)
    {
        $dmeta = array();
        $dmeta["dmeta_run_id"] = $doc["_id"];
        $dmeta["dmeta_server"] = $dmetaServer;
        $dmeta["dmeta_out"] = !empty($doc["out"]) ? $doc["out"] : "";
        $dmeta["dmeta_project"] = !empty($info["project"]) ? $info["project"] : "";
        return json_encode($dmeta);
    }

    function startRun($body, $params, $user)
    {
        $ret = array();
        $info = $body["info"];
        $doc = $body["doc"];
        $inputs = $doc["in"]; // run inputs e.g. $inputs["reads"]
        $proVarObj = array();
        foreach ($inputs as $module => $varObj) :
            if (is_array($varObj) && !isset($varObj[0])) {
                foreach ($varObj as $varName => $val) :
                    $val = json_encode($val, JSON_UNESCAPED_SLASHES);
                    $proVarObj[$module][$varName] = "params.$module.$varName = $val";
                endforeach;
            }
        endforeach;

        $proVarObj = json_encode($proVarObj);
        $dmetaServer = $info["dmetaServer"];
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $process_opt = isset($info["process_opt"]) ? addslashes(htmlspecialchars(urldecode($info["process_opt"]), ENT_QUOTES)) : null;
        $run_name = $doc["name"]; // test_run
        $tmplt_run_id = $doc["tmplt_id"]; //template run id e.g. 140
        $project_id = isset($doc["project_id"]) ? $doc["project_id"] : null;
        $description = isset($doc["description"]) ?  addslashes(htmlspecialchars(urldecode($doc["description"]), ENT_QUOTES)) : null;

        $run_env = !empty($doc["run_env"]) ? $doc["run_env"] : null; //run_env e.g. cluster-5
        $work_dir = !empty($doc["work_dir"]) ? $doc["work_dir"] : null;
        // if hostname/amazon/google is entered get profile id.
        if (!empty($run_env)) {
            if ($run_env == "amazon") {
                $profiles = $dbfuncs->getProfileAmazon($ownerID);
                $profiles = json_decode($profiles, true);
                if (!empty($profiles[0]["id"])) {
                    $run_env = "amazon-" . $profiles[0]["id"];
                }
            } else if ($run_env == "google") {
                $profiles = $dbfuncs->getProfileGoogle($ownerID);
                $profiles = json_decode($profiles, true);
                if (!empty($profiles[0]["id"])) {
                    $run_env = "google-" . $profiles[0]["id"];
                }
            } else {
                $profiles = $dbfuncs->getProfileCluster($ownerID);
                $profiles = json_decode($profiles, true);
                for ($i = 0; $i < count($profiles); $i++) {
                    if ($profiles[$i]["hostname"] == $run_env) {
                        $run_env = "cluster-" . $profiles[$i]["id"];
                        break;
                    }
                }
            }
        }
        $dmeta = $this->getDmetaObj($doc, $dmetaServer, $info);
        // update name and insert
        $type = "dmeta";
        $project_pipeline_id = $dbfuncs->duplicateProjectPipeline($tmplt_run_id, $ownerID, $inputs, $dmeta, $run_name, $run_env, $work_dir, $process_opt, $project_id, $description, $type);

        if (empty($project_pipeline_id)) {
            error_log("duplicateProjectPipeline failed.");
            return null;
        }

        $manualRun = "false";
        $runType = "newrun"; //"resumerun" or "newrun"
        $uuid = $dbfuncs->updateRunAttemptLog($manualRun, $project_pipeline_id, $ownerID);
        $nextText = $dbfuncs->getServerRunTemplateNFFile($tmplt_run_id, $uuid);
        if (empty($nextText)) {
            error_log("getServerRunTemplateNFFile failed.");
            return null;
        }
        if (empty($uuid)) {
            error_log("updateRunAttemptLog failed.");
            return null;
        }
        if (!empty($nextText) && !empty($uuid)) {
            $data = $dbfuncs->saveRun($project_pipeline_id, $nextText, $runType, $manualRun, $uuid, $proVarObj, $ownerID);
            if (!empty($data)) {
                $run_url = "{$this->BASE_PATH}/index.php?np=3&id=" . $project_pipeline_id;
                $ret["status"] = "initiated";
                $ret["log"] = "Run submitted.";
                $ret["run_url"] = $run_url;
                return $ret;
            }
        }
        return null;
    }
}
