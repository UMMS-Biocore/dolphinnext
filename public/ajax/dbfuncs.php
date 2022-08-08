<?php

use Gettext\Extractors\Po;

require_once(__DIR__ . "/../api/funcs.php");
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../php/jwt.php");
require_once(__DIR__ . "/../php/email.php");


class dbfuncs
{
    private $nf_path = __DIR__ . "/../../nf";
    private $dbhost = DBHOST;
    public $db = DB;
    private $dbuser = DBUSER;
    private $dbpass = DBPASS;
    private $dbport = DBPORT;
    private $run_path = RUNPATH;
    private $tmp_path = TEMPPATH;
    private $ssh_path = SSHPATH;
    private $base_path = BASE_PATH;
    private $pubweb_url = PUBWEB_URL;
    private $ssh_settings = "-oStrictHostKeyChecking=no -q -oChallengeResponseAuthentication=no -oBatchMode=yes -oPasswordAuthentication=no -oConnectTimeout=3";
    private $amz_path = AMZPATH;
    private $goog_path = GOOGPATH;
    private $amazon = AMAZON;
    private $next_ver = NEXTFLOW_VERSION;
    private $test_profile_group_id = TEST_PROFILE_GROUP_ID;
    private static $link;
    private $JWT_SECRET = JWT_SECRET;
    private $JWT_COOKIE_EXPIRES_IN = JWT_COOKIE_EXPIRES_IN;
    private $DEFAULT_GROUP_ID = DEFAULT_GROUP_ID;
    private $DEFAULT_RUN_ENVIRONMENT = DEFAULT_RUN_ENVIRONMENT;
    private $INITIAL_RUN_DOCKER = INITIAL_RUN_DOCKER;
    private $INITIAL_RUN_SINGULARITY = INITIAL_RUN_SINGULARITY;
    private $MOUNTED_VOLUME = MOUNTED_VOLUME;
    private $EMAIL_TYPE = EMAIL_TYPE;
    private $EMAIL_URL = EMAIL_URL;
    private $EMAIL_HEADER_KEY = EMAIL_HEADER_KEY;
    private $EMAIL_HEADER_VALUE = EMAIL_HEADER_VALUE;
    private $AWS_CONFIG_PATH = "/data/.aws/config";
    private $AWS_CREDENTIALS_PATH = "/data/.aws/credentials";

    function __construct()
    {
        if (!isset(self::$link)) {
            self::$link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db, $this->dbport);
            // check connection
            if (mysqli_connect_errno()) {
                exit('Connect failed: ' . mysqli_connect_error());
            }
        }
    }

    // __destruct removed for unit testing
    //    function __destruct() {
    //        if (isset(self::$link)) {
    //            self::$link->close();
    //        }
    //    }
    function runSQL($sql)
    {
        ini_set('max_execution_time', '300');
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: ' . mysqli_connect_error());
        }
        $result = self::$link->query($sql);
        $link->close();

        if (!$result) {
            error_log($sql);
            trigger_error('Database Error: ' . self::$link->error);
            return json_encode("");
        }
        if ($result && $result != "1") {
            return $result;
        }
        return json_encode(json_decode("{}"));
    }
    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            while (($row = $res->fetch_assoc())) {
                if (isset($row['sname'])) {
                    $row['sname'] = htmlspecialchars_decode($row['sname'], ENT_QUOTES);
                }
                $data[] = $row;
            }

            $res->close();
        }
        return json_encode($data);
    }

    function queryAVal($sql)
    {
        $res = $this->runSQL($sql);
        if (is_object($res)) {
            $num_rows = $res->num_rows;
            if (is_object($res) && $num_rows > 0) {
                $row = $res->fetch_array();
                return $row[0];
            }
        }
        return "0";
    }

    function insTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            $insertID = self::$link->insert_id;
            $data = array('id' => $insertID);
        }
        return json_encode($data);
    }

    function writeLog($uuid, $text, $mode, $filename)
    {
        $file = fopen("{$this->run_path}/$uuid/run/$filename", $mode);
        fwrite($file, $text . "\n");
        fclose($file);
    }
    function writeFile($filepath, $text, $mode)
    {
        $file = fopen($filepath, $mode);
        fwrite($file, $text . "\n");
        fclose($file);
    }
    //$img: path of image
    //$singu_save=true to overwrite on image
    function imageCmd($singu_cache, $img, $singu_save, $type, $profileType, $profileId, $runType, $dolphin_publish_real, $ownerID)
    {
        $cmd = "";
        $imgPath = $img;
        $downPath = '$NXF_SINGULARITY_CACHEDIR';
        //full path
        if (substr($img, 0, 1) == "/") {
            $imgPath = $img;
        } else if (preg_match("/http:/i", $img) || preg_match("/https:/i", $img) || preg_match("/ftp:/i", $img)) {
            if ($profileType == "amazon") {
                $amzData = $this->getProfileCloudbyID($profileId, $profileType, $ownerID);
                $amzDataArr = json_decode($amzData, true);
                $downPath = $amzDataArr[0]["shared_storage_mnt"] . "/.dolphinnext/singularity"; // /mnt/efs
            }
            if (!empty($singu_cache)) {
                $downPath = $singu_cache;
            }
            $imageNameAr = explode('/', $img);
            $imageName = $imageNameAr[count($imageNameAr) - 1];
            $imgPath = "$downPath/$imageName";
            $wgetCmd = "if [ ! -f $downPath/$imageName ]; then wget --secure-protocol=TLSv1 --no-check-certificate $img; fi";
            if ($singu_save == "true") {
                $cmd = "mkdir -p $downPath && cd $downPath && rm -f " . $imageName . " && $wgetCmd";
            } else {
                $cmd = "mkdir -p $downPath && cd $downPath && $wgetCmd";
            }
        } else if ($type == 'singularity') {
            $prefix = "";
            preg_match("/(shub|docker):\/\/(.*)/", $img, $matches);
            //docker or singularity image
            if (!empty($matches[2])) {
                $prefix = str_replace("/", "-", $matches[2]);
                //docker image if doesn't start with shub|docker
            } else if (substr($img, 0, 1) != "/") {
                $prefix = str_replace("/", "-", $img);
                $prefix = str_replace(":", "-", $prefix);
                $img = "docker://$img";
            }
            if (!empty($prefix)) {
                if ($profileType == "amazon") {
                    $amzData = $this->getProfileCloudbyID($profileId, $profileType, $ownerID);
                    $amzDataArr = json_decode($amzData, true);
                    $downPath = $amzDataArr[0]["shared_storage_mnt"] . "/.dolphinnext/singularity"; // /mnt/efs
                }
                if (!empty($singu_cache)) {
                    $downPath = $singu_cache;
                }

                $imageName = "$prefix.simg";
                $imgPath = "$downPath/$imageName";
                $shubCmd = "if [ ! -f $downPath/{$imageName} ]; then singularity pull --name {$imageName} $img; fi";
                if ($singu_save == "true") {
                    $cmd = "mkdir -p $downPath && cd $downPath && rm -f {$imageName} && $shubCmd";
                } else {
                    $cmd = "mkdir -p $downPath && cd $downPath && $shubCmd";
                }
            }
        }

        if (!empty($cmd) && $runType == "initial") {
            $cmd .= " && find $downPath -type f -regex '.*UMMS-Biocore-initialrun.*img' -not -name '{$imageName}' -delete";
        }
        // nextflow google cloud doesn't support gs://singularity.images. error: Unsupported transport type: gs
        //        if (!empty($cmd) && $profileType == 'google'){
        //            $cmd.= " && gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -o GSUtil:parallel_composite_upload_threshold=150M cp -n $imageName $dolphin_publish_real/$imageName";
        //            $imgPath ="$dolphin_publish_real/$imageName";
        //        }
        return array($cmd, $imgPath);
    }

    //type:w creates new file
    function createDirFile($pathDir, $fileName, $type, $text)
    {
        if ($pathDir != "") {
            if (!file_exists($pathDir)) {
                mkdir($pathDir, 0755, true);
            }
        }
        if ($fileName != "") {
            $file = fopen("$pathDir/$fileName", $type);
            fwrite($file, $text);
            fclose($file);
            chmod("$pathDir/$fileName", 0755);
        }
    }

    //if logArray not exist than send empty ""
    function runCommand($cmd, $logName, $logArray)
    {
        $pid_command = popen($cmd, 'r');
        $pid = fread($pid_command, 2096);
        pclose($pid_command);
        if (empty($logArray)) {
            $log_array = array($logName => $pid);
        } else {
            $log_array[$logName] = $pid;
        }
        return $log_array;
    }

    //full path for file
    function readFile($path)
    {
        $content = "";
        if (file_exists($path)) {
            $handle = fopen($path, 'r');
            if (filesize($path) > 0) {
                $content = fread($handle, filesize($path));
            }
            fclose($handle);
            return $content;
        } else {
            return null;
        }
    }

    function getDirectorySize($f)
    {
        $size = 0;
        if (file_exists($f)) {
            $io = popen('/usr/bin/du -sk ' . $f, 'r');
            $size = fgets($io, 4096);
            $size = substr($size, 0, strpos($size, "\t"));
            pclose($io);
        }
        return $size;
    }


    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function getCloudConfig($project_pipeline_id, $attempt, $ownerID)
    {
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $configFileDir = "";
        $amazon_cre_id_Ar = array();
        $google_cre_id_Ar = array();
        foreach ($allinputs as $inputitem) :
            $collection_id = $inputitem->{'collection_id'};
            if (!empty($collection_id)) {
                $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                foreach ($allfiles as $fileData) :
                    $file_dir = $fileData->{'file_dir'};
                    $s3_archive_dir = $fileData->{'s3_archive_dir'};
                    $gs_archive_dir = $fileData->{'gs_archive_dir'};
                    if (preg_match("/s3:/i", $file_dir) || preg_match("/gs:/i", $file_dir)) {
                        $strData = explode("\t", $file_dir);
                        $cre_id = trim($strData[1]);
                        if (preg_match("/s3:/i", $file_dir)) {
                            if (!in_array($cre_id, $amazon_cre_id_Ar)) {
                                $amazon_cre_id_Ar[] = $cre_id;
                            }
                        } else if (preg_match("/gs:/i", $file_dir)) {
                            if (!in_array($cre_id, $google_cre_id_Ar)) {
                                $google_cre_id_Ar[] = $cre_id;
                            }
                        }
                    }
                    if (preg_match("/s3:/i", $s3_archive_dir)) {
                        $strData = explode("\t", $s3_archive_dir);
                        $cre_id = trim($strData[1]);
                        if (!in_array($cre_id, $amazon_cre_id_Ar)) {
                            $amazon_cre_id_Ar[] = $cre_id;
                        }
                    }
                    if (preg_match("/gs:/i", $gs_archive_dir)) {
                        $strData = explode("\t", $gs_archive_dir);
                        $cre_id = trim($strData[1]);
                        if (!in_array($cre_id, $google_cre_id_Ar)) {
                            $google_cre_id_Ar[] = $cre_id;
                        }
                    }
                endforeach;
            }
        endforeach;

        foreach ($amazon_cre_id_Ar as $amazon_cre_id) :
            if (!empty($amazon_cre_id)) {
                $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
                if (!empty($amz_data[0])) {
                    foreach ($amz_data as $d) {
                        $access = $d->amz_acc_key;
                        $d->amz_acc_key = trim($this->amazonDecode($access));
                        $secret = $d->amz_suc_key;
                        $d->amz_suc_key = trim($this->amazonDecode($secret));
                    }
                    $access_key = $amz_data[0]->{'amz_acc_key'};
                    $secret_key = $amz_data[0]->{'amz_suc_key'};
                    $confText = "access_key=$access_key\nsecret_key=$secret_key\n";
                    $s3configDir = "{$this->amz_path}/config/run{$project_pipeline_id}/initialrun";
                    $configFileDir = $s3configDir;
                    $s3tmpFile = "$s3configDir/.conf.$amazon_cre_id";
                    if (!file_exists($s3configDir)) {
                        mkdir($s3configDir, 0700, true);
                    }
                    $file = fopen($s3tmpFile, 'w'); //creates new file
                    fwrite($file, $confText);
                    fclose($file);
                    chmod($s3tmpFile, 0700);
                }
            }
        endforeach;

        //        foreach ($google_cre_id_Ar as $google_cre_id):
        //        if (!empty($google_cre_id)){
        //            $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
        //            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
        //            $confText = $this->readFile($credFile);
        //            $confDir = "{$this->goog_path}/config/run{$project_pipeline_id}/initialrun";
        //            $configFileDir = $confDir;
        //            $tmpFile = "$confDir/.confGoog.$google_cre_id";
        //            if (!file_exists($confDir)) {
        //                mkdir($confDir, 0700, true);
        //            }
        //            $file = fopen($tmpFile, 'w');//creates new file
        //            fwrite($file, $confText);
        //            fclose($file);
        //            chmod($tmpFile, 0700);
        //        }
        //        endforeach;

        return $configFileDir;
    }


    //default singularity
    function getInitialRunImg($docker_check, $singu_check)
    {
        $initialrun_img = "";
        if ($docker_check == "true") {
            $initialrun_img = $this->INITIAL_RUN_DOCKER;
        } else if ($singu_check == "true") {
            $initialrun_img = $this->INITIAL_RUN_SINGULARITY;
        }
        return $initialrun_img;
    }


    function getConfigHostnameVariable($profileId, $profileType, $ownerID)
    {
        $hostname = "";
        $variable = "";
        if ($profileType == 'cluster') {
            $cluData = $this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            $hostname = $cluDataArr[0]["hostname"];
            $variable = $cluDataArr[0]["variable"];
        } else if ($profileType == 'amazon') {
            $cluData = $this->getProfileCloudbyID($profileId, $profileType, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            $hostname = $cluDataArr[0]["shared_storage_id"];
            $variable = $cluDataArr[0]["variable"];
        }
        $variable = htmlspecialchars_decode($variable, ENT_QUOTES);
        return array($hostname, $variable);
    }

    function getCluAmzData($profileId, $profileType, $ownerID)
    {
        $connect = "";
        $cluDataArr = array();
        if ($profileType == 'cluster') {
            $cluData = $this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            if (!empty($cluDataArr[0])) {
                $connect = $cluDataArr[0]["username"] . "@" . $cluDataArr[0]["hostname"];
            }
        } else if ($profileType == 'amazon' || $profileType == 'google') {
            $cluData = $this->getProfileCloudbyID($profileId, $profileType, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            if (!empty($cluDataArr[0])) {
                $connect = $cluDataArr[0]["ssh"];
            }
        }
        $cluDataArr[0]["bash_variable"] = isset($cluDataArr[0]["bash_variable"]) ? trim($this->amazonDecode($cluDataArr[0]["bash_variable"])) : "";
        $ssh_port = !empty($cluDataArr[0]["port"]) ? " -p " . $cluDataArr[0]["port"] : "";
        $scp_port = !empty($cluDataArr[0]["port"]) ? " -P " . $cluDataArr[0]["port"] : "";
        return array($connect, $ssh_port, $scp_port, $cluDataArr);
    }

    function getKey($len)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $token       = "";
        $ret        = "";
        for ($i = 0; $i < $len; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        # If this random key exist it randomize another key
        if ($this->checkUniqToken($token))
            $ret = $this->getKey($len);
        else
            $ret = $token;
        return $ret;
    }

    function checkUniqToken($token)
    {
        $curr_token = $this->queryAVal("SELECT token FROM $this->db.token WHERE token='$token'");
        if (empty($curr_token)) {
            return 0;
        } else {
            return 1;
        }
    }



    function getReportDir($proPipeAll)
    {
        $project_pipeline_id = $proPipeAll[0]->{'id'};
        $reportDir = $proPipeAll[0]->{'output_dir'} . "/report" . $project_pipeline_id;
        $publish_dir = isset($proPipeAll[0]->{'publish_dir'}) ? $proPipeAll[0]->{'publish_dir'} : "";
        $publish_dir_check = isset($proPipeAll[0]->{'publish_dir_check'}) ? $proPipeAll[0]->{'publish_dir_check'} : "";
        if ($publish_dir_check == "true" && !empty($publish_dir)) {
            $reportDir = $publish_dir . "/report" . $project_pipeline_id;
        }
        $reportDir = trim($reportDir);
        return $reportDir;
    }
    //should end with && if not empty
    function getCleanReportCmd($proPipeAll)
    {
        $cmd = "";
        $repDir = $this->getReportDir($proPipeAll);
        if (!empty($repDir)) {
            if (substr($repDir, 0, 1) == "/") {
                $cmd = "rm -rf $repDir &&";
            } else if (preg_match("/gs:/i", $repDir)) {
                $cmd = "gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $repDir 2> /dev/null || true &&";
            } else if (preg_match("/s3:/i", $repDir)) {
                // 2> /dev/null || true part removed to effectively use the beforerun script
                //$cmd = "aws s3 rm $repDir --recursive 2> /dev/null || true &&";
                $cmd = "aws s3 rm $repDir --recursive &&";
            }
        }
        return $cmd;
    }

    function getServerRunPath($uuid)
    {
        return "{$this->run_path}/$uuid/run";;
    }
    function getServerRunTemplateDir()
    {
        return "{$this->tmp_path}/runtmplt";
    }
    function getServerRunTemplateFile($runId)
    {
        return "{$this->tmp_path}/runtmplt/$runId" . ".tar.gz";
    }

    function getServerRunTemplateNFFile($runId, $uuid)
    {
        if (empty($runId) || empty($uuid)) return "";
        $tmplt_run_targz = $this->getServerRunTemplateFile($runId);
        $tmplt_run_dir = $this->getServerRunTemplateDir();
        $extractedDir = "$tmplt_run_dir/$uuid";
        $extractedDirNFfile = "$tmplt_run_dir/$uuid/nextflow.nf";
        if (file_exists($extractedDir)) system('rm -rf ' . escapeshellarg("$extractedDir"));

        mkdir($extractedDir, 0700, true);
        system("tar xf $tmplt_run_targz -C $extractedDir", $retval);
        if ($retval != 0) return "";
        $nffile = $this->readFile($extractedDirNFfile);
        if (file_exists($extractedDir)) system('rm -rf ' . escapeshellarg("$extractedDir"));
        return $nffile;
    }

    function getDolphinPathReal($proPipeAll)
    {
        $project_pipeline_id = $proPipeAll[0]->{'id'};
        $outdir = $proPipeAll[0]->{'output_dir'};
        $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
        $publish_dir = !empty($proPipeAll[0]->{'publish_dir'}) ? $proPipeAll[0]->{'publish_dir'} : "";
        $publish_dir_check = isset($proPipeAll[0]->{'publish_dir_check'}) ? $proPipeAll[0]->{'publish_dir_check'} : "";
        $dolphin_publish_real = "";
        if ($publish_dir_check == "true" && !empty($publish_dir)) {
            $dolphin_publish_real = "$publish_dir/run{$project_pipeline_id}";
        }
        return array($dolphin_path_real, $dolphin_publish_real);
    }

    function initialRunParams($proPipeAll, $project_pipeline_id, $attempt, $profileId, $profileType, $ownerID)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $executor_job = $cluDataArr[0]['executor_job'];
        $params = "";
        $checkGeoFiles = "false";
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $collection = array();
        $file_name = array();
        $file_dir = array();
        $file_type = array();
        $files_used = array();
        $archive_dir = array();
        $s3_archive_dir = array();
        $gs_archive_dir = array();
        $collection_type = array();
        //url download
        $url = array();
        $urlzip = array();
        $checkpath = array();
        $given_name = array();
        $input_name = array();
        foreach ($allinputs as $inputitem) :
            $collection_id = $inputitem->{'collection_id'};
            if (!empty($collection_id)) {
                $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                foreach ($allfiles as $fileData) :
                    $collection[] = $collection_id;
                    $file_name[] = $fileData->{'name'};
                    $file_dir[] = $fileData->{'file_dir'};
                    $file_type[] = $fileData->{'file_type'};
                    $files_used[] = $fileData->{'files_used'};
                    $archive_dir[] = $fileData->{'archive_dir'};
                    $s3_archive_dir[] = $fileData->{'s3_archive_dir'};
                    $gs_archive_dir[] = $fileData->{'gs_archive_dir'};
                    $collection_type[] = $fileData->{'collection_type'};
                    if (empty($fileData->{'file_dir'})) {
                        $checkGeoFiles = "true";
                    }
                endforeach;
            }
            if (!empty($inputitem->{'url'}) || !empty($inputitem->{'urlzip'})) {
                $given_name[] = $inputitem->{'given_name'};
                $input_name[] = $inputitem->{'name'};
                $url[] = $inputitem->{'url'};
                $urlzip[] = $inputitem->{'urlzip'};
                $checkpath[] = $inputitem->{'checkpath'};
            }
        endforeach;



        if (!empty($file_name) || !empty($url) || !empty($urlzip)) {
            $params  = "params {\n";
            $params .= "  attempt = '" . $attempt . "'\n";
            $params .= "  run_dir = '" . $dolphin_path_real . "'\n";
            $params .= "  cloud_run_dir = '" . $dolphin_publish_real . "'\n";
            //if $profile eq "amazon" then allow s3 backupdir download.
            $params .= "  profile = '" . $profileType . "'\n";
            $params .= "  executor_job = '" . $executor_job . "'\n";
            $paramNameAr = array("given_name", "input_name", "url", "urlzip", "checkpath");
            $paramAr = array($given_name, $input_name, $url, $urlzip, $checkpath);
            $paramFileAr = array($collection, $file_name, $file_dir, $file_type, $files_used, $archive_dir, $s3_archive_dir, $gs_archive_dir, $collection_type);
            $paramNameFileAr = array("collection", "file_name", "file_dir", "file_type", "files_used", "archive_dir", "s3_archive_dir", "gs_archive_dir", "collection_type");
            for ($i = 0; $i < count($paramNameAr); $i++) {
                if (!empty($paramAr[$i])) {
                    $params .= "  " . $paramNameAr[$i] . " = '\'" . implode("\',\'", $paramAr[$i]) . "\''\n";
                }
            }
            for ($i = 0; $i < count($paramNameFileAr); $i++) {
                $params .= "  " . $paramNameFileAr[$i] . ' = new File(".' . $paramNameFileAr[$i] . "\").text\n";
            }
            $params .= "}\n";
        }
        return array($params, $checkGeoFiles);
    }

    //get nextflow input parameters
    function getMainRunInputs($project_pipeline_id, $proPipeAll, $profileType, $profileId, $ownerID)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $executor_job = $cluDataArr[0]['executor_job'];
        // get outputdir
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        if ($profileType == "google" && !empty($dolphin_publish_real)) {
            $dolphin_path_real_coll = $dolphin_publish_real;
        } else if ($executor_job == "awsbatch" && !empty($dolphin_publish_real)) {
            $dolphin_path_real_coll = $dolphin_publish_real;
        } else {
            $dolphin_path_real_coll = $dolphin_path_real;
        }
        $next_inputs = "";
        if (!empty($project_pipeline_id)) {
            $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
            if (!empty($allinputs)) {
                $next_inputs = "params {\n";
                foreach ($allinputs as $inputitem) :
                    $inputName = $inputitem->{'name'};
                    $collection_id = $inputitem->{'collection_id'};
                    if (!empty($collection_id)) {
                        $inputsPath = "$dolphin_path_real_coll/inputs/$collection_id";
                        $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                        $reg = "*";
                        if (count($allfiles) == 1) {
                            $reg = $allfiles[0]->{'name'};
                        }
                        $file_type = $allfiles[0]->{'file_type'};
                        $collection_type = $allfiles[0]->{'collection_type'};
                        if ($collection_type == "single") {
                            $inputName = "$inputsPath/$reg.$file_type";
                        } else if ($collection_type == "pair") {
                            $inputName = "$inputsPath/$reg.{R1,R2}.$file_type";
                        } else if ($collection_type == "triple") {
                            $inputName = "$inputsPath/$reg.{R1,R2,R3}.$file_type";
                        } else if ($collection_type == "quadruple") {
                            $inputName = "$inputsPath/$reg.{R1,R2,R3,R4}.$file_type";
                        }
                    }
                    //if profile variable not defined in the profile then use run_work directory (eg. ${params.DOWNDIR}) 
                    if (preg_match('/\$\{.*\}/U', $inputName)) {
                        $inputName = preg_replace('/\$\{.*\}/U', "$dolphin_path_real/downloads", $inputName);
                    }
                    $next_inputs .= "  " . $inputitem->{'given_name'} . " = '" . $inputName . "'\n";
                endforeach;
                $next_inputs .= "}\n";
            }
        }
        return $next_inputs;
    }


    function getDownCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType)
    {
        $cacheCmd = "";
        if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
            $cacheCmd = "if [ ! -d $dolphin_path_real/.nextflow ]; then mkdir -p $dolphin_path_real && gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -o GSUtil:parallel_composite_upload_threshold=150M -m cp -r $dolphin_publish_real/.nextflow $dolphin_path_real 2> /dev/null || true; fi";
        }
        return $cacheCmd;
    }

    function getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType)
    {
        $cacheCmd = "";
        if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
            $cacheCmd = "if [ -d $dolphin_path_real/.nextflow ]; then gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $dolphin_publish_real/.nextflow 2> /dev/null || true && gsutil -o GSUtil:parallel_composite_upload_threshold=150M -m cp -r $dolphin_path_real/.nextflow $dolphin_publish_real; fi";
        }
        return $cacheCmd;
    }



    function getInitialImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID)
    {
        $initImageCmd  = "";
        $initImagePath = "";
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $singu_cache = $cluDataArr[0]["singu_cache"];

        $runType = "initial";
        $containerType = ''; //default
        if ($docker_check == "true") {
            $containerType = 'docker';
        } else if ($singu_check == "true") {
            $containerType = 'singularity';
        }
        $initialrun_img = $this->getInitialRunImg($docker_check, $singu_check);
        list($initImageCmd, $initImagePath) = $this->imageCmd($singu_cache, $initialrun_img, "", $containerType, $profileType, $profileId, $runType, $dolphin_publish_real, $ownerID);
        return array($initImageCmd, $initImagePath);
    }

    function getImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID)
    {
        $imageCmd  = "";
        $imagePath = "";
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $singu_cache = $cluDataArr[0]["singu_cache"];

        if ($singu_check == "true") {
            $runType = "main";
            $singu_img = $proPipeAll[0]->{'singu_img'};
            $singu_save = $proPipeAll[0]->{'singu_save'};
            list($imageCmd, $imagePath) = $this->imageCmd($singu_cache, $singu_img, $singu_save, 'singularity', $profileType, $profileId, $runType, $dolphin_publish_real, $ownerID);
        } else if ($docker_check == "true") {
            $docker_img = $proPipeAll[0]->{'docker_img'};
            $imagePath = $docker_img;
        }
        return array($imageCmd, $imagePath);
    }



    //get nextflow executor parameters
    function getNextExecParam($proPipeAll, $project_pipeline_id, $profileType, $profileId, $initialRunParams, $ownerID)
    {
        $proPipeCmd = $proPipeAll[0]->{'cmd'};
        $jobname = html_entity_decode($proPipeAll[0]->{'pp_name'}, ENT_QUOTES);
        //get dolphin paths in target location
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($imageCmd, $imagePath) = $this->getImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        $initImageCmd = "";
        if (!empty($initialRunParams)) {
            list($initImageCmd, $initImagePath) = $this->getInitialImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        }
        //get report options
        $reportOptions = "";
        $withReport = $proPipeAll[0]->{'withReport'};
        $withTrace = $proPipeAll[0]->{'withTrace'};
        $withTimeline = $proPipeAll[0]->{'withTimeline'};
        $withDag = $proPipeAll[0]->{'withDag'};
        if ($withReport == "true") {
            $reportOptions .= " -with-report";
        }
        if ($withTrace == "true") {
            $reportOptions .= " -with-trace";
        }
        if ($withTimeline == "true") {
            $reportOptions .= " -with-timeline";
        }
        if ($withDag == "true") {
            $reportOptions .= " -with-dag dag.html";
        }
        return array($dolphin_path_real, $dolphin_publish_real, $proPipeCmd, $jobname, $imageCmd, $initImageCmd, $reportOptions);
    }


    //get username and hostname and exec info for connection
    function getNextConnectExec($profileId, $ownerID, $profileType)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $next_path = $cluDataArr[0]["next_path"];
        $profileCmd = $cluDataArr[0]["cmd"];
        $executor = $cluDataArr[0]['executor'];
        $next_time = $cluDataArr[0]['next_time'];
        $next_queue = $cluDataArr[0]['next_queue'];
        $next_memory = $cluDataArr[0]['next_memory'];
        $next_cpu = $cluDataArr[0]['next_cpu'];
        $next_clu_opt = $cluDataArr[0]['next_clu_opt'];
        $executor_job = $cluDataArr[0]['executor_job'];
        return array($connect, $next_path, $profileCmd, $executor, $next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job, $ssh_id, $ssh_port);
    }

    function getPostCmd($proPipeAll, $dolphin_path_real, $dolphin_publish_real, $profileType, $executor_job, $run_path_real)
    {
        $interdel = $proPipeAll[0]->{'interdel'};
        $interdelCmd = "";
        if ($interdel == "true") {
            if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
                $nxf_work = "$dolphin_publish_real/work";
                $interdelCmd = "gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $nxf_work 2> /dev/null || true";
            } else if ($executor_job == "awsbatch" && !empty($dolphin_publish_real) && preg_match("/s3:/i", $dolphin_publish_real)) {
                $nxf_work = "$dolphin_publish_real/work";
                $interdelCmd = "aws s3 rm $nxf_work --recursive 2> /dev/null || true";
            } else if (!empty($dolphin_path_real)) {
                $nxf_work = "$dolphin_path_real/work";
                $interdelCmd = "rm -rf $nxf_work";
            }
        }
        $afterRun = "";
        if (file_exists($run_path_real . "/afterrun.sh")) {
            $afterRun = "bash $dolphin_path_real/afterrun.sh >> $dolphin_path_real/log.txt";
        }


        // ### combine post-run cmd
        // should start with && and end without &&
        $arr = array($interdelCmd, $afterRun);
        $postCmd = "";
        for ($i = 0; $i < count($arr); $i++) {
            if (!empty($arr[$i])) {
                $postCmd .= " && ";
            }
            $postCmd .= $arr[$i];
        }


        // ### combine fail commands
        //copy .nextflow folder to cloud anycase
        $upCacheCmd = $this->getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType);
        // $failCmd should start with ; and use && inbetween and end without &&
        $failarr = array($upCacheCmd);
        $failCmd = "";
        for ($i = 0; $i < count($failarr); $i++) {
            if (!empty($failarr[$i]) && !empty($failCmd)) {
                $failCmd .= " && ";
            } else if (!empty($failarr[$i]) && empty($failCmd)) {
                $failCmd .= " ; ";
            }
            $failCmd .= $failarr[$i];
        }


        return $postCmd . $failCmd;
    }

    function getPreCmd($profileType, $profileCmd, $proPipeCmd, $imageCmd, $initImageCmd, $downCacheCmd, $run_path_real, $dolphin_path_real, $attempt, $proPipeAll)
    {
        $profile_def = "";
        if ($profileType == "amazon" || $profileType == "google") {
            $profile_def = "source /etc/profile && source ~/.bash_profile";
        }
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)) {
            $nextVerText = "export NXF_VER=$nextVer";
        }
        $nextANSILog = "export NXF_ANSI_LOG=false";
        $nextDSL = "export NXF_DEFAULT_DSL=1";
        //export 
        // set NXF_SINGULARITY_CACHEDIR as $HOME/.dolphinnext/singularity, if it is not defined.
        $singu_cachedir = 'NXF_SINGULARITY_CACHEDIR="${NXF_SINGULARITY_CACHEDIR:-$HOME/.dolphinnext/singularity}" && export NXF_SINGULARITY_CACHEDIR=$NXF_SINGULARITY_CACHEDIR';
        $beforeSchedule = '';
        settype($attempt, 'integer');
        error_log("attempt: $attempt");
        $proPipeType = $proPipeAll[0]->{'type'};
        error_log("proPipeType: $proPipeType ");

        if ($proPipeType == "auto" && $attempt == 1 && file_exists($run_path_real . "/beforeschedule.sh")) {
            $beforeSchedule = "echo \"INFO: Executing beforeschedule.sh...\" >> $dolphin_path_real/log.txt && bash $dolphin_path_real/beforeschedule.sh >> $dolphin_path_real/log.txt && echo \"INFO: beforeschedule.sh execution passed.\" >> $dolphin_path_real/log.txt";
        }
        $beforeRun = '';
        if (file_exists($run_path_real . "/beforerun.sh")) {
            $beforeRun = "bash $dolphin_path_real/beforerun.sh >> $dolphin_path_real/log.txt";
        }


        // combine pre-run cmd
        // should start without && and end with &&

        $arr = array($profile_def, $nextVerText, $nextANSILog, $nextDSL, $profileCmd, $proPipeCmd, $singu_cachedir, $imageCmd, $initImageCmd, $downCacheCmd, $beforeSchedule, $beforeRun);
        $preCmd = "";
        for ($i = 0; $i < count($arr); $i++) {
            if (!empty($arr[$i]) && !empty($preCmd)) {
                $preCmd .= " && ";
            }
            $preCmd .= $arr[$i];
        }
        if (!empty($preCmd)) {
            $preCmd .= " && ";
        }

        return $preCmd;
    }

    function getNextPathReal($next_path)
    {
        if (!empty($next_path)) {
            $next_path_real = "$next_path/nextflow";
        } else {
            $next_path_real  = "nextflow";
        }
        return $next_path_real;
    }

    function convertToHoursMins($time)
    {
        $format = '%d:%s';
        settype($time, 'integer');
        $hours = floor($time / 60);
        $minutes = $time % 60;
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($hours < 10) {
            $hours = '0' . $hours;
        }
        return sprintf($format, $hours, $minutes);
    }
    function cleanName($name, $limit)
    {
        $name = str_replace("/", "_", $name);
        $name = str_replace(" ", "", $name);
        $name = str_replace("(", "_", $name);
        $name = str_replace(")", "_", $name);
        $name = str_replace("\'", "_", $name);
        $name = str_replace("\"", "_", $name);
        $name = str_replace("\\", "_", $name);
        $name = str_replace("&", "_", $name);
        $name = str_replace("<", "_", $name);
        $name = str_replace(">", "_", $name);
        $name = str_replace("-", "_", $name);
        $name = str_replace("'", "_", $name);
        $name = str_replace('"', "_", $name);
        $name = substr($name, 0, $limit);
        return $name;
    }
    function escapeRegex($name)
    {
        $name = str_replace("&", "\&", $name);
        $name = str_replace("/", "\/", $name);
        $name = str_replace('"', '\"', $name);
        $name = str_replace("'", "\'", $name);
        return $name;
    }

    function getMemory($next_memory, $executor)
    {
        $memoryText = "";
        if (!empty($next_memory)) {
            if ($executor == "lsf") {
                //convert gb to mb
                settype($next_memory, 'integer');
                $next_memory = $next_memory * 1000;
                $memoryText = "#BSUB -R rusage[mem=" . $next_memory . "]\\n";
            } else if ($executor == "sge") {
                $memoryText = "#$ -l h_vmem=" . $next_memory . "G\\n";
            } else if ($executor == "slurm") {
                //#SBATCH --mem 100 # memory pool for all cores default GB
                $memoryText = "#SBATCH --mem=" . $next_memory . "G\\n";
            }
        }
        return $memoryText;
    }
    function getJobName($jobname, $executor)
    {
        $jobname = $this->cleanName($jobname, 9);
        $jobNameText = "";
        if (!empty($jobname)) {
            if ($executor == "lsf") {
                $jobNameText = "#BSUB -J $jobname\\n";
            } else if ($executor == "sge") {
                $jobNameText = "#$ -N $jobname\\n";
            } else if ($executor == "slurm") {
                $jobNameText = "#SBATCH --job-name=$jobname\\n";
            }
        }
        return $jobNameText;
    }
    function getTime($next_time, $executor)
    {
        $timeText = "";
        if (!empty($next_time)) {
            if ($executor == "lsf") {
                //$next_time is in minutes convert into hours and minutes.
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#BSUB -W $next_time\\n";
            } else if ($executor == "sge") {
                //$next_time is in minutes convert into hours and minutes.
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#$ -l h_rt=$next_time:00\\n";
            } else if ($executor == "slurm") {
                //#SBATCH -t hours:minutes:seconds
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#SBATCH -t $next_time:00\\n";
            }
        }
        return $timeText;
    }
    function getQueue($next_queue, $executor)
    {
        $queueText = "";
        if (!empty($next_queue)) {
            if ($executor == "lsf") {
                $queueText = "#BSUB -q $next_queue\\n";
            } else if ($executor == "sge") {
                $queueText = "#$ -q $next_queue\\n";
            } else if ($executor == "slurm") {
                //#SBATCH --partition=defq
                $queueText = "#SBATCH --partition=$next_queue\\n";
            }
        }
        return $queueText;
    }
    function getNextCluOpt($next_clu_opt, $executor)
    {
        $next_clu_optText = "";
        if (!empty($next_clu_opt)) {
            if ($executor == "lsf") {
                $next_clu_optText = "#BSUB $next_clu_opt\\n";
            } else if ($executor == "sge") {
                $next_clu_optText = "#$ $next_clu_opt\\n";
            } else if ($executor == "slurm") {
                $next_clu_optText = "#SBATCH $next_clu_opt\\n";
            }
        }
        return $next_clu_optText;
    }
    function getCPU($next_cpu, $executor)
    {
        $cpuText = "";
        if (!empty($next_cpu)) {
            if ($executor == "lsf") {
                $cpuText = "#BSUB -n $next_cpu\\n";
            } else if ($executor == "sge") {
                $cpuText = "#$ -l slots=$next_cpu\\n";
            } else if ($executor == "slurm") {
                $cpuText = "#SBATCH --nodes=$next_cpu\\n#SBATCH --ntasks=$next_cpu\\n";
            }
        }
        return $cpuText;
    }

    //get all nextflow executor text
    function getExecNextAll($proPipeAll, $executor, $dolphin_path_real, $dolphin_publish_real, $next_path_real, $next_queue, $next_cpu, $next_time, $next_memory, $jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId, $profileType, $logName, $initialRunParams, $postCmd, $preCmd, $run_path_real, $ownerID)
    {
        $cleanReportCmd = "";
        $replaceInitialRunCode = "";
        if ($runType == "resumerun") {
            $runType = "-resume";
        } else {
            $runType = "";
            $cleanReportCmd = $this->getCleanReportCmd($proPipeAll);
        }
        $initialRunCmd = "";
        $igniteExec = "-process.executor ignite";
        if ($profileType == "google") {
            $igniteExec = "";
        }
        $igniteCmd = "";
        $nxf_work  = "";
        $timeouts = "-cluster.failureDetectionTimeout 100000000 -cluster.clientFailureDetectionTimeout 100000000 -cluster.tcp.socketTimeout 100000000";
        //      $timeouts = " -cluster.tcp.reconnectCount 100000000 -cluster.tcp.networkTimeout 100000000 -cluster.tcp.ackTimeout 100000000 -cluster.tcp.maxAckTimeout 100000000  -cluster.tcp.joinTimeout 100000000";
        if ($executor == "local" && $executor_job == 'ignite') {
            $nxf_work  = "-w $dolphin_path_real/work";
            $igniteCmd = "$igniteExec $timeouts";
        }
        if ($profileType == "google") {
            $nxf_work = "-w $dolphin_publish_real/work";
        }
        if ($executor_job == "awsbatch") {
            $nxf_work = "-w $dolphin_publish_real/work -bucket-dir $dolphin_publish_real/work";
        }

        if (!empty($initialRunParams)) {
            $nxf_work_init = "";
            if ($executor == "local" && $executor_job == 'ignite') {
                $nxf_work_init = "-w $dolphin_path_real/initialrun/work";
            }
            if ($profileType == "google") {
                $nxf_work_init = "-w $dolphin_publish_real/initialrun/work";
            }
            if ($executor_job == "awsbatch") {
                $nxf_work_init = "-w $dolphin_publish_real/initialrun/work";
            }
            if (file_exists($run_path_real . "/initialrun.nf")) {
                $replaceInitialRunCode = "cp $dolphin_path_real/initialrun.nf $dolphin_path_real/initialrun/nextflow.nf &&";
            }

            $initialRunCmd = "$replaceInitialRunCode cd $dolphin_path_real/initialrun && $next_path_real $dolphin_path_real/initialrun/nextflow.nf $nxf_work_init $igniteCmd $runType $reportOptions > $dolphin_path_real/initialrun/initial.log && ";
        }
        $mainNextCmd = "$preCmd $cleanReportCmd $initialRunCmd cd $dolphin_path_real && $next_path_real $dolphin_path_real/nextflow.nf $nxf_work $igniteCmd $runType $reportOptions >> $dolphin_path_real/$logName $postCmd";

        //for lsf "bsub -q short -n 1  -W 100 -R rusage[mem=32024]";
        if ($executor == "local") {
            $exec_next_all = "$mainNextCmd ";
        } else if ($executor == "lsf") {
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $lsfRunFile = "printf '#!/bin/bash \\n" . $queueText . $jobnameText . $cpuText . $timeText . $memoryText . $clu_optText . "$mainNextCmd" . "'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "bsub -o $dolphin_path_real/out.log -e $dolphin_path_real/err.log  < $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $lsfRunFile && $exec_string";
        } else if ($executor == "sge") {
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            //-j y ->Specifies whether or not the standard error stream of the job is merged into the standard output stream.
            $sgeRunFile = "printf '#!/bin/bash \\n#$ -j y\\n#$ -V\\n#$ -notify\\n#$ -wd $dolphin_path_real\\n#$ -o $dolphin_path_real/.dolphinnext.log\\n" . $jobnameText . $memoryText . $timeText . $queueText . $clu_optText . $cpuText . "$mainNextCmd" . "'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "qsub -e $dolphin_path_real/err.log $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $sgeRunFile && $exec_string";
        } else if ($executor == "slurm") {
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            $errText = "#SBATCH -e $dolphin_path_real/err.log\\n";
            $outText = "#SBATCH -o $dolphin_path_real/.dolphinnext.log\\n";
            $runFile = "printf '#!/bin/bash \\n" . $outText . $errText . $jobnameText . $memoryText . $timeText . $queueText . $clu_optText . $cpuText . "$mainNextCmd" . "'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "sbatch $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $runFile && $exec_string";
        }
        return $exec_next_all;
    }

    function getMainRunOpt($proPipeAll, $profileId, $profileType, $ownerID)
    {
        $configText = "";
        // Step 1. Add process.executor
        // e.g. process.executor = 'lsf'
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $executor = $cluDataArr[0]['executor'];
        $executor_job = $cluDataArr[0]['executor_job'];
        $configText .= "process.executor = '" . $executor_job . "'\n";

        // Step 2. if executor is local add cpu and memory fields in profile.
        $configText = $this->addLocalConfigSettings($configText, $executor, $cluDataArr);
        // Step 3. add general process settings
        $configText = $this->addGeneralConfigSettings($configText, $proPipeAll, $cluDataArr, $executor_job);
        // Step 4. add each process settings
        $configText = $this->addEachConfigSettings($configText, $proPipeAll, $executor_job, $ownerID);
        return $configText;
    }

    function addLocalConfigSettings($configText, $executor, $cluDataArr)
    {
        if ($executor == "local") {
            $next_cpu = $cluDataArr[0]['next_cpu'];
            $next_memory = $cluDataArr[0]['next_memory'];
            if (!empty($next_cpu)) {
                $configText .= 'executor.$local.cpus' . ' = ' . $next_cpu . "\n";
            }
            if (!empty($next_memory)) {
                $configText .= 'executor.$local.memory' . " = '" . $next_memory . " GB'" . "\n";
            }
        }
        return $configText;
    }

    function getPipelineNodeData($pipeData, $type, $id)
    {
        $ret = "";
        if ($type == "main") $pipeObj = $pipeData["main_pipeline_$id"];
        if ($type == "module") $pipeObj = $pipeData["pipeline_module_$id"];
        if (!empty($pipeObj)) {
            $pipeNodes = $pipeObj["nodes"];
            $pipeNodes = preg_replace("/'/", '\"', $pipeNodes);
            $pipeNodes = json_decode($pipeNodes, true);
            if (!empty($pipeNodes)) {
                $ret = $pipeNodes;
            }
        }
        return $ret;
    }

    function getMergedProcessNameWithGnum($type, $pipeline_id, $pipeData, $processGnum, $pipelineGnum, $mergedProcessName)
    {
        // e.g. $processGnum: 177 -> process gnum 177 in main pipeline
        // e.g. $pipelineGnum: 216 -> mainParentPipelineGnum=216
        // e.g. $pipelineGnum: 216_16 -> mainParentPipelineGnum=216 childPipelineGnum=16
        if (!empty($pipelineGnum) || $pipelineGnum == "0") {
            $pipelineGnumAr = explode("_", $pipelineGnum);
            $pipelineGnum = $pipelineGnumAr[0];
            $pipelineGnumRestAr = array_shift($pipelineGnumAr);
            if (count($pipelineGnumAr) > 1) {
                $pipelineGnumRest = implode("_", $pipelineGnumRestAr);
            } else if (count($pipelineGnumAr) == 1) {
                $pipelineGnumRest = $pipelineGnumAr[0];
            } else {
                $pipelineGnumRest = "";
            }

            $pipeNodes = $this->getPipelineNodeData($pipeData, $type, $pipeline_id);

            if (!empty($pipeNodes)) {
                foreach ($pipeNodes as $proGnum => $settObj) :
                    if ("g-$pipelineGnum" == $proGnum) {
                        $pipeModuleName = $settObj[3]; // module name
                        if (!empty($mergedProcessName)) {
                            $mergedProcessName = $mergedProcessName . "_" . $pipeModuleName;
                        } else {
                            $mergedProcessName = $pipeModuleName;
                        }
                        $pipeModuleID = $settObj[2]; // $pipeModuleID e.g. p22
                        $pipeModuleID = preg_replace('/^p/', '', $pipeModuleID);
                    }
                endforeach;
            }
            if (!empty($pipeModuleID)) {
                $mergedProcessName = $this->getMergedProcessNameWithGnum("module", $pipeModuleID, $pipeData, $processGnum, $pipelineGnumRest, $mergedProcessName);
            }
        } else {
            $pipeNodes = $this->getPipelineNodeData($pipeData, $type, $pipeline_id);
            if (!empty($pipeNodes)) {
                foreach ($pipeNodes as $proGnum => $settObj) :
                    if ("g-$processGnum" == $proGnum) {
                        $processName = $settObj[3]; // process name
                        if (!empty($mergedProcessName)) {
                            $mergedProcessName = $mergedProcessName . "_" . $processName;
                        } else {
                            $mergedProcessName = $processName;
                        }
                    }
                endforeach;
            }
        }
        return $mergedProcessName;
    }

    function replaceProcGnumWithProcName($exec_each_settings, $pipeline_id, $ownerID)
    {
        $ret = array();
        $pipeData = $this->exportPipeline($pipeline_id, $ownerID, "main", 0);
        if (!empty($pipeData)) $pipeData = json_decode($pipeData, true);
        foreach ($exec_each_settings as $processGnum => $configObj) :
            // procGnum format: procGnum-(proGnum)(p)(mainParentPipelineGnum)(_)(childPipelineGnum) 
            // e.g. procGnum-177 -> process gnum 177 in main pipeline
            // e.g. procGnum-11p216 -> process gnum 11 in mainParentPipelineGnum=216
            // e.g. procGnum-21p216_16 -> process gnum 21 in mainParentPipelineGnum=216 childPipelineGnum=16
            // note-1: propanelDiv 216(main pipeline gnum)
            // note-2: propanelDiv 216_16(pipeline gnum=16 in pipeline gnum 216)
            $processGnum = preg_replace('/^procGnum-/', '', $processGnum);
            $pipelineGnum = "";
            if (preg_match("/p/", $processGnum)) {
                $processGnumAr = explode("p", $processGnum);
                $processGnum = $processGnumAr[0];
                $pipelineGnum = $processGnumAr[1];
            }
            $processName = $this->getMergedProcessNameWithGnum("main", $pipeline_id, $pipeData, $processGnum, $pipelineGnum, "");
            if (!empty($processName)) $ret[$processName] = $configObj;
        endforeach;
        return $ret;
    }

    function addEachConfigSettings($configText, $proPipeAll, $executor_job, $ownerID)
    {
        // add each process settings
        // e.g. process {
        //      withName: bar { cpus = 32 } 
        //      withName: bar { memory = '32 GB' } 
        //      withName: bar { time = '20m' } 
        //      withName: bar { clusterOptions = '-E "file /project/umw_garberlab"' } 
        // }
        $exec_each = $proPipeAll[0]->{'exec_each'};
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        $exec_each_settings = htmlspecialchars_decode($proPipeAll[0]->{'exec_each_settings'}, ENT_QUOTES);
        $exec_each_settings = json_decode($exec_each_settings, true);
        if ($exec_each == "true" && !empty($exec_each_settings) && !empty($pipeline_id)) {
            $exec_each_settings = $this->replaceProcGnumWithProcName($exec_each_settings, $pipeline_id, $ownerID);
            if (!empty($exec_each_settings)) {
                $configText .= "process {\n";
                foreach ($exec_each_settings as $processName => $configObj) :
                    $time = $configObj['time'];
                    $queue = $configObj['queue'];
                    $cpu = $configObj['cpu'];
                    $memory = $configObj['memory'];
                    $clu_opt = $configObj['opt'];

                    if (!empty($time) && $executor_job != "ignite" &&  $executor_job != "local") {
                        $configText .= "  withName: {$processName} { time  = '$time m' }\n";
                    }
                    if (!empty($cpu)) {
                        $configText .= "  withName: {$processName} { cpus  = $cpu  }\n";
                    }
                    if (!empty($queue) && $executor_job != "ignite" &&  $executor_job != "local") {
                        $configText .= "  withName: {$processName} { queue  = '$queue' }\n";
                    }
                    if (!empty($memory)) {
                        $configText .= "  withName: {$processName} { memory  = '$memory GB' }\n";
                    }
                    if (!empty($clu_opt) &&  $executor_job != "local") {
                        $configText .= "  withName: {$processName} { clusterOptions  = '$clu_opt' }\n";
                    }
                endforeach;
                $configText .= "}\n";
            }
        }
        return $configText;
    }


    function addGeneralConfigSettings($configText, $proPipeAll, $cluDataArr, $executor_job)
    {
        // add general process settings
        // e.g. process.queue = 'short'
        //      process.memory = '32 GB'
        //      process.cpus = 1
        //      process.time = '20m'
        //      process.clusterOptions = '-E "file /project/umw_garberlab"'
        $exec_all_settings = $proPipeAll[0]->{'exec_all_settings'};
        $exec_all = $proPipeAll[0]->{'exec_all'};
        if ($exec_all == "true") {
            $exec_all_settings = htmlspecialchars_decode($exec_all_settings, ENT_QUOTES);
            $exec_all_settings = json_decode($exec_all_settings, true);
            $time = $exec_all_settings['job_time'];
            $queue = $exec_all_settings['job_queue'];
            $cpu = $exec_all_settings['job_cpu'];
            $memory = $exec_all_settings['job_memory'];
            $clu_opt = $exec_all_settings['job_clu_opt'];
        } else {
            // use profile settings
            $time = $cluDataArr[0]['job_time'];
            $queue = $cluDataArr[0]['job_queue'];
            $cpu = $cluDataArr[0]['job_cpu'];
            $memory = $cluDataArr[0]['job_memory'];
            $clu_opt = $cluDataArr[0]['job_clu_opt'];
        }

        if (!empty($time) && $executor_job != "ignite" &&  $executor_job != "local" &&  $executor_job != "awsbatch") {
            $configText .= "process.time" . " = '" . $time . "m'\n";
        }
        if (!empty($cpu)) {
            $configText .= "process.cpus" . " = " . $cpu . "\n";
        }
        if (!empty($queue) && $executor_job != "ignite" &&  $executor_job != "local") {
            $configText .= "process.queue" . " = '" . $queue . "'\n";
        }
        if (!empty($memory)) {
            $configText .= "process.memory" . " = '" . $memory . " GB'\n";
        }
        if (!empty($clu_opt) &&  $executor_job != "local") {
            $configText .= "process.clusterOptions" . " = '" . $clu_opt . "'\n";
        }
        return $configText;
    }

    function getInitialRunOpt($proPipeAll, $profileId, $profileType, $ownerID)
    {
        $configText = "";
        // Step 1a. Add profile Variables 
        list($hostVar, $variable) = $this->getConfigHostnameVariable($profileId, $profileType, $ownerID);
        if (!empty($variable)) {
            $configText .=  "$variable\n";
        }
        $docker_opt = $proPipeAll[0]->{'docker_opt'};
        $singu_opt = $proPipeAll[0]->{'singu_opt'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};

        // Step 1b. Add runOptions for singularity (default) or docker
        // e.g. singularity.runOptions='-B /project:/project -B /home:/home -B /share:/share '
        if ($docker_check == "true") {
            if (!empty($docker_opt)) {
                $configText .= "docker.runOptions = '" . $docker_opt . "'\n";
            }
        }
        //default for initial run
        if ($singu_check == "true") {
            if (!empty($singu_opt)) {
                $configText .= "singularity.runOptions = '" . $singu_opt . "'\n";
            }
        }
        // Step 2. Add process.executor
        // e.g. process.executor = 'lsf'
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $executor = $cluDataArr[0]['executor'];
        $executor_job = $cluDataArr[0]['executor_job'];
        $configText .= "process.executor = '" . $executor_job . "'\n";

        // Step 3. if executor is local add cpu and memory fields in profile.
        $configText = $this->addLocalConfigSettings($configText, $executor, $cluDataArr);
        // Step 4. add general process settings
        // e.g. process.queue = 'short'
        //      process.memory = '32 GB'
        //      process.cpus = 1
        //      process.time = '20m'
        //      process.clusterOptions = '-E "file /project/umw_garberlab"'
        $configText = $this->addGeneralConfigSettings($configText, $proPipeAll, $cluDataArr, $executor_job);
        return $configText;
    }

    function getContainerRunOpt($proPipeAll, $profileType, $profileId, $runType, $ownerID)
    {
        $configText = "";
        $docker_opt = $proPipeAll[0]->{'docker_opt'};
        $singu_opt = $proPipeAll[0]->{'singu_opt'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};

        if ($docker_check == "true") {
            if (!empty($docker_opt)) {
                $configText .= "docker.runOptions = '" . $docker_opt . "'\n";
            }
        }
        if ($singu_check == "true") {
            if (!empty($singu_opt)) {
                $configText .= "singularity.runOptions = '" . $singu_opt . "'\n";
            }
        }
        return $configText;
    }

    //$runType == "main" or "initial"
    function getContainerConfig($proPipeAll, $profileType, $profileId, $runType, $ownerID)
    {
        $configText = "";
        $docker_check = $proPipeAll[0]->{'docker_check'};
        $docker_img = $proPipeAll[0]->{'docker_img'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $singu_img = $proPipeAll[0]->{'singu_img'};
        if ($runType == "main") {
            list($imageCmd, $imagePath) = $this->getImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        } else if ($runType == "initial") {
            list($imageCmd, $imagePath) = $this->getInitialImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        }
        //escape $ in the nexflow config
        $imagePath = str_replace('$', '//$', $imagePath);

        if ($docker_check == "true") {
            $configText .= "process.container = '" . $imagePath . "'\n";
            $configText .= "docker.enabled = true\n";
        } else if ($singu_check == "true") {
            $configText .= "process.container = '" . $imagePath . "'\n";
            $configText .= "singularity.enabled = true\n";
        }
        return $configText;
    }

    function getMainTestRunConfig($proPipeAll, $configText, $project_pipeline_id, $profileId, $profileType, $proVarObj, $code, $ownerID)
    {
        $containerConfig = $this->getContainerConfig($proPipeAll, $profileType, $profileId, "main", $ownerID);
        $containerRunOpt = $this->getContainerRunOpt($proPipeAll, $profileType, $profileId, "main", $ownerID);

        $configText = "// Process Config:\n\n{$containerConfig}{$containerRunOpt}{$configText}";
        //get nextflow.config from pipeline.
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        $pipe = $this->loadPipeline($pipeline_id, $ownerID);
        $pipe_obj = json_decode($pipe, true);
        $script_pipe_configRaw = isset($pipe_obj[0]["script_pipe_config"]) ? $pipe_obj[0]["script_pipe_config"] : "";
        $script_pipe_config = htmlspecialchars_decode($script_pipe_configRaw, ENT_QUOTES);
        $configText .= "\n// Pipeline Config:\n\n";
        list($hostVar, $variable) = $this->getConfigHostnameVariable($profileId, $profileType, $ownerID);
        $reportDir = $this->getReportDir($proPipeAll);

        $configText .= "\$HOSTNAME='" . $hostVar . "'\n";
        $configText .= "params.outdir='" . $reportDir . "'\n";
        $configText .= "$variable\n";
        $configText .= "$script_pipe_config\n";
        //get main run input parameters
        $mainRunParams = $this->getMainRunInputs($project_pipeline_id, $proPipeAll, $profileType, $profileId, $ownerID);
        $configText .= "\n// Run Parameters:\n\n" . $mainRunParams;
        //get main run local variable parameters:
        $configText = $this->getProcessParams(json_decode($proVarObj), $configText);
        $configText .= "\n// Test Parameters:\n\n";
        $test_params = $code["test_params"];
        if (!empty($test_params)) {
            $configText .= urldecode($test_params) . "\n\n";
        }
        return $configText;
    }

    function getMainRunConfig($proPipeAll, $configText, $project_pipeline_id, $profileId, $profileType, $proVarObj, $ownerID)
    {
        $containerConfig = $this->getContainerConfig($proPipeAll, $profileType, $profileId, "main", $ownerID);
        $containerRunOpt = $this->getContainerRunOpt($proPipeAll, $profileType, $profileId, "main", $ownerID);

        $configText = "// Process Config:\n\n{$containerConfig}{$containerRunOpt}{$configText}";
        //get nextflow.config from pipeline.
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        $pipe = $this->loadPipeline($pipeline_id, $ownerID);
        $pipe_obj = json_decode($pipe, true);
        $script_pipe_configRaw = isset($pipe_obj[0]["script_pipe_config"]) ? $pipe_obj[0]["script_pipe_config"] : "";
        $script_pipe_config = htmlspecialchars_decode($script_pipe_configRaw, ENT_QUOTES);
        $configText .= "\n// Pipeline Config:\n\n";
        list($hostVar, $variable) = $this->getConfigHostnameVariable($profileId, $profileType, $ownerID);
        $reportDir = $this->getReportDir($proPipeAll);

        $configText .= "\$HOSTNAME='" . $hostVar . "'\n";
        $configText .= "params.outdir='" . $reportDir . "'\n";
        $configText .= "$variable\n";
        $configText .= "$script_pipe_config\n";
        //get main run input parameters
        $mainRunParams = $this->getMainRunInputs($project_pipeline_id, $proPipeAll, $profileType, $profileId, $ownerID);
        $configText .= "\n// Run Parameters:\n\n" . $mainRunParams;
        //get main run local variable parameters:
        $configText = $this->getProcessParams(json_decode($proVarObj), $configText);
        return $configText;
    }

    function getProcessParams($proVarObj, $configText)
    {
        $checkVarObj = (array)$proVarObj;
        if (!empty($checkVarObj)) {
            $configText .= "\n\n// Process Parameters:\n";
            foreach ($proVarObj as $processName => $varObj) :
                $configText .= "\n// Process Parameters for $processName:\n";
                foreach ($varObj as $varname => $line) :
                    $configText .= "$line\n";
                endforeach;
            endforeach;
        }
        return $configText;
    }

    function getInitialRunConfig($proPipeAll, $project_pipeline_id, $attempt, $profileType, $profileId, $docker_check, $initRunOptions, $ownerID)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $containerConfig = $this->getContainerConfig($proPipeAll, $profileType, $profileId, "initial", $ownerID);
        $configText = "// Process Config:\n\n{$containerConfig}{$initRunOptions}\n";

        //get initial run input paramaters
        list($initialRunParams, $checkGeoFiles) = $this->initialRunParams($proPipeAll, $project_pipeline_id, $attempt, $profileId, $profileType, $ownerID);
        if (isset($checkGeoFiles)) {
            $executor = $cluDataArr[0]['executor'];
            $executor_job = $cluDataArr[0]['executor_job'];
            if ($checkGeoFiles == "true" && (($executor == "local" && $executor_job == "local") || $profileType == "amazon")) {
                $configText .= "\n//parallel download limit for GEO files on local executor:\n";
                $configText .= "executor.queueSize = 4 \n";
            }
        }
        $configText .= "\n//Initial Run Parameters\n" . $initialRunParams;
        return array($configText, $initialRunParams);
    }

    function createUUIDCmd($dolphin_path_real, $uuid)
    {
        $uuidCmd = "";
        if (!empty($dolphin_path_real)) {
            $uuidCmd = "mkdir -p $dolphin_path_real/.dolphinnext/uuid && touch $dolphin_path_real/.dolphinnext/uuid/$uuid &&";
        }
        return $uuidCmd;
    }

    // if comes with - then convert cluster-2 to umass.edu 
    // or return as its
    function getRunEnv($run_env, $ownerID)
    {
        if (preg_match("/-/", $run_env)) {
            $profileAr = explode("-", $run_env);
            $profileType = $profileAr[0];
            $profileId = isset($profileAr[1]) ? $profileAr[1] : "";
            $run_env = $profileType;
            if ($profileType == "cluster" && !empty($profileId)) {
                $proData = $this->getProfileClusterbyID($profileId, $ownerID);
                $proDataAll = json_decode($proData, true);
                if (!empty($proDataAll[0])) {
                    $hostname = $proDataAll[0]["hostname"];
                    $run_env = $hostname;
                }
            }
        }
        return $run_env;
    }

    function getRenameCmd($dolphin_path_real, $attempt)
    {
        $renameLog = "";
        $pathArr = array($dolphin_path_real, "$dolphin_path_real/initialrun");
        foreach ($pathArr as $path) :
            if ($path == $dolphin_path_real) {
                $renameArr = array("log.txt", "timeline.html", "trace.txt", "dag.html", "report.html", ".nextflow.log", "err.log", "out.log");
            } else {
                $renameArr = array("initial.log", "timeline.html", "trace.txt", "dag.html", "report.html", ".nextflow.log", "err.log", "out.log");
            }
            foreach ($renameArr as $item) :
                if ($item == "log.txt" || $item == "initial.log") {
                    $renameLog .= "cp $path/$item $path/$item.$attempt 2>/dev/null || true && >$path/$item && ";
                } else {
                    $renameLog .= "mv $path/$item $path/$item.$attempt 2>/dev/null || true && ";
                }
            endforeach;
        endforeach;
        return $renameLog;
    }

    function tarGzDirectory($dir, $targz_file)
    {
        // Get real path for our folder
        $rootPath = realpath($dir);
        $tar_file = substr($targz_file, 0, -3); //remove .gz part
        if (file_exists($targz_file)) {
            unlink($targz_file);
        }
        if (file_exists($tar_file)) {
            unlink($tar_file);
        }
        $archive = new PharData($tar_file);
        $archive->buildFromDirectory($rootPath); // make path\to\archive\arch1.tar
        $archive->compress(Phar::GZ); // make path\to\archive\arch1.tar.gz
        unlink($tar_file); // deleting path\to\archive\arch1.tar
    }

    function zipDirectory($dir, $zip_file)
    {
        $ret = "";
        // Get real path for our folder
        $rootPath = realpath($dir);
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
        if (!$zip->status == ZIPARCHIVE::ER_OK) {
            $ret = "Failed to write files to zip\n";
        }
        return $ret;
    }


    function execute_ssh_cmd($cmd, $logObj, $log_name, $cmd_name, $profileId, $profileType, $ownerID)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
        $sshcmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect $cmd";
        $ret = $this->execute_cmd($sshcmd, $logObj, $log_name, $cmd_name);
        return $ret;
    }

    function execute_cmd($cmd, $logObj, $log_name, $cmd_name)
    {
        $log = shell_exec($cmd);
        $logObj[$cmd_name] = $cmd;
        $logObj[$log_name] = $log;
        return $logObj;
    }

    function execute_cmd_logfile($cmd, $logObj, $log_name, $cmd_name, $logfile, $mode)
    {
        $log = shell_exec($cmd);
        $logObj[$cmd_name] = $cmd;
        $logObj[$log_name] = $log;
        $file = fopen($logfile, $mode);
        fwrite($file, $cmd . "\n" . $log);
        fclose($file);
        return $logObj;
    }

    function removePipelineGithub($id, $ownerID)
    {
        $sql = "UPDATE $this->db.biocorepipe_save SET github=NULL,last_modified_user ='$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updatePipelineGithub($pipeline_id, $username, $repo, $branch, $commit, $ownerID)
    {
        $obj = array();
        $obj["username"] = $username;
        $obj["repository"] = $repo;
        $obj["branch"] = $branch;
        $obj["commit"] = $commit;
        $github = json_encode($obj);
        $sql = "UPDATE $this->db.biocorepipe_save SET github='$github', last_modified_user ='$ownerID', date_modified=now() WHERE deleted = 0 AND id = '$pipeline_id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }

    function checkNewRelease($version, $ownerID)
    {
        $ret = array();
        $newVer = "false";
        if (!empty($ownerID)) {
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin") {
                $release_cmd = "curl -s https://api.github.com/repos/umms-biocore/dolphinnext/releases/latest 2>&1";
                $ret = $this->execute_cmd($release_cmd, $ret, "release_cmd_log", "release_cmd");
                $rel = json_decode($ret["release_cmd_log"]);
                if (!empty($rel)) {
                    $obj = array();
                    if (isset($rel->tag_name)) {
                        $obj["tag_name"] = $rel->tag_name;
                        //new version check
                        if (version_compare($obj["tag_name"], $version) > 0) {
                            $newVer = "true";
                            if (isset($rel->html_url)) {
                                $obj["html_url"] = $rel->html_url;
                            }
                            if (isset($rel->published_at)) {
                                $obj["published_at"] = $rel->published_at;
                            }
                            if (isset($rel->body)) {
                                $obj["body"] = $rel->body;
                            }
                            $ret["release_cmd_log"] = $obj;
                            $scriptsPath = realpath(__DIR__ . "/../../scripts");
                            $ret["scripts_path"] = $scriptsPath;
                        }
                    }
                }
                if ($newVer == "false") {
                    $ret["release_cmd_log"] = "";
                }
            }
        }
        return json_encode($ret);
    }



    //$type: "downPack" or "pushGithub"
    function initGitRepo($description, $pipeline_id, $pipeline_name, $username_id, $github_repo, $github_branch, $configText, $nfData, $dnData, $type, $ownerID)
    {
        $ret = array();
        $dir_name = !empty($username_id) ? "{$github_repo}_{$github_branch}" : $pipeline_name;
        //create git folder
        $gitDir = "{$this->tmp_path}/git/$ownerID";
        $repoDir = "{$this->tmp_path}/git/$ownerID/$dir_name";
        $zip_file = "{$this->tmp_path}/git/$ownerID/$dir_name.zip";
        $zip_file_public = "{$this->base_path}/tmp/git/$ownerID/$dir_name.zip";
        if (!file_exists($gitDir)) {
            mkdir($gitDir, 0755, true);
        }
        system('rm -f ' . escapeshellarg("$zip_file"), $retval);
        //create empty git repo folder
        if (!file_exists($repoDir)) {
            mkdir($repoDir, 0755, true);
        } else {
            system('rm -rf ' . escapeshellarg("$repoDir"), $retval);
            if ($retval == 0) {
                $ret["clean_repo_dir_log"] = $repoDir . " successfully deleted";
                mkdir($repoDir, 0755, true);
            } else {
                $ret["clean_repo_dir_log"] = $repoDir . " could not deleted->$retval";
                return $ret;
            }
        }

        if ($type == "pushGithub") {
            $git_data = json_decode($this->getGithubbyID($username_id, $ownerID));
            $token = trim($this->amazonDecode($git_data[0]->token));
            $username = $git_data[0]->username;
            $email = $git_data[0]->email;
            $check_repo_cmd = "curl https://api.github.com/repos/$username/$github_repo 2>&1";
            $ret = $this->execute_cmd($check_repo_cmd, $ret, "check_repo_cmd_log", "check_repo_cmd");
            $repo_found = "";
            if (preg_match('/"message": "Not Found"/', $ret["check_repo_cmd_log"])) {
                $repo_found = "false";
            }
            $git_init_cmd = "";

            if ($repo_found == "false") {
                //repo not found, create with curl
                $init_cmd = "curl -H 'Authorization: token $token' https://api.github.com/user/repos -d '{\"name\":\"$github_repo\"}' && cd $repoDir && git init 2>&1";
                $ret = $this->execute_cmd($init_cmd, $ret, "init_cmd_log", "init_cmd");
            } else {
                //repo found, git clone 
                $init_cmd = "git clone https://github.com/$username/{$github_repo}.git $repoDir 2>&1";
                $ret = $this->execute_cmd($init_cmd, $ret, "init_cmd_log", "init_cmd");
            }
            //change branch if required 
            $branch_cmd = "cd $repoDir && git checkout $github_branch || git checkout -b $github_branch 2>&1";
            $ret = $this->execute_cmd($branch_cmd, $ret, "branch_cmd_log", "branch_cmd");
            //Erase all the files in the repo instead of .git directory
            //$clean_cmd = "cd $repoDir && find . -maxdepth 1 -mindepth 1 -not -name '.git' -exec rm -r {} + 2>&1";

            //save files into new repo
            $this->createMultiConfig($repoDir, $configText);
            $this->createDirFile($repoDir, "main.nf", 'w', $nfData);
            $this->createDirFile($repoDir, "main.dn", 'w', $dnData);
            $this->createDirFile($repoDir, "README.md", 'w', $description);
            $date = date("d-m-Y H:i:s", time());

            //push to github
            $push_cmd = "cd $repoDir && git config --local user.name \"$username\" && git config --local user.email \"$email\" && git add . && git commit -m \"$date\" && git  push --porcelain https://{$username}:{$token}@github.com/$username/{$github_repo}.git $github_branch 2>&1";
            $ret = $this->execute_cmd($push_cmd, $ret, "push_cmd_log", "push_cmd");
            //parse commit_id
            //[master 407d677] 01-08-2019 21:08:45
            if (preg_match('/Done/', $ret["push_cmd_log"])) {
                if (preg_match("/\[$github_branch(.*)\] $date/", $ret["push_cmd_log"])) {
                    preg_match("/\[$github_branch(.*)\] $date/", $ret["push_cmd_log"], $match);
                    $block = explode(" ", trim($match[1]));
                    $part_of_commit_id = end($block);
                    if (!empty($part_of_commit_id)) {
                        $get_commit_id_cmd = "cd $repoDir && git config --local user.name \"$username\" && git config --local user.email \"$email\" && git log -1 $part_of_commit_id | head -1 2>&1";
                        $ret = $this->execute_cmd($get_commit_id_cmd, $ret, "get_commit_id_cmd_log", "get_commit_id_cmd");
                        preg_match("/commit(.*)/", $ret["get_commit_id_cmd_log"], $commit_log);
                        if (!empty($commit_log[1])) {
                            $commit_id = $commit_log[1];
                            $ret["commit_id"] = trim($commit_id);
                            $this->updatePipelineGithub($pipeline_id, $username, $github_repo, $github_branch, trim($commit_id), $ownerID);
                        }
                    }
                }
            }
        }
        if ($type == "downPack") {
            $this->createMultiConfig($repoDir, $configText);
            $this->createDirFile($repoDir, "main.nf", 'w', $nfData);
            $this->createDirFile($repoDir, "main.dn", 'w', $dnData);
            $this->createDirFile($repoDir, "README.md", 'w', $description);
            $ret["zip_log"] = $this->zipDirectory($repoDir, $zip_file);
            $ret["zip_file"] = $zip_file_public;
        }
        system('rm -rf ' . escapeshellarg("$repoDir"), $retval);
        foreach ($ret as $key => $val) {
            if (!empty($token)) {
                $valClean = str_replace($token, "****", $val);
                $ret[$key] = $valClean;
            }
        }
        return json_encode($ret);
    }

    function getAmazonVariables($amazon_cre_id, $ownerID)
    {
        $configText = "";
        if (!empty($amazon_cre_id)) {
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach ($amz_data as $d) {
                $access = $d->amz_acc_key;
                $d->amz_acc_key = trim($this->amazonDecode($access));
                $secret = $d->amz_suc_key;
                $d->amz_suc_key = trim($this->amazonDecode($secret));
            }
            $access_key = $amz_data[0]->{'amz_acc_key'};
            $secret_key = $amz_data[0]->{'amz_suc_key'};
            $default_region = $amz_data[0]->{'amz_def_reg'};
            $configText = "export AWS_ACCESS_KEY_ID=$access_key\nexport AWS_SECRET_ACCESS_KEY=$secret_key\nexport AWS_DEFAULT_REGION=$default_region";
        }
        return $configText;
    }

    function getBashVariables($profileId, $profileType, $ownerID)
    {
        $configText = "";
        $bash_variable = "";
        if ($profileType == 'cluster') {
            $cluData = $this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            $bash_variable = $cluDataArr[0]["bash_variable"];
            $bash_variable = trim($this->amazonDecode($bash_variable));
        } else if ($profileType == 'amazon' || $profileType == 'google') {
            $cluData = $this->getProfileCloudbyID($profileId, $profileType, $ownerID);
            $cluDataArr = json_decode($cluData, true);
            $bash_variable = $cluDataArr[0]["bash_variable"];
            $bash_variable = trim($this->amazonDecode($bash_variable));
        }
        $bash_variable = htmlspecialchars_decode($bash_variable, ENT_QUOTES);
        $bash_variables = explode("\n", $bash_variable);
        $exported_variables = preg_filter('/^/', 'export ', $bash_variables);
        $configText = join("\n", $exported_variables);
        return $configText;
    }

    //{{DNEXT_PUBLISH_DIR}} {{DNEXT_LAB}} {{DNEXT_WEB_RUN_DIR}} {{DNEXT_WEB_REPORT_DIR}}
    function replaceDnextVariables($dir, $uuid, $proPipeAll, $ownerID)
    {
        $DNEXT_WEB_REPORT_DIR = "{$this->pubweb_url}/$uuid/pubweb";
        $DNEXT_WEB_RUN_DIR = "{$this->pubweb_url}/$uuid/run";
        $DNEXT_PUBLISH_DIR = $this->getReportDir($proPipeAll);
        $project_pipeline_id = $proPipeAll[0]->{'id'};
        $DNEXT_RUN_URL = "{$this->base_path}/index.php?np=3&id=" . $project_pipeline_id;
        $userData = json_decode($this->getUserById($ownerID))[0];
        $username = $userData->{'username'};
        $email = $userData->{'email'};
        $lab = $userData->{'lab'};
        $allvars = array();
        $allvars["DNEXT_RUN_URL"] =  $this->escapeRegex($DNEXT_RUN_URL);
        error_log($DNEXT_RUN_URL);
        error_log($allvars["DNEXT_RUN_URL"]);
        $allvars["DNEXT_WEB_REPORT_DIR"] =  $this->escapeRegex($DNEXT_WEB_REPORT_DIR);
        $allvars["DNEXT_WEB_RUN_DIR"] =  $this->escapeRegex($DNEXT_WEB_RUN_DIR);
        $allvars["DNEXT_PUBLISH_DIR"] =  $this->escapeRegex($DNEXT_PUBLISH_DIR);
        $allvars["DNEXT_LAB"] = $this->escapeRegex($lab);
        $allvars["DNEXT_USERNAME"] = $this->escapeRegex($username);
        $allvars["DNEXT_EMAIL"] = $this->escapeRegex($email);
        foreach ($allvars as $var => $newVal) :
            if (!empty($var) && !empty($newVal)) {
                $cmd = "cd $dir && grep -rl \"{{{$var}}}\" . | xargs sed -i 's/{{{$var}}}/$newVal/g' 2> /dev/null";
                shell_exec($cmd);
            }
        endforeach;
    }

    //nextflow config tag and label separated: \n//~@:~\n@~:"filename"\n//~@:~\ntext
    //Use createMultiConfig function to parse and save into run folder
    function createMultiConfig($dir, $allConf)
    {
        //if empty or null, then show as empty nextflow.config
        $filename = "nextflow.config";
        $this->createDirFile($dir, $filename, "w", "");
        if (!empty($allConf)) {
            $sep    = "\n//~@:~\n";
            $lines = explode($sep, $allConf);
            $filename = "";
            $checkLabel = "false";
            for ($i = 0; $i < count($lines); $i++) {
                if (preg_match("/@~:\"(.*)\"/", $lines[$i])) {
                    //initiate sub config
                    preg_match("/@~:\"(.*)\"/", $lines[$i], $match);
                    if (!empty($match[1]) && isset($lines[$i + 1])) {
                        $publishDir = $dir . "/" . $match[1];
                        $block = explode("/", $publishDir);
                        $filename = end($block);
                        //remove last item and join with "/"
                        array_pop($block);
                        $publishDir = join("/", $block);
                        $writeType = "w";
                        if ($filename == "nextflow.config") {
                            $writeType = "a";
                        }
                        $this->createDirFile($publishDir, $filename, $writeType, $lines[$i + 1]); //empty file
                        $checkLabel = "true";
                        continue;
                    }
                } else {
                    //getMainRunConfig function might append or prepend nextflow.config text.
                    if ($i == 0 || $i == count($lines) - 1) {
                        $filename = "nextflow.config";
                        $this->createDirFile($dir, $filename, "a", $lines[$i]);
                    }
                }
            }
            //if header info is not found, then show as nextflow.config
            if ($checkLabel == "false") {
                $filename = "nextflow.config";
                $this->createDirFile($dir, $filename, "w", $allConf);
            }
        }
    }

    function recurse_copy($source, $dest)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $this->recurse_copy("$source/$entry", "$dest/$entry");
        }
        $dir->close();
        return true;
    }

    function triggerRunErr($message, $uuid, $project_pipeline_id, $ownerID)
    {
        $this->writeLog($uuid, $message, 'a', 'serverlog.txt');
        if (!empty($project_pipeline_id)) {
            $this->updateRunLog($project_pipeline_id, "Error", "", $ownerID);
            $this->updateRunStatus($project_pipeline_id, "Error", $ownerID);
        }
        die(json_encode($message));
    }

    function addRunNotes($uuid, $ownerID)
    {
        if (!empty($uuid)) {
            $targetDir = "{$this->run_path}/$uuid/pubweb/_Description";
            //if not _Description not created before, create only for once
            if (!file_exists($targetDir)) {
                $this->createReadmeMD($uuid);
                return json_encode("file created");
            }
        }
        return json_encode("file exist");
    }
    function createReadmeMD($uuid)
    {
        $this->createDirFile("{$this->run_path}/$uuid/pubweb/_Description", "README.md", 'w', "#### **Run Description**\n\nYou can use this space for adding notes about your run such as its aims, experimental context, and any other ideas that you’d like to share with your group members. We support <a style=\"color:#1479cc;\" href=\"https://guides.github.com/features/mastering-markdown/\" target=\"_blank\">Markdown</a> for styling and formatting your notes.\n\nTo start editing this text, click **Edit Markdown** <i style=\"font-size: 14px;\" class=\"fa fa-pencil-square-o\"></i> icon on the right.\n\nYou can also upload additional files such as images, PDFs, Excel docs, etc. Please click the **Add File** <i style=\"font-size: 14px;\" class=\"fa fa-plus\"></i> icon on the left to upload your files.\n\n</br>\nHere are some examples for markdown format:\n\na) Lists\n\n* Star used for unordered list.\n* if you have sub points, put tab before the star\n\t* Like this\n\n1. You can user numbers for ordered list\n2. Like this\n\nb) Images\n\n![rnaseq](https://dolphinnext.umassmed.edu/public/images/stranded_rnaseq.png)\n\nc) Code Blocks\n\n```\nplot(gene)\n```\n\nd) Tables\n\nHeader 1 | Header 2\n-------- | --------\nCell 1   | Cell 2\nCell 3   | Cell 4\n");
    }

    function createCopyReadmeMD($attempt, $uuid, $project_pipeline_id)
    {
        settype($attempt, 'integer');
        $succ = 0;
        if ($attempt > 1) {
            $run_log_data = $this->getRunLog($project_pipeline_id, "default");
            $run_logs = json_decode($run_log_data);
            $log_count = count($run_logs);
            if ($log_count > 1) {
                $prev_uuid = $run_logs[$log_count - 2]->{'run_log_uuid'};
                $prevDir = "{$this->run_path}/$prev_uuid/pubweb/_Description";
                $targetDir = "{$this->run_path}/$uuid/pubweb/_Description";
                if (file_exists($prevDir)) {
                    $this->recurse_copy($prevDir, $targetDir);
                    $succ = 1;
                }
            }
        }
    }

    function createInitialRunConfigFiles($project_pipeline_id, $uuid, $ownerID)
    {
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $collection = array();
        $file_name = array();
        $file_dir = array();
        $file_type = array();
        $files_used = array();
        $archive_dir = array();
        $s3_archive_dir = array();
        $gs_archive_dir = array();
        $collection_type = array();
        foreach ($allinputs as $inputitem) :
            $collection_id = $inputitem->{'collection_id'};
            if (!empty($collection_id)) {
                $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                foreach ($allfiles as $fileData) :
                    $collection[] = $collection_id;
                    $file_name[] = $fileData->{'name'};
                    $file_dir[] = $fileData->{'file_dir'};
                    $file_type[] = $fileData->{'file_type'};
                    $files_used[] = $fileData->{'files_used'};
                    $archive_dir[] = $fileData->{'archive_dir'};
                    $s3_archive_dir[] = $fileData->{'s3_archive_dir'};
                    $gs_archive_dir[] = $fileData->{'gs_archive_dir'};
                    $collection_type[] = $fileData->{'collection_type'};
                endforeach;
            }
        endforeach;

        $paramFileAr = array($collection, $file_name, $file_dir, $file_type, $files_used, $archive_dir, $s3_archive_dir, $gs_archive_dir, $collection_type);
        $paramNameFileAr = array("collection", "file_name", "file_dir", "file_type", "files_used", "archive_dir", "s3_archive_dir", "gs_archive_dir", "collection_type");
        for ($i = 0; $i < count($paramNameFileAr); $i++) {
            $this->createDirFile("{$this->run_path}/$uuid/run/initialrun", "." . $paramNameFileAr[$i], 'w', "'" . implode("','", $paramFileAr[$i]) . "'");
        }
    }

    function getOptionalProcessParameter($id)
    {
        $sql = "SELECT COUNT(pp.parameter_id) as opt_input_count
                FROM $this->db.process_parameter pp
                WHERE pp.process_id = '$id' and pp.type = 'input' and pp.optional = 'true'";
        return self::queryTable($sql);
    }

    // get the maximum optional input number for the processes of the pipeline
    function getMaxOptionalInputNum($pipeline_id, $ownerID)
    {
        $pipe = $this->loadPipeline($pipeline_id, $ownerID);
        $pipe_obj = json_decode($pipe, true);
        $max = 0;
        if (!empty($pipe_obj[0])) {
            $process_list = $pipe_obj[0]["process_list"];
            if (!empty($process_list)) {
                $process_list_ar = explode(",", $process_list);
                for ($i = 0; $i < count($process_list_ar); $i++) {
                    $proID = $process_list_ar[$i];
                    $opt = $this->getOptionalProcessParameter($proID);
                    $opt_obj = json_decode($opt, true);
                    $opt_input_count = $opt_obj[0]["opt_input_count"];
                    settype($opt_input_count, 'integer');
                    if (!empty($opt_input_count) && $opt_input_count > $max) {
                        $max = $opt_input_count;
                    }
                }
            }
        }
        return $max;
    }

    function initTestRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $amzBashConfigText, $attempt, $runType, $ownerID)
    {
        //create files and folders
        $this->createDirFile("{$this->run_path}/$uuid/run", "nextflow.nf", 'w', $nextText);
        // Dummy file to be used as an optional input where required
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        $optionalInputNum = $this->getMaxOptionalInputNum($pipeline_id, $ownerID);
        for ($i = 1; $i < $optionalInputNum + 1; $i++) {
            $this->createDirFile("{$this->run_path}/$uuid/run/.emptyfiles", "NO_FILE_$i", 'w', "");
        }
        //separate nextflow config (by using @config tag).
        $this->createMultiConfig("{$this->run_path}/$uuid/run", $mainConfigText);

        // replace DNEXT global variables
        $this->replaceDnextVariables("{$this->run_path}/$uuid/run", $uuid, $proPipeAll, $ownerID);

        //create clean serverlog.txt 
        $this->writeLog($uuid, '', 'w', 'serverlog.txt');
        $run_path_real = "{$this->run_path}/$uuid/run";

        if (!file_exists($run_path_real . "/nextflow.nf")) {
            $this->triggerRunErr('ERROR: Nextflow file is not found in server!', $uuid, $project_pipeline_id, $ownerID);
        }
        if (!file_exists($run_path_real . "/nextflow.config")) {
            $this->triggerRunErr('ERROR: Nextflow config file is not found!', $uuid, $project_pipeline_id, $ownerID);
        }

        //get nextflow executor parameters
        list($dolphin_path_real, $dolphin_publish_real, $proPipeCmd, $jobname, $imageCmd, $initImageCmd, $reportOptions) = $this->getNextExecParam($proPipeAll, $project_pipeline_id, $profileType, $profileId, $initialRunParams, $ownerID);

        //get username and hostname and exec info for connection
        list($connect, $next_path, $profileCmd, $executor, $next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job, $ssh_id, $ssh_port) = $this->getNextConnectExec($profileId, $ownerID, $profileType);
        //get cmd before run
        $downCacheCmd = $this->getDownCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType);
        $preCmd = $this->getPreCmd($profileType, $profileCmd, $proPipeCmd, $imageCmd, $initImageCmd, $downCacheCmd, $run_path_real, $dolphin_path_real, $attempt, $proPipeAll);
        $next_path_real = $this->getNextPathReal($next_path); //eg. /project/umw_biocore/bin
        $postCmd = $this->getPostCmd($proPipeAll, $dolphin_path_real, $dolphin_publish_real, $profileType, $executor_job, $run_path_real);


        //get command for renaming previous log file
        $renameLog = $this->getRenameCmd($dolphin_path_real, $attempt);
        $createUUID = $this->createUUIDCmd($dolphin_path_real, $uuid);
        $exec_next_all = $this->getExecNextAll($proPipeAll, $executor, $dolphin_path_real, $dolphin_publish_real, $next_path_real, $next_queue, $next_cpu, $next_time, $next_memory, $jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId, $profileType, "log.txt", $initialRunParams, $postCmd, $preCmd, $run_path_real, $ownerID);
        $amzCmd = "";
        //temporarily copy s3/gs config file into initialrun folder 
        if (!empty($getCloudConfigFileDir)) {
            $this->recurse_copy($getCloudConfigFileDir, $run_path_real . "/initialrun");
        }
        //.cred file to export credentials to remote machine
        if (!empty($amzBashConfigText)) {
            $this->createDirFile($run_path_real, ".cred", 'w', $amzBashConfigText);
            $amzCmd = "source $dolphin_path_real/.cred && rm $dolphin_path_real/.cred && ";
        }
        //create run cmd file (.dolphinnext.init)
        $runCmdAll = "$amzCmd $renameLog $createUUID $exec_next_all";
        $this->createDirFile($run_path_real, ".dolphinnext.init", 'w', $runCmdAll);

        // compress run folder
        $targz_file = $run_path_real . ".tar.gz";
        $this->tarGzDirectory($run_path_real, $targz_file);
        // remove credentials from run folder after compressing run folder
        if (file_exists($run_path_real . "/initialrun")) {
            system('rm -rf ' . escapeshellarg($run_path_real . "/initialrun") . "/.conf*", $retval);
        }
        // remove .cred file after compressing run folder
        if (file_exists($run_path_real . "/.cred")) {
            unlink($run_path_real . "/.cred");
        }
        // save $targz_file into $run_template_dir
        $run_arch_file = $this->getServerRunTemplateFile($project_pipeline_id);
        $run_tmp_dir = $this->getServerRunTemplateDir();
        if (!file_exists($run_tmp_dir)) {
            mkdir($run_tmp_dir, 0700, true);
        }
        copy($targz_file, $run_arch_file);
        return array($targz_file, $dolphin_path_real, $runCmdAll);
    }

    function initRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $amzBashConfigText, $attempt, $runType, $ownerID)
    {
        //create files and folders
        $this->createDirFile("{$this->run_path}/$uuid/run", "nextflow.nf", 'w', $nextText);
        // Dummy file to be used as an optional input where required
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        $optionalInputNum = $this->getMaxOptionalInputNum($pipeline_id, $ownerID);
        for ($i = 1; $i < $optionalInputNum + 1; $i++) {
            $this->createDirFile("{$this->run_path}/$uuid/run/.emptyfiles", "NO_FILE_$i", 'w', "");
        }
        //create Run Description
        $this->createCopyReadmeMD($attempt, $uuid, $project_pipeline_id);
        //separate nextflow config (by using @config tag).
        $this->createMultiConfig("{$this->run_path}/$uuid/run", $mainConfigText);
        // replace DNEXT global variables
        $this->replaceDnextVariables("{$this->run_path}/$uuid/run", $uuid, $proPipeAll, $ownerID);

        //create clean serverlog.txt 
        $this->writeLog($uuid, '', 'w', 'serverlog.txt');
        $run_path_real = "{$this->run_path}/$uuid/run";
        if (!empty($initialRunParams)) {
            $this->createDirFile("{$this->run_path}/$uuid/run/initialrun", "nextflow.config", 'w', $initialConfigText);
            copy("{$this->nf_path}/initialrun.nf", "{$this->run_path}/$uuid/run/initialrun/nextflow.nf");
            $this->createInitialRunConfigFiles($project_pipeline_id, $uuid, $ownerID);
        }
        if (!file_exists($run_path_real . "/nextflow.nf")) {
            $this->triggerRunErr('ERROR: Nextflow file is not found in server!', $uuid, $project_pipeline_id, $ownerID);
        }
        if (!file_exists($run_path_real . "/nextflow.config")) {
            $this->triggerRunErr('ERROR: Nextflow config file is not found!', $uuid, $project_pipeline_id, $ownerID);
        }

        //get nextflow executor parameters
        list($dolphin_path_real, $dolphin_publish_real, $proPipeCmd, $jobname, $imageCmd, $initImageCmd, $reportOptions) = $this->getNextExecParam($proPipeAll, $project_pipeline_id, $profileType, $profileId, $initialRunParams, $ownerID);

        //get username and hostname and exec info for connection
        list($connect, $next_path, $profileCmd, $executor, $next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job, $ssh_id, $ssh_port) = $this->getNextConnectExec($profileId, $ownerID, $profileType);
        //get cmd before run
        $downCacheCmd = $this->getDownCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType);
        $preCmd = $this->getPreCmd($profileType, $profileCmd, $proPipeCmd, $imageCmd, $initImageCmd, $downCacheCmd, $run_path_real, $dolphin_path_real, $attempt, $proPipeAll);
        $next_path_real = $this->getNextPathReal($next_path); //eg. /project/umw_biocore/bin
        $postCmd = $this->getPostCmd($proPipeAll, $dolphin_path_real, $dolphin_publish_real, $profileType, $executor_job, $run_path_real);


        //get command for renaming previous log file
        $renameLog = $this->getRenameCmd($dolphin_path_real, $attempt);
        $createUUID = $this->createUUIDCmd($dolphin_path_real, $uuid);
        $exec_next_all = $this->getExecNextAll($proPipeAll, $executor, $dolphin_path_real, $dolphin_publish_real, $next_path_real, $next_queue, $next_cpu, $next_time, $next_memory, $jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId, $profileType, "log.txt", $initialRunParams, $postCmd, $preCmd, $run_path_real, $ownerID);
        $amzCmd = "";
        //temporarily copy s3/gs config file into initialrun folder 
        if (!empty($getCloudConfigFileDir)) {
            $this->recurse_copy($getCloudConfigFileDir, $run_path_real . "/initialrun");
        }
        //.cred file to export credentials to remote machine
        if (!empty($amzBashConfigText)) {
            $this->createDirFile($run_path_real, ".cred", 'w', $amzBashConfigText);
            $amzCmd = "source $dolphin_path_real/.cred && rm $dolphin_path_real/.cred && ";
        }
        //create run cmd file (.dolphinnext.init)
        $runCmdAll = "$amzCmd $renameLog $createUUID $exec_next_all";
        $this->createDirFile($run_path_real, ".dolphinnext.init", 'w', $runCmdAll);

        // compress run folder
        $targz_file = $run_path_real . ".tar.gz";
        $this->tarGzDirectory($run_path_real, $targz_file);
        // remove credentials from run folder after compressing run folder
        if (file_exists($run_path_real . "/initialrun")) {
            system('rm -rf ' . escapeshellarg($run_path_real . "/initialrun") . "/.conf*", $retval);
        }
        // remove .cred file after compressing run folder
        if (file_exists($run_path_real . "/.cred")) {
            unlink($run_path_real . "/.cred");
        }
        // save $targz_file into $run_template_dir
        $run_arch_file = $this->getServerRunTemplateFile($project_pipeline_id);
        $run_tmp_dir = $this->getServerRunTemplateDir();
        if (!file_exists($run_tmp_dir)) {
            mkdir($run_tmp_dir, 0700, true);
        }
        copy($targz_file, $run_arch_file);
        return array($targz_file, $dolphin_path_real, $runCmdAll);
    }

    function runCmd($project_pipeline_id, $profileType, $profileId, $uuid, $targz_file, $dolphin_path_real, $runCmdAll, $ownerID)
    {
        $ret = array();
        // get scp port
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
        if (!file_exists($userpky)) {
            $this->triggerRunErr('ERROR: Private key is not found!', $uuid, $project_pipeline_id, $ownerID);
        }
        $run_path_real = $this->getServerRunPath($uuid);
        // 1. Mkdir $dolphin_path_real
        $mkdir_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"mkdir -p $dolphin_path_real && echo 'INFO: Run directory created.'\" 2>&1";
        $ret = $this->execute_cmd_logfile($mkdir_cmd, $ret, "mkdir_cmd_log", "mkdir_cmd", "$run_path_real/serverlog.txt", "a");
        if (!preg_match("/INFO: Run directory created\./", $ret["mkdir_cmd_log"])) {
            $this->triggerRunErr('ERROR: Run directory cannot be created.\nLOG: ' . $ret["mkdir_cmd_log"], $uuid, $project_pipeline_id, $ownerID);
        }
        // 2. rsync $targz_file
        $rsync_cmd = "rsync -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $targz_file $connect:$dolphin_path_real  2>&1";
        $ret = $this->execute_cmd_logfile($rsync_cmd, $ret, "scp_cmd_log", "scp_cmd", "$run_path_real/serverlog.txt", "a");
        // 3. remove local $targz_file after transfer (if this command couldn't executed, cronjob will remove it->cleanTempDir)
        if (file_exists($targz_file)) {
            unlink($targz_file);
        }
        // 4. check $targz_file
        $package_exist_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \" test  -f '$dolphin_path_real/run.tar.gz' && echo 'INFO: Run package exists.'\" 2>&1";
        $ret = $this->execute_cmd_logfile($package_exist_cmd, $ret, "package_exist_cmd_log", "package_exist_cmd", "$run_path_real/serverlog.txt", "a");
        if (!preg_match("/INFO: Run package exists\./", $ret["package_exist_cmd_log"])) {
            $this->triggerRunErr('ERROR: Run directory cannot be transfered.\nLOG: ' . $ret["package_exist_cmd_log"], $uuid, $project_pipeline_id, $ownerID);
        }
        // 4. extract and execute 
        $exec_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"source /etc/profile && tar xf $dolphin_path_real/run.tar.gz -C $dolphin_path_real && rm $dolphin_path_real/run.tar.gz && bash $dolphin_path_real/.dolphinnext.init\" >> $run_path_real/serverlog.txt 2>&1 & echo $! &";
        $this->writeLog($uuid, $exec_cmd, 'a', 'serverlog.txt');
        $this->writeLog($uuid, $runCmdAll, 'a', 'serverlog.txt');
        $next_submit_pid = shell_exec($exec_cmd); //"Job <203477> is submitted to queue <long>.\n"
        if (!$next_submit_pid) {
            $this->triggerRunErr('ERROR: Connection failed! Please check your connection profile or internet connection.', $uuid, $project_pipeline_id, $ownerID);
        }
        $ret['next_submit_pid'] = $next_submit_pid;
        $this->updateRunLog($project_pipeline_id, "Waiting", "", $ownerID);
        $this->updateRunStatus($project_pipeline_id, "Waiting", $ownerID);
        return json_encode($ret);
    }

    function getManualRunCmd($targz_file, $uuid, $dolphin_path_real)
    {
        $ret = array();
        if (!empty($targz_file)) {
            $targz_file_public = "{$this->base_path}/tmp/pub/$uuid/run.tar.gz";
            $ret["manualRunCmd"] = "mkdir -p $dolphin_path_real && cd $dolphin_path_real && rm -f run.tar.gz && wget $targz_file_public && tar xf run.tar.gz && rm run.tar.gz && bash .dolphinnext.init";
            $this->writeLog($uuid, "RUN COMMAND:\n" . $ret["manualRunCmd"], 'a', 'serverlog.txt');
        }
        return json_encode($ret);
    }

    function getInputParamContent($input, $inputParamName, $channelName, $mateExist)
    {
        $qualifier = $input["qualifier"];
        $test = trim(urldecode($input["test"]));
        $firstChar = substr($test, 0, 1);
        $parameter_name = $input["parameter_name"];
        $input_name =  urldecode($input["name"]);
        $channelFormat = "";
        $secPartTemp = "";
        //check if input has glob(*,?{,}) characters -> use in the file section
        $checkRegex = false;



        if (preg_match('/(\{|\*|\?|\})/', $test)) $checkRegex = true;
        if ($qualifier === "file") {
            if ($checkRegex === false) {
                $channelFormat = "f1";
                //g_18_genome_url_g_17 = params.inputparam && file(params.inputparam, type: 'any').exists() ? file(params.inputparam, type: 'any') : ch_empty_file_3
                $secPartTemp = $channelName . " = file(params.{$inputParamName}, type: 'any')\n";
            } else if ($checkRegex === true) {
                $channelFormat = "f2";
                $secPartTemp = "Channel.fromPath(params.{$inputParamName}, type: 'any').set{{$channelName}}\n";
            }
        } else if ($qualifier === "set") {
            //if mate defined in process use fromFilePairs
            if ($mateExist === true) {
                $channelFormat = "f3";
                $secPartTemp = "Channel\n\t.fromFilePairs( params.{$inputParamName} , size: params.mate == \"single\" ? 1 : params.mate == \"pair\" ? 2 : params.mate == \"triple\" ? 3 : params.mate == \"quadruple\" ? 4 : -1 )\n\t.ifEmpty { error \"Cannot find any {$parameter_name} matching: \${params.{$inputParamName}}\" }\n\t.set{{$channelName}}\n\n";
                //if mate not defined in process use fromPath
            } else {
                //if val(name), file(read) format -> turn into set input
                if (preg_match('/.*val\(.*\).*file\(.*\).*/', $input_name)) {
                    $channelFormat = "f4";
                    $secPartTemp = "Channel.fromPath(params.{$inputParamName}, type: 'any').map{ file -> tuple(file.baseName, file) }.set{{$channelName}}\n";
                    //or other formats eg. file(fastq1), file(fastq2), file(fastq3)    
                } else {
                    $channelFormat = "f5";
                    $secPartTemp = "Channel.fromPath(params.{$inputParamName}, type: 'any').toSortedList().set{{$channelName}}\n";
                }
            }
            if ($firstChar == "[") {
                // check if any file path defined as test value
                // params.input2 = [["file1", "contr", ["/home/c_rep1.1.fastq","/home/c_rep1.2.fastq"]]]
                // Channel.from(params.input2).map{ row-> tuple(row[0], row[1], tuple(file(row[2][0]), file(row[2][1])) ) }.set{channel2}
                // params.input2 = [["file1", "contr", "/home/c_rep1.2.fastq"]]
                // Channel.from(params.input2).map{ row-> tuple(row[0], row[1], file(row[2]))}.set{channel2}
                // Channel.from(params.input2).map{ row->  prepChannel(row) }.set{channel2}
                $secPartTemp = "Channel.from(params.{$inputParamName}).map{ row-> prepChannel(row) }.set{{$channelName}}\n";
            }
        } else if ($qualifier === "val") {
            $channelFormat = "f6";
            $secPartTemp = "Channel.value(params.{$inputParamName}).set{{$channelName}}\n";
        }
        return $secPartTemp;
    }

    function getOperatorText($in)
    {
        $inputOperatorText = '';
        $operator = !empty($in["operator"]) ? urldecode($in["operator"]) : "";
        $closure = !empty($in["closure"]) ? urldecode($in["closure"]) : "";
        if ($operator === 'mode flatten') {
            $inputOperatorText = ' ' . $operator + $closure;
        } else if (!empty($operator)) {
            if (!empty($closure)) {
                $inputOperatorText = '.' . $operator .  $closure;
            } else {
                $inputOperatorText = '.' . $operator . "()";
            }
        }
        return $inputOperatorText;
    }

    function createTestNextflowNF($inputs, $outputs, $code, $profileId, $profileType, $ownerID)
    {
        $script = urldecode($code["script"]);
        $pro_header = $code["pro_header"];
        $pro_footer = $code["pro_footer"];
        $pipe_header = $code["pipe_header"];
        $test_params = $code["test_params"];
        $pipe_footer = $code["pipe_footer"];
        $input_count = count($inputs);
        $output_count = count($outputs);

        $nextflow = "";
        list($hostVar, $variable) = $this->getConfigHostnameVariable($profileId, $profileType, $ownerID);
        $nextflow .= "\$HOSTNAME='" . $hostVar . "'\n";
        if (!empty($test_params)) {
            $nextflow .= urldecode($test_params) . "\n\n";
        }
        if (!empty($pipe_header)) {
            $nextflow .= urldecode($pipe_header) . "\n\n";
        }
        // method: file-fromPath(''), value-(), set-of([1, 'alpha'], [2, 'beta']), each-[5,10]
        $nextflow .= 'def prepChannel(row){
            def ret = []
            for(i in 0..row.size-1) {
              if (row[i] instanceof String && (row[i].trim().substring(0, 1) == "/" || row[i].trim().substring(0, 3) == "s3:")){
                  ret[i]= file(row[i])
              } else if (row[i] instanceof List) {
                  ret[i]= prepChannel(row[i])
              } else {
                  ret[i]= row[i]
              }
            }
            return tuple(ret)
          }' . "\n\n";

        //Input parameters and channels
        $inChn_firstPart = "";
        $inChn_secondPart = "";
        $mateExist = false;
        for ($i = 0; $i < $input_count; $i++) {
            if ($inputs[$i]["parameter_name"] == "mate") {
                $mateExist = true;
            }
        }

        for ($i = 0; $i < $input_count; $i++) {
            $num = $i + 1;
            $inputParamName = "input{$num}";
            if ($inputs[$i]["parameter_name"] == "mate") {
                $inputParamName = "mate";
            }
            $channelName = "channel{$num}";
            $test = trim(urldecode($inputs[$i]["test"]));
            $firstChar = substr($test, 0, 1);
            if ($firstChar == "[") {
                $inChn_firstPart .= "params.{$inputParamName} = [{$test}] \n";
            } else {
                $inChn_firstPart .= "params.{$inputParamName} = \"{$test}\" \n";
            }
            $inChn_secondPart .= $this->getInputParamContent($inputs[$i], $inputParamName, $channelName, $mateExist);
        }
        $nextflow .= $inChn_firstPart . $inChn_secondPart . "\n";

        $nextflow .= "process test {\n";
        for ($i = 0; $i < $input_count; $i++) {
            $num = $i + 1;
            $inputOperatorText = $this->getOperatorText($inputs[$i]);
            if ($i === 0) $nextflow .= "\tinput:\n";
            $channelName = "channel{$num}";
            $nextflow .= "\t" . $inputs[$i]["qualifier"] . " " . urldecode($inputs[$i]["name"]) . " from " . $channelName . $inputOperatorText . "\n";
        }

        for ($i = 0; $i < $output_count; $i++) {
            $num = $i + 1;
            $channelName = "result{$num}";
            $operatorText = $this->getOperatorText($outputs[$i]);
            if ($i === 0) $nextflow .= "\n\toutput:\n";
            $nextflow .= "\t" . $outputs[$i]["qualifier"] . " " . urldecode($outputs[$i]["name"]) . " into " . $channelName . $operatorText . "\n";
        }
        //insert """ for script if not exist
        if (strpos($script, '"""') === false && strpos($script, "'''") === false && strpos($script, 'when:') === false && strpos($script, 'script:') === false && strpos($script, 'shell:') === false && strpos($script, 'exec:') === false) {
            $script = "\"\"\"\n" . $script . "\n\"\"\"";
        }
        $nextflow .= $script . "\n}\n";
        for ($i = 0; $i < $output_count; $i++) {
            $num = $i + 1;
            $output_test = "";
            $output_parameter_name = !empty($outputs[$i]["parameter_name"]) ? urldecode($outputs[$i]["parameter_name"]) : "";
            $output_qualifier = !empty($outputs[$i]["qualifier"]) ? $outputs[$i]["qualifier"] : "";
            if (!empty($output_parameter_name) && !empty($output_qualifier)) {
                $output_test = "{$output_parameter_name}({$output_qualifier})";
            }
            $output_test_text = !empty($output_test) ? "##Output-{$num}:$output_test\\n" : "";

            $nextflow .= "result" . ($i + 1) . ".subscribe { println \"$output_test_text##Received:\$it\\n\" }\n";
        }

        if (!empty($pipe_footer)) {
            $nextflow .= urldecode($pipe_footer) . "\n";
        }
        $nextflow .= "workflow.onComplete {\n\tprintln \"##Pipeline execution summary##\"";
        $nextflow .= "\n\tprintln \"----------------------------\"";
        $nextflow .= "\n\tprintln \"##Completed at: \${workflow.complete}\"";
        $nextflow .= "\n\tprintln \"##Duration: \${workflow.duration}\"";
        $nextflow .= "\n\tprintln \"##Success: \${workflow.success ? 'OK' : 'FAILED' }\"";
        $nextflow .= "\n\tprintln \"##Exit status: \${workflow.exitStatus}\"";
        $nextflow .= "\n}\n";
        return $nextflow;
    }

    //            inputs: [],
    //            outputs: [],
    //            code: {
    //                pro_header: "",
    //                pro_footer: "",
    //                pipe_header: "",
    //                pipe_footer: "",
    //                script: "",
    //                test_params: ""
    //            },
    //            env: {
    //                test_env: "",
    //                test_work_dir: "",
    //                singu_check: "",
    //                docker_check: "",
    //                singu_img: "",
    //                docker_img: "",
    //                singu_opt: "",
    //                docker_opt: "",
    //                pipeline_id: "",    
    //                process_id: ""   
    //        }
    function saveTestRun($inputs, $outputs, $code, $env, $ownerID)
    {
        $profile = $env['test_env'];
        $profileAr = explode("-", $profile);
        $profileType = $profileAr[0];
        $profileId = isset($profileAr[1]) ? $profileAr[1] : "";

        $test_work_dir = $env['test_work_dir'];
        $singu_check = $env['singu_check'] == "1" ? 'true' : 'false';
        $docker_check = $env['docker_check'] == "1" ? 'true' : 'false';
        $singu_img = $env['singu_img'];
        $docker_img = $env['docker_img'];
        $singu_opt = $env['singu_opt'];
        $docker_opt = $env['docker_opt'];
        $pipeline_id = $env['pipeline_id'];
        $process_id = $env['process_id'];
        $nextText = $this->createTestNextflowNF($inputs, $outputs, $code, $profileId, $profileType, $ownerID);
        $proPipeAll = array();
        $object = new stdClass();
        $proPipeAll[] = $object;
        $proPipeAll[0]->{'docker_check'} = $docker_check;
        $proPipeAll[0]->{'singu_check'} = $singu_check;
        $proPipeAll[0]->{'docker_img'} = $docker_img;
        $proPipeAll[0]->{'singu_img'} = $singu_img;
        $proPipeAll[0]->{'singu_save'} = "false";
        $proPipeAll[0]->{'docker_opt'} = $docker_opt;
        $proPipeAll[0]->{'singu_opt'} = $singu_opt;
        $proPipeAll[0]->{'id'} = "";
        $proPipeAll[0]->{'output_dir'} = $test_work_dir;
        $proPipeAll[0]->{'publish_dir_check'} = "";
        $proPipeAll[0]->{'publish_dir'} = "";
        $proPipeAll[0]->{'exec_all_settings'} = "";
        $proPipeAll[0]->{'exec_all'} = "false";
        $proPipeAll[0]->{'exec_each'} = "false";
        $proPipeAll[0]->{'exec_each_settings'} = "";
        $proPipeAll[0]->{'pipeline_id'} = $pipeline_id;
        $proPipeAll[0]->{'cmd'} = "";
        $proPipeAll[0]->{'pp_name'} = "process_test";
        $proPipeAll[0]->{'withReport'} = "";
        $proPipeAll[0]->{'withTrace'} = "";
        $proPipeAll[0]->{'withTimeline'} = "";
        $proPipeAll[0]->{'withDag'} = "";
        $proPipeAll[0]->{'interdel'} = "";


        $res = $this->getUUIDLocal("run_log");
        $uuid = $res->rev_uuid;
        $this->updateProcessRunPid($process_id, "0", $ownerID);
        $this->updateProcessRunUUID($process_id, $uuid);
        $runStatus = "init";
        $this->updateProcessRunStatus($process_id, $runStatus, $ownerID);
        $attempt = "1";
        $project_pipeline_id = "";
        $proVarObj = "";
        $initialRunParams = "";
        $initialConfigText = "";
        $getCloudConfigFileDir = "";
        $runType = "newrun";
        $runConfig = $this->getMainRunOpt($proPipeAll, $profileId, $profileType, $ownerID);
        $bashConfigText = $this->getBashVariables($profileId, $profileType, $ownerID);
        $mainConfigText = $this->getMainTestRunConfig($proPipeAll, $runConfig, $project_pipeline_id, $profileId, $profileType, $proVarObj, $code, $ownerID);
        //create file and folders
        list($targz_file, $dolphin_path_real, $runCmdAll) = $this->initTestRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $bashConfigText, $attempt, $runType, $ownerID);
        //run the script in remote machine
        $data = $this->runCmd($project_pipeline_id, $profileType, $profileId, $uuid, $targz_file, $dolphin_path_real, $runCmdAll, $ownerID);
        $data = json_encode("");
        return $data;
    }


    function saveRun($project_pipeline_id, $nextText, $runType, $manualRun, $uuid, $proVarObj, $ownerID)
    {
        $data = null;
        $permCheck = 1;
        //don't allow to update if user not own the project_pipeline.
        $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
        $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (!empty($permCheck)) {
            $this->updateProPipeLastRunUUID($project_pipeline_id, $uuid);
            $this->updateProjectPipelineNewRun($project_pipeline_id, 0, $ownerID);
            $attemptData = json_decode($this->getRunAttempt($project_pipeline_id));
            $attempt = isset($attemptData[0]->{'attempt'}) ? $attemptData[0]->{'attempt'} : "";
            if (empty($attempt) || $attempt == 0 || $attempt == "0") {
                $attempt = "0";
            }
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
            $docker_check = $proPipeAll[0]->{'docker_check'};
            $amazon_cre_id = $proPipeAll[0]->{'amazon_cre_id'};
            $google_cre_id = $proPipeAll[0]->{'google_cre_id'};
            $profile = $proPipeAll[0]->{'profile'};
            $profileAr = explode("-", $profile);
            $profileType = $profileAr[0];
            $profileId = isset($profileAr[1]) ? $profileAr[1] : "";
            $initRunOptions = $this->getInitialRunOpt($proPipeAll, $profileId, $profileType, $ownerID);
            $runConfig = $this->getMainRunOpt($proPipeAll, $profileId, $profileType, $ownerID);
            $this->saveRunLogOpt($project_pipeline_id, $proPipeAll, $uuid, $proVarObj, $ownerID);
            $amzBashConfigText = $this->getAmazonVariables($amazon_cre_id, $ownerID);
            $bashConfigText = $this->getBashVariables($profileId, $profileType, $ownerID);
            if (!empty($bashConfigText)) {
                $amzBashConfigText = "$amzBashConfigText\n$bashConfigText";
            }
            list($initialConfigText, $initialRunParams) = $this->getInitialRunConfig($proPipeAll, $project_pipeline_id, $attempt, $profileType, $profileId, $docker_check, $initRunOptions, $ownerID);
            $mainConfigText = $this->getMainRunConfig($proPipeAll, $runConfig, $project_pipeline_id, $profileId, $profileType, $proVarObj, $ownerID);
            $getCloudConfigFileDir = $this->getCloudConfig($project_pipeline_id, $attempt, $ownerID);
            //create file and folders
            list($targz_file, $dolphin_path_real, $runCmdAll) = $this->initRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $amzBashConfigText, $attempt, $runType, $ownerID);
            if ($manualRun == "true") {
                $data = $this->getManualRunCmd($targz_file, $uuid, $dolphin_path_real);
            } else {
                //run the script in remote machine
                $data = $this->runCmd($project_pipeline_id, $profileType, $profileId, $uuid, $targz_file, $dolphin_path_real, $runCmdAll, $ownerID);
                //activate autoshutdown feature for cloud
                if ($profileType == "amazon" || $profileType == "google") {
                    $autoshutdown_active = "true";
                    $this->updateCloudShutdownActive($profileId, $autoshutdown_active, $profileType, $ownerID);
                    $this->updateCloudShutdownDate($profileId, NULL, $profileType, $ownerID);
                }
            }
        }
        return $data;
    }

    function saveRunLogSize($uuid, $project_pipeline_id, $ownerID)
    {
        if (empty($uuid)) {
            $rundir = "run$project_pipeline_id";
        } else {
            $rundir = $uuid;
        }
        $run_path_server = "{$this->run_path}/$rundir";
        $size = $this->getDirectorySize($run_path_server);
        settype($size, 'integer');
        $ret = $this->updateRunLogSize($project_pipeline_id, $uuid, $size, $ownerID);
        return $ret;
    }

    function saveRunLogSizeUser($userID, $ownerID)
    {
        if (!empty($userID) && !empty($ownerID)) {
            $userLogRaw = $this->getRunLogUser($userID, "all");
            $userLog = json_decode($userLogRaw);
            foreach ($userLog as $log) :
                $run_log_uuid = !empty($log->{'run_log_uuid'}) ? $log->{'run_log_uuid'} : "";
                $project_pipeline_id = !empty($log->{'project_pipeline_id'}) ? $log->{'project_pipeline_id'} : "";
                $data = $this->saveRunLogSize($run_log_uuid, $project_pipeline_id, $userID);
            endforeach;
            //update total user disk_usage
            $userLogRaw = $this->saveUserDiskUsage($userID, $ownerID);
        }
    }

    function saveUserDiskUsage($userID, $ownerID)
    {
        $tsize = $this->queryAVal("SELECT SUM(size) as tsize FROM $this->db.run_log WHERE owner_id='$userID'");
        settype($tsize, 'integer');
        $this->updateUserDiskUsage($tsize, $userID, $ownerID);
    }

    function updateUserDiskUsage($disk_usage, $userID, $ownerID)
    {
        $sql = "UPDATE $this->db.users SET disk_usage='$disk_usage', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$userID'";
        return self::runSQL($sql);
    }
    function updateProfileUser($emailNotif, $ownerID)
    {
        $sql = "UPDATE $this->db.users SET email_notif='$emailNotif', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$ownerID'";
        return self::runSQL($sql);
    }

    function saveRunLogOpt($project_pipeline_id, $proPipeAll, $uuid, $proVarObj, $ownerID)
    {
        $newObj = $proPipeAll[0];
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        // escape new lines
        $newObj->{'summary'} = str_replace("\n", "</br>", $newObj->{"summary"});
        $newObj->{"dmeta"} = ""; // removed to protect json format
        $newObj->{'project_pipeline_input'} = $allinputs;
        $pro_var_obj_db =  addslashes(htmlspecialchars($proVarObj, ENT_QUOTES));
        $newObj->{'proVarObj'} = htmlspecialchars($proVarObj, ENT_QUOTES);
        foreach ($allinputs as $inputitem) :
            $collection_id = $inputitem->{'collection_id'};
            $collection_arr = array();
            if (!empty($collection_id)) {
                $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                foreach ($allfiles as $fileData) :
                    $name = $fileData->{'name'};
                    if (!in_array($name, $collection_arr)) {
                        $collection_arr[] = $name;
                    }
                endforeach;
                $newObj->{"collection-$collection_id"} = $collection_arr;
            }
        endforeach;
        $this->updateRunLogOpt(json_encode($newObj), $uuid, $pro_var_obj_db, $ownerID);
    }

    function updateRunAttemptLog($manualRun, $project_pipeline_id, $ownerID)
    {
        $res = $this->getUUIDLocal("run_log");
        $uuid = $res->rev_uuid;
        $status = ($manualRun == "true") ? "Manual" : "init";
        //check if $project_pipeline_id already exits un run table
        $checkRun = $this->getRun($project_pipeline_id, $ownerID);
        $checkarray = json_decode($checkRun, true);
        $attempt = isset($checkarray[0]["attempt"]) ? $checkarray[0]["attempt"] : "";
        settype($attempt, 'integer');
        if (empty($attempt)) {
            $attempt = 0;
        }
        $attempt += 1;
        if (isset($checkarray[0])) {
            $this->updateRunAttempt($project_pipeline_id, $attempt, $ownerID);
            $this->updateRunStatus($project_pipeline_id, $status, $ownerID);
            $this->updateLastRunDate($project_pipeline_id, $ownerID);
            $this->updateRunPid($project_pipeline_id, "0", $ownerID);
            $this->updateRunSessionUUID("", "", $project_pipeline_id, $ownerID);
        } else {
            $this->insertRun($project_pipeline_id, $status, "1", $ownerID);
        }
        $this->insertRunLog($project_pipeline_id, $uuid, $status, $ownerID);
        return $uuid;
    }

    function parseKeyLine($txt, $key)
    {
        $txt = trim($txt);
        $lines = explode("\n", $txt);
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match("/$key/i", $lines[$i])) {
                return $lines[$i];
            }
        }
        return "";
    }

    function validateToken($tk, $id, $np)
    {
        $ret = 0;
        $sql = "SELECT id, np FROM $this->db.token WHERE token = '$tk'";
        $lData = $this->queryTable($sql);
        $ln = json_decode($lData, true);
        if (isset($ln[0])) {
            $tk_id = $ln[0]["id"];
            $tk_np = $ln[0]["np"];
        }
        if ($tk_np == $np && $tk_id == $id) {
            $ret = 1;
        }
        // if token belong to project_pipeline -> allow acces to pipeline
        if (empty($ret) && $tk_np == "3" && $np == "1") {
            $pipeline_id = $this->queryAVal("SELECT pipeline_id FROM $this->db.project_pipeline WHERE id='$tk_id'");
            if ($pipeline_id == $id) {
                $ret = 1;
            }
        }
        return $ret;
    }

    function validateSSH($connect, $ssh_id, $ssh_port, $type, $cmd, $path, $ownerID)
    {
        $ret = array();
        if (!empty($cmd)) {
            $cmd = $cmd . " && ";
        }
        $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
        $ssh_port = !empty($ssh_port) ? " -p " . $ssh_port : "";
        $subcmd = "";
        $precmd = "source /etc/profile ";
        if ($type == "ssh") {
            $subcmd = "$precmd && ls ";
        } else if ($type == "java") {
            $subcmd = "$precmd && which java ";
        } else if ($type == "nextflow") {
            if (!empty($path)) {
                $subcmd = "$precmd && which $path/nextflow ";
            } else {
                $subcmd = "$precmd && which nextflow ";
            }
        } else if ($type == "docker") {
            $subcmd = "$precmd && docker --version ";
        } else if ($type == "singularity") {
            $subcmd = "$precmd && singularity --version ";
        }
        $runcmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$cmd $subcmd && echo 'query_validated'\" 2>&1 &";
        $ret = $this->execute_cmd($runcmd, $ret, "cmd_log", "cmd");
        $ret["validation"] = "success";
        $ret["version"] = "";
        if (empty($ret["cmd_log"])) {
            $ret["validation"] = "failed";
        } else if (!preg_match("/query_validated/i", $ret["cmd_log"])) {
            $ret["validation"] = "failed";
        } else if (preg_match("/command not found/i", $ret["cmd_log"])) {
            $ret["validation"] = "failed";
        } else {
            if ($type == "java" || $type == "nextflow" || $type == "docker" || $type == "singularity") {
                $ret["version"] = $this->parseKeyLine($ret["cmd_log"], "version");
            }
        }

        return json_encode($ret);
    }

    function generateKeys($ownerID)
    {
        $cmd = "rm -rf {$this->ssh_path}/.tmp$ownerID && mkdir -p {$this->ssh_path}/.tmp$ownerID && cd {$this->ssh_path}/.tmp$ownerID && ssh-keygen -C @dolphinnext -f tkey -t rsa -N '' > logTemp.txt";
        $resText = shell_exec("$cmd");
        $keyPubPath = "{$this->ssh_path}/.tmp$ownerID/tkey.pub";
        $keyPriPath = "{$this->ssh_path}/.tmp$ownerID/tkey";
        $keyPub = $this->readFile($keyPubPath);
        $keyPri = $this->readFile($keyPriPath);
        $log_array = array('$keyPub' => $keyPub);
        $log_array['$keyPri'] = $keyPri;
        //remove the directory after reading files.
        $cmd = "rm -rf {$this->ssh_path}/.tmp$ownerID 2>&1 & echo $! &";
        $log_remove = $this->runCommand($cmd, 'remove_key', '');
        return json_encode($log_array);
    }

    function insertGoogKey($id, $key_name, $ownerID)
    {
        $suffix = "goog.json";
        $targetFile = "{$this->goog_path}/{$ownerID}_{$id}_{$suffix}";
        $tmpFile = "{$this->goog_path}/uploads/{$ownerID}/{$ownerID}_tmpkey";
        if (empty($key_name)) {
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
        } else {
            if (file_exists($tmpFile)) {
                rename($tmpFile, $targetFile);
                chmod($targetFile, 0600);
            }
        }
    }

    function insertKey($id, $key, $type, $ownerID)
    {
        if (!file_exists("{$this->ssh_path}")) {
            mkdir("{$this->ssh_path}", 0700, true);
        }
        if ($type == 'clu') {
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}.pky", 'w'); //new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}.pky", 0600);
        } else if ($type == 'amz_pri') {
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w'); //new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'amz_pub') {
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w'); //creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'ssh_pub') {
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w'); //creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'ssh_pri') {
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w'); //creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        }
    }
    function readKey($id, $type, $ownerID)
    {
        if ($type == 'clu') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}.pky";
        } else if ($type == 'amz_pub' || $type == 'amz_pri') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        } else if ($type == 'ssh_pub' || $type == 'ssh_pri') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        }
        $handle = fopen($filename, 'r'); //creates new file
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return $content;
    }
    function delKey($id, $type, $ownerID)
    {
        if ($type == 'clu') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}.pky";
        } else if ($type == 'amz_pub' || $type == 'amz_pri') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        } else if ($type == 'ssh_pri' || $type == 'ssh_pub') {
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        }
        unlink($filename);
    }

    function amazonEncode($a_key)
    {
        $encrypted_string = openssl_encrypt($a_key, "AES-128-ECB", $this->amazon);
        return $encrypted_string;
    }
    function amazonDecode($a_key)
    {
        $decrypted_string = openssl_decrypt($a_key, "AES-128-ECB", $this->amazon);
        return $decrypted_string;
    }
    function keyAsterisk($key)
    {
        if (strlen($key) > 3) {
            $key = str_repeat('*', strlen($key) - 4) . substr($key, -4);
        }
        return $key;
    }
    function getCloudName($profileName, $cloud)
    {
        $profileName = str_replace("_", "", $profileName);
        $cloudName = "$profileName{$cloud}";
        return $cloudName;
    }

    function startProCloud($id, $cloud, $ownerID, $username)
    {
        $text = "";
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
        if ($cloud == "amazon") {
            $main_path = $this->amz_path;
            $amazon_cre_id = $data[0]->{'amazon_cre_id'};
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach ($amz_data as $d) {
                $access = $d->amz_acc_key;
                $d->amz_acc_key = trim($this->amazonDecode($access));
                $secret = $d->amz_suc_key;
                $d->amz_suc_key = trim($this->amazonDecode($secret));
            }
            $access_key = $amz_data[0]->{'amz_acc_key'};
            $secret_key = $amz_data[0]->{'amz_suc_key'};
            $default_region = $amz_data[0]->{'amz_def_reg'};
            $subnet_id = $data[0]->{'subnet_id'};
            $security_group = $data[0]->{'security_group'};
            $shared_storage_id = $data[0]->{'shared_storage_id'};
            $shared_storage_mnt = $data[0]->{'shared_storage_mnt'};


            $text .= "aws{\n";
            $text .= "   accessKey = '$access_key'\n";
            $text .= "   secretKey = '$secret_key'\n";
            $text .= "   region = '$default_region'\n";
            $text .= "}\n";
        } else if ($cloud == "google") {
            $main_path = $this->goog_path;
            $google_cre_id = $data[0]->{'google_cre_id'};
            $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
            $project_id = $goog_data[0]->{'project_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";

            $zone = $data[0]->{'zone'};
            $text .= "google{\n";
            $text .= "   project = '$project_id'\n";
            $text .= "   zone = '$zone'\n";
            $text .= "}\n";
        }
        $name = $data[0]->{'name'};
        $ssh_id = $data[0]->{'ssh_id'};
        $username = $data[0]->{'username'};
        $image_id = $data[0]->{'image_id'};
        $instance_type = $data[0]->{'instance_type'};

        $keyFile = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pub.pky";
        $nodes = $data[0]->{'nodes'};
        settype($nodes, "integer");
        $autoscale_check = $data[0]->{'autoscale_check'};
        $autoscale_maxIns = $data[0]->{'autoscale_maxIns'};
        $text .= "cloud { \n";
        if (!empty($username)) {
            $text .= "   userName = '$username'\n";
        }
        if (!empty($image_id)) {
            $text .= "   imageId = '$image_id'\n";
        }
        if (!empty($instance_type)) {
            $text .= "   instanceType = '$instance_type'\n";
        }
        if ($cloud == "google") {
            $text .= "   driver = 'google'\n";
        }
        if (!empty($security_group) && ($cloud == "amazon")) {
            $text .= "   securityGroup = '$security_group'\n";
        }
        if (!empty($subnet_id) && ($cloud == "amazon")) {
            $text .= "   subnetId = '$subnet_id'\n";
        }
        if (!empty($shared_storage_id) && ($cloud == "amazon")) {
            $text .= "   sharedStorageId = '$shared_storage_id'\n";
        }
        if (!empty($shared_storage_mnt) && ($cloud == "amazon")) {
            $text .= "   sharedStorageMount = '$shared_storage_mnt'\n";
        }
        $text .= "   keyFile = '$keyFile'\n";
        if ($autoscale_check == "true") {
            $text .= "   autoscale {\n";
            $text .= "       enabled = true \n";
            $text .= "       terminateWhenIdle = true\n";
            if (!empty($autoscale_maxIns)) {
                $text .= "       maxInstances = $autoscale_maxIns\n";
            }
            $text .= "   }\n";
        }
        $text .= "}\n";

        $this->createDirFile("{$main_path}/pro_{$profileName}", "nextflow.config", 'w', $text);
        $nodeText = "";
        if ($nodes > 1) {
            $nodeText = "-c $nodes";
        }
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)) {
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google") {
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }

        //start cloud cluster
        $cmd = "cd {$main_path}/pro_{$profileName} && $nextVerText $nextModeText yes | nextflow cloud create $cloudName $nodeText > logStart.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand($cmd, 'start_cloud', '');
        $log_array['start_cloud_cmd'] = $cmd;
        //xxx save pid of nextflow cloud create cluster job
        if (preg_match("/([0-9]+)(.*)/", $log_array['start_cloud'])) {
            $this->updateCloudProStatus($id, "waiting", $cloud, $ownerID);
        } else {
            $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
        }
        return json_encode($log_array);
    }

    function stopProCloud($id, $ownerID, $username, $cloud)
    {
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        if ($cloud == "amazon") {
            $main_path = $this->amz_path;
        } else if ($cloud == "google") {
            $main_path = $this->goog_path;
        }
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)) {
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google") {
            $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
            $google_cre_id = $data[0]->{'google_cre_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }
        //stop cluster
        $cmd = "cd {$main_path}/pro_{$profileName} && $nextVerText $nextModeText yes | nextflow cloud shutdown $cloudName > logStop.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand($cmd, 'stop_cloud', '');
        return json_encode($log_array);
    }
    function triggerShutdown($id, $cloud, $ownerID, $type)
    {
        $cloudDataJS = $this->getProfileCloudbyID($id, $cloud, $ownerID);
        $cloudData = json_decode($cloudDataJS, true)[0];
        $username = $cloudData["username"];
        if (!empty($username)) {
            $usernameCl = str_replace(".", "__", $username);
        }
        $autoshutdown_date = $cloudData["autoshutdown_date"];
        $autoshutdown_active = $cloudData["autoshutdown_active"];
        $autoshutdown_check = $cloudData["autoshutdown_check"];
        // get list of active runs using this profile 
        $activeRun = json_decode($this->getActiveRunbyProID($id, $cloud, $ownerID), true);
        if (count($activeRun) > 0) {
            return "Active run is found";
        }
        //if process comes to this checkpoint it has to be activated
        if ($autoshutdown_check == "true" && $autoshutdown_active == "true") {
            error_log("Active run not found->checking autoshutdown.");
            //if timer not set then set timer
            if (empty($autoshutdown_date)) {
                $autoshutdown_date = strtotime("+10 minutes");
                $mysqltime = date("Y-m-d H:i:s", $autoshutdown_date);
                $this->updateCloudShutdownDate($id, $mysqltime, $cloud, $ownerID);
                return "Timer set to: $mysqltime";
            } else {
                //if timer is set then check if time elapsed -> stopProCloud
                $expected_date = strtotime($autoshutdown_date);
                $remaining = $expected_date - time();
                error_log("expected_date:" . $expected_date);
                error_log("time:" . time());
                error_log("remaining:" . $remaining);
                if ($remaining < 1) {
                    error_log("autoshutdown triggered");
                    $newStatus = "";
                    $stopProCloud = $this->stopProCloud($id, $ownerID, $usernameCl, $cloud);
                    //track termination of instance
                    if ($type == "slow") {
                        for ($i = 0; $i < 10; $i++) {
                            $runAmzCloudCheck = $this->runCloudCheck($id, $cloud, $ownerID, $usernameCl);
                            sleep(15);

                            $checkCloudStatus = $this->checkCloudStatus($id, $ownerID, $usernameCl, $cloud);
                            $newStatus = json_decode($checkCloudStatus)->{'status'};
                            if ($newStatus == "terminated") {
                                break;
                            }
                        }
                    }
                    return json_encode("Shutdown Triggered:" . $stopProCloud . " New Status:" . $newStatus);
                } else {
                    return "$remaining seconds left to shutdown.";
                }
            }
        } else {
            return "Shutdown feature has not been activated.";
        }
    }


    //read both start and list files
    function readCloudListStart($id, $username, $cloud)
    {
        if ($cloud == "amazon") {
            $main_path = $this->amz_path;
        } else if ($cloud == "google") {
            $main_path = $this->goog_path;
        }
        $profileName = "{$username}_{$id}";
        //read logCloudList.txt
        $logPath = "{$main_path}/pro_{$profileName}/logCloudList.txt";
        $logCloudList = $this->readFile($logPath);
        $log_array = array('logCloudList' => $logCloudList);
        //read logStart.txt
        $logPathStart = "{$main_path}/pro_{$profileName}/logStart.txt";
        $logStart = $this->readFile($logPathStart);
        $log_array['logStart'] = $logStart;
        return $log_array;
    }
    //available status: waiting, initiated, terminated, running
    function checkCloudStatus($id, $ownerID, $username, $cloud)
    {
        $profileName = "{$username}_{$id}";
        //check status
        $cloudStat = json_decode($this->getCloudStatus($id, $cloud, $ownerID));
        $status = $cloudStat[0]->{'status'};
        $node_status = $cloudStat[0]->{'node_status'};
        if ($status == "waiting") {
            //check cloud list
            $log_array = $this->readCloudListStart($id, $username, $cloud);
            if (preg_match("/running/i", $log_array['logCloudList'])) {
                $this->updateCloudProStatus($id, "initiated", $cloud, $ownerID);
                $log_array['status'] = "initiated";
                return json_encode($log_array);
            } else if (!preg_match("/STATUS/", $log_array['logCloudList']) && (preg_match("/Missing/i", $log_array['logCloudList']) || preg_match("/denied/i", $log_array['logCloudList']) || preg_match("/ERROR/", $log_array['logCloudList']))) {
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
                //Downloading dependency com.google.errorprone:error_prone
            } else if (preg_match("/Unknow cloud/i", $log_array['logStart']) || preg_match("/Invalid/i", $log_array['logStart']) || preg_match("/Missing/i", $log_array['logStart']) || preg_match("/denied/i", $log_array['logStart']) || (preg_match("/ERROR/", $log_array['logStart']) && !preg_match("/WARN: One or more errors/i", $log_array['logStart'])) || preg_match("/couldn't/i", $log_array['logStart'])  || preg_match("/help/i", $log_array['logStart']) || preg_match("/wrong/i", $log_array['logStart']) || preg_match("/No space left on device/i", $log_array['logStart'])) {
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                //error
                $log_array['status'] = "waiting";
                return json_encode($log_array);
            }
        } else if ($status == "initiated") {
            //check cloud list
            $log_array = $this->readCloudListStart($id, $username, $cloud);
            if (preg_match("/running/i", $log_array['logCloudList']) && preg_match("/STATUS/", $log_array['logCloudList'])) {
                $startLog = $log_array['logStart'];
                if (preg_match("/ssh -i(.*)/", $startLog)) {
                    preg_match("/ssh -i <(.*)> (.*)/", $startLog, $match);
                    $sshText = $match[2];
                    $log_array['sshText'] = $sshText;
                    $log_array['status'] = "running";
                    $this->updateCloudProStatus($id, "running", $cloud, $ownerID);
                    $this->updateCloudProSSH($id, $sshText, $cloud, $ownerID);
                    //parse child nodes
                    $cluData = $this->getProfileCloudbyID($id, $cloud, $ownerID);
                    $cluDataArr = json_decode($cluData, true);
                    $numNodes = $cluDataArr[0]["nodes"];
                    settype($numNodes, "integer");
                    $username = $cluDataArr[0]["username"];
                    if ($numNodes > 1) {
                        $log_array['nodes'] = $numNodes;
                        if (preg_match("/.*Launching worker node.*/", $startLog)) {
                            preg_match("/.*Launching worker node.*ready\.(.*)Launching master node --/s", $startLog, $matchNodes);
                            if (!empty($matchNodes[1])) {
                                preg_match_all("/[ ]+[^ ]+[ ]+(.*\.com)\n.*/sU", $matchNodes[1], $matchNodesAll);
                                $log_array['childNodes'] = $matchNodesAll[1];
                            }
                        }
                    }
                    return json_encode($log_array);
                } else {
                    $log_array['status'] = "initiated";
                    return json_encode($log_array);
                }
            } else if (!preg_match("/running/i", $log_array['logCloudList']) && preg_match("/STATUS/", $log_array['logCloudList'])) {
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        } else if ($status == "running") {
            //check cloud list
            $log_array = $this->readCloudListStart($id, $username, $cloud);
            if (preg_match("/running/i", $log_array['logCloudList']) && preg_match("/STATUS/", $log_array['logCloudList'])) {
                $log_array['status'] = "running";
                $sshTextArr = json_decode($this->getCloudProSSH($id, $cloud, $ownerID));
                $sshText = $sshTextArr[0]->{'ssh'};
                $log_array['sshText'] = $sshText;
                return json_encode($log_array);
            } else if (!preg_match("/running/i", $log_array['logCloudList']) && !preg_match("/stopping/i", $log_array['logCloudList']) && preg_match("/STATUS/", $log_array['logCloudList'])) {
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        } else if ($status == "terminated") {
            $log_array = $this->readCloudListStart($id, $username, $cloud);
            $log_array['status'] = "terminated";
            if (preg_match("/running/i", $log_array['logCloudList']) && preg_match("/STATUS/", $log_array['logCloudList'])) {
                $log_array['status'] = "running";
                $this->updateCloudProStatus($id, "running", $cloud, $ownerID);
                $sshTextArr = json_decode($this->getCloudProSSH($id, $cloud, $ownerID));
                $sshText = $sshTextArr[0]->{'ssh'};
                $log_array['sshText'] = $sshText;
                return json_encode($log_array);
            }
            return json_encode($log_array);
        } else if ($status == "") {
            $log_array = array('status' => 'inactive');
            return json_encode($log_array);
        } else if ($status == "inactive") {
            $log_array = array('status' => 'inactive');
            return json_encode($log_array);
        }
    }

    //check cloud list
    function runCloudCheck($id, $cloud, $ownerID, $username)
    {
        if ($cloud == "amazon") {
            $main_path = $this->amz_path;
        } else if ($cloud == "google") {
            $main_path = $this->goog_path;
        }
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)) {
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google") {
            $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
            $google_cre_id = $data[0]->{'google_cre_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }
        $cmd = "cd {$main_path}/pro_$profileName && rm -f logCloudList.txt && $nextVerText $nextModeText nextflow cloud list $cloudName >> logCloudList.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand($cmd, 'cloudlist', '');
        return json_encode($log_array);
    }

    function getLastRunData($project_pipeline_id)
    {
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.date_modified, pp.owner_id, r.run_status, pp.type
            FROM $this->db.project_pipeline pp
            INNER JOIN (
                        SELECT DISTINCT rr.run_status, rr.id, rr.project_pipeline_id
                        FROM $this->db.run_log rr
                        WHERE rr.project_pipeline_id = '$project_pipeline_id' AND rr.deleted=0 ORDER BY rr.id DESC LIMIT 1
                    ) r ON pp.id = r.project_pipeline_id";
        return self::queryTable($sql);
    }
    function cleanlist($list, $ret)
    {
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                $dir = trim($list[$i]);
                if (!empty($dir) && file_exists($dir) && $dir != "{$this->tmp_path}" && $dir != "{$this->tmp_path}/uploads/" && $dir != "{$this->tmp_path}/uploads" && $dir != "{$this->tmp_path}/pub" && $dir != "{$this->tmp_path}/pub/") {
                    system('rm -rf ' . escapeshellarg($dir), $retval);
                    if ($retval == 0) {
                        $ret .= $dir . " ";
                    } else {
                        $ret .= $dir . " not deleted->$retval";
                    }
                }
            }
        }
        return $ret;
    }

    function execCleanCmd($ret, $cmd)
    {
        $dirlist = array();
        exec($cmd, $dirlist, $exit);
        $ret = $this->cleanlist($dirlist, $ret);
        return $ret;
    }

    function cleanTempDir()
    {
        //clean upload directories if not modified in 30 minutes.
        $ret = "";
        $cmd = "find {$this->tmp_path}/uploads/ -mindepth 1 -type d -mmin +30 2>&1 & ";
        $ret = $this->execCleanCmd($ret, $cmd);
        $cmd2 = "find {$this->goog_path}/uploads/ -mindepth 1 -type d -mmin +30 2>&1 & ";
        $ret = $this->execCleanCmd($ret, $cmd2);
        //clean run.tar.gz in run directories if not modified in 10 minutes.
        $run_targz = "find {$this->tmp_path}/pub/*/run.tar.gz -mmin +10 2>&1";
        $ret = $this->execCleanCmd($ret, $run_targz);
        return $ret;
    }



    // return assoc Arr:  array("filename" => row, 
    //                          "feature" => column, 
    //                          "target" => rsem_tpm_gene, 
    //                          "id" => "g-155",  
    //                          "name" => star)
    function getPubDmetaInfo($pipeData)
    {
        $nodes = $pipeData[0]->{'nodes'};
        $data = array();
        if (!empty($nodes)) {
            $nodes = json_decode($nodes, true);
            foreach ($nodes as $gNum => $item) :
                $out = array();
                $push = false;
                if ($item[2] == "outPro") {
                    $name = $item[3];
                    $processOpt = $item[4];
                    $out["id"] = $gNum;
                    $out["name"] = $name; //directory name which has the report files
                    foreach ($processOpt as $key => $feature) :
                        if ($key == "pubDmeta") {
                            $push = true;
                            $out = array_merge($feature, $out);
                        }
                    endforeach;
                    if ($push == true) $data[] = $out;
                }
            endforeach;
        }
        return $data;
    }

    function getProjectPipelineCollection($project_pipeline_id, $ownerID)
    {
        $ret = array();
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        if (!empty($allinputs)) {
            foreach ($allinputs as $inputitem) :
                $collection_id = $inputitem->{'collection_id'};
                if (!empty($collection_id)) {
                    $allfiles = json_decode($this->getCollectionFiles($collection_id, $ownerID));
                    foreach ($allfiles as $fileData) :
                        $sample_name = $fileData->{'name'};
                        if (!empty($sample_name)) $ret[] = $sample_name;
                    endforeach;
                }
            endforeach;
        }
        return $ret;
    }


    function patchDmetaAPI($dmeta_server, $dmeta_out_collection, $sName, $targetDmetaRowId, $doc, $accessToken, $project_pipeline_id, $dmeta_project)
    {
        error_log("patchDmetaAPI:" . $sName);
        $projectPath = !empty($dmeta_project) ? "projects/$dmeta_project/" : "";
        $url = "$dmeta_server/api/v1/{$projectPath}data/$dmeta_out_collection/$targetDmetaRowId";
        $run_url = "{$this->base_path}/index.php?np=3&id=" . $project_pipeline_id;
        $data = json_encode(array(
            'doc' => $doc
        ));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Bearer $accessToken"));
        // secure it:
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');

        $body = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) && $statusCode != 200) {
            error_log("patchDmetaAPI failed");
        }
        curl_close($curl);
        $msg = json_decode($body, true);
        error_log(print_r($msg, TRUE));


        return $msg;
    }


    //if name contains regular expression with curly brackets: {a,b,c} then turn into (a|b|c) format
    function fixCurlyBrackets($outputName)
    {
        $patt = "/(.*){(.*?),(.*?)}(.*)/";
        if (preg_match($patt, $outputName)) {
            $insideBrackets = preg_replace($patt, '$2' . "," . '$3', $outputName);
            $insideBrackets = str_replace(",", "|", $insideBrackets);
            $outputNameFix = preg_replace($patt, '$1' . "(" . $insideBrackets . ")" . '$4', $outputName);
            if (preg_match($patt, $outputName)) {
                return $this->fixCurlyBrackets($outputNameFix);
            } else {
                return $outputNameFix;
            }
        } else {
            return $outputName;
        }
    }

    function getDmetaPublishFiles($pipeData, $uuid, $dmetaInfoPipe)
    {
        // get all existing files with their directories in array
        // e.g. ["rsem_summary/expected_count.tsv", "star/Log.progress.out"]
        $fileListPubweb = array_values((array)json_decode($this->getFileList($uuid, "pubweb", "onlyfile")));
        $fileListPubDmeta = array_values((array)json_decode($this->getFileList($uuid, "pubdmeta", "onlyfile")));
        $fileList = array_merge($fileListPubweb, $fileListPubDmeta);
        // removes empty values
        $fileList = array_filter($fileList);
        $sendObj = array();
        $dmetaObj = null;
        // $pubDmetaDir : [{"dir":"summary","pattern":"\"overall_summary.tsv\""}]
        $pubDmetaDir = $pipeData[0]->{'publish_dmeta_dir'};
        $pubDmetaDir = json_decode(htmlspecialchars_decode($pubDmetaDir, ENT_QUOTES), true);

        foreach ($pubDmetaDir as $value) {
            $filepaths = array();
            if (!empty($value["dir"]) && !empty($value["pattern"])) {
                $pattern = trim($value["pattern"], '"');
                $pattern = trim($pattern, "'");
                $regex = $value["dir"] . "/" . $pattern;
                //if name contains path and separated by '/' then replace with escape character '\/'
                $regex = str_replace("/", "\/", $regex);
                //if name contains regular expression with curly brackets: {a,b,c} then turn into (a|b|c) format
                $regex = $this->fixCurlyBrackets($regex);
                $regex = str_replace("*", ".*", $regex);
                $regex = str_replace("?", ".?", $regex);
                $regex = str_replace("'", "", $regex);
                $regex = str_replace('"', "", $regex);
                $regex = "/^" . $regex . "$/i";
                $matches  = preg_grep($regex, $fileList);
                $matches = array_values((array) $matches);
                for ($i = 0; $i < count($matches); $i++) {
                    // get File Content
                    $parentDir = "";
                    if (in_array($matches[$i], $fileListPubweb)) {
                        $parentDir = "pubweb";
                    } else {
                        $parentDir = "pubdmeta";
                    }
                    $filepaths[] = "$parentDir/" . $matches[$i];
                }
                // get location of the sample name 
                // find dmeta array that matches $value["dir"]
                foreach ($dmetaInfoPipe as $dmetaItem) :
                    if ($dmetaItem["name"] == $value["dir"]) {
                        $dmetaObj = $dmetaItem;
                    }
                endforeach;
                //$value["dir"] = "summary_folder" or "analysis_folder"
                //$dmetaObj["target"] = "summary" or "analysis"
                if (!empty($dmetaObj["target"])) {
                    $target = $dmetaObj["target"];
                    if (!isset($sendObj[$target])) {
                        $sendObj[$target] = array();
                    }
                    $sendItem = array();
                    $sendItem["filepaths"] = $filepaths;
                    $sendItem["dmetaObj"] = $dmetaObj;
                    $sendObj[$target][] = $sendItem;
                }
            }
        }
        return $sendObj;
    }

    function sendDmetaReport($project_pipeline_id, $proPipeAll, $pipeData, $uuid, $ownerID, $accessToken)
    {
        error_log("sendDmetaReport");
        // $dmetaServerInfo: 
        // {"dmeta_run_id":"5fbeaacfe71265b775a76e35","dmeta_server":"https://localhost:4000",
        //  "dmeta_out":{"sample_summary":{"sample1":"5fbeaa..","sample2":"5fbeaacfe7126.."},
        //  "analysis":{"sample1":"5fbeaacfe7...","sample2":"5fbeaacf.."}}
        //  "dmeta_project":"vitiligo"}
        // * details of dmeta_out part:{ targetCollection: {$sampleName1: targetDocument}}
        $dmetaServerInfo = json_decode($proPipeAll[0]->{'dmeta'}, true);
        $dmeta_run_id = $dmetaServerInfo["dmeta_run_id"];
        $dmeta_server = $dmetaServerInfo["dmeta_server"];
        $dmeta_out = $dmetaServerInfo["dmeta_out"];
        $dmeta_project = !empty($dmetaServerInfo["dmeta_project"]) ? $dmetaServerInfo["dmeta_project"] : "";

        // get $dmetaInfoPipe from pipeline
        // return assoc Arr:  array("filename" => row, 
        //                          "feature" => column, 
        //                          "target" => rsem_tpm_gene, 
        //                          "id" => "g-155",  
        //                          "name" => star)
        $dmetaInfoPipe = $this->getPubDmetaInfo($pipeData);

        $sendObj  = $this->getDmetaPublishFiles($pipeData, $uuid, $dmetaInfoPipe);
        foreach ($sendObj as $sendItems) {
            $allDoc = array();
            foreach ($sendItems as $sendItem) :
                $filepaths = $sendItem["filepaths"];
                $dmetaObj = $sendItem["dmetaObj"];
                if (!empty($dmetaObj) && !empty($filepaths)) {
                    $pipelineFolder = $dmetaObj["name"];
                    $sNameLoc = $dmetaObj["filename"];
                    $featureLoc = $dmetaObj["feature"];
                    $targetDmetaColl = $dmetaObj["target"];
                    // get $out_collection, sample names ($sName) and $targetDmetaRowId
                    //$dmeta_out -> array( "sample_summary" => array ("control" => 5f74e0a7cc3f75507d1e6f26\n            "experiment" => 5f74e0a7cc3f75507d1e6f28))
                    // e.g. $dmeta_out_collection = "sample_summary"
                    // e.g. $sName = "control"
                    // e.g. $targetDmetaRowId = "5f74e0a7cc3f75507d1e6f26"
                    foreach ($dmeta_out as $dmeta_out_collection => $out_collectionData) :
                        if ($targetDmetaColl == $dmeta_out_collection) {
                            foreach ($out_collectionData as $sName => $targetDmetaRowId) :
                                if (!isset($allDoc[$sName])) $allDoc[$sName] = array();
                                $allDoc[$sName][$pipelineFolder] = $this->dmetaFileConvert($uuid, $filepaths, $sName, $sNameLoc, $featureLoc, $ownerID);

                            endforeach;
                        }
                    endforeach;
                }
            endforeach;
            foreach ($dmeta_out as $dmeta_out_collection => $out_collectionData) :
                if ($targetDmetaColl == $dmeta_out_collection) {
                    foreach ($out_collectionData as $sName => $targetDmetaRowId) :
                        if (!empty($allDoc[$sName])) {
                            $this->patchDmetaAPI($dmeta_server, $dmeta_out_collection, $sName, $targetDmetaRowId, $allDoc[$sName], $accessToken, $project_pipeline_id, $dmeta_project);
                        }
                    endforeach;
                }
            endforeach;
        }
    }

    function savePubWeb($project_pipeline_id, $profileType, $profileId, $pipeline_id, $ownerID, $accessToken)
    {
        $data = json_encode("pubweb is not defined");
        $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
        // get $reportDir
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
        list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        $reportDir = $this->getReportDir($proPipeAll);
        //get pubWebDir
        $pipeData = json_decode($this->loadPipeline($pipeline_id, $ownerID));
        $pubWebDir = $pipeData[0]->{'publish_web_dir'};
        $pubDmetaDir = $pipeData[0]->{'publish_dmeta_dir'};
        $down_file_list = array();
        $down_file_dmeta_list = array();
        if (!empty($pubWebDir)) {
            $down_file_list = explode(',', $pubWebDir);
            foreach ($down_file_list as &$value) {
                $value = $reportDir . "/" . $value;
            }
            unset($value);
            $data = $this->saveNextflowLog($down_file_list,  $uuid, "pubweb", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);
        }
        if (!empty($pubDmetaDir)) {
            $pubDmetaDir = json_decode(htmlspecialchars_decode($pubDmetaDir, ENT_QUOTES), true);
            foreach ($pubDmetaDir as $value) {
                if (!empty($value["dir"])) {
                    $path = $reportDir . "/" . $value["dir"];
                    // only save files that are not found in pubweb list
                    if (!in_array($path, $down_file_list)) {
                        $down_file_dmeta_list[] = $path;
                    }
                }
            }
            $this->saveNextflowLog($down_file_dmeta_list,  $uuid, "pubdmeta", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);

            if (!empty($accessToken)) {
                error_log("* accessToken found");
                $this->sendDmetaReport($project_pipeline_id, $proPipeAll, $pipeData, $uuid, $ownerID, $accessToken);
            }
        }
        return $data;
    }


    function parseUpdateSessionUUID($dotNextflowLog, $dotNextflowLogInitial, $project_pipeline_id, $ownerID)
    {
        $mainUUID = "";
        $initialUUID = "";
        preg_match("/Session uuid:(.*)/", $dotNextflowLog, $mainUUIDAr);
        preg_match("/Session uuid:(.*)/", $dotNextflowLogInitial, $initialUUIDAr);
        if (!empty($mainUUIDAr[1])) $mainUUID = trim($mainUUIDAr[1]);
        if (!empty($initialUUIDAr[1])) $initialUUID = trim($initialUUIDAr[1]);
        if (!empty($project_pipeline_id)) {
            $this->updateRunSessionUUID($mainUUID, $initialUUID, $project_pipeline_id, $ownerID);
        }
    }

    //for lsf: Job <203477> is submitted to queue <long>.\n"
    //for sge: Your job 2259 ("run_bowtie2") has been submitted
    //for slurm: Submitted batch job 8748700
    function parseUpdateRunPid($serverLog, $project_pipeline_id, $process_id, $ownerID)
    {
        $runPid = "";
        $regEx = "";

        if (preg_match("/Job <(.*)> is submitted/", $serverLog)) {
            $regEx = "/Job <(.*)> is submitted/";
        } else if (preg_match("/job (.*) \(.*\) .* submitted/", $serverLog)) {
            $regEx = "/job (.*) \(.*\) .* submitted/";
        } else if (preg_match("/Submitted batch job (.*)/", $serverLog)) {
            $regEx = "/Submitted batch job (.*)/";
        }
        if (!empty($regEx)) {
            preg_match($regEx, $serverLog, $runPidAr);
            if (!empty($runPidAr) && !empty($runPidAr[1])) {
                $runPid = trim($runPidAr[1]);
            }
            if (!empty($runPid) && !empty($project_pipeline_id)) {
                $this->updateRunPid($project_pipeline_id, $runPid, $ownerID);
            } else if (!empty($runPid) && !empty($process_id)) {
                $this->updateProcessRunPid($process_id, $runPid, $ownerID);
            }
        }
    }

    function terminateApp($app_id, $ownerID)
    {
        $ret = array();
        $appData = $this->getApps($app_id, "", $ownerID);
        $appData = json_decode($appData, true);

        $uuid = $appData[0]["run_log_uuid"];
        $dir = $appData[0]["dir"];
        $filename = $appData[0]["filename"];
        $pUUID = $appData[0]["app_uid"];
        $pid = $appData[0]["pid"];
        error_log($pid);

        $targetDir = "{$this->run_path}/{$uuid}/pubweb/{$dir}/.app";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $log = "{$targetDir}/{$filename}.log{$pUUID}";
        $container_name = "{$pUUID}_{$ownerID}";

        $process_kill_cmd = "kill {$pid} >> {$log} 2>&1 &";
        $docker_kill_cmd = "docker kill {$container_name} >> {$log} 2>&1 && echo 'INFO: App successfully terminated.' >> {$log} 2>&1 & ";

        shell_exec($process_kill_cmd);
        shell_exec($docker_kill_cmd);
        return json_encode($ret);
    }

    function checkUpdateAppStatus($app_id, $ownerID)
    {
        $ret = array();
        $appData = $this->getApps($app_id, "", $ownerID);
        $appData = json_decode($appData, true);
        $run_log_uuid = $appData[0]["run_log_uuid"];
        $app_uid = $appData[0]["app_uid"];
        $filename = $appData[0]["filename"];
        $oldStatus = $appData[0]["status"];
        $newStatus = "";
        $ret["status"] = $oldStatus;
        $dir = $appData[0]["dir"];
        $appLog = json_decode($this->getFileContent($run_log_uuid, "pubweb/{$dir}/.app/{$filename}.log{$app_uid}", $ownerID));
        $jupyterServerLog = json_decode($this->getFileContent($run_log_uuid, "pubweb/.app/{$app_uid}_jupyter_server.log", $ownerID));
        $startupLog = json_decode($this->getFileContent($run_log_uuid, "pubweb/.app/{$app_uid}_startup_stderr.txt", $ownerID));
        $startupScript = "{$this->run_path}/{$run_log_uuid}/pubweb/.app/{$app_uid}_startup.ipynb";
        $startupScriptExists = false;
        // startupLog:
        if (file_exists($startupScript)) {
            $startupScriptExists = true;
        }
        $ret["log"] = $appLog . "\n" . $jupyterServerLog . "\n" . $startupLog;

        if (preg_match("/No such container/i", $appLog) || preg_match("/App successfully terminated/i", $appLog)) {
            $newStatus = "terminated";
        } else if (!$startupScriptExists && !empty($appLog) && preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $jupyterServerLog)) {
            preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $jupyterServerLog, $match);
            $ret["server_url"] = $match[1];
            $newStatus = "running";
        } else if ($startupScriptExists && !empty($startupLog) && preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $startupLog) && !empty($appLog) && preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $jupyterServerLog)) {
            // first get the jupyter port
            preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $jupyterServerLog, $match);
            $jupyterServer  = trim($match[1]);
            $ret["server_url"] = $match[1];
            error_log($jupyterServer);
            // $ret["server_url"] = $jupyterServer;
            $jupyterServerPort = parse_url($jupyterServer, PHP_URL_PORT);
            error_log($jupyterServerPort);

            // then get startup app port
            preg_match("/(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/", $startupLog, $match_startup);
            $startupServer  = trim($match_startup[1]);
            error_log($startupServer);

            $startupServerPort = parse_url($startupServer, PHP_URL_PORT);
            $ret["startup_server_url"] = "http://127.0.0.1:{$jupyterServerPort}/proxy/{$startupServerPort}/";
            $newStatus = "running";
        } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i", $appLog) || preg_match("/command not found/i", $appLog) || preg_match("/Got permission denied while trying to connect/i", $appLog)) {
            $newStatus = "error";
        }



        if (!empty($newStatus)) {
            $ret["status"] = $newStatus;
            $this->updateAppStatus($app_id, $newStatus, $ownerID);
        }
        return json_encode($ret);
    }

    // clean ScheduledRun related data from database since beforeschedule script failed.
    function cleanScheduledRun($project_pipeline_id, $uuid, $ownerID)
    {
        error_log("cleanScheduledRun $project_pipeline_id $uuid $ownerID");
        // 5. move log files to schedular directory. with date 
        $rundir = $uuid;
        $run_path_server = "{$this->run_path}/$rundir";
        $run_path_server_log = "{$this->run_path}/$rundir/run/log.txt";
        $run_path_server_log0 = "{$this->run_path}/$rundir/run/serverlog.txt";
        error_log("run_path_server_log $run_path_server_log");
        if (file_exists($run_path_server_log) && !empty("{$this->run_path}") && !empty($rundir)) {
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
            error_log(print_r($proPipeAll[0]->{'template_uuid'}, TRUE));

            if (!empty($proPipeAll[0]) && !empty($proPipeAll[0]->{'template_uuid'})) {
                $template_uuid = $proPipeAll[0]->{'template_uuid'};
                $now = date("Y-m-d_H-i-s");
                $target_template_file = "{$this->run_path}/$template_uuid/auto/log_txt/$now";
                error_log($target_template_file);
                if (!file_exists("{$this->run_path}/$template_uuid/auto")) {
                    mkdir("{$this->run_path}/$template_uuid/auto", 0755, true);
                }
                if (!file_exists("{$this->run_path}/$template_uuid/auto/log_txt")) {
                    mkdir("{$this->run_path}/$template_uuid/auto/log_txt", 0755, true);
                }
                system('cat ' . escapeshellarg("$run_path_server_log0") . " " . escapeshellarg("$run_path_server_log") . " >>" . escapeshellarg("$target_template_file"), $retval);
            }
        }


        // 6. remove log directory
        if (file_exists($run_path_server) && !empty("{$this->run_path}") && !empty($rundir)) {
            system('rm -rf ' . escapeshellarg("$run_path_server"), $retval);
            if ($retval != 0) {
                error_log("cleanScheduledRun failed.");
            }
        }
        // 1. delete project_pipeline_inputs
        $sql = "DELETE FROM $this->db.project_pipeline_input WHERE project_pipeline_id = '$project_pipeline_id'";
        self::runSQL($sql);
        // 2. delete run
        $sql = "DELETE FROM $this->db.run WHERE project_pipeline_id = '$project_pipeline_id'";
        self::runSQL($sql);
        // 3. delete run_log
        $sql = "DELETE FROM $this->db.run_log WHERE run_log_uuid = '$uuid'";
        self::runSQL($sql);
        // 4. delete project_pipeline
        $sql = "DELETE FROM $this->db.project_pipeline WHERE id = '$project_pipeline_id'";
        self::runSQL($sql);
    }

    function updateProPipeStatus($project_pipeline_id, $process_id, $loadtype, $ownerID)
    {
        $out = array();
        $duration = ""; //run duration
        $newRunStatus = "";
        $saveNextLog = "";
        $run_type = "";
        if (!empty($project_pipeline_id)) {
            $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
            $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
            if (empty($permCheck)) {
                exit();
            }
            // get active runs //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted, Manual
            // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. 
            $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
            //fix for old runs 
            if (empty($uuid)) {
                //old run folder format may exist (runID)
                $runStat = json_decode($this->getRunStatus($project_pipeline_id, $ownerID));
                if (!empty($runStat)) {
                    $runStatus = $runStat[0]->{"run_status"};
                    $last_run_uuid = "run" . $project_pipeline_id;
                    $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
                    $output_dir = $proPipeAll[0]->{'output_dir'};
                    $profile = $proPipeAll[0]->{'profile'};
                    $subRunLogDir = "";
                }
            } else {
                // latest last_uuid format exist
                $runDataJS = $this->getLastRunData($project_pipeline_id);
                if (!empty(json_decode($runDataJS, true))) {
                    $runData = json_decode($runDataJS, true)[0];
                    $runStatus = $runData["run_status"];
                    $last_run_uuid = $runData["last_run_uuid"];
                    $output_dir = $runData["output_dir"];
                    $profile = $runData["profile"];
                    $run_type = $runData["type"];
                    $subRunLogDir = "run";
                }
            }
        } else if (!empty($process_id)) {
            $process_data = json_decode($this->getProcessDataById($process_id, $ownerID), true);
            if (!empty($process_data[0])) {
                $pro_owner_id = $process_data[0]["owner_id"];
                $permCheck = $this->checkUserOwnPerm($pro_owner_id, $ownerID);
                if (empty($permCheck)) {
                    exit();
                }
                $runStatus = $process_data[0]["run_status"];
                $last_run_uuid = $process_data[0]["run_uuid"];
                $output_dir = $process_data[0]["test_work_dir"];
                $profile = $process_data[0]["test_env"];
                $subRunLogDir = "run";
            }
        }
        if (!empty($profile)) {
            $profileAr = explode("-", $profile);
            $profileType = $profileAr[0];
            $profileId = $profileAr[1];
            list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
            $executor = $cluDataArr[0]['executor'];
            if (!empty($last_run_uuid)) {
                $dolphin_path_real = "$output_dir/run{$project_pipeline_id}";
                $down_file_list = array("log.txt", ".nextflow.log", "report.html", "timeline.html", "trace.txt", "dag.html", "err.log", "initialrun/initial.log", "initialrun/.nextflow.log", "initialrun/trace.txt");
                foreach ($down_file_list as &$value) {
                    $value = $dolphin_path_real . "/" . $value;
                }
                unset($value);
                //wait for the downloading logs
                if ($loadtype == "slow") {
                    $saveNextLog = $this->saveNextflowLog($down_file_list, $last_run_uuid, "run", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);
                    sleep(5);
                    $out["saveNextLog"] = $saveNextLog;
                }
                $serverLog = json_decode($this->getFileContent($last_run_uuid, "run/serverlog.txt", $ownerID));

                $errorLog = json_decode($this->getFileContent($last_run_uuid, "$subRunLogDir/err.log", $ownerID));
                $initialLog = json_decode($this->getFileContent($last_run_uuid, "$subRunLogDir/initialrun/initial.log", $ownerID));
                $nextflowLog = json_decode($this->getFileContent($last_run_uuid, "$subRunLogDir/log.txt", $ownerID));
                $dotNextflowLog = json_decode($this->getFileContent($last_run_uuid, "$subRunLogDir/.nextflow.log", $ownerID));
                $dotNextflowLogInitial = json_decode($this->getFileContent($last_run_uuid, "$subRunLogDir/initialrun/.nextflow.log", $ownerID));
                $serverLog = isset($serverLog) ? trim($serverLog) : "";
                $errorLog = isset($errorLog) ? trim($errorLog) : "";
                $initialLog = isset($initialLog) ? trim($initialLog) : "";
                $nextflowLog = isset($nextflowLog) ? trim($nextflowLog) : "";
                $dotNextflowLog = isset($dotNextflowLog) ? trim($dotNextflowLog) : "";
                $dotNextflowLogInitial = isset($dotNextflowLogInitial) ? trim($dotNextflowLogInitial) : "";
                if (!empty($errorLog)) {
                    $serverLog = $serverLog . "\n" . $errorLog;
                }
                if (!empty($initialLog)) {
                    $nextflowLog = $initialLog . "\n" . $nextflowLog;
                }
                $out["serverLog"] = $serverLog;
                $out["nextflowLog"] = $nextflowLog;
                $this->parseUpdateRunPid($serverLog, $project_pipeline_id, $process_id, $ownerID);
                $this->parseUpdateSessionUUID($dotNextflowLog, $dotNextflowLogInitial, $project_pipeline_id, $ownerID);
                // keep this beforeschedule\.sh check at the top of if chain to prevent setting status to error with pid check
                if (preg_match("/[\n\r\s]error[\n\r\s:=]/i", $nextflowLog) && preg_match("/INFO: Executing beforeschedule\.sh\.\.\./", $nextflowLog) && !preg_match("/INFO: beforeschedule\.sh execution passed\./", $nextflowLog)) {
                    $newRunStatus = "Terminated";
                    $this->cleanScheduledRun($project_pipeline_id, $uuid, $ownerID);
                    // run is not active
                } else if ($runStatus === "Terminated" || $runStatus === "NextSuc" || $runStatus === "Error" || $runStatus === "NextErr" || $runStatus === "Manual") {
                    // when run hasn't finished yet and connection is down
                } else if ($loadtype == "slow" && $saveNextLog == "logNotFound" && ($runStatus != "Waiting" && $runStatus !== "init")) {
                    //log file might be deleted or couldn't read the log file
                    $newRunStatus = "Aborted";
                } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i", $serverLog) || preg_match("/command not found/i", $serverLog)) {
                    error_log("err1");
                    $newRunStatus = "Error";
                    // otherwise parse nextflow file to get status
                } else if (!empty($nextflowLog)) {
                    if (preg_match("/N E X T F L O W/", $nextflowLog)) {
                        //run completed with error
                        //load initial .nextflow.log and add && preg_match("/DEBUG nextflow.script.ScriptRunner - > Execution complete/",$dotInitNextflowLog)
                        if (preg_match("/##Success: failed/", $nextflowLog)) {
                            preg_match("/##Duration:(.*)\n/", $nextflowLog, $matchDur);
                            $duration = !empty($matchDur[1]) ? $matchDur[1] : "";
                            $newRunStatus = "NextErr";
                            //run completed with success
                        } else if (preg_match("/##Success: OK/", $nextflowLog) && preg_match("/DEBUG nextflow.script.ScriptRunner - > Execution complete/", $dotNextflowLog)) {
                            preg_match("/##Duration:(.*)/", $nextflowLog, $matchDur);
                            $duration = !empty($matchDur[1]) ? $matchDur[1] : "";
                            $newRunStatus = "NextSuc";
                            // run error
                            //"WARN: Failed to publish file" gives error
                            //|| preg_match("/failed/i",$nextflowLog) removed 
                        } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i", $nextflowLog) || preg_match("/\n -- Check script /", $nextflowLog) || preg_match("/Unable to parse config file/", $nextflowLog)) {
                            $confirmErr = true;
                            if (preg_match("/-- Execution is retried/i", $nextflowLog) || preg_match("/WARN: One or more errors/i", $nextflowLog) || preg_match("/-- Error is ignored/i", $nextflowLog)) {
                                //if only process retried, status shouldn't set as error.
                                $confirmErr = false;
                                $txt = trim($nextflowLog);
                                $lines = explode("\n", $txt);
                                for ($i = 0; $i < count($lines); $i++) {
                                    if (preg_match("/error/i", $lines[$i]) && !preg_match("/-- Execution is retried/i", $lines[$i]) && !preg_match("/WARN: One or more errors/i", $lines[$i]) && !preg_match("/-- Error is ignored/i", $lines[$i])) {
                                        error_log("WARN: One or more errors");
                                        error_log($lines[$i]);
                                        $confirmErr = true;
                                        break;
                                    }
                                }
                            }
                            if ($confirmErr == true) {
                                $newRunStatus = "NextErr";
                            } else {
                                $newRunStatus = "NextRun";
                            }
                        } else {
                            //update status as running  
                            $newRunStatus = "NextRun";
                        }
                        //Nextflow log file exist but /N E X T F L O W/ not printed yet
                    } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i", $nextflowLog) || preg_match("/command not found/i", $nextflowLog)) {
                        $newRunStatus = "Error";
                        // otherwise parse nextflow file to get status
                    } else {
                        $newRunStatus = "Waiting";
                    }
                } else {
                    //"Nextflow log is not exist yet."
                    $newRunStatus = "Waiting";
                }
                // check job pid:
                $inactiveJobStatus = array("Terminated", "NextSuc", "Error", "NextErr", "Manual");

                // if runStatus is active =>make sure pid is exist
                // if checkRunPid returns EXIT, ZOMBIE or UKNOWN code than trigger error
                if (!empty($project_pipeline_id) && (!empty($runStatus) && !in_array($runStatus, $inactiveJobStatus)) && (empty($newRunStatus) || !in_array($newRunStatus, $inactiveJobStatus))) {
                    if ($executor == "lsf") {
                        $pid = json_decode($this->getRunPid($project_pipeline_id))[0]->{'pid'};
                        if (!empty($pid)) {
                            $checkRunPid = $this->sshExeCommand("checkRunPid", $pid, $profileType, $profileId, $project_pipeline_id, $process_id, $ownerID);
                            $checkRunPid = json_decode($checkRunPid);
                            if ($checkRunPid == "exited") {
                                // $newRunStatus = "Error";
                                $run_path_real = $this->getServerRunPath($uuid);
                                $serverlogFile = "$run_path_real/serverlog.txt";
                                $file = fopen($serverlogFile, "a");
                                fwrite($file, "ERROR: Job exited.");
                                fclose($file);

                                $runSession =  json_decode($this->getRunSessionUUID($project_pipeline_id), TRUE);
                                if (!empty($runSession[0])) {
                                    $main_session_uuid = $runSession[0]["main_session_uuid"];
                                    $initial_session_uuid = $runSession[0]["initial_session_uuid"];
                                    //$dolphin_path_real/initialrun/.nextflow/cache/c5899bb6-8b3a-457a-b574-247d8a7c991e/db/LOCK
                                    //$dolphin_path_real/.nextflow/cache/7aec1b19-0e19-4b91-9134-62fe4274a4a6/db/LOCK
                                    $rmSessionCmd = "";
                                    if (!empty($main_session_uuid)) {
                                        $rmSessionCmd = "\"rm $dolphin_path_real/.nextflow/cache/$main_session_uuid/db/LOCK\" 2>&1";
                                    } else if (!empty($initial_session_uuid)) {
                                        $rmSessionCmd = "\"rm $dolphin_path_real/initialrun/.nextflow/cache/$initial_session_uuid/db/LOCK\" 2>&1";
                                    }
                                    if (!empty($rmSessionCmd)) {
                                        $cmdlog = array();
                                        $cmdlog = $this->execute_ssh_cmd($rmSessionCmd, $cmdlog, "rm_session_lock", "rm_session_cmd", $profileId, $profileType, $ownerID);
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($newRunStatus)) {
                    // send email for scheduled jobs
                    if ($newRunStatus == "NextRun" && $runStatus !== "NextRun" && $run_type == "auto") {
                        $this->sendRunStatusEmail($newRunStatus, $project_pipeline_id, $curr_ownerID);
                    }

                    if (!empty($project_pipeline_id)) {
                        $setStatus = $this->updateRunStatus($project_pipeline_id, $newRunStatus, $ownerID);
                        $setLog = $this->updateRunLog($project_pipeline_id, $newRunStatus, $duration, $ownerID);
                    } else if (!empty($process_id)) {
                        $setStatus = $this->updateProcessRunStatus($process_id, $newRunStatus, $ownerID);
                    }

                    $out["runStatus"] = $newRunStatus;
                    if (($newRunStatus == "NextErr" || $newRunStatus == "NextSuc" || $newRunStatus == "Error") && ($profileType == "amazon" || $profileType == "google")) {
                        error_log("triggerShutdown fast1");
                        $triggerShutdown = $this->triggerShutdown($profileId, $profileType, $ownerID, "fast");
                    }
                } else {
                    $out["runStatus"] = $runStatus;
                }
            }
        }
        return json_encode($out);
    }
    function getHostFile($path, $uid, $profile, $ownerID)
    {
        $ret = array();
        $upDir = "{$this->tmp_path}/runuploads/$uid";
        if (!empty($path) && strpos($path, '/') !== false) {
            // remove last slash if exist
            if (substr($path, -1) === "/") {
                $path = substr($path, 0, -1);
            }
            // download file from host machine
            if (!empty($path) && strpos($path, '/') !== false) {
                $pathAr = explode('/', $path);
                $fileName = array_pop($pathAr);
                $target_dir =  join("/", $pathAr);
                $localFile = "{$this->tmp_path}/runuploads/$uid/$fileName";
                $localFile_public = "{$this->base_path}/tmp/runuploads/$uid/$fileName";
                $profileAr = explode("-", $profile);
                $profileType = $profileAr[0];
                $profileId = $profileAr[1];
                if (!file_exists($upDir)) {
                    mkdir($upDir, 0755, true);
                }
                $rsync_log = $this->rsyncTransfer($localFile, $fileName, $target_dir, $upDir, $profileId, $profileType, $ownerID, "sync-download");
                $ret["rsync_log"] = $rsync_log;
            }
            // check if file ready
            if (file_exists("$upDir/$fileName")) {
                $ret["location"] = $localFile_public;
                $ret["name"] = $fileName;
            }
        }

        return $ret;
    }

    //------------- SideBar Funcs --------
    function getParentSideBar($ownerID)
    {
        if ($ownerID != '') {
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin") {
                $sql = "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                  FROM $this->db.process_group pg ";
                return self::queryTable($sql);
            }
            $sql = "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                FROM $this->db.process_group pg
                LEFT JOIN $this->db.user_group ug ON  pg.group_id=ug.g_id
                where pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15) ";
        } else {
            $sql = "SELECT DISTINCT group_name name, id FROM $this->db.process_group where perms = 63";
        }
        return self::queryTable($sql);
    }
    function getParentSideBarPipeline($ownerID)
    {
        if ($ownerID != '') {
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin") {
                $sql = "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                  FROM $this->db.pipeline_group pg ";
                return self::queryTable($sql);
            }
            $sql = "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                FROM $this->db.pipeline_group pg
                LEFT JOIN $this->db.user_group ug ON  pg.group_id=ug.g_id
                where pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15) ";
        } else {
            $sql = "SELECT DISTINCT group_name name, id FROM $this->db.pipeline_group where perms = 63";
        }
        return self::queryTable($sql);
    }



    function getPipelineSideBar($ownerID)
    {
        if ($ownerID != '') {
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin") {
                $where = " WHERE p.deleted = 0";
            } else {
                $where = " WHERE p.deleted = 0 AND (p.owner_id='$ownerID' OR (p.perms = 63 AND p.pin = 'true') OR (ug.u_id ='$ownerID' and p.perms = 15)) ";
            }
            $sql = "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin, pip.rev_id, pip.summary, pip.date_modified, u.username, pip.pipeline_group_id, pip.pipeline_gid
                FROM $this->db.biocorepipe_save pip
                INNER JOIN $this->db.users u ON pip.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON  pip.group_id=ug.g_id
                INNER JOIN (
                  SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                  FROM $this->db.biocorepipe_save p
                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                  LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                  $where
                  GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.deleted = 0 AND pip.pipeline_gid=b.pipeline_gid";
        } else {
            $sql = "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin, pip.rev_id,  pip.summary, pip.date_modified, u.username, pip.pipeline_group_id, pip.pipeline_gid
                FROM $this->db.biocorepipe_save pip
                INNER JOIN $this->db.users u ON pip.owner_id = u.id
                INNER JOIN (
                  SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                  FROM $this->db.biocorepipe_save p
                  INNER JOIN $this->db.users u ON pip.owner_id = u.id
                  WHERE p.perms = 63 AND p.deleted=0
                  GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid AND pip.pin = 'true' AND pip.deleted = 0";
        }
        return self::queryTable($sql);
    }

    function getSubMenuFromSideBar($parent, $ownerID)
    {
        $admin_only = "";
        $admin_only_group_by = "";
        if (!empty($ownerID)) {
            $userRole = $this->getUserRoleVal($ownerID);
            $where_pr = "pr.deleted=0 AND (pr.owner_id='$ownerID' OR (pr.perms = 63 AND pr.publicly_searchable = 'true') OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            if ($userRole == "admin") {
                $admin_only = ", p.owner_id, p.publish, MIN(IF((p.owner_id='$ownerID' OR (p.perms = 63 AND p.publicly_searchable = 'true') OR (ug.u_id ='$ownerID' and p.perms = 15)),0,1)) as admin_only";
                $where_pr = "pr.deleted=0";
                $admin_only_group_by = " GROUP BY p.id";
            }
        } else {
            $where_pr = "pr.deleted=0 AND (pr.perms = 63 AND pr.publicly_searchable = 'true') ";
        }
        $sql = "SELECT DISTINCT p.id, p.name, p.perms, p.group_id $admin_only
              FROM $this->db.process p
              LEFT JOIN $this->db.user_group ug ON  p.group_id=ug.g_id
              INNER JOIN $this->db.process_group pg
              ON p.process_group_id = pg.id and pg.group_name='$parent'
              INNER JOIN (
                SELECT pr.process_gid, MAX(pr.rev_id) rev_id
                FROM $this->db.process pr
                LEFT JOIN $this->db.user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.process_gid
              ) b ON p.rev_id = b.rev_id AND p.process_gid=b.process_gid AND p.deleted = 0
              $admin_only_group_by";

        return self::queryTable($sql);
    }
    // get pipelines belong to pipeline_group name=$parent
    function getSubMenuFromSideBarPipe($parent, $ownerID)
    {
        $admin_only = "";
        $admin_only_group_by = "";
        if (!empty($ownerID)) {
            $userRole = $this->getUserRoleVal($ownerID);
            $where_pr = "pr.deleted=0 AND (pr.owner_id='$ownerID' OR (pr.perms = 63 AND pr.publicly_searchable = 'true') OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            if ($userRole == "admin") {
                $admin_only = ", p.owner_id, p.publish, MIN(IF((p.owner_id='$ownerID' OR (p.publicly_searchable = 'true' AND p.perms = 63) OR (ug.u_id ='$ownerID' and p.perms = 15)),0,1)) as admin_only";
                $where_pr = "pr.deleted=0";
                $admin_only_group_by = " GROUP BY p.id";
            }
        } else {
            $where_pr = "pr.deleted=0 AND (pr.publicly_searchable = 'true' AND pr.perms = 63)";
        }
        $sql = "SELECT DISTINCT p.id, p.name, p.perms, p.group_id, p.pin $admin_only
              FROM $this->db.biocorepipe_save p
              LEFT JOIN $this->db.user_group ug ON  p.group_id=ug.g_id
              INNER JOIN $this->db.pipeline_group pg
              ON p.pipeline_group_id = pg.id and pg.group_name='$parent'
              INNER JOIN (
                SELECT DISTINCT pr.pipeline_gid, MAX(pr.rev_id) rev_id
                FROM $this->db.biocorepipe_save pr
                LEFT JOIN $this->db.user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.pipeline_gid
              ) b ON p.rev_id = b.rev_id AND p.pipeline_gid=b.pipeline_gid AND p.deleted = 0
              $admin_only_group_by";
        return self::queryTable($sql);
    }


    function getSubMenuFromSideBarProject($parent, $ownerID)
    {
        $where = "pp.deleted = 0 AND (pp.project_id='$parent' AND (pp.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and pp.perms = 15)))";
        $sql = "SELECT DISTINCT pp.id, pp.name, pj.owner_id, pp.project_id
              FROM $this->db.project_pipeline pp
              LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id
              INNER JOIN $this->db.project pj ON pp.project_id = pj.id and $where ";
        return self::queryTable($sql);
    }


    //    ---------------  Users ---------------
    function getUserByGoogleId($google_id)
    {
        $sql = "SELECT * FROM $this->db.users WHERE google_id = '$google_id' AND deleted=0";
        return self::queryTable($sql);
    }
    function getUserById($id)
    {
        $sql = "SELECT * FROM $this->db.users WHERE id = '$id' AND deleted=0";
        return self::queryTable($sql);
    }
    function getUserBySSOId($sso_id)
    {
        $sql = "SELECT * FROM $this->db.users WHERE sso_id = '$sso_id' AND deleted=0";
        return self::queryTable($sql);
    }
    function getUserByEmail($email)
    {
        $email = str_replace("'", "''", $email);
        $sql = "SELECT * FROM $this->db.users WHERE email = '$email' AND deleted=0";
        return self::queryTable($sql);
    }
    function getUserByEmailorUsername($emailusername)
    {
        $emailusername = strtolower(str_replace("'", "''", $emailusername));
        $sql = "SELECT * FROM $this->db.users WHERE (email = '$emailusername' OR username = '$emailusername' ) AND deleted=0";
        return self::queryTable($sql);
    }
    function updateUIDproPipeInput($id, $uid, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET uid='$uid', last_modified_user='$ownerID' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateUserManual($id, $name, $email, $username, $institute, $lab, $logintype, $ownerID)
    {
        $email = str_replace("'", "''", $email);
        $sql = "UPDATE $this->db.users SET name='$name', institute='$institute', username='$username', lab='$lab', logintype='$logintype', email='$email', last_modified_user='$ownerID' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateUserPassword($id, $pass_hash, $ownerID)
    {
        $sql = "UPDATE $this->db.users SET pass_hash='$pass_hash', last_modified_user='$ownerID' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertUserManual($name, $email, $username, $institute, $lab, $logintype, $role, $active, $pass_hash, $verify, $google_id)
    {
        $email = str_replace("'", "''", $email);
        $sql = "INSERT INTO $this->db.users(name, email, username, institute, lab, logintype, role, active, memberdate, date_created, date_modified, perms, pass_hash, verification, google_id) VALUES ('$name', '$email', '$username', '$institute', '$lab', '$logintype','$role', $active, now() , now(), now(), '3', '$pass_hash', '$verify', '$google_id')";
        return self::insTable($sql);
    }
    function checkExistUser($id, $username, $email)
    {
        $email = str_replace("'", "''", $email);
        $error = array();
        if (!empty($id)) { //update
            //check if username or e-mail is altered
            $userData = json_decode($this->getUserById($id))[0];
            $usernameDB = $userData->{'username'};
            $emailDB = $userData->{'email'};
            if ($usernameDB != $username) {
                $checkUsername = $this->queryAVal("SELECT id FROM $this->db.users WHERE deleted=0 AND username = LCASE('" . $username . "')");
            }
            if ($emailDB != $email) {
                $checkEmail = $this->queryAVal("SELECT id FROM $this->db.users WHERE deleted=0 AND email = LCASE('" . $email . "')");
            }
        } else { //insert
            $checkUsername = $this->queryAVal("SELECT id FROM $this->db.users WHERE deleted=0 AND username = LCASE('" . $username . "')");
            $checkEmail = $this->queryAVal("SELECT id FROM $this->db.users WHERE deleted=0 AND email = LCASE('" . $email . "')");
        }
        if (!empty($checkUsername)) {
            $error['username'] = "This username already exists.";
        }
        if (!empty($checkEmail)) {
            $error['email'] = "This e-mail already exists.";
        }
        return $error;
    }

    function changeActiveUser($user_id, $type)
    {
        if ($type == "activate" || $type == "activateSendUser") {
            $active = 1;
            $verify = "verification=NULL,";
        } else {
            $active = "NULL";
            $verify = "";
        }
        $sql = "UPDATE $this->db.users SET $verify active=$active, last_modified_user='$user_id' WHERE id = '$user_id'";
        return self::runSQL($sql);
    }
    function changeRoleUser($user_id, $type, $ownerID)
    {
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            $sql = "UPDATE $this->db.users SET role='$type', last_modified_user='$ownerID' WHERE id = '$user_id'";
            return self::runSQL($sql);
        }
    }

    //    ------------- Profiles   ------------
    function insertSSH($name, $hide, $check_userkey, $check_ourkey, $ownerID)
    {
        $sql = "INSERT INTO $this->db.ssh(name, hide, check_userkey, check_ourkey, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$hide', '$check_userkey', '$check_ourkey', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateSSH($id, $name, $hide, $check_userkey, $check_ourkey, $ownerID)
    {
        $sql = "UPDATE $this->db.ssh SET name='$name', hide='$hide', check_userkey='$check_userkey', check_ourkey='$check_ourkey', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertAmz($name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID)
    {
        $sql = "INSERT INTO $this->db.amazon_credentials (name, amz_def_reg, amz_acc_key, amz_suc_key, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$amz_def_reg', '$amz_acc_key', '$amz_suc_key', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateAmz($id, $name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID)
    {
        $sql = "UPDATE $this->db.amazon_credentials SET name='$name', amz_def_reg='$amz_def_reg', amz_acc_key='$amz_acc_key', amz_suc_key='$amz_suc_key', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function getWizardAll($ownerID)
    {
        $sql = "SELECT id, name, status, deleted FROM $this->db.wizard WHERE owner_id ='$ownerID' ";
        return self::queryTable($sql);
    }
    function checkActiveWizard($ownerID)
    {
        $sql = "SELECT id, name, status FROM $this->db.wizard WHERE owner_id ='$ownerID' AND status = 'active' AND deleted = 0 ";
        return self::queryTable($sql);
    }
    function getWizardByID($id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.wizard WHERE id = '$id' AND owner_id ='$ownerID' AND deleted = 0";
        return self::queryTable($sql);
    }
    function insertWizard($name, $data, $status, $ownerID)
    {
        $sql = "INSERT INTO $this->db.wizard (name, data, status, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$data', '$status', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateWizard($id, $name, $data, $status, $ownerID)
    {
        $sql = "UPDATE $this->db.wizard SET name='$name', data='$data', status='$status', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertGoogle($name, $project_id, $key_name, $ownerID)
    {
        $sql = "INSERT INTO $this->db.google_credentials (name, project_id, key_name, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
        ('$name', '$project_id', '$key_name', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateGoogle($id, $name, $project_id, $key_name, $ownerID)
    {
        $sql = "UPDATE $this->db.google_credentials SET name='$name', project_id='$project_id', key_name='$key_name', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function signJWTToken($id)
    {
        $token = "";
        $JWT_COOKIE_EXPIRES_IN = $this->JWT_COOKIE_EXPIRES_IN;
        if (!empty($this->JWT_SECRET) && !empty($JWT_COOKIE_EXPIRES_IN)) {
            /* creating access token */
            $issuedAt = time();
            // jwt valid for 365 days
            settype($JWT_COOKIE_EXPIRES_IN, 'integer');
            $expirationTime = $issuedAt + 60 * 60 * 24 * $JWT_COOKIE_EXPIRES_IN;
            $payload = array(
                'id' => $id,
                'iat' => $issuedAt,
                'exp' => $expirationTime,
            );

            $JWT = new JWT();
            $token = $JWT->encode($payload, $this->JWT_SECRET);
        }
        return $token;
    }

    //protect run/pipeline entrance 
    function insertToken($id, $np, $ownerID)
    {
        $token = $this->getKey(10);
        if (empty($token)) {
            exit();
        }
        $sql = "INSERT INTO $this->db.token (id, np, token, date_created, owner_id) 
                    VALUES ('$id', '$np', '$token', now() , '$ownerID')";
        self::insTable($sql);
        $curr_token = $this->queryAVal("SELECT token FROM $this->db.token WHERE np='$np' AND id='$id'");
        $ret = array();
        $ret["token"] = $curr_token;
        return json_encode($ret);
    }

    function insertAccessToken($accessToken, $expirationDate, $sso_user_id, $client_id, $scope, $user_id)
    {
        $sql = "INSERT INTO $this->db.access_tokens(accessToken, expirationDate, sso_user_id, client_id, scope, user_id) VALUES ('$accessToken', '$expirationDate',  '$sso_user_id', '$client_id', '$scope', '$user_id')";
        return self::insTable($sql);
    }
    function insertRefreshToken($refreshToken, $sso_user_id, $client_id, $scope, $user_id)
    {
        $sql = "INSERT INTO $this->db.refresh_tokens(refreshToken, sso_user_id, client_id, scope, user_id) VALUES ('$refreshToken', '$sso_user_id', '$client_id', '$scope' , '$user_id')";
        return self::insTable($sql);
    }
    function insertGithub($username, $email, $token, $ownerID)
    {
        $sql = "INSERT INTO $this->db.github (username, email, token, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$username', '$email', '$token', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateGithub($id, $username, $email, $token, $ownerID)
    {
        $sql = "UPDATE $this->db.github SET username='$username', email='$email', token='$token', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function getSSOAccessTokenByUserID($ownerID)
    {
        $sql = "SELECT accessToken FROM $this->db.access_tokens WHERE user_id = '$ownerID' ORDER BY id DESC LIMIT 1";
        return self::queryTable($sql);
    }
    function getSSOAccessToken($accessToken)
    {
        $sql = "SELECT * FROM $this->db.access_tokens WHERE accessToken = '$accessToken' ORDER BY id DESC LIMIT 1";
        return self::queryTable($sql);
    }
    function getGithub($ownerID)
    {
        $sql = "SELECT id, username, email, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM $this->db.github WHERE owner_id = '$ownerID' AND deleted=0";
        return self::queryTable($sql);
    }
    function getGithubbyID($id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.github WHERE owner_id = '$ownerID' and id = '$id' AND deleted=0";
        return self::queryTable($sql);
    }
    function getAmz($ownerID)
    {
        $sql = "SELECT id, name, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM $this->db.amazon_credentials WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getGoogle($ownerID)
    {
        $sql = "SELECT id, name, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM $this->db.google_credentials WHERE deleted = 0 AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getGooglebyID($id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.google_credentials WHERE deleted = 0 AND owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    function getAmzbyID($id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.amazon_credentials WHERE owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    function getSSH($userRole, $admin_id, $type, $ownerID)
    {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id) || $type == "hidden") {
            $hide = "";
        }
        $sql = "SELECT * FROM $this->db.ssh WHERE $hide owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getSSHbyID($id, $userRole, $admin_id, $ownerID)
    {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id)) {
            $hide = "";
        }
        $sql = "SELECT * FROM $this->db.ssh WHERE $hide owner_id = '$ownerID' AND id = '$id'";
        return self::queryTable($sql);
    }
    function getSSHbyName($name, $userRole, $admin_id, $ownerID)
    {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id)) {
            $hide = "";
        }
        $sql = "SELECT * FROM $this->db.ssh WHERE $hide owner_id = '$ownerID' AND name = BINARY '$name'";
        return self::queryTable($sql);
    }
    function getProfileClusterSSHWithID($id)
    {
        $sql = "SELECT p.ssh_id, p.owner_id
                FROM $this->db.profile_cluster p
                WHERE p.id = '$id'";
        return self::queryTable($sql);
    }
    function getProfileClusterbyID($id, $ownerID)
    {
        $where = " WHERE (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' AND p.perms = 15)) AND p.id = '$id'";
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            $where = " WHERE p.id = '$id'";
        }
        $sql = "SELECT p.* 
                FROM $this->db.profile_cluster p
                INNER JOIN $this->db.users u ON p.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                $where";
        return self::queryTable($sql);
    }
    function getProfileCluster($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_cluster WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getRunProfileCluster($ownerID)
    {
        $sql = "SELECT DISTINCT p.* FROM $this->db.profile_cluster p 
                INNER JOIN $this->db.users u ON p.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }

    function getRunStatsByPipeline($type)
    {
        if ($type == "runAttempt") {
            $sql = "SELECT rl.owner_id as own, rl.id, rl.run_status as stat, rl.duration as dur, rl.date_created as date, b.pipeline_id as pip, b.pipeline_gid as gid, b.name as pname, e.name as oname,  e.lab as olab
            FROM $this->db.run_log rl
            INNER JOIN (
              SELECT pp.id, pp.pipeline_id, c.pipeline_gid, c.name
              FROM $this->db.project_pipeline pp
                INNER JOIN (
                SELECT p.id, p.pipeline_gid, p.name
                FROM $this->db.biocorepipe_save p
                ) c ON pp.pipeline_id = c.id
              ) b ON rl.project_pipeline_id = b.id
              INNER JOIN (
              SELECT u.id, u.name, u.lab
              FROM $this->db.users u
              ) e ON rl.owner_id = e.id 
              WHERE rl.deleted = 0";
            return self::queryTable($sql);
        } else if ($type == "run") {
            $sql = "SELECT DISTINCT pp.owner_id as own, pp.id, r.run_status as stat, r.date_created_last_run as date,
            pip.id as pip, pip.pipeline_gid as gid, pip.name as pname, u.name as oname,  u.lab as olab
            FROM $this->db.project_pipeline pp
            LEFT JOIN $this->db.run r  ON r.project_pipeline_id = pp.id
            INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
            INNER JOIN $this->db.users u ON pp.owner_id = u.id 
            WHERE pp.deleted = 0";
            return self::queryTable($sql);
        } else if ($type == "file_count") {
            $sql = "SELECT  count(pp.id) AS fileCount, pp.owner_id as own, pip.id as pip, pip.pipeline_gid as gid, pip.name as pname
            FROM $this->db.project_pipeline pp
            LEFT JOIN  $this->db.project_pipeline_input pi ON pi.project_pipeline_id = pp.id 
            LEFT JOIN  $this->db.file_collection fc ON pi.collection_id = fc.c_id 
            INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
            WHERE pp.deleted = 0 AND pi.collection_id != 0 AND pi.deleted = 0
            GROUP BY pp.id";
            return self::queryTable($sql);
        } else if ($type == "active_user") {
            $sql = "SELECT  owner_id, DATE_FORMAT(date_created, '%Y') as year, DATE_FORMAT(date_created, '%m') as month
            FROM $this->db.run_log 
            GROUP BY year, month, owner_id";
            return self::queryTable($sql);
        }
    }

    function getCollections($ownerID)
    {
        $sql = "SELECT c.id, c.name, count(fc.c_id) AS fileCount
                FROM $this->db.collection c
                INNER JOIN $this->db.file_collection fc ON c.id=fc.c_id
                WHERE c.owner_id = '$ownerID' AND c.deleted = 0 AND fc.deleted=0
                GROUP BY fc.c_id";
        return self::queryTable($sql);
    }
    function getCollectionById($id, $userRole, $ownerID)
    {

        $where = " WHERE c.deleted = 0 AND (c.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' AND c.perms = 15)) AND c.id = '$id'";
        if ($userRole == "admin") {
            $where = " WHERE c.id = '$id' AND c.deleted = 0";
        }

        $sql = "SELECT c.id, c.name, c.owner_id, c.group_id, c.perms 
        FROM $this->db.collection  c
        INNER JOIN $this->db.users u ON c.owner_id = u.id
        LEFT JOIN $this->db.user_group ug ON c.group_id=ug.g_id
        $where";
        return self::queryTable($sql);
    }
    function getFiles($ownerID)
    {
        $sql = "SELECT DISTINCT f.id, f.name, f.files_used, f.file_dir, f.collection_type, f.archive_dir, f.s3_archive_dir, f.gs_archive_dir, f.date_created, f.date_modified, f.last_modified_user, f.file_type, f.run_env, 
              GROUP_CONCAT( DISTINCT fp.p_id order by fp.p_id) as p_id,
              GROUP_CONCAT( DISTINCT p.name order by p.name) as project_name,
              GROUP_CONCAT( DISTINCT c.name order by c.name) as collection_name,
              GROUP_CONCAT( DISTINCT c.id order by c.id) as collection_id
              FROM $this->db.file f
              LEFT JOIN $this->db.file_collection fc  ON f.id = fc.f_id
              LEFT JOIN $this->db.file_project fp ON f.id = fp.f_id
              LEFT JOIN $this->db.collection c on fc.c_id = c.id
              LEFT JOIN $this->db.project p on fp.p_id = p.id
              WHERE f.owner_id = '$ownerID' AND f.deleted = 0 AND (fc.deleted = 0 OR fc.deleted IS NULL) AND (fp.deleted = 0 OR fp.deleted IS NULL) AND (p.deleted = 0 OR p.deleted IS NULL) AND (c.deleted = 0 OR c.deleted IS NULL)
              GROUP BY f.id, f.name, f.files_used, f.file_dir, f.collection_type, f.archive_dir, f.s3_archive_dir, f.gs_archive_dir, f.date_created, f.date_modified, f.last_modified_user, f.file_type, f.run_env";
        return self::queryTable($sql);
    }
    function getProfiles($type, $ownerID)
    {
        if (empty($type)) {
            $proClu = $this->getProfileCluster($ownerID);
            $proAmz = $this->getProfileAmazon($ownerID);
            $proGoog = $this->getProfileGoogle($ownerID);
        } else if ($type == "public") {
            $proClu = $this->getPublicProfileCluster($ownerID);
            $proAmz = $this->getPublicProfileAmazon($ownerID);
            $proGoog = $this->getPublicProfileGoogle($ownerID);
        } else if ($type == "run") {
            $proClu = $this->getRunProfileCluster($ownerID);
            $proAmz = $this->getRunProfileAmazon($ownerID);
            $proGoog = $this->getRunProfileGoogle($ownerID);
        }
        $clu_obj = json_decode($proClu, true);
        $amz_obj = json_decode($proAmz, true);
        $goog_obj = json_decode($proGoog, true);
        $merged = array_merge($clu_obj, $amz_obj);
        $result = array_merge($goog_obj, $merged);
        return json_encode($result);
    }

    function getPublicProfileCluster($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_cluster WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getProfileAmazon($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_amazon WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getProfileGoogle($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_google WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getPublicProfileAmazon($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_amazon WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getPublicProfileGoogle($ownerID)
    {
        $sql = "SELECT * FROM $this->db.profile_google WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getRunProfileAmazon($ownerID)
    {
        $sql = "SELECT DISTINCT p.* FROM $this->db.profile_amazon p 
                INNER JOIN $this->db.users u ON p.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getRunProfileGoogle($ownerID)
    {
        $sql = "SELECT DISTINCT p.* FROM $this->db.profile_google p 
                INNER JOIN $this->db.users u ON p.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getProfileCloudbyID($id, $cloud, $ownerID)
    {
        $where = " WHERE (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' AND p.perms = 15)) AND p.id = '$id'";
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            $where = " WHERE p.id = '$id'";
        }
        $sql = "SELECT p.*, u.username
                FROM $this->db.profile_{$cloud} p
                INNER JOIN $this->db.users u ON p.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                $where";
        return self::queryTable($sql);
    }
    function getActiveRunbyProID($id, $cloud, $ownerID)
    {
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.date_modified, pp.owner_id, r.run_status
            FROM $this->db.project_pipeline pp
            INNER JOIN $this->db.run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid AND r.deleted=0 AND pp.deleted=0 AND pp.owner_id = '$ownerID' AND pp.profile = '$cloud-$id' AND (r.run_status = 'init' OR r.run_status = 'Waiting' OR r.run_status = 'NextRun' OR r.run_status = 'Aborted')";
        return self::queryTable($sql);
    }
    function insertProfileLocal($name, $executor, $next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID)
    {
        $sql = "INSERT INTO $this->db.profile_local (name, executor, next_path, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$name', '$executor','$next_path', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileLocal($id, $name, $executor, $next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_local SET name='$name', executor='$executor', next_path='$next_path', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu',  last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateProfileVariable($id, $table, $variable, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_$table SET variable='$variable', last_modified_user ='$ownerID', date_modified= now() WHERE id = '$id'";
        return self::runSQL($sql);
    }


    function insertProfileCluster($name, $executor, $next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $bash_variable, $group_id, $auto_workdir, $perms, $amazon_cre_id, $def_publishdir, $def_workdir, $ownerID)
    {
        $sql = "INSERT INTO $this->db.profile_cluster(name, executor, next_path, port, singu_cache, username, hostname, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, next_clu_opt, job_clu_opt, public, variable, bash_variable, group_id, auto_workdir, owner_id, perms, date_created, date_modified, amazon_cre_id, def_publishdir, def_workdir, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$username', '$hostname', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ssh_id', '$next_clu_opt','$job_clu_opt', '$public', '$variable', '$bash_variable', '$group_id', '$auto_workdir', '$ownerID', '$perms', now(), now(), '$amazon_cre_id','$def_publishdir', '$def_workdir','$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileCluster($id, $name, $executor, $next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $bash_variable, $group_id, $auto_workdir, $perms, $amazon_cre_id, $def_publishdir, $def_workdir, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_cluster SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', username='$username', hostname='$hostname', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', job_clu_opt='$job_clu_opt', next_clu_opt='$next_clu_opt', ssh_id='$ssh_id', public='$public', variable='$variable', bash_variable='$bash_variable', group_id='$group_id', auto_workdir='$auto_workdir', last_modified_user ='$ownerID', perms='$perms', amazon_cre_id='$amazon_cre_id',  def_publishdir='$def_publishdir', def_workdir='$def_workdir'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertProfileAmazon($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID)
    {
        $sql = "INSERT INTO $this->db.profile_amazon(name, executor, next_path, port, singu_cache, instance_type, image_id, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, subnet_id, shared_storage_id, shared_storage_mnt, ssh_id, amazon_cre_id, next_clu_opt, job_clu_opt, public, security_group, variable, bash_variable, group_id, auto_workdir, def_publishdir, def_workdir,  owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$ins_type', '$image_id', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$subnet_id','$shared_storage_id','$shared_storage_mnt','$ssh_id','$amazon_cre_id', '$next_clu_opt', '$job_clu_opt', '$public', '$security_group', '$variable', '$bash_variable', '$group_id', '$auto_workdir', '$def_publishdir', '$def_workdir', '$ownerID', '$perms', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileAmazon($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_amazon SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', instance_type='$ins_type', image_id='$image_id', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', subnet_id='$subnet_id', shared_storage_id='$shared_storage_id', shared_storage_mnt='$shared_storage_mnt', ssh_id='$ssh_id', next_clu_opt='$next_clu_opt', job_clu_opt='$job_clu_opt', amazon_cre_id='$amazon_cre_id', public='$public', security_group='$security_group', variable='$variable', bash_variable='$bash_variable', group_id='$group_id', auto_workdir='$auto_workdir', def_publishdir='$def_publishdir', def_workdir='$def_workdir', last_modified_user ='$ownerID', perms='$perms' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertProfileGoogle($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID)
    {
        $sql = "INSERT INTO $this->db.profile_google(name, executor, next_path, port, singu_cache, instance_type, image_id, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, google_cre_id, next_clu_opt, job_clu_opt, public, zone, variable, bash_variable, group_id, auto_workdir, def_publishdir, def_workdir, owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$ins_type', '$image_id', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ssh_id', '$google_cre_id', '$next_clu_opt', '$job_clu_opt', '$public', '$zone', '$variable', '$bash_variable','$group_id', '$auto_workdir', '$def_publishdir', '$def_workdir', '$ownerID', '$perms', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileGoogle($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_google SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', instance_type='$ins_type', image_id='$image_id', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu',  ssh_id='$ssh_id', next_clu_opt='$next_clu_opt', job_clu_opt='$job_clu_opt', google_cre_id='$google_cre_id', public='$public', zone='$zone', variable='$variable',  bash_variable='$bash_variable', group_id='$group_id', auto_workdir='$auto_workdir', def_publishdir='$def_publishdir', def_workdir='$def_workdir', last_modified_user ='$ownerID', perms='$perms' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateProfileCloudOnStart($id, $nodes, $autoscale_check, $autoscale_maxIns, $autoscale_minIns, $autoshutdown_date, $autoshutdown_active, $autoshutdown_check, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET nodes='$nodes', autoscale_check='$autoscale_check', autoscale_maxIns='$autoscale_maxIns', autoscale_minIns='$autoscale_minIns',  autoshutdown_date=" . ($autoshutdown_date == NULL ? "NULL" : "'$autoshutdown_date'") . ", autoshutdown_active='$autoshutdown_active', autoshutdown_check='$autoshutdown_check', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownDate($id, $autoshutdown_date, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET autoshutdown_date=" . ($autoshutdown_date == NULL ? "NULL" : "'$autoshutdown_date'") . ", last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownActive($id, $autoshutdown_active, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET autoshutdown_active='$autoshutdown_active', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownCheck($id, $autoshutdown_check, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET autoshutdown_check='$autoshutdown_check', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudProStatus($id, $status, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAmazonProNodeStatus($id, $node_status, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_amazon SET node_status='$node_status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAmazonProPid($id, $pid, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_amazon SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudProSSH($id, $sshText, $cloud, $ownerID)
    {
        $sql = "UPDATE $this->db.profile_{$cloud} SET ssh='$sshText', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function getCloudProSSH($id, $cloud, $ownerID)
    {
        $sql = "SELECT ssh FROM $this->db.profile_{$cloud} WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function removeAmz($id)
    {
        $sql = "DELETE FROM $this->db.amazon_credentials WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeSSH($id)
    {
        $sql = "DELETE FROM $this->db.ssh WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProLocal($id)
    {
        $sql = "DELETE FROM $this->db.profile_local WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProCluster($id)
    {
        $sql = "DELETE FROM $this->db.profile_cluster WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProAmazon($id)
    {
        $sql = "DELETE FROM $this->db.profile_amazon WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProGoogle($id)
    {
        $sql = "DELETE FROM $this->db.profile_google WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeSSOAccessToken($accessToken)
    {
        $sql = "DELETE FROM $this->db.access_tokens WHERE accessToken = '$accessToken'";
        return self::runSQL($sql);
    }
    //    ------------- Parameters ------------
    function getAllParameters($ownerID)
    {
        if ($ownerID == "") {
            $ownerID = "''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT p.id, p.file_type, p.qualifier, p.name, p.group_id, p.perms FROM $this->db.parameter p";
                    return self::queryTable($sql);
                }
            }
        }

        $sql = "SELECT DISTINCT p.id, p.file_type, p.qualifier, p.name, p.group_id, p.perms
              FROM $this->db.parameter p
              LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
              WHERE p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
        return self::queryTable($sql);
    }
    function getEditDelParameters($ownerID)
    {
        $sql = "SELECT DISTINCT * FROM $this->db.parameter p
              WHERE p.owner_id = '$ownerID' AND id not in (select parameter_id from $this->db.process_parameter WHERE owner_id != '$ownerID')";
        return self::queryTable($sql);
    }

    function insertParameter($name, $qualifier, $file_type, $ownerID)
    {
        $sql = "INSERT INTO $this->db.parameter(name, qualifier, file_type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
              ('$name', '$qualifier', '$file_type', '$ownerID', 63, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }

    function updateParameter($id, $name, $qualifier, $file_type, $ownerID)
    {
        $sql = "UPDATE $this->db.parameter SET name='$name', qualifier='$qualifier', last_modified_user ='$ownerID', file_type='$file_type'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateProPipe_ProjectID($project_pipeline_id, $new_project_id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET project_id='$new_project_id', group_id='$group_id', perms='$perms', last_modified_user='$ownerID', date_modified=now()  WHERE id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateProPipeInput_ProjectID($project_pipeline_id, $new_project_id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET project_id='$new_project_id', group_id='$group_id', perms='$perms', last_modified_user ='$ownerID', date_modified=now()  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function insertProcessGroup($group_name, $ownerID)
    {
        $sql = "INSERT INTO $this->db.process_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updateProcessGroup($id, $group_name, $ownerID)
    {
        $sql = "UPDATE $this->db.process_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAllProcessGroupByGid($process_gid, $process_group_id, $ownerID)
    {
        $sql = "UPDATE $this->db.process SET process_group_id='$process_group_id', last_modified_user ='$ownerID', date_modified=now()  WHERE process_gid = '$process_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateAllProcessNameByGid($process_gid, $name, $ownerID)
    {
        $sql = "UPDATE $this->db.process SET name='$name', last_modified_user ='$ownerID', date_modified=now()  WHERE process_gid = '$process_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateAllPipelineGroupByGid($pipeline_gid, $pipeline_group_id, $name, $ownerID)
    {
        $sql = "UPDATE $this->db.biocorepipe_save SET pipeline_group_id='$pipeline_group_id', name='$name', last_modified_user ='$ownerID', date_modified=now() WHERE deleted = 0 AND pipeline_gid = '$pipeline_gid'";
        return self::runSQL($sql);
    }
    function removeParameter($id)
    {
        $sql = "DELETE FROM $this->db.parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcessGroup($id)
    {
        $sql = "DELETE FROM $this->db.process_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removePipelineGroup($id)
    {
        $sql = "DELETE FROM $this->db.pipeline_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
    // --------- Process -----------
    function getAllProcessGroups($ownerID)
    {
        $sql = "SELECT DISTINCT pg.id, pg.group_name
              FROM $this->db.process_group pg";
        return self::queryTable($sql);
    }
    function getProcessGroupById($id)
    {
        $sql = "SELECT DISTINCT pg.group_name
              FROM $this->db.process_group pg
              WHERE pg.id = '$id'";
        return self::queryTable($sql);
    }
    function getProcessGroupByName($group_name)
    {
        $sql = "SELECT DISTINCT pg.id
              FROM $this->db.process_group pg
              WHERE pg.group_name = '$group_name'";
        return self::queryTable($sql);
    }
    function getCollectionByName($col_name, $owner_id)
    {
        $sql = "SELECT DISTINCT c.id
              FROM $this->db.collection c
              WHERE c.deleted = 0 AND c.name = '$col_name' AND owner_id='$owner_id'";
        return self::queryTable($sql);
    }
    function getPipelineGroupByName($group_name)
    {
        $sql = "SELECT DISTINCT pg.id
              FROM $this->db.pipeline_group pg
              WHERE pg.group_name = '$group_name'";
        return self::queryTable($sql);
    }
    function getParameterByName($name, $qualifier, $file_type)
    {
        $sql = "SELECT DISTINCT id FROM $this->db.parameter
              WHERE name = '$name' AND qualifier = '$qualifier' AND file_type = '$file_type'";
        return self::queryTable($sql);
    }
    function getEditDelProcessGroups($ownerID)
    {
        $sql = "SELECT DISTINCT id, group_name
              FROM $this->db.process_group pg
              Where pg.owner_id = '$ownerID' AND id not in (select process_group_id from $this->db.process where owner_id != '$ownerID')";
        return self::queryTable($sql);
    }
    //$table="process" or "biocorepipe_save"
    function getSharedItemByUser($table, $u_id, $g_id)
    {
        $sql = "SELECT id, name
                FROM $this->db.$table 
                WHERE owner_id = '$u_id' AND group_id = '$g_id' AND (perms=11 OR perms=15)";
        return self::queryTable($sql);
    }

    function insertProcess($name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $rev_id, $rev_comment, $group, $perms, $script_mode, $script_mode_header, $process_uuid, $process_rev_uuid, $test_env, $test_work_dir, $docker_check, $docker_img, $docker_opt, $singu_check, $singu_img, $singu_opt, $script_test, $script_test_mode, $write_group_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.process(name, process_gid, summary, process_group_id, script, script_header, script_footer, rev_id, rev_comment, owner_id, date_created, date_modified, last_modified_user, perms, group_id, script_mode, script_mode_header, process_uuid, process_rev_uuid, test_env, test_work_dir, docker_check, docker_img, docker_opt, singu_check, singu_img, singu_opt, script_test, script_test_mode, write_group_id) VALUES ('$name', '$process_gid', '$summary', '$process_group_id', '$script', '$script_header', '$script_footer', '$rev_id','$rev_comment', '$ownerID', now(), now(), '$ownerID', '$perms', '$group','$script_mode', '$script_mode_header', '$process_uuid', '$process_rev_uuid', '$test_env', '$test_work_dir', '$docker_check', '$docker_img', '$docker_opt', '$singu_check', '$singu_img', '$singu_opt', '$script_test', '$script_test_mode', '$write_group_id')";
        return self::insTable($sql);
    }

    function updateProcess($id, $name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $group, $perms, $script_mode, $script_mode_header, $test_env, $test_work_dir, $docker_check, $docker_img, $docker_opt, $singu_check, $singu_img, $singu_opt, $script_test, $script_test_mode, $write_group_id, $ownerID)
    {
        $sql = "UPDATE $this->db.process SET name= '$name', process_gid='$process_gid', summary='$summary', process_group_id='$process_group_id', script='$script', script_header='$script_header',  script_footer='$script_footer', last_modified_user='$ownerID', group_id='$group', perms='$perms', script_mode='$script_mode', date_modified = now(), script_mode_header='$script_mode_header', test_env='$test_env', test_work_dir='$test_work_dir', docker_check='$docker_check', docker_img='$docker_img', docker_opt='$docker_opt', singu_check='$singu_check', singu_img='$singu_img', singu_opt='$singu_opt', script_test='$script_test', script_test_mode='$script_test_mode', write_group_id='$write_group_id' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcess($id)
    {
        $sql = "DELETE FROM $this->db.process WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProject($id, $name, $ownerID)
    {
        //check if other user's run are found in the project
        $table = "project";
        list($checkUsed, $warn) = $this->checkUsed($table, $name, $id, $ownerID);
        if (!empty($checkUsed)) {
            return json_encode("Your project is not allowed to delete since it's used by other group members. For details, please contact with admin.");
        } else {
            $this->removeProjectPipelineInputbyProjectID($id);
            $this->removeRunByProjectID($id);
            $this->removeProjectPipelinebyProjectID($id);
            $this->removeProjectInputbyProjectID($id);
            $sql = "UPDATE $this->db.project SET deleted = 1, date_modified = now() WHERE owner_id = '$ownerID' AND id = '$id'";
            return self::runSQL($sql);
        }
    }

    function removeContainer($id, $ownerID)
    {
        $sql = "UPDATE $this->db.container SET deleted = 1, date_modified = now() WHERE owner_id = '$ownerID' AND id = '$id'";
        return self::runSQL($sql);
    }

    function checkGroupItem($table, $g_id, $ownerID)
    {
        $checkGroupItem = $this->queryAVal("SELECT id, name 
                                    FROM $this->db.$table 
                                    WHERE deleted = 0 AND owner_id != '$ownerID' AND group_id ='$g_id'");
        if (!empty($checkGroupItem)) {
            return "It is not allowed to remove your group because your group has been used by other members. For details, please contact with admin.";
        } else {
            return "";
        }
    }

    function removeGroup($g_id, $ownerID)
    {
        $ret = array();
        $checkUserOwnGroup = $this->queryAVal("SELECT id FROM $this->db.groups WHERE owner_id = '$ownerID' AND id = '$g_id'");
        if (empty($checkUserOwnGroup)) {
            $ret["error"] = "You don't have permission to remove this group.";
            return json_encode($ret);
        } else {
            // check if group is used in any processes/pipeline that are shared
            $warn = $this->checkGroupItem("process", $g_id, $ownerID);
            if (empty($warn)) {
                $warn = $this->checkGroupItem("biocorepipe_save", $g_id, $ownerID);
            }
            if (empty($warn)) {
                $this->removeUserGroup($g_id);
                $sql = "DELETE FROM $this->db.groups WHERE id = '$g_id'";
                return self::runSQL($sql);
            } else {
                $ret["error"] = $warn;
                return json_encode($ret);
            }
        }
    }

    function checkUsersSharedItem($table, $u_id, $g_id)
    {
        $item_ids = json_decode($this->getSharedItemByUser($table, $u_id, $g_id));
        for ($i = 0; $i < count($item_ids); $i++) {
            $item_id = $item_ids[$i]->{"id"};
            $warnName = $item_ids[$i]->{"name"};
            list($checkUsed, $warn) = $this->checkUsed($table, $warnName, $item_id, $u_id);
            if (!empty($checkUsed)) {
                return "It is not allowed to remove user from your group because user has shared processes/pipelines that are used by other group members. For details, please contact with admin.";
            }
        }
        return "";
    }

    function removeUserFromGroup($u_id, $g_id, $ownerID)
    {
        $ret = array();
        $checkUserOwnGroup = $this->queryAVal("SELECT id FROM $this->db.groups WHERE owner_id = '$ownerID' AND id = '$g_id'");
        if (empty($checkUserOwnGroup)) {
            $ret["error"] = "You don't have permission to remove user from group.";
            return json_encode($ret);
        } else {
            // check if user has any process/pipeline shared within group and used within in pipeline that owner is not the user
            //get all shared process and pipeline of the user.
            $warn = $this->checkUsersSharedItem("process", $u_id, $g_id);
            if (empty($warn)) {
                $warn = $this->checkUsersSharedItem("biocorepipe_save", $u_id, $g_id);
            }
            if (empty($warn)) {
                $sql = "DELETE FROM $this->db.user_group WHERE g_id = '$g_id' AND u_id='$u_id'";
                return self::runSQL($sql);
            } else {
                $ret["error"] = $warn;
                return json_encode($ret);
            }
        }
    }
    function removeUserGroup($id)
    {
        $sql = "DELETE FROM $this->db.user_group WHERE g_id = '$id'";
        return self::runSQL($sql);
    }
    function removeUser($id, $ownerID)
    {
        $sql = "UPDATE $this->db.users SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeGithub($id, $ownerID)
    {
        $sql = "UPDATE $this->db.github SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }


    function removeGoogle($id, $ownerID)
    {
        $sql = "UPDATE $this->db.google_credentials SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function removeWizard($id, $ownerID)
    {
        $sql = "UPDATE $this->db.wizard SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function removeProjectPipeline($id)
    {
        $sql = "UPDATE $this->db.project_pipeline SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeRun($id)
    {
        $sql = "UPDATE $this->db.run SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    function removeRunLogByPipe($id)
    {
        $sql = "UPDATE $this->db.run_log SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    function removeRunLog($id, $ownerID)
    {
        $sql = "UPDATE $this->db.run_log SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function recoverRunLog($id, $ownerID)
    {
        $sql = "UPDATE $this->db.run_log SET deleted = 0, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function purgeRunLog($id, $ownerID)
    {
        $log = $this->getRunLogById($id, $ownerID);
        $delsuccess = 0;
        if (!empty($log)) {
            $logData = json_decode($log);
            if (!empty($logData[0])) {
                $uuid = $logData[0]->{"run_log_uuid"};
                $project_pipeline_id = $logData[0]->{"project_pipeline_id"};
                if (empty($uuid)) {
                    $rundir = "run$project_pipeline_id";
                } else {
                    $rundir = $uuid;
                }
                $run_path_server = "{$this->run_path}/$rundir";
                if (!file_exists($run_path_server)) {
                    $delsuccess = 1;
                } else {
                    if (!empty("{$this->run_path}")) {
                        system('rm -rf ' . escapeshellarg("$run_path_server"), $retval);
                        if ($retval == 0) {
                            $delsuccess = 1;
                        }
                    }
                }
            }
        }
        if (!empty($delsuccess)) {
            $sql = "DELETE FROM $this->db.run_log WHERE id = '$id' AND owner_id='$ownerID'";
            return self::runSQL($sql);
        } else {
            return json_encode("Error occured.");
        }
    }
    function removeInput($id)
    {
        $sql = "DELETE FROM $this->db.input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeFile($id, $ownerID)
    {
        $sql = "UPDATE $this->db.file SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeCollection($id, $ownerID)
    {
        $sql = "UPDATE $this->db.collection SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeFileProject($id, $ownerID)
    {
        $sql = "UPDATE $this->db.file_project SET deleted = 1, date_modified = now() WHERE f_id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeFileCollection($id, $ownerID)
    {
        $sql = "UPDATE $this->db.file_collection SET deleted = 1, date_modified = now() WHERE f_id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeSingleFileCollection($f_id, $c_id, $ownerID)
    {
        $sql = "UPDATE $this->db.file_collection SET deleted = 1, date_modified = now() WHERE f_id = '$f_id' AND c_id = '$c_id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInput($id)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputByPipeAndName($id, $given_name)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id' AND given_name= '$given_name'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputByPipe($id)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputByCollection($id)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET deleted = 1, date_modified = now() WHERE collection_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectInput($id)
    {
        $sql = "DELETE FROM $this->db.project_input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelinebyProjectID($id)
    {
        $sql = "UPDATE $this->db.project_pipeline SET deleted = 1, date_modified = now() WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeRunByProjectID($id)
    {
        $sql = "UPDATE $this->db.run
              JOIN $this->db.project_pipeline ON project_pipeline.id = run.project_pipeline_id
              SET run.deleted = 1 WHERE project_pipeline.project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputbyProjectID($id)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET deleted = 1, date_modified = now() WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectInputbyProjectID($id)
    {
        $sql = "DELETE FROM $this->db.project_input WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcessByProcessGroupID($process_group_id)
    {
        $sql = "DELETE FROM $this->db.process WHERE process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
    //    ------ Groups -------
    function getAllGroups($ownerID)
    {
        $sql = "SELECT id, name FROM $this->db.groups";
        return self::queryTable($sql);
    }
    function getAllAvailableGroups($user_id, $ownerID)
    {
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            $sql = "SELECT DISTINCT id, name 
                    FROM $this->db.groups
                    WHERE name NOT IN (SELECT DISTINCT g.name 
                    FROM $this->db.groups g
                    INNER JOIN $this->db.user_group ug ON g.id = ug.g_id 
                    WHERE ug.u_id = '$user_id')";
            return self::queryTable($sql);
        }
    }
    function getGroups($id, $ownerID)
    {
        $where = " where u.deleted = 0";
        if ($id != "") {
            $where = " where u.deleted = 0 AND g.id = '$id'";
        }
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.date_modified
              FROM $this->db.groups g
              INNER JOIN $this->db.users u ON g.owner_id = u.id $where";
        return self::queryTable($sql);
    }
    function viewGroupMembers($g_id)
    {
        $sql = "SELECT id, name, username, email
              FROM $this->db.users
              WHERE deleted = 0 AND id in (
                SELECT u_id
                FROM $this->db.user_group
                WHERE g_id = '$g_id')";
        return self::queryTable($sql);
    }
    function saveGroupMemberByEmail($email, $g_id, $ownerID)
    {
        $ret = array();
        $email = str_replace("'", "''", $email);
        $checkEmail = $this->queryAVal("SELECT id FROM $this->db.users WHERE deleted=0 AND email = LCASE('" . $email . "')");
        if (!empty($checkEmail)) {
            $u_id = $checkEmail;
            $checkGroup = $this->getUserGroupsById($g_id, $u_id);
            if (empty(json_decode($checkGroup))) {
                return $this->insertUserGroup($g_id, $u_id, $ownerID);
            } else {
                $ret["error"] = "User already exist in your group.";
                return json_encode($ret);
            }
        } else {
            $ret["error"] = "User not found.";
            return json_encode($ret);
        }
    }
    function getAllUsers($ownerID)
    {
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])) {
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if ($userRole == "admin") {
                $sql = "SELECT * FROM $this->db.users WHERE deleted=0";
                return self::queryTable($sql);
            }
        }
    }
    function getUserGroupsIDs($ownerID)
    {
        $sql = "SELECT g.id
                  FROM $this->db.groups g
                  INNER JOIN $this->db.user_group ug ON  ug.g_id =g.id
                  where ug.u_id = '$ownerID'";
        return self::queryTable($sql);
    }

    function getUserGroups($ownerID)
    {
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.owner_id, ug.u_id
                  FROM $this->db.groups g
                  INNER JOIN $this->db.user_group ug ON  ug.g_id =g.id
                  INNER JOIN $this->db.users u ON u.id = g.owner_id
                  where u.deleted = 0 AND ug.u_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getUserGroupsById($id, $ownerID)
    {
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.owner_id, ug.u_id
                  FROM $this->db.groups g
                  INNER JOIN $this->db.user_group ug ON  ug.g_id =g.id
                  INNER JOIN $this->db.users u ON u.id = g.owner_id
                  where u.deleted = 0 AND ug.u_id = '$ownerID' AND g.id = '$id'";
        return self::queryTable($sql);
    }
    function getUserRole($ownerID)
    {
        $sql = "SELECT role
                  FROM $this->db.users
                  where id = '$ownerID' AND deleted=0";
        return self::queryTable($sql);
    }

    function getUserRoleVal($ownerID)
    {
        $role = "";
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])) {
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if (!empty($userRole)) {
                $role = $userRole;
            }
        }
        return $role;
    }

    function insertGroup($name, $ownerID)
    {
        $sql = "INSERT INTO $this->db.groups(name, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }

    function saveTestGroup($ownerID)
    {
        $g_id = $this->test_profile_group_id;
        $checkTestGroup = $this->getUserGroupsById($g_id, $ownerID);
        if (empty(json_decode($checkTestGroup))) {
            return $this->insertUserGroup($g_id, $ownerID, $ownerID);
        } else {
            return $checkTestGroup;
        }
    }
    function insertDefaultGroup($u_id)
    {
        $g_id = $this->DEFAULT_GROUP_ID;
        if (!empty($g_id) && !empty($u_id)) {
            $checkTestGroup = $this->getUserGroupsById($g_id, $u_id);
            if (empty(json_decode($checkTestGroup))) {
                $this->insertUserGroup($g_id, $u_id, $u_id);
            }
        }
    }
    function insertDefaultRunEnvironment($u_id)
    {
        $runEnv_id = $this->DEFAULT_RUN_ENVIRONMENT;
        $new_ssh_id = "";
        if (!empty($runEnv_id) && !empty($u_id)) {
            // TODO: add other profile types  
            $type = "cluster";
            if ($type == "cluster") {
                $sshData = json_decode($this->getProfileClusterSSHWithID($runEnv_id), true);
                if (!empty($sshData[0])) {
                    $ssh_id = $sshData[0]["ssh_id"];
                    $ssh_owner_id = $sshData[0]["owner_id"];
                    if (!empty($ssh_id) && !empty($ssh_owner_id)) {
                        $data = $this->duplicateSSHKey($ssh_id, $u_id);
                        $idArray = json_decode($data, true);
                        $new_ssh_id = $idArray["id"];
                        $prikey = $this->readKey($ssh_id, 'ssh_pri', $ssh_owner_id);
                        $pubkey = $this->readKey($ssh_id, 'ssh_pub', $ssh_owner_id);
                        $this->insertKey($new_ssh_id, $prikey, "ssh_pri", $u_id);
                        $this->insertKey($new_ssh_id, $pubkey, "ssh_pub", $u_id);
                    }
                }
                $this->duplicateRunEnvironment($runEnv_id, $type, $u_id, $new_ssh_id);
            }
        }
    }
    function insertUserGroup($g_id, $u_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.user_group (g_id, u_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$g_id', '$u_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateGroup($id, $name, $ownerID)
    {
        $sql = "UPDATE $this->db.groups SET name= '$name', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //  --------  Containers --------
    function getContainers($id, $type, $ownerID)
    {
        if ($type == "user") {
            $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id = '$ownerID'";
        } else if ($type == "shared") {
            $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id <> '$ownerID' AND (p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        } else {
            $where = " where u.deleted=0 AND p.deleted=0 AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            if (!empty($id)) {
                $where = " where u.deleted=0 AND p.deleted=0 AND p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            }
        }
        $sql = "SELECT DISTINCT p.owner_id, p.perms, p.group_id, p.id, p.name, p.summary, p.type, p.status, p.image_name, p.date_created, u.username, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own, u.deleted
                  FROM $this->db.container p
                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                  LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                  $where";
        $ret =  self::queryTable($sql);
        if (!empty($id)) {
            $ret = json_decode($ret, true);
            $files = $this->readAppFiles($id, $ownerID);
            if (!empty($files)) {
                $ret[0]["files"] = $files;
            }
            $ret = json_encode($ret);
        }
        return $ret;
    }
    //   ------------ Apps   -------------
    function getApps($id, $type, $ownerID)
    {
        if ($type == "user") {
            $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id = '$ownerID'";
        } else {
            $where = " where u.deleted=0 AND p.deleted=0 AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            if (!empty($id)) {
                $where = " where u.deleted=0 AND p.deleted=0 AND p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            }
        }
        $sql = "SELECT DISTINCT p.*, u.username, IF(p.owner_id='$ownerID',1,0) as own, u.deleted
                  FROM $this->db.app p
                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                  LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                  $where";
        return  self::queryTable($sql);
    }
    function insertApp($status, $type, $uuid, $location, $dir, $filename, $container_id, $memory, $cpu, $time, $pUUID, $ownerID)
    {
        $sql = "INSERT INTO $this->db.app(status, type, run_log_uuid, location, dir, filename, container_id, memory, cpu, time, app_uid, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$status', '$type', '$uuid', '$location', '$dir', '$filename', '$container_id', '$memory', '$cpu', '$time', '$pUUID', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateApp($id, $status, $type, $uuid, $location, $dir, $filename, $container_id, $memory, $cpu, $time, $pUUID, $ownerID)
    {
        $sql = "UPDATE $this->db.app SET status= '$status', type= '$type', run_log_uuid= '$uuid', location= '$location', dir='$dir', filename='$filename', container_id='$container_id', memory='$memory', cpu='$cpu', time='$time', app_uid='$pUUID', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateAppPid($id, $pid, $ownerID)
    {
        $sql = "UPDATE $this->db.app SET pid= '$pid', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }

    //    ----------- Projects   ---------
    function getProjects($id, $type, $ownerID)
    {
        $join = " LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id ";

        if ($type == "user") {
            $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id = '$ownerID'";
        } else if ($type == "shared") {
            $join = "INNER JOIN $this->db.project_pipeline pp ON pp.project_id = p.id 
                     LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id";
            $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id <> '$ownerID' AND (ug.u_id ='$ownerID' and pp.perms = 15) ";
        } else {
            $where = " where u.deleted=0 AND p.deleted=0 AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            if ($id != "") {
                $where = " where u.deleted=0 AND p.deleted=0 AND p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
            }
        }
        $sql = "SELECT DISTINCT p.owner_id, p.perms, p.group_id, p.id, p.name, p.summary, p.date_created, u.username, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own, u.deleted
                  FROM $this->db.project p
                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                  $join 
                  $where";
        return self::queryTable($sql);
    }
    function insertProject($name, $summary, $ownerID)
    {
        $sql = "INSERT INTO $this->db.project(name, summary, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$summary', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateProject($id, $name, $summary, $ownerID)
    {
        $sql = "UPDATE $this->db.project SET name= '$name', summary= '$summary', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //  ----------- Containers -----------
    function insertContainer($name, $summary, $type, $image_name, $status, $group_id, $perms, $ownerID)
    {
        $sql = "INSERT INTO $this->db.container(name, summary,type,image_name,status, owner_id, date_created, date_modified, last_modified_user, group_id, perms) VALUES ('$name', '$summary', '$type','$image_name','$status','$ownerID', now(), now(), '$ownerID', '$group_id', '$perms')";
        return self::insTable($sql);
    }
    function updateContainer($id, $name, $summary, $type, $image_name, $status, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.container SET name= '$name', summary= '$summary', type= '$type',image_name= '$image_name', perms= '$perms', group_id= '$group_id', status= '$status', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //    ----------- Runs     ---------
    function insertRun($project_pipeline_id, $status, $attempt, $ownerID)
    {
        $sql = "INSERT INTO $this->db.run (project_pipeline_id, run_status, attempt, owner_id, perms, date_created, date_modified, last_modified_user, date_created_last_run) VALUES
                  ('$project_pipeline_id', '$status', '$attempt', '$ownerID', 3, now(), now(), '$ownerID', now())";
        return self::insTable($sql);
    }
    function insertRunLog($project_pipeline_id, $uuid, $status, $ownerID)
    {
        $sql = "INSERT INTO $this->db.run_log (project_pipeline_id, run_log_uuid, run_status, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$project_pipeline_id', '$uuid', '$status', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    //get maximum of $project_pipeline_id
    function updateRunLog($project_pipeline_id, $status, $duration, $ownerID)
    {
        $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
        $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (empty($permCheck)) {
            exit();
        }
        $sql = "UPDATE $this->db.run_log SET run_status='$status', duration='$duration', date_ended= now(), date_modified= now(), last_modified_user ='$ownerID'  WHERE deleted=0 AND project_pipeline_id = '$project_pipeline_id' ORDER BY id DESC LIMIT 1";
        return self::runSQL($sql);
    }
    function updateRunLogSize($project_pipeline_id, $uuid, $size, $ownerID)
    {
        if (empty($uuid)) {
            $sql = "UPDATE $this->db.run_log SET size='$size', date_modified= now(), last_modified_user ='$ownerID'  WHERE  deleted=0 AND project_pipeline_id = '$project_pipeline_id' AND owner_id = '$ownerID' ORDER BY id DESC LIMIT 1";
            return self::runSQL($sql);
        } else {
            $sql = "UPDATE $this->db.run_log SET size='$size', date_modified= now(), last_modified_user ='$ownerID'  WHERE run_log_uuid = '$uuid' AND owner_id = '$ownerID'";
            return self::runSQL($sql);
        }
    }
    function updateRunLogName($id, $name, $ownerID)
    {
        $sql = "UPDATE $this->db.run_log SET name='$name', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateRunLogOpt($runLogOpt, $uuid, $pro_var_obj, $ownerID)
    {
        $pro_var_obj_text = "run_opt='$runLogOpt', ";
        if (is_null($pro_var_obj)) $pro_var_obj_text = "";
        $sql = "UPDATE $this->db.run_log SET $pro_var_obj_text pro_var_obj='$pro_var_obj', date_modified= now(), last_modified_user ='$ownerID'  WHERE run_log_uuid = '$uuid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function getRunLogById($id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.run_log WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getRunLog($project_pipeline_id, $type)
    {
        $where = "";
        if ($type == "default") {
            $where = "AND deleted=0";
        } else if ($type == "all") {
            $where = "";
        }
        $sql = "SELECT * FROM $this->db.run_log WHERE project_pipeline_id = '$project_pipeline_id' $where";
        return self::queryTable($sql);
    }
    function getRunLogUser($userID, $type)
    {
        $where = "";
        if ($type == "default") {
            $where = "AND deleted=0";
        } else if ($type == "all") {
            $where = "";
        }
        $sql = "SELECT * FROM $this->db.run_log WHERE owner_id='$userID' $where";
        return self::queryTable($sql);
    }
    function getRunLogOpt($uuid)
    {
        $sql = "SELECT run_opt, pro_var_obj FROM $this->db.run_log WHERE run_log_uuid = '$uuid'";
        return self::queryTable($sql);
    }
    function getRunLogStatus($uuid)
    {
        $sql = "SELECT run_status, IF(run_opt IS NULL,0,1) as run_opt_check FROM $this->db.run_log WHERE run_log_uuid = '$uuid'";
        return self::queryTable($sql);
    }

    function updateLastRunDate($project_pipeline_id, $ownerID)
    {
        $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
        $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (empty($permCheck)) {
            exit();
        }
        $sql = "UPDATE $this->db.run SET date_created_last_run= now(), date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateAppStatus($app_id, $status, $ownerID)
    {
        $sql = "UPDATE $this->db.app SET status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$app_id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateRunStatus($project_pipeline_id, $status, $ownerID)
    {
        $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
        $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (empty($permCheck)) {
            exit();
        }
        // Send Email to user if status is NextErr,NextSuc or Error
        if ($status == "NextErr" || $status == "NextSuc" || $status == "Error") {
            $runStat = json_decode($this->getRunStatus($project_pipeline_id, $ownerID));
            if (!empty($runStat)) {
                $oldStatus = $runStat[0]->{"run_status"};
                if ($oldStatus != $status) {
                    $this->sendRunStatusEmail($status, $project_pipeline_id, $curr_ownerID);
                }
            }
        }
        $sql = "UPDATE $this->db.run SET run_status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateProcessRunStatus($process_id, $status, $ownerID)
    {
        $curr_ownerID = $this->queryAVal("SELECT owner_id FROM $this->db.process WHERE id='$process_id'");
        $permCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (empty($permCheck)) {
            exit();
        }
        $sql = "UPDATE $this->db.process SET run_status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$process_id'";
        return self::runSQL($sql);
    }
    function updateRunAttempt($project_pipeline_id, $attempt, $ownerID)
    {
        $sql = "UPDATE $this->db.run SET attempt= '$attempt', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateRunSessionUUID($mainUUID, $initialUUID, $project_pipeline_id, $ownerID)
    {
        $sql = "UPDATE $this->db.run SET initial_session_uuid='$initialUUID', main_session_uuid='$mainUUID', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateRunPid($project_pipeline_id, $pid, $ownerID)
    {
        $sql = "UPDATE $this->db.run SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateProcessRunPid($process_id, $pid, $ownerID)
    {
        $sql = "UPDATE $this->db.process SET run_pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$process_id'";
        return self::runSQL($sql);
    }
    function getRunPid($project_pipeline_id)
    {
        $sql = "SELECT pid FROM $this->db.run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function getProcessRunPid($process_id)
    {
        $sql = "SELECT run_pid FROM $this->db.process WHERE id = '$process_id'";
        return self::queryTable($sql);
    }
    function getRunSessionUUID($project_pipeline_id)
    {
        $sql = "SELECT main_session_uuid, initial_session_uuid FROM $this->db.run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }

    function getRunAttempt($project_pipeline_id)
    {
        $sql = "SELECT attempt FROM $this->db.run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }

    function getUpload($name, $ownerID)
    {
        $filename = "{$this->tmp_path}/uploads/$ownerID/$name";
        // get contents of a file into a string
        $handle = fopen($filename, "r");
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return json_encode($content);
    }
    function removeUpload($name, $ownerID)
    {
        $filename = "{$this->tmp_path}/uploads/$ownerID/$name";
        unlink($filename);
        return json_encode("file deleted");
    }
    function getRun($project_pipeline_id, $ownerID)
    {
        $sql = "SELECT * FROM $this->db.run WHERE deleted = 0 AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function getRunStatus($project_pipeline_id, $ownerID)
    {
        $sql = "SELECT run_status FROM $this->db.run WHERE deleted = 0 AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function getCloudStatus($id, $cloud, $ownerID)
    {
        if ($cloud == "amazon") {
            $sql = "SELECT status, node_status FROM $this->db.profile_amazon WHERE id = '$id'";
        } else if ($cloud == "google") {
            $sql = "SELECT status, node_status FROM $this->db.profile_google WHERE id = '$id'";
        }
        return self::queryTable($sql);
    }
    function getAmazonPid($id, $ownerID)
    {
        $sql = "SELECT pid FROM $this->db.profile_amazon WHERE id = '$id'";
        return self::queryTable($sql);
    }
    function sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $process_id, $ownerID)
    {
        $ret = array();
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $executor = $cluDataArr[0]['executor'];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";

        //get $preSSH to load prerequisites and run qstat qdel
        $preSSH = "source /etc/profile && ";
        $upCacheCmd = "";
        if (!empty($project_pipeline_id)) {
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
            list($dolphin_path_real, $dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
            $upCacheCmd = $this->getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType);
        }

        if (!empty($upCacheCmd)) {
            $upCacheCmd = str_replace('$', '\$', $upCacheCmd);
            $upCacheCmd = "; $upCacheCmd";
        }
        if ($executor == "lsf" && $commandType == "checkRunPid") {
            // * LSF jobs states:
            // PEND — Waiting in a queue for scheduling and dispatch
            // RUN — Dispatched to a host and running
            // DONE — Finished normally with zero exit value
            // EXIT — Finished with non-zero exit value
            // PSUSP — Suspended while pending
            // USUSP — Suspended by user
            // SSUSP — Suspended by the LSF system
            // POST_DONE — Post-processing completed without errors
            // POST_ERR — Post-processing completed with errors
            // WAIT — Members of a chunk job that are waiting to run
            // UNKNOWN — No other job status is available
            // ZOMBIE — Job was killed but not acknowledged by sbd
            $errorJobStates = array("EXIT", "UNKNOWN", "ZOMBIE");
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH timeout 5 bjobs -w $pid\" 2>&1 &");
            if (preg_match("/JOBID/", $check_run)) {
                $arr = explode("\n", $check_run);
                for ($i = 0; $i < count($arr); $i++) {
                    if (preg_match("/$pid/", $arr[$i])) {
                        $statusAr = preg_split('/\s+/', $arr[$i]);
                        if (!empty($statusAr[2]) && in_array($statusAr[2], $errorJobStates)) {
                            return json_encode('exited');
                        }
                    }
                }
            }
            return json_encode('');
        } else if ($executor == "sge" && $commandType == "checkRunPid") {
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH timeout 5 qstat -j $pid\" 2>&1 &");
            if (preg_match("/job_number:/", $check_run)) {
                return json_encode('running');
            } else {
                return json_encode('done');
            }
        } else if ($executor == "slurm" && $commandType == "checkRunPid") {
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH timeout 5 squeue --job $pid\" 2>&1 &");
            if (preg_match("/$pid/", $check_run)) {
                return json_encode('running');
            } else {
                return json_encode('done');
            }
        } else if ($executor == "sge" && $commandType == "terminateRun") {
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH qdel $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "lsf" && $commandType == "terminateRun") {
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH bkill $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "slurm" && $commandType == "terminateRun") {
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH scancel $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "local" && $commandType == "terminateRun") {
            $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH ps -ef |grep nextflow.*/run$project_pipeline_id/ |grep -v grep | awk '{print \\\"kill \\\"\\\$2}' |bash $upCacheCmd\" 2>&1 &";
            $uuid = "";
            if (!empty($project_pipeline_id)) {
                $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
            } else if (!empty($process_id)) {
                $process_data = json_decode($this->getProcessDataById($process_id, $ownerID), true);
                if (!empty($process_data[0])) {
                    $uuid = $process_data[0]["run_uuid"];
                }
            }
            if (!empty($uuid)) {
                $run_path_real = $this->getServerRunPath($uuid);
                $ret = $this->execute_cmd_logfile($cmd, $ret, "terminate_cmd_log", "terminate_cmd", "$run_path_real/serverlog.txt", "a");
                return json_encode('terminateCommandExecuted');
            }
            return json_encode('');
        } else if ($commandType == "getRemoteFileList") {
            $target_dir = $pid;
            $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH ls $target_dir \" 2>&1 & echo $! &";
            $file_list = shell_exec($cmd);
            return json_encode($file_list);
        }
    }

    function file_get_contents_utf8($fn)
    {
        $content = file_get_contents($fn);
        return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
    function getFileContent($uuid, $filename, $ownerID)
    {
        $file = "{$this->run_path}/$uuid/$filename";
        $content = "";
        $secretline = "@DNEXT_SECRET_LINE";
        if (file_exists($file)) {
            $content = $this->file_get_contents_utf8($file);
            if (preg_match("/(run\/log\.txt|run\/\.nextflow\.log)/", $filename) && preg_match("/($secretline)/", $content)) {
                $rows = explode("\n", $content);
                foreach ($rows as $key => $row) {
                    if (preg_match("/($secretline)/", $row)) {
                        unset($rows[$key]);
                    }
                }
                $content = implode("\n", $rows);
            }
        }
        return json_encode($content);
    }
    function saveFileContent($text, $uuid, $filename, $ownerID)
    {
        if (preg_match("/\//i", $filename)) {
            $dirname = dirname($filename);
        }
        if (!file_exists("{$this->run_path}/$uuid/$dirname")) {
            mkdir("{$this->run_path}/$uuid/$dirname", 0755, true);
        }
        $file = fopen("{$this->run_path}/$uuid/$filename", "w");
        $res = fwrite($file, $text);
        fclose($file);
        return json_encode($res);
    }
    function checkFileExist($location, $uuid, $ownerID)
    {
        $ret = 0;
        error_log("{$this->run_path}/$uuid/$location");
        if (file_exists("{$this->run_path}/$uuid/$location")) {
            $ret = 1;
        }
        return $ret;
    }
    function deleteFile($uuid, $filename, $ownerID)
    {
        $file = "{$this->run_path}/$uuid/$filename";
        if (file_exists($file)) {
            unlink($file);
            return json_encode("file deleted.");
        }
        return json_encode("file not found.");
    }

    // check the process and kill it if elapsed time is higher that $limit_sec
    function checkKillPID($cmdFile, $limit_sec)
    {
        if (file_exists($cmdFile)) {
            $prev_cmd = $this->readFile($cmdFile);
            $prev_cmd = trim($prev_cmd);
            // replace multiple spaces with one space for ps command
            $prev_cmd = preg_replace('!\s+!', ' ', $prev_cmd);
            if (!empty($prev_cmd)) {
                $cmd = "ps -ef | grep '$prev_cmd' |grep -v grep | awk '{print $2}' 2>&1";
                $pids = shell_exec($cmd);
                if (!empty($pids)) {
                    $all_pids = explode("\n", $pids);
                    if (!empty($all_pids[0])) {
                        $pid = trim($all_pids[0]);
                        $cmd = "ps -p $pid -o etimes= 2>&1";
                        $log_exist = shell_exec($cmd);
                        if (!empty($log_exist)) {
                            $elapsed = trim($log_exist);
                            if (!empty($elapsed)) {
                                settype($elapsed, "integer");
                                if ($elapsed > $limit_sec) {
                                    //error_log("high");
                                    $killcmd = "ps -ef | grep '$prev_cmd' |grep -v grep | awk '{print \"kill \"$2}' | bash 2>&1";
                                    $kill_log = shell_exec($killcmd);
                                    return true;
                                } else {
                                    //error_log("low");
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    function write_php_ini($array, $file)
    {
        $res = array();
        error_log("write_php_ini");
        error_log(print_r($file, TRUE));
        error_log(print_r($array, TRUE));

        foreach ($array as $key => $val) {
            error_log(print_r($key, TRUE));
            error_log(print_r($val, TRUE));

            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval) $res[] = "$skey = " . $sval;
            } else $res[] = "$key = " . $val;
        }

        $this->safefilerewrite($file, implode("\r\n", $res));
    }

    function safefilerewrite($fileName, $dataToSave)
    {
        if ($fp = fopen($fileName, 'w')) {
            $startTime = microtime(TRUE);
            do {
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                if (!$canWrite) usleep(round(rand(0, 100) * 1000));
            } while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

            //file was locked so now we can store information
            if ($canWrite) {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }

    function updateAWSCliConfig($amazon_cre_id, $ownerID)
    {
        $profileID = "";
        $access_key = "";
        $secret_key = "";
        if (!empty($amazon_cre_id)) {
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach ($amz_data as $d) {
                $access = $d->amz_acc_key;
                $d->amz_acc_key = trim($this->amazonDecode($access));
                $secret = $d->amz_suc_key;
                $d->amz_suc_key = trim($this->amazonDecode($secret));
            }
            $access_key = $amz_data[0]->{'amz_acc_key'};
            $secret_key = $amz_data[0]->{'amz_suc_key'};
        }

        // 1. read config file ~/.aws/config
        //https://docs.aws.amazon.com/cli/latest/topic/config-vars.html
        $confPath = $this->AWS_CONFIG_PATH;
        $credPath = $this->AWS_CREDENTIALS_PATH;
        putenv("AWS_CONFIG_FILE=$confPath");
        putenv("AWS_SHARED_CREDENTIALS_FILE=$credPath");
        if (!file_exists("/data")) {
            mkdir("/data", 0755, true);
        }
        if (!file_exists("/data/.aws")) {
            mkdir("/data/.aws", 0755, true);
        }
        if (!file_exists("$confPath")) {
            $file = fopen("$confPath", 'w') or die("can't open file");
            fclose($file);
        }
        if (!file_exists("$credPath")) {
            $file = fopen("$credPath", 'w') or die("can't open file");
            fclose($file);
        }
        $confArr = parse_ini_file($confPath, true);
        $credArr = parse_ini_file($credPath, true);


        if (empty($confArr["default"])) {
            $confArr['default'] = array(
                "credential_source" => "Ec2InstanceMetadata"
            );
        }
        if (!empty($access_key) && !empty($secret_key)) {
            $profileID = "cred{$amazon_cre_id}";
            $confArr["profile $profileID"] = array(
                "credential_source" => $profileID
            );

            $credArr[$profileID] = array(
                "aws_access_key_id" => "$access_key",
                "aws_secret_access_key" => "$secret_key"
            );
        }

        // 2. write to ini file 
        $this->write_php_ini($confArr, $confPath);
        $this->write_php_ini($credArr, $credPath);

        return $profileID;
    }

    //$last_server_dir is last directory in $uuid folder: eg. run, pubweb
    function saveNextflowLog($files, $uuid, $last_server_dir, $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID)
    {
        if (empty($files) ||  empty($files[0])) return json_encode("logNotFound");
        $nextflow_log = "";
        $ret = array();
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        if (!empty($cluDataArr)) {
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            if (!file_exists("{$this->run_path}/$uuid/$last_server_dir")) {
                mkdir("{$this->run_path}/$uuid/$last_server_dir", 0755, true);
            }
            // check uuid_file before downloading file
            $uuid_exist_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \" test  -f '$dolphin_path_real/.dolphinnext/uuid/$uuid' && echo 'INFO: Run package for $uuid exists.'\" 2>&1";
            $ret = $this->execute_cmd($uuid_exist_cmd, $ret, "uuid_exist_cmd_log", "uuid_exist_cmd");
            if (preg_match("/INFO: Run package for $uuid exists\./", $ret["uuid_exist_cmd_log"])) {
                if (preg_match("/s3:/i", $files[0])) {
                    $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
                    $amazon_cre_id = $proPipeAll[0]->{'amazon_cre_id'};
                    $profileText = "";
                    $profileID = $this->updateAWSCliConfig($amazon_cre_id, $ownerID);
                    if (!empty($profileID)) {
                        $profileText = "--profile $profileID";
                    }

                    $confPath = $this->AWS_CONFIG_PATH;
                    $credPath = $this->AWS_CREDENTIALS_PATH;
                    putenv("AWS_CONFIG_FILE=$confPath");
                    putenv("AWS_SHARED_CREDENTIALS_FILE=$credPath");
                    // $cmd = "s3cmd sync $keys $fileList {$this->run_path}/$uuid/$last_server_dir/ 2>&1 &";
                    $cmd = "";
                    foreach ($files as $item) :
                        $target = "";
                        if (!empty($item) && preg_match("/\//", $item)) {
                            $pathSplit = explode("/", $item);
                            $target = end($pathSplit);
                        }
                        $cmd .= " aws s3 sync $profileText $item {$this->run_path}/$uuid/$last_server_dir/$target 2>&1 & ";
                    endforeach;
                } else if (preg_match("/gs:/i", $files[0])) {
                    $fileList = "";
                    foreach ($files as $item) :
                        $fileList .= "$item ";
                    endforeach;
                    $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
                    $google_cre_id = $proPipeAll[0]->{'google_cre_id'};
                    if (!empty($google_cre_id)) {
                        $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
                        $project_id = $goog_data[0]->{'project_id'};
                        $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
                        $cmd = "gcloud auth activate-service-account --project=$project_id --key-file=$credFile && gsutil cp -rcn $fileList {$this->run_path}/$uuid/$last_server_dir/ 2>&1 &";
                    }
                } else {
                    $cmdFile = "{$this->run_path}/$uuid/$last_server_dir/.rsyncCmd";
                    if (file_exists($cmdFile)) {
                        // rsync command should be finished in 5 minutes
                        // otherwise it will kill rsync command and restart it
                        $limit_sec = 300;
                        $continue = $this->checkKillPID($cmdFile, $limit_sec);
                        if ($continue == true) {
                            unlink($cmdFile);
                        } else {
                            return json_encode("nextflow log saved");
                        }
                    }
                    $fileList = "";
                    $fileListInitialRun = "";
                    foreach ($files as $item) :
                        if (preg_match("/initialrun\//", $item)) {
                            $fileListInitialRun .= "$connect:$item ";
                        } else {
                            $fileList .= "$connect:$item ";
                        }
                    endforeach;
                    $cmd_content = "$fileList {$this->run_path}/$uuid/$last_server_dir/";
                    if (!empty($fileListInitialRun)) {
                        $cmd_content2 = "$fileListInitialRun {$this->run_path}/$uuid/$last_server_dir/initialrun/";
                        $cmd = "rsync --info=progress2 -avzu -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $cmd_content 2>&1 || true && rsync --info=progress2 -avzu -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $cmd_content2 2>&1 &";
                    } else {
                        $cmd = "rsync --info=progress2 -avzu -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $cmd_content 2>&1 &";
                    }
                    file_put_contents($cmdFile, $cmd_content);
                }
                $nextflow_log = shell_exec($cmd);
            }
        }
        if (!is_null($nextflow_log) && !empty($nextflow_log)) {
            if (!empty($project_pipeline_id)) {
                $this->saveRunLogSize($uuid, $project_pipeline_id, $ownerID);
            }
            return json_encode("nextflow log saved");
        } else {
            return json_encode("logNotFound");
        }
    }

    function rsyncTransfer($localFile, $fileName, $target_dir, $upload_dir, $profileId, $profileType, $ownerID, $type)
    {
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $cmd_log = "";
        $async = " & echo $! &";
        if ($type == "sync" || $type == "sync-download") {
            $async = "&& echo rsync successfully completed || cat $upload_dir/.$fileName";
        }
        if (!empty($cluDataArr)) {
            $fileName = str_replace(" ", "\\ ", $fileName);
            $localFile = str_replace(" ", "\\ ", $localFile);
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            $cmd = "rsync --info=progress2 --partial-dir='$target_dir/.tmp_$fileName' -avzu --rsync-path='mkdir -p $target_dir && rsync' -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $localFile $connect:$target_dir/ > $upload_dir/.$fileName 2>&1 $async";
            if ($type == "sync-download") {
                $cmd = "rsync --info=progress2 --partial-dir='$target_dir/.tmp_$fileName' -avzu  -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $connect:$target_dir/$fileName $localFile > $upload_dir/.$fileName 2>&1 $async";
                error_log($cmd);
            }
            $cmd_log = shell_exec($cmd);
            if (!empty($cmd_log)) {
                $cmd_log = trim($cmd_log);
                $pidFile = $upload_dir . "/." . $fileName . ".rsyncPid";
                file_put_contents($pidFile, $cmd_log);
            }
        }
        return $cmd_log;
    }

    function saveAppFiles($files, $app_id, $ownerID)
    {
        $ret = "passed";
        $tmp_path = $this->tmp_path;
        $app_dir = "$tmp_path/apps/{$app_id}_{$ownerID}";
        if (file_exists($app_dir)) system('rm -rf ' . escapeshellarg("$app_dir"));

        foreach ($files as $file => $text) {
            $finalDir =  "{$app_dir}/$file";
            $block = explode("/", $finalDir);
            $filename = end($block);
            //remove last item and join with "/"
            array_pop($block);
            $finalDir = join("/", $block);
            $this->createDirFile($finalDir, $filename, 'w', $text);
        }
        return $ret;
    }

    function readAppFiles($app_id, $ownerID)
    {
        $ret = array();
        $tmp_path = $this->tmp_path;
        $app_dir = "$tmp_path/apps/{$app_id}_{$ownerID}";
        $opt = "onlyfile";

        if (file_exists($app_dir)) {
            //recursive read of all subdirectories
            foreach ($this->readFileSubDir($app_dir, $opt) as $fileItem) {
                //remove initial part of the path
                $fileItemRet = preg_replace('/^' . preg_quote($app_dir . '/', '/') . '/', '', $fileItem);
                $text = $this->readFile($fileItem);
                $ret[$fileItemRet] = $text;
            }
        }
        return $ret;
    }

    function getRsyncStatus($fileName, $ownerID)
    {
        $log = "";
        $tmp_path = $this->tmp_path;
        $upload_dir = "$tmp_path/uploads/{$ownerID}";
        $logfile = $upload_dir . "/." . $fileName;
        if (file_exists($logfile)) {
            $log = $this->readFile($logfile);
        }
        return json_encode($log);
    }

    function resetUpload($fileName, $ownerID)
    {
        $tmp_path = $this->tmp_path;
        $upload_dir = "$tmp_path/uploads/{$ownerID}";
        $rsyncPidFile = $upload_dir . "/." . $fileName . ".rsyncPid";
        if (file_exists($rsyncPidFile)) {
            $rsyncPid = $this->readFile($rsyncPidFile);
            if (!empty($rsyncPid)) {
                $rsyncPid = trim($rsyncPid);
                exec("kill $rsyncPid", $res, $err);
                return json_encode($res);
            }
        }
        $res = "";
        return json_encode($res);
    }

    function retryRsync($fileName, $target_dir, $run_env, $email, $ownerID)
    {
        $profileAr = explode("-", $run_env);
        $profileType = $profileAr[0];
        $profileId = $profileAr[1];
        $logReset = $this->resetUpload($fileName, $email, $ownerID);
        error_log($logReset);
        $tmp_path = TEMPPATH;
        $upload_dir = "$tmp_path/uploads/{$email}";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $localFile = $upload_dir . DIRECTORY_SEPARATOR . $fileName;
        $data = $this->rsyncTransfer($localFile, $fileName, $target_dir, $upload_dir, $profileId, $profileType, $ownerID, "async");
        return json_encode($data);
    }

    function retrieve_remote_file_size($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return $size;
    }

    function getDiskSpace($dir, $profileType, $profileId, $ownerID)
    {
        $log = array();
        if (!empty($dir)) {
            $dir = trim($dir);
            $blocks = explode("/", $dir);
            if (2 < count($blocks)) {
                $checkdir = "/" . $blocks[1] . "/" . $blocks[2];
                list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
                if (!empty($cluDataArr[0])) {
                    $ssh_id = !empty($cluDataArr[0]["ssh_id"]) ? $cluDataArr[0]["ssh_id"] : "";
                    $ssh_own_id = !empty($cluDataArr[0]["owner_id"]) ? $cluDataArr[0]["owner_id"] : "";
                    if (empty($ssh_id) || empty($ssh_own_id)) {
                        $log["ret"] = "Query failed! Please check your query, connection profile or internet connection";
                        return json_encode($log);
                    }
                    $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
                    if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
                    $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"df -hP \\$(readlink -f $checkdir)\" 2>&1 &";
                    $log["ret"] = shell_exec($cmd);
                    if (!is_null($log["ret"]) && isset($log["ret"])) {
                        $ret_blocks = explode("\n", $log["ret"]);
                        for ($i = 0; $i < count($ret_blocks); ++$i) {
                            if (preg_match("/Use%/i", $ret_blocks[$i])) {
                                $vals = $ret_blocks[$i + 1];
                                $val_blok = preg_split("/[\s,]+/", $vals);
                                $log["total"] = $val_blok[1];
                                $log["used"] = $val_blok[2];
                                $log["free"] = $val_blok[3];
                                $log["percent"] = $val_blok[4];
                                break;
                            }
                        }
                        $duCmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"timeout 30 du -hs $dir\" 2>&- | cut -f1";
                        $log["ret_duCmd"] = shell_exec($duCmd);
                        $log["ret_duCmd"] = trim($log["ret_duCmd"]);
                        if (!empty($log["ret_duCmd"])) {
                            $log["workdir_size"] = $log["ret_duCmd"];
                        }
                    } else {
                        $log["ret"] = "Query failed! Please check your query, connection profile or internet connection";
                    }
                }
            }
        }
        return json_encode($log);
    }

    function getLsDir($dir, $profileType, $profileId, $amazon_cre_id, $google_cre_id, $project_pipeline_id, $ownerID)
    {
        $dir = trim($dir);
        $log = "";
        if (preg_match("/s3:/i", $dir)) {
            $lastChar = substr($dir, -1);
            if ($lastChar != "/") {
                $dir = $dir . "/";
            }
            $profileText = "";
            $profileID = $this->updateAWSCliConfig($amazon_cre_id, $ownerID);
            if (!empty($profileID)) {
                $profileText = "--profile $profileID";
            }
            $confPath = $this->AWS_CONFIG_PATH;
            $credPath = $this->AWS_CREDENTIALS_PATH;
            putenv("AWS_CONFIG_FILE=$confPath");
            putenv("AWS_SHARED_CREDENTIALS_FILE=$credPath");

            $cmd = "aws s3 ls $profileText $dir --summarize 2>&1 &";
            error_log($cmd);
            if (preg_match('/[*?]/', $dir)) {
                $s3bloks = explode('/', $dir);
                $regexBlocks = array();
                $staticBlocks = array();
                $findRegex = false;
                for ($i = 0; $i < count($s3bloks); ++$i) {
                    $block = $s3bloks[$i];
                    if (preg_match('/[*?]/', $block) || $findRegex === true) {
                        $findRegex = true;
                        $regexBlocks[] = $block;
                    } else {
                        $staticBlocks[] = $block;
                    }
                }

                $initial_dir = implode('/', $staticBlocks);
                $regex_part = implode('/', $regexBlocks);
                // $cmd = "s3cmd ls -r --access_key $access_key  --secret_key $secret_key $initial_dir 2>&1 &";
                $cmd = "aws s3 ls $profileText --recursive $initial_dir --summarize 2>&1 &";
            }
            $log = shell_exec($cmd);
            // For google storage queries
        } else if (preg_match("/gs:/i", $dir)) {
            if (!empty($google_cre_id)) {
                $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
                $project_id = $goog_data[0]->{'project_id'};
                $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            }
            $lastChar = substr($dir, -1);
            if ($lastChar != "/") {
                $dir = $dir . "/";
            }
            $cmd = "gcloud auth activate-service-account --project=$project_id --key-file=$credFile && gsutil ls $dir 2>&1 &";
            $log = shell_exec($cmd);
            // For https http ftp queries
        } else if (preg_match("/:\/\//i", $dir)) {
            $log = $this->retrieve_remote_file_size($dir);
            // if $log > -1 then file has been searched, it should be directory
            if ($log > 100) {
                $subs = substr($dir, 0, strrpos($dir, '/') + 1);
                $log = "Query failed! Please search directory: $subs instead of the file: $dir";
            } else {
                $lastChar = substr($dir, -1);
                if ($lastChar != "/") {
                    $dir = $dir . "/";
                }
                $html = shell_exec("curl -s -k $dir 2>&1 &");
                $log = "";
                //ftp connection:
                if (preg_match("/ftp:\/\//i", $dir)) {
                    $count = explode("\n", $html);
                    for ($i = 0; $i < count($count); ++$i) {
                        $block = explode(" ", $count[$i]);
                        $last = end($block);
                        $log .= $last . "\n";
                    }
                    //http connection:
                } else {
                    //<a href="control_rep3.2.gz">control_rep3.2.gz</a>
                    $count = preg_match_all('/<a.*href="(.*)">.*<\/a>/i', $html, $files);
                    for ($i = 0; $i < $count; ++$i) {
                        if (!preg_match("/\//i", $files[1][$i]) && !preg_match("/;/i", $files[1][$i])) {
                            $log .= $files[1][$i] . "\n";
                        }
                    }
                }
            }
        } else {
            list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
            $ssh_id = isset($cluDataArr[0]["ssh_id"]) ? $cluDataArr[0]["ssh_id"] : "";
            $perms = isset($cluDataArr[0]["perms"]) ? $cluDataArr[0]["perms"] : "";
            $auto_workdir = !empty($cluDataArr[0]["auto_workdir"]) ? $cluDataArr[0]["auto_workdir"] : "";
            if (!empty($perms)) {
                if ($perms == "15") {
                    if (empty($auto_workdir)) {
                        return json_encode("Query failed! Generic work directory not defined in shared run environment.");
                    }
                    $rundir = $auto_workdir;
                    $rundir = preg_replace('/(\/+)/', '/', $rundir);
                    $dir = preg_replace('/(\/+)/', '/', $dir);
                    if (strpos($dir, $rundir) === false) {
                        return json_encode("Query failed! You don't have permission to access this directory.");
                    }
                }
            }
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"ls -1 $dir\" 2>&1 &";
            $log = shell_exec($cmd);
        }
        error_log($cmd);
        error_log($log);
        if (!is_null($log) && isset($log)) {
            return json_encode($log);
        } else {
            return json_encode("Query failed! Please check your query, connection profile or internet connection");
        }
    }

    function chkRmDirWritable($dir, $profileType, $profileId, $ownerID)
    {
        $dir = trim($dir);
        $log = "";
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
        if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
        $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"mkdir -p $dir && [ -w $dir ] && echo 'writeable' || echo 'write permission denied'\" 2>&1 &";
        $log = shell_exec($cmd);
        //writeable\n will be successful $log
        if (!is_null($log) && isset($log)) {
            return json_encode($log);
        } else {
            return json_encode("Query failed! Please check your query, connection profile or internet connection");
        }
    }

    function getUcscSessionID($hubFileLoc, $genomeFileLoc, $run_log_uuid, $dir, $ownerID)
    {
        // boolean for developer_tests -> refreshes session file and uses direct urls for hub and genome
        $developer_test = false;
        // 1.check if session file exist
        $ucsc_session = json_decode($this->getFileContent($run_log_uuid, "pubweb/{$dir}/.ucsc_session", $ownerID));

        if (!empty($developer_test) || empty($ucsc_session)) {
            // 2.if not then create one
            $genomeFileText = json_decode($this->getFileContent($run_log_uuid, "pubweb/{$dir}/{$genomeFileLoc}", $ownerID));
            preg_match("/genome (.*)/", $genomeFileText, $matchGenome);
            $genome = isset($matchGenome[1]) ? $matchGenome[1] : "";
            $sessionFilePath =  "{$this->run_path}/$run_log_uuid/pubweb/$dir/.ucsc_session";
            $hubUrl = "$this->base_path/tmp/pub/{$run_log_uuid}/pubweb/$dir/$hubFileLoc";

            if (!empty($developer_test)) {
                $hubUrl = "https://dnext.dolphinnext.com/tmp/pub/2jk8tXl8RqSyEfoa9FB5eR7omXBpq0/pubweb/USCS_Track_Hubs/hub.txt";
                $genome = "mm10";
            }
            $check_cmd = 'echo $(curl -s "https://genome.ucsc.edu/cgi-bin/hgGateway?genome=' . $genome . '&hubUrl=' . $hubUrl . '" | grep hgsid= | head -n1 | awk \'BEGIN{RS="\""; FS="hgsid="}NF>1{print $NF}\') > ' . $sessionFilePath;
            error_log($check_cmd);
            shell_exec($check_cmd);
            $ucsc_session = json_decode($this->getFileContent($run_log_uuid, "pubweb/{$dir}/.ucsc_session", $ownerID));
        }
        // 3. read the session id
        return json_encode($ucsc_session);
    }

    function getRemoteData($url)
    {
        $check_cmd = "curl -s '$url' 2>&1";
        $log = shell_exec($check_cmd);
        return $log;
    }

    function getSRRDataENA($srr_id)
    {
        $obj = new stdClass();
        if (preg_match("/SRR/i", $srr_id)) {
            //use www.ebi.ac.uk api which is faster and accurate if exist
            $check_cmd = "curl -s 'https://www.ebi.ac.uk/ena/data/warehouse/filereport?result=read_run&fields=fastq_ftp&accession=$srr_id' 2>&1";
            $log = shell_exec($check_cmd);
            if (!empty($log)) {
                $log = trim($log);
                $lines = explode("\n", $log);
                if (count($lines) == 2) {
                    if (preg_match("/fastq_ftp/i", $lines[0]) && preg_match("/fastq/i", $lines[1])) {
                        $obj->srr_id = trim($srr_id);
                        if (preg_match("/;/i", $lines[1])) {
                            $obj->collection_type = "pair";
                        } else {
                            $obj->collection_type = "single";
                        }
                    }
                }
            }
        }
        return $obj;
    }


    //installed edirect(esearch,efetch) path should be added into .bashrc
    function getSRRData($srr_id)
    {
        $obj = new stdClass();
        $command = "esearch -db sra -query $srr_id |efetch -format runinfo";
        $resText = shell_exec("$command 2>&1 & echo $! &");
        if (!empty($resText)) {
            $resText = trim($resText);
            $lines = explode("\n", $resText);
            if (count($lines) == 3) {
                $header = explode(",", $lines[1]);
                $vals = explode(",", $lines[2]);
                for ($i = 0; $i < count($header); $i++) {
                    $col = $header[$i];
                    if ($col == "Run") {
                        $obj->srr_id = trim($vals[$i]);
                        $retObj = $this->getSRRDataENA($obj->srr_id);
                        if (isset($retObj->collection_type)) {
                            return $retObj;
                        }
                    } else if ($col == "LibraryLayout") {
                        if (trim($vals[$i]) == "PAIRED") {
                            $obj->collection_type = "pair";
                        } else {
                            $obj->collection_type = "single";
                        }
                    }
                }
            }
        }
        return $obj;
    }

    //installed edirect(esearch,efetch) path should be added into .bashrc
    function getGeoData($geo_id, $ownerID)
    {
        $data = array();
        if (preg_match("/SRR/i", $geo_id) || preg_match("/GSM/i", $geo_id) || preg_match("/SRX/i", $geo_id)) {
            $obj = $this->getSRRData($geo_id);
            $data[] = $obj;
        } else if (preg_match("/GSE/i", $geo_id)) {
            $command = "esearch -db gds -query $geo_id | esummary | xtract -pattern DocumentSummary -element title Accession";
            $resText = shell_exec("$command 2>&1 & echo $! &");
            if (!empty($resText)) {
                $resText = trim($resText);
                $lines = explode("\n", $resText);
                for ($i = 0; $i < count($lines); $i++) {
                    $cols = explode("\t", $lines[$i]);
                    if (count($cols) == 2) {
                        $obj = $this->getSRRData($cols[1]);
                        $obj->name = trim(str_replace(" ", "_", $cols[0]));
                        $data[] = $obj;
                        usleep(400000); //wait ncbi api limit 3query/sec
                    }
                }
            }
        }
        return json_encode($data);
    }
    function readFileSubDir($path, $opt)
    {
        $scanned_directory = array_diff(scandir($path), array('..', '.'));
        foreach ($scanned_directory as $fileItem) {
            // skip '.' and '..' and .tmp hidden directories
            if ($fileItem[0] == '.' && $opt != "onlyfilehidden")  continue;
            $fileItem = rtrim($path, '/') . '/' . $fileItem;
            // if dir found call again recursively
            if (is_dir($fileItem)) {
                foreach ($this->readFileSubDir($fileItem, $opt) as $childFileItem) {
                    yield $childFileItem;
                }
            } else {
                yield $fileItem;
            }
        }
    }


    //$last_server_dir is last directory in $uuid folder: eg. run, pubweb, pubDmeta
    //$opt = "onlyfile", "filedir"
    function getFileList($uuid, $last_server_dir, $opt)
    {
        $path = "{$this->run_path}/$uuid/$last_server_dir";
        $scanned_directory = array();
        if (file_exists($path)) {
            if ($opt == "filedir") {
                $scanned_directory = array_diff(scandir($path), array('..', '.'));
            } else if ($opt == "onlyfile" || $opt == "onlyfilehidden") {
                //recursive read of all subdirectories
                foreach ($this->readFileSubDir($path, $opt) as $fileItem) {
                    //remove initial part of the path
                    $fileItemRet = preg_replace('/^' . preg_quote($path . '/', '/') . '/', '', $fileItem);
                    $scanned_directory[] = $fileItemRet;
                }
            }
        }
        return json_encode($scanned_directory);
    }

    function checkDescriptionBox($data, $uuid, $path)
    {
        $name = "_Description"; //folder name
        $module = "run_description";
        $id = $module;
        $fileList = array_values((array)json_decode($this->getFileList($uuid, "$path/$name", "onlyfile")));
        $fileList = array_filter($fileList);
        $targetDir = "{$this->run_path}/$uuid/pubweb/_Description";
        //if _Description exist then send new data
        if (file_exists($targetDir)) {
            $out["fileList"] = $fileList;
            $out["name"] = $name;
            $out["pubWeb"] = $module;
            $out["id"] = $id . "_" . $module;
            array_unshift($data, $out); //push to the top of the array
        }

        return $data;
    }

    //    ----------- Inputs, Project Inputs   ---------
    function getInputs($id, $ownerID)
    {
        $where = "";
        if ($id != "") {
            $where = " where i.id = '$id' ";
        }
        $sql = "SELECT DISTINCT i.id, i.name, IF(i.owner_id='$ownerID',1,0) as own
                  FROM $this->db.input i
                  LEFT JOIN $this->db.user_group ug ON i.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectInputs($project_id, $ownerID)
    {
        $where = " where pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))";
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM $this->db.project_input pi
                  INNER JOIN $this->db.input i ON i.id = pi.input_id
                  LEFT JOIN $this->db.user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectFiles($project_id, $ownerID)
    {
        $where = " where (i.type = 'file' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))";
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM $this->db.project_input pi
                  INNER JOIN $this->db.input i ON i.id = pi.input_id
                  LEFT JOIN $this->db.user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getPublicInputs($id)
    {
        $where = " WHERE i.perms = 63";
        if ($id != "") {
            $where = " where i.id = '$id' AND i.perms = 63";
        }
        $sql = "SELECT i.*, u.username
                  FROM $this->db.input i
                  INNER JOIN $this->db.users u ON i.owner_id = u.id
                  $where";
        return self::queryTable($sql);
    }
    function getPublicFiles($host)
    {
        $sql = "SELECT id as input_id, name, date_modified FROM $this->db.input WHERE type = 'file' AND host = '$host' AND perms = 63 ";
        return self::queryTable($sql);
    }
    function getPublicValues($host)
    {
        $sql = "SELECT id as input_id, name, date_modified FROM $this->db.input WHERE type = 'val' AND host = '$host' AND perms = 63 ";
        return self::queryTable($sql);
    }
    function getProjectValues($project_id, $ownerID)
    {
        $where = " where (i.type = 'val' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))";
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM $this->db.project_input pi
                  INNER JOIN $this->db.input i ON i.id = pi.input_id
                  LEFT JOIN $this->db.user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectInput($id, $ownerID)
    {
        $where = " where pi.id = '$id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63)";
        $sql = "SELECT pi.id, i.id as input_id, i.name
                  FROM $this->db.project_input pi
                  INNER JOIN $this->db.input i ON i.id = pi.input_id
                  $where";
        return self::queryTable($sql);
    }
    function insertProjectInput($project_id, $input_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.project_input(project_id, input_id, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$project_id', '$input_id', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function insertFile($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID)
    {
        $name = trim($name);
        $file_dir = trim($file_dir);
        $archive_dir = trim($archive_dir);
        $s3_archive_dir = trim($s3_archive_dir);
        $gs_archive_dir = trim($gs_archive_dir);
        $sql = "INSERT INTO $this->db.file(name, file_dir, file_type, files_used, collection_type, archive_dir, s3_archive_dir, gs_archive_dir, run_env, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$name', '$file_dir', '$file_type', '$files_used', '$collection_type', '$archive_dir', '$s3_archive_dir', '$gs_archive_dir', '$run_env', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateFile($id, $name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $ownerID)
    {
        $name = is_null($name) ? $name : trim($name);
        $file_dir = is_null($file_dir) ? $file_dir : trim($file_dir);
        $archive_dir = is_null($archive_dir) ? $archive_dir : trim($archive_dir);
        $s3_archive_dir = is_null($s3_archive_dir) ? $s3_archive_dir : trim($s3_archive_dir);
        $gs_archive_dir = is_null($gs_archive_dir) ? $gs_archive_dir : trim($gs_archive_dir);
        $name_update = is_null($name) ? "" : "name='$name',";
        $file_dir_update = is_null($file_dir) ? "" : "file_dir='$file_dir',";
        $file_type_update = is_null($file_type) ? "" : "file_type='$file_type',";
        $files_used_update = is_null($files_used) ? "" : "files_used='$files_used',";
        $collection_type_update = is_null($collection_type) ? "" : "collection_type='$collection_type',";
        $archive_dir_update = is_null($archive_dir) ? "" : "archive_dir='$archive_dir',";
        $s3_archive_dir_update = is_null($s3_archive_dir) ? "" : "s3_archive_dir='$s3_archive_dir',";
        $gs_archive_dir_update = is_null($gs_archive_dir) ? "" : "gs_archive_dir='$gs_archive_dir',";
        $sql = "UPDATE $this->db.file SET $name_update $file_dir_update $file_type_update $files_used_update $collection_type_update $archive_dir_update $s3_archive_dir_update $gs_archive_dir_update date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function saveCollection($name, $ownerID)
    {
        $colData = $this->getCollectionByName($name, $ownerID);
        $colData = json_decode($colData, true);
        if (isset($colData[0])) {
            $colId = $colData[0]["id"];
        } else {
            $colId = "";
        }
        if (empty($colId)) {
            $data = $this->insertCollection($name, $ownerID);
        } else {
            $data = json_encode(array('id' => $colId));
        }
        return $data;
    }


    function insertCollection($name, $ownerID)
    {
        $sql = "INSERT INTO $this->db.collection (name, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$name', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateCollection($id, $name, $ownerID)
    {
        $sql = "UPDATE $this->db.collection SET name='$name', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //ON DUPLICATE KEY UPDATE prevents DUPLICATE KEY ERROR
    function insertFileCollection($f_id, $c_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.file_collection (f_id, c_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$f_id', '$c_id', '$ownerID', now(), now(), '$ownerID', 3) ON DUPLICATE KEY UPDATE deleted=0 AND date_modified = now() ";
        return self::insTable($sql);
    }
    function insertFileProject($f_id, $p_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.file_project (f_id, p_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$f_id', '$p_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }

    function insertInput($name, $type, $ownerID)
    {
        $sql = "INSERT INTO $this->db.input(name, type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$name', '$type', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateInput($id, $name, $type, $ownerID)
    {
        $sql = "UPDATE $this->db.input SET name='$name', type='$type', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertPublicInput($name, $type, $host, $ownerID)
    {
        $sql = "INSERT INTO $this->db.input(name, type, host, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$type', '$host', '$ownerID', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updatePublicInput($id, $name, $type, $host, $ownerID)
    {
        $sql = "UPDATE $this->db.input SET name= '$name', type= '$type', host= '$host', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }

    // ------- Project Pipelines  ------
    function insertProjectPipeline($name, $project_id, $pipeline_id, $summary, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $perms, $group_id, $cron_check, $cron_prefix, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $notif_email_list, $ownerID)
    {
        $sql = "INSERT INTO $this->db.project_pipeline(name, project_id, pipeline_id, summary, output_dir, profile, interdel, cmd, exec_each, exec_all, exec_all_settings, exec_each_settings, docker_check, docker_img, singu_check, singu_save, singu_img, exec_next_settings, docker_opt, singu_opt, amazon_cre_id, google_cre_id, publish_dir, publish_dir_check, withReport, withTrace, withTimeline, withDag, process_opt, onload, owner_id, date_created, date_modified, last_modified_user, perms, group_id, cron_check, cron_prefix, cron_min, cron_hour, cron_day, cron_week, cron_month, new_run, notif_email_list)
                  VALUES ('$name', '$project_id', '$pipeline_id', '$summary', '$output_dir', '$profile', '$interdel', '$cmd', '$exec_each', '$exec_all', '$exec_all_settings', '$exec_each_settings', '$docker_check', '$docker_img', '$singu_check', '$singu_save', '$singu_img', '$exec_next_settings', '$docker_opt', '$singu_opt', '$amazon_cre_id', '$google_cre_id', '$publish_dir','$publish_dir_check', '$withReport', '$withTrace', '$withTimeline', '$withDag', '$process_opt', '$onload', '$ownerID', now(), now(), '$ownerID', '$perms', '$group_id', '$cron_check', '$cron_prefix', '$cron_min', '$cron_hour', '$cron_day', '$cron_week', '$cron_month', '1', '$notif_email_list')";
        return self::insTable($sql);
    }
    function updateProjectPipelineOnload($id, $onload, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET onload='$onload', last_modified_user ='$ownerID', date_modified= now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateProjectPipelineDmeta($id, $dmeta, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET dmeta='$dmeta', last_modified_user ='$ownerID', date_modified= now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateRunLogSummary($summary, $uuid, $ownerID)
    {
        $raw_data = json_decode($this->getRunLogOpt($uuid));
        if (!empty($raw_data[0])) {
            $raw_data[0]->{'run_opt'} = str_replace('\\', '\\\\', $raw_data[0]->{'run_opt'});
            $data = json_decode($raw_data[0]->{'run_opt'});
            $data->{'summary'} = str_replace("\n", "</br>", $summary);
            return $this->updateRunLogOpt(json_encode($data), $uuid, null, $ownerID);
        }
        return json_encode("");
    }

    function updateProjectPipelineSummary($id, $uuid, $summary, $ownerID)
    {
        $last_uuid = $this->getProPipeLastRunUUID($id);
        if ($uuid == "newrun" || $last_uuid == $uuid) {
            $sql = "UPDATE $this->db.project_pipeline SET summary='$summary', last_modified_user ='$ownerID', date_modified= now() WHERE id = '$id'";
            return self::runSQL($sql);
        } else {
            return $this->updateRunLogSummary($summary, $uuid, $ownerID);
        }
    }
    function updateProjectPipelineNewRun($project_pipeline_id, $new_run, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET new_run='$new_run', last_modified_user ='$ownerID', date_modified= now() WHERE id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }

    function updateProjectPipelineWithOldRun($id, $run_log_uuid, $ownerID)
    {
        $ret = json_encode("");
        $raw_data = json_decode($this->getRunLogOpt($run_log_uuid));
        if (!empty($raw_data[0])) {
            $raw_data[0]->{'run_opt'} = str_replace('\\', '\\\\', $raw_data[0]->{'run_opt'});
            $data = json_decode($raw_data[0]->{'run_opt'});
            $project_id = $data->{'project_id'};
            $pipeline_id = $data->{'pipeline_id'};
            $output_dir = $data->{'output_dir'};
            $publish_dir = $data->{'publish_dir'};
            $publish_dir_check = $data->{'publish_dir_check'};
            $profile = $data->{'profile'};
            $interdel = $data->{'interdel'};
            $cmd = $data->{'cmd'};
            $exec_each = $data->{'exec_each'};
            $exec_all = $data->{'exec_all'};
            $exec_all_settings = $data->{'exec_all_settings'};
            $exec_each_settings = $data->{'exec_each_settings'};
            $exec_next_settings = $data->{'exec_next_settings'};
            $docker_check = $data->{'docker_check'};
            $docker_img = $data->{'docker_img'};
            $docker_opt = $data->{'docker_opt'};
            $singu_check = $data->{'singu_check'};
            $singu_save = $data->{'singu_save'};
            $singu_img = $data->{'singu_img'};
            $singu_opt = $data->{'singu_opt'};
            $amazon_cre_id = $data->{'amazon_cre_id'};
            $google_cre_id = $data->{'google_cre_id'};
            $withReport = $data->{'withReport'};
            $withTrace = $data->{'withTrace'};
            $withTimeline = $data->{'withTimeline'};
            $withDag = $data->{'withDag'};
            $process_opt = $data->{'process_opt'};
            $new_run = 1;
            settype($amazon_cre_id, 'integer');
            settype($google_cre_id, 'integer');
            $ret = $this->updtProjectPipelineWithOldRun($id, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $new_run, $ownerID);
            if (!empty($ret)) {
                $ret = $this->removeProjectPipelineInputByPipe($id);
                if (!empty($ret)) {
                    // insert new inputs:
                    $allinputs = $data->{'project_pipeline_input'};
                    foreach ($allinputs as $inputitem) :
                        $input_id = $inputitem->{'input_id'};
                        $g_num = $inputitem->{'g_num'};
                        $given_name = $inputitem->{'given_name'};
                        $qualifier = $inputitem->{'qualifier'};
                        $collection_id = $inputitem->{'collection_id'};
                        $url = $inputitem->{'url'};
                        $urlzip = $inputitem->{'urlzip'};
                        $checkpath = $inputitem->{'checkpath'};
                        settype($input_id, 'integer');
                        settype($collection_id, 'integer');
                        settype($g_num, 'integer');
                        settype($url, 'integer');
                        settype($urlzip, 'integer');
                        settype($checkpath, 'integer');
                        $ret = $this->insertProPipeInput($id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url, $urlzip, $checkpath, $ownerID);
                    endforeach;
                    //return last data
                    $ret =  $this->getProjectPipelines($id, "", $ownerID, "");
                }
            }
        }
        return $ret;
    }

    function updtProjectPipelineWithOldRun($id, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $new_run, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET output_dir='$output_dir', profile='$profile', interdel='$interdel', cmd='$cmd', exec_each='$exec_each', exec_all='$exec_all', exec_all_settings='$exec_all_settings', exec_each_settings='$exec_each_settings', docker_check='$docker_check', docker_img='$docker_img', singu_check='$singu_check', singu_save='$singu_save', singu_img='$singu_img', exec_next_settings='$exec_next_settings', docker_opt='$docker_opt', singu_opt='$singu_opt', amazon_cre_id='$amazon_cre_id', google_cre_id='$google_cre_id', publish_dir='$publish_dir', publish_dir_check='$publish_dir_check', date_modified= now(), last_modified_user ='$ownerID', withReport='$withReport', withTrace='$withTrace', withTimeline='$withTimeline', withDag='$withDag',  process_opt='$process_opt', new_run='$new_run' WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }

    function updateProjectPipelineCronTargetDate($id, $cron_target_date)
    {
        $cron_target_date = $cron_target_date == NULL ? "NULL" : "'$cron_target_date'";
        $sql = "UPDATE $this->db.project_pipeline SET cron_target_date=$cron_target_date WHERE id = '$id' ";
        return self::runSQL($sql);
    }
    function resetProjectPipelineCron($project_pipeline_id)
    {
        $sql = "UPDATE $this->db.project_pipeline SET cron_target_date=NULL,  cron_check='false'  WHERE id = '$project_pipeline_id'";
        self::runSQL($sql);
    }

    function updateProjectPipeline($id, $name, $summary, $output_dir, $perms, $profile, $interdel, $cmd, $group_id, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $release_date, $cron_check, $cron_prefix, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $notif_check, $email_notif, $cron_first, $notif_email_list, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline SET name='$name', summary='$summary', output_dir='$output_dir', perms='$perms', profile='$profile', interdel='$interdel', cmd='$cmd', group_id='$group_id', exec_each='$exec_each', exec_all='$exec_all', exec_all_settings='$exec_all_settings', exec_each_settings='$exec_each_settings', docker_check='$docker_check', docker_img='$docker_img', singu_check='$singu_check', singu_save='$singu_save', singu_img='$singu_img', exec_next_settings='$exec_next_settings', docker_opt='$docker_opt', singu_opt='$singu_opt', amazon_cre_id='$amazon_cre_id', google_cre_id='$google_cre_id', publish_dir='$publish_dir', publish_dir_check='$publish_dir_check', date_modified= now(), last_modified_user ='$ownerID', withReport='$withReport', withTrace='$withTrace', withTimeline='$withTimeline', withDag='$withDag',  process_opt='$process_opt', onload='$onload', cron_check='$cron_check', cron_prefix='$cron_prefix', cron_min='$cron_min', cron_hour='$cron_hour', cron_day='$cron_day', cron_week='$cron_week', cron_month='$cron_month', notif_check='$notif_check', notif_email_list='$notif_email_list', email_notif='$email_notif', release_date=" . ($release_date == NULL ? "NULL" : "'$release_date'") . ", cron_first=" . ($cron_first == "" ? "NULL" : "'$cron_first'") . " WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateProjectPipelineCron($project_pipeline_id, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $cron_prefix, $cron_first,  $ownerID)
    {
        $php_set_date = strtotime("now");
        $cron_set_date = date("Y-m-d H:i:s", $php_set_date);

        $php_target_date = strtotime("+{$cron_min} minutes {$cron_hour} hours {$cron_day} days {$cron_week} weeks {$cron_month} months");
        $cron_target_date = date("Y-m-d H:i:s", $php_target_date);
        error_log($cron_target_date);
        if (!empty($cron_first)) $cron_target_date = $cron_first;
        $cron_date_first_text = "";
        if (is_null($cron_first)) {
            $cron_date_first_text = "";
        } else if ($cron_first == "") {
            $cron_date_first_text = ', cron_first=NULL';
        } else if (!empty($cron_first)) {
            $cron_date_first_text = ", cron_first='$cron_first'";
        }

        $sql = "UPDATE $this->db.project_pipeline SET type='cron', cron_min='$cron_min', cron_hour='$cron_hour', cron_day='$cron_day', cron_week='$cron_week', cron_month='$cron_month', cron_set_date='$cron_set_date', cron_target_date='$cron_target_date', cron_prefix='$cron_prefix', cron_check='true' $cron_date_first_text WHERE id = '$project_pipeline_id'";
        self::runSQL($sql);
        return json_encode($cron_target_date);
    }

    function getProPipeLastRunUUID($project_pipeline_id)
    {
        $uuid = $this->queryAVal("SELECT last_run_uuid FROM $this->db.project_pipeline WHERE id='$project_pipeline_id'");
        return $uuid;
    }

    function updateProcessRunUUID($process_id, $uuid)
    {
        $sql = "UPDATE $this->db.process SET run_uuid='$uuid' WHERE id='$process_id'";
        return self::runSQL($sql);
    }
    function updateProPipeLastRunUUID($project_pipeline_id, $uuid)
    {
        $sql = "UPDATE $this->db.project_pipeline SET last_run_uuid='$uuid' WHERE id='$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function getProjectPipelinesCron($ownerID, $userRole)
    {
        if ($userRole == "admin") {
            $where = " WHERE pp.deleted = 0 AND (pp.type='auto' OR pp.type='cron')";
        } else {
            $where = " LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id 
            WHERE pp.deleted = 0 AND (pp.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and pp.perms = 15)) AND (pp.type='auto' OR pp.type='cron')";
        }

        $sql = "SELECT DISTINCT r.date_created_last_run as run_date_created, r.run_status, pp.id as project_pipeline_id, pp.name, pp.summary, pp.output_dir,  pp.date_created as pp_date_created, pip.name as pipeline_name, pip.rev_id as pipeline_rev, pip.id as pipeline_id, u.email, u.username, pp.owner_id, IF(pp.owner_id='$ownerID',1,0) as own, pp.type, pp.template_id, pp.cron_target_date
        FROM $this->db.project_pipeline pp
        LEFT JOIN $this->db.run r  ON r.project_pipeline_id = pp.id
        INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
        INNER JOIN $this->db.users u ON pp.owner_id = u.id
        $where";
        return self::queryTable($sql);
    }
    function getProjectPipelines($id, $project_id, $ownerID, $userRole)
    {
        if ($id != "") {
            if ($userRole == "admin") {
                $where = " where pp.id = '$id' AND pip.deleted = 0 AND pp.deleted = 0";
            } else {
                $where = " where pp.id = '$id' AND pip.deleted = 0 AND pp.deleted = 0 AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
            }


            $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.project_id, pp.pipeline_id, pp.date_created, pp.date_modified, pp.owner_id, p.name as project_name, pp.output_dir, pp.profile, pp.interdel, pp.group_id, pp.exec_each, pp.exec_all, pp.exec_all_settings, pp.exec_each_settings, pp.perms, pp.docker_check, pp.docker_img, pp.singu_check, pp.singu_save, pp.singu_img, pp.exec_next_settings, pp.cmd, pp.singu_opt, pp.docker_opt, pp.amazon_cre_id, pp.google_cre_id, pp.publish_dir, pp.publish_dir_check, pp.withReport, pp.withTrace, pp.withTimeline, pp.withDag, pp.process_opt, pp.onload, pp.new_run, pp.release_date, pp.cron_check, pp.cron_prefix, pp.cron_min, pp.cron_hour, pp.cron_day, pp.cron_week, pp.cron_month, pp.cron_target_date, pp.dmeta, pp.type, pp.email_notif, pp.notif_check, pp.cron_first, pp.template_uuid, pp.notif_email_list, IF(pp.owner_id='$ownerID',1,0) as own
                      FROM $this->db.project_pipeline pp
                      INNER JOIN $this->db.users u ON pp.owner_id = u.id
                      INNER JOIN $this->db.project p ON pp.project_id = p.id
                      INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
                      LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id
                      $where";
        } else {
            //for sidebar menu 
            if ($project_id != "") {
                $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.date_modified, IF(pp.owner_id='$ownerID',1,0) as own
                      FROM $this->db.project_pipeline pp
                      INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
                      INNER JOIN $this->db.users u ON pp.owner_id = u.id
                      LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id
                      WHERE pp.deleted = 0 AND pip.deleted = 0 AND pp.project_id = '$project_id' AND (pp.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and pp.perms = 15))";
                //for run status page 
            } else {
                if ($userRole == "admin") {
                    $where = " WHERE pp.deleted = 0 AND (pp.type<>'auto' OR pp.type IS NULL)";
                } else {
                    $where = " LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id 
                    WHERE pp.deleted = 0 AND (pp.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and pp.perms = 15))";
                }

                $sql = "SELECT DISTINCT r.date_created_last_run as run_date_created, r.run_status, pp.id as project_pipeline_id, pp.name, pp.summary, pp.output_dir,  pp.date_created as pp_date_created, pip.name as pipeline_name, pip.rev_id as pipeline_rev, pip.id as pipeline_id, u.email, u.username, pp.owner_id, IF(pp.owner_id='$ownerID',1,0) as own, pp.type, pp.cron_target_date
                FROM $this->db.project_pipeline pp
                LEFT JOIN $this->db.run r  ON r.project_pipeline_id = pp.id
                INNER JOIN $this->db.biocorepipe_save pip ON pip.id = pp.pipeline_id
                INNER JOIN $this->db.users u ON pp.owner_id = u.id
                $where";
            }
        }
        return self::queryTable($sql);
    }

    function getExistProjectPipelines($pipeline_id, $type, $ownerID)
    {
        if ($type == "user") {
            $where = " where u.deleted=0 AND pp.deleted = 0 AND pp.pipeline_id = '$pipeline_id' AND pp.owner_id = '$ownerID'";
        } else if ($type == "shared") {
            $where = " where u.deleted=0 AND pp.deleted = 0 AND pp.pipeline_id = '$pipeline_id' AND pp.owner_id <> '$ownerID' AND (pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
        } else {
            $where = " where u.deleted=0 AND pp.deleted = 0 AND pp.pipeline_id = '$pipeline_id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
        }
        $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, u.username, pp.date_modified, p.name as project_name
                    FROM $this->db.project_pipeline pp
                    INNER JOIN $this->db.users u ON pp.owner_id = u.id
                    INNER JOIN $this->db.project p ON pp.project_id = p.id
                    LEFT JOIN $this->db.user_group ug ON pp.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    // ------- Project Pipeline Inputs  ------
    function insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url, $urlzip, $checkpath, $ownerID)
    {
        $sql = "INSERT INTO $this->db.project_pipeline_input(collection_id, project_pipeline_id, input_id, project_id, pipeline_id, g_num, given_name, qualifier, url, urlzip, checkpath, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$collection_id', '$project_pipeline_id', '$input_id', '$project_id', '$pipeline_id', '$g_num', '$given_name', '$qualifier', '$url', '$urlzip', '$checkpath', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProPipeInput($id, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url, $urlzip, $checkpath, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET collection_id='$collection_id', url='$url', urlzip='$urlzip', checkpath='$checkpath', project_pipeline_id='$project_pipeline_id', input_id='$input_id', project_id='$project_id', pipeline_id='$pipeline_id', g_num='$g_num', given_name='$given_name', qualifier='$qualifier', last_modified_user ='$ownerID'  WHERE id = $id";
        return self::runSQL($sql);
    }
    function updateProPipeInputCollInput($id, $input_id, $collection_id, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET collection_id='$collection_id', input_id='$input_id', last_modified_user ='$ownerID', date_modified= now()  WHERE id = $id";
        return self::runSQL($sql);
    }
    function duplicateProjectPipelineInput($new_id, $old_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.project_pipeline_input(url, urlzip, checkpath, input_id, project_id, pipeline_id, g_num, given_name, qualifier, collection_id, project_pipeline_id, owner_id, perms, date_created, date_modified, last_modified_user)
                    SELECT url, urlzip, checkpath, input_id, project_id, pipeline_id, g_num, given_name, qualifier, collection_id, '$new_id', '$ownerID', '3', now(), now(),'$ownerID'
                    FROM $this->db.project_pipeline_input
                    WHERE deleted=0 AND project_pipeline_id='$old_id'";
        return self::insTable($sql);
    }

    function checkAndInsertProjectInput($project_id, $input_id, $ownerID)
    {
        $projectInputID = 0;
        //check if project input is exist
        $input_id = (string)$input_id;
        $checkPro = $this->checkProjectInput($project_id, $input_id);
        $checkProData = json_decode($checkPro, true);
        if (isset($checkProData[0])) {
            $projectInputID = $checkProData[0]["id"];
        } else {
            //insert into project_input table
            $insertPro = $this->insertProjectInput($project_id, $input_id, $ownerID);
            $insertProData = json_decode($insertPro, true);
            $projectInputID = $insertProData["id"];
        }
        return $projectInputID;
    }

    function getEmptyCollectionId($ownerID, $collection_name, $collNameArr, $suffix)
    {
        $collection_id = 0;
        $suffix += 1;
        $newColName = "";
        if (!empty($collection_name)) {
            $newColName = "{$collection_name}-{$suffix}";
        } else {
            $newColName = "collection-{$suffix}";
        }
        // first check if it is in $collNameArr
        if (!in_array($newColName, $collNameArr)) {
            $collData = json_decode($this->saveCollection($newColName, $ownerID), true);
            if (!empty($collData["id"])) {
                $collection_id = $collData["id"];
            }
            return $collection_id;
        } else {
            // if it is found in $collNameArr add suffix
            return $this->getEmptyCollectionId($ownerID, $collection_name, $collNameArr, $suffix);
        }
    }

    function checkCollectionFiles($file_array, $collection_name, $ownerID)
    {
        $collection_id = 0;
        // get all collections of user
        $allColl = json_decode($this->getCollections($ownerID), true);
        $collNameArr = array();
        // a. check if is there any collection that has all the files in it 
        sort($file_array);
        for ($i = 0; $i < count($allColl); $i++) {
            $coll_id = $allColl[$i]["id"];
            $collNameArr[] = $allColl[$i]["name"];
            $collFileIds = array();
            $files = json_decode($this->getCollectionFiles($coll_id, $ownerID), true);
            foreach ($files as $file_item) :
                $file_id = $file_item["id"];
                settype($file_id, 'integer');
                $collFileIds[] = $file_id;
            endforeach;
            sort($collFileIds);
            // same collection found return collection_id
            if ($collFileIds == $file_array) {
                return $coll_id;
            }
        }
        // b. if collection is not found, use $collection_name to create coll_name
        $collection_id = $this->getEmptyCollectionId($ownerID, $collection_name, $collNameArr, 1);
        // use $file_array and insert collections
        settype($collection_id, 'integer');
        for ($i = 0; $i < count($file_array); $i++) {
            $file_id = $file_array[$i];
            settype($file_id, 'integer');
            $this->insertFileCollection($file_id, $collection_id, $ownerID);
        }
        return $collection_id;
    }

    function mergeUserSpecificCloudConfig($file_dir, $ownerID)
    {
        if (preg_match("/s3:/i", $file_dir) || preg_match("/gs:/i", $file_dir)) {
            if (preg_match("/s3:/i", $file_dir)) {
                $creds = $this->getAmz($ownerID);
            } else if (preg_match("/gs:/i", $file_dir)) {
                $creds = $this->getGoogle($ownerID);
            }
            if (!empty($creds)) {
                $creds = json_decode($creds, true);
                $new_file_dir = explode("\t", $file_dir);
                $new_cre = "";
                $old_cre = !empty($new_file_dir[1]) ? $new_file_dir[1] : "";
                // use same cre if user have $old_cre
                if (!empty($old_cre)) {
                    for ($n = 0; $n < count($creds); $n++) {
                        if ($creds[$n]["id"] == $old_cre) {
                            $new_cre = $creds[$n]["id"];
                        }
                    }
                }

                // use new user's cre if user has changed.
                if (empty($new_cre)) {
                    $new_cre = $creds[0]["id"];
                }

                if (!empty($new_cre)) {
                    $new_file_dir[1] = $new_cre;
                    $file_dir = implode("\t", $new_file_dir);
                }
            }
        }
        return $file_dir;
    }

    // duplicateProjectPipelineInput specific collection duplication
    function checkAndInsertCollectionForDup($allfiles,  $collection_name, $ownerID)
    {
        // use checkFileAndSave to save file and get file_id and fill $file_array
        $file_array = array();
        foreach ($allfiles as $file_item) :
            $name = $file_item["name"];
            $file_dir = $file_item["file_dir"];
            $file_type = $file_item["file_type"];
            $files_used = $file_item["files_used"];
            $collection_type = $file_item["collection_type"];
            $archive_dir = $file_item["archive_dir"];
            $s3_archive_dir = $file_item["s3_archive_dir"];
            $gs_archive_dir = $file_item["gs_archive_dir"];
            $run_env = $file_item["run_env"];
            // get new amz_cre_id for user
            $file_dir = $this->mergeUserSpecificCloudConfig($file_dir, $ownerID);
            $s3_archive_dir = $this->mergeUserSpecificCloudConfig($s3_archive_dir, $ownerID);
            $gs_archive_dir = $this->mergeUserSpecificCloudConfig($gs_archive_dir, $ownerID);
            $file_id = $this->checkFileAndSave($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID);
            if (!empty($file_id)) {
                $file_array[] = $file_id;
            }
        endforeach;
        $collection_id = $this->checkCollectionFiles($file_array, $collection_name, $ownerID);
        return $collection_id;
    }



    // Dmeta specific duplicate project pipeline insertion
    function checkAndInsertCollection($inputVal, $ownerID)
    {
        // e.g. collection data:
        // $name: experiment
        // $file_env: cluster-2
        // $files_used [["c_rep1.1.fq","c_rep1.2.fq"],["c_rep2.1.fq","c_rep2.2.fq"]]
        // $files_used converted to: c_rep1.1.fq,c_rep1.2.fq | c_rep2.1.fq,c_rep2.2.fq
        // $file_dir:[["s3://Vitiligo_11_15_17/dolphin_import","9"]]
        // $file_dir converted to:s3://Vitiligo_11_15_17/dolphin_import\t9
        // $archive_dir:"/local_archive_dir"
        // $s3_archive_dir:["s3://Vitiligo_11_15_17/dolphin_import","9"] or ["s3://Vitiligo_11_15_17/dolphin_import"]
        // $gs_archive_dir:["gs://Vitiligo_11_15_17/dolphin_import","9"] or ["gs://Vitiligo_11_15_17/dolphin_import"]
        // $collection_type: pair
        // $file_type: fastq
        // $collection_name: Vitiligo experiment-2

        // use checkFileAndSave to save file and get file_id and fill $file_array
        $file_array = array();

        for ($i = 0; $i < count($inputVal); $i++) {
            $name = $inputVal[$i]["name"];
            $run_env = $inputVal[$i]["file_env"];
            $run_env = $this->getRunEnv($run_env, $ownerID);
            $files_used = $inputVal[$i]["file_used"];
            $file_dir = $inputVal[$i]["file_dir"];
            $collection_type = $inputVal[$i]["collection_type"];
            $file_type = $inputVal[$i]["file_type"];
            $archive_dir = $inputVal[$i]["archive_dir"];
            $s3_archive_dir = $inputVal[$i]["s3_archive_dir"];
            $gs_archive_dir = $inputVal[$i]["gs_archive_dir"];

            for ($k = 0; $k < count($file_dir); $k++) {
                $file_dir[$k] = implode("\t", $file_dir[$k]);
            }
            $file_dir = implode("\t", $file_dir);
            $s3_archive_dir = implode("\t", $s3_archive_dir);
            $gs_archive_dir = implode("\t", $gs_archive_dir);
            // get new amz_cre_id for user
            $file_dir = $this->mergeUserSpecificCloudConfig($file_dir, $ownerID);
            $s3_archive_dir = $this->mergeUserSpecificCloudConfig($s3_archive_dir, $ownerID);
            $gs_archive_dir = $this->mergeUserSpecificCloudConfig($gs_archive_dir, $ownerID);

            for ($m = 0; $m < count($files_used); $m++) {
                $files_used[$m] = implode(",", $files_used[$m]);
            }
            $files_used = implode(" | ", $files_used);

            $file_id = $this->checkFileAndSave($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID);
            if (!empty($file_id)) {
                $file_array[] = $file_id;
            }
        }
        $collection_name = $inputVal[0]["collection_name"];
        $collection_id = $this->checkCollectionFiles($file_array, $collection_name, $ownerID);
        return $collection_id;
    }

    function checkFileAndSave($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID)
    {
        $checkFile = json_decode($this->checkFile($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID), true);
        if (isset($checkFile[0])) {
            $file_id = $checkFile[0]["id"];
        } else {
            $insert = json_decode($this->insertFile($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID), true);
            $file_id = $insert["id"];
        }
        return $file_id;
    }

    function checkAndInsertInput($inputName, $inputType, $ownerID)
    {
        //check if input exist?
        $input_id = 0;
        if (!empty($inputName)) {
            $checkIn = $this->checkInput($inputName, $inputType);
            $checkInData = json_decode($checkIn, true);
            if (isset($checkInData[0])) {
                $input_id = $checkInData[0]["id"];
            } else {
                //insert into input table
                $insertIn = $this->insertInput($inputName, $inputType, $ownerID);
                $insertInData = json_decode($insertIn, true);
                $input_id = $insertInData["id"];
            }
        }
        return $input_id;
    }

    function duplicateProjectPipeline($old_run_id, $ownerID, $inputs, $dmeta, $run_name, $run_env, $work_dir, $process_opt, $project_id, $description, $type)
    {
        ini_set('memory_limit', '900M');
        $newProPipeId = null;
        $userRole = $this->getUserRoleVal($ownerID);
        $proPipeAll = json_decode($this->getProjectPipelines($old_run_id, "", $ownerID, $userRole));
        if (empty($proPipeAll[0])) error_log("ProjectPipelines not found.");
        if (!empty($proPipeAll[0])) {
            $run_env = !empty($run_env) ? "'$run_env'" : "profile";
            $work_dir = !empty($work_dir) ? "'$work_dir'" : "output_dir";
            $project_id = !empty($project_id) ? "'$project_id'" : "project_id";
            $summary = !is_null($description) ? "'$description'" : "summary";
            $dmeta = !is_null($dmeta) ? "'$dmeta'" : "dmeta";
            $process_opt = !is_null($process_opt) ? "'$process_opt'" : "process_opt";
            $type = !is_null($type) ? "'$type'" : "NULL";
            $template_id = "'$old_run_id'";
            $template_uuid = 0;
            $runDataJS = $this->getLastRunData($old_run_id);
            if (!empty(json_decode($runDataJS, true))) {
                $runData = json_decode($runDataJS, true)[0];
                $template_uuid = "'" . $runData["last_run_uuid"] . "'";
            }
            // save source_id as template_id for cron_jobs and dmetaruns

            $sql = "INSERT INTO $this->db.project_pipeline (name, project_id, pipeline_id, summary, output_dir, profile, interdel, cmd, exec_each, exec_all, exec_all_settings, exec_each_settings, docker_check, docker_img, singu_check, singu_save, singu_img, exec_next_settings, docker_opt, singu_opt, amazon_cre_id, google_cre_id, publish_dir, publish_dir_check, withReport, withTrace, withTimeline, withDag, process_opt, onload, owner_id, date_created, date_modified, last_modified_user, perms, group_id, new_run, dmeta, type, template_id, template_uuid, email_notif, notif_check, notif_email_list)
                    SELECT '$run_name', $project_id, pipeline_id, $summary, $work_dir, $run_env, interdel, cmd, exec_each, exec_all, exec_all_settings, exec_each_settings, docker_check, docker_img, singu_check, singu_save, singu_img, exec_next_settings, docker_opt, singu_opt, amazon_cre_id, google_cre_id, publish_dir, publish_dir_check, withReport, withTrace, withTimeline, withDag, $process_opt, onload, $ownerID, now(), now(), $ownerID, perms, group_id, new_run, $dmeta, $type, $template_id, $template_uuid, email_notif, notif_check, notif_email_list
                    FROM $this->db.project_pipeline
                    WHERE id='$old_run_id'";
            $proPipe = self::insTable($sql);
            $newProPipe = json_decode($proPipe, true);
            $newProPipeId = $newProPipe["id"];
            if (empty($newProPipeId)) error_log("newProPipeId not found.");
            if (!empty($newProPipeId)) {
                $this->duplicateProjectPipelineInput($newProPipeId, $old_run_id, $ownerID);
                if (!is_null($inputs)) {
                    // use $inputs and replace entered projectPipelineInputs
                    foreach ($inputs as $inputName => $inputVal) :
                        $input_id = 0;
                        $collection_id = 0;
                        $proPipeInData = json_decode($this->getProjectPipelineInputIdByInputName($inputName, $newProPipeId, $ownerID), true);

                        if (!empty($proPipeInData[0]) && $proPipeInData[0]["id"]) {
                            $proPipeInputId = $proPipeInData[0]["id"];
                            $inputType = $proPipeInData[0]["input_type"];
                            $project_id = $proPipeInData[0]["project_id"];
                            // if $inputVal is array of array -> save as a collection
                            if (is_array($inputVal) && is_array($inputVal[0])) {
                                //collection
                                $collection_id = $this->checkAndInsertCollection($inputVal, $ownerID);
                            } else {
                                //simple input
                                $input_id = $this->checkAndInsertInput($inputVal, $inputType, $ownerID);
                                if (!empty($input_id)) {
                                    $this->checkAndInsertProjectInput($project_id, $input_id, $ownerID);
                                }
                            }
                            if (!empty($input_id) || !empty($collection_id)) {
                                error_log("update updateValProPipeInput $input_id $collection_id");
                                $this->updateProPipeInputCollInput($proPipeInputId, $input_id, $collection_id, $ownerID);
                            }
                        }
                    endforeach;
                }
            }
        }
        return $newProPipeId;
    }

    function duplicateSSHKey($old_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.ssh(name, hide, check_userkey, check_ourkey, date_created, date_modified, last_modified_user, perms, owner_id)
                    SELECT name, hide, check_userkey, check_ourkey, now(), now(), '$ownerID', perms, '$ownerID'
                    FROM $this->db.ssh
                    WHERE id='$old_id'";
        return self::insTable($sql);
    }

    function duplicateRunEnvironment($old_id, $type, $ownerID, $new_ssh_id)
    {
        $ssh_id = "ssh_id";
        if (!empty($new_ssh_id)) {
            $ssh_id = "'$new_ssh_id'";
        }
        if ($type == "cluster") {
            $sql = "INSERT INTO $this->db.profile_cluster(name, executor, next_path, port, singu_cache, username, hostname, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, next_clu_opt, job_clu_opt, public, variable, bash_variable, group_id, auto_workdir, owner_id, perms, date_created, date_modified, amazon_cre_id, def_publishdir, def_workdir, last_modified_user)
                    SELECT name, executor, next_path, port, singu_cache, username, hostname, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, $ssh_id, next_clu_opt, job_clu_opt, public, variable, bash_variable, group_id, auto_workdir, '$ownerID', perms, now(), now(), amazon_cre_id, def_publishdir, def_workdir, '$ownerID'
                    FROM $this->db.profile_cluster
                    WHERE id='$old_id'";
        }

        return self::insTable($sql);
    }
    function duplicateProcess($new_process_gid, $new_name, $old_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.process(process_uuid, process_rev_uuid, process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, owner_id, perms, date_created, date_modified, last_modified_user, rev_id, process_gid)
                    SELECT '', '', process_group_id, '$new_name', summary, script, script_header, script_footer, script_mode, script_mode_header, '$ownerID', '3', now(), now(),'$ownerID', '0', '$new_process_gid'
                    FROM $this->db.process
                    WHERE id='$old_id'";
        return self::insTable($sql);
    }

    function duplicateProcessParameter($new_pro_id, $old_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.process_parameter(process_id, parameter_id, type, sname, operator, closure, reg_ex, optional, owner_id, perms, date_created, date_modified, last_modified_user)
                    SELECT '$new_pro_id', parameter_id, type, sname, operator, closure, reg_ex, optional, '$ownerID', '3', now(), now(),'$ownerID'
                    FROM $this->db.process_parameter
                    WHERE process_id='$old_id'";
        return self::insTable($sql);
    }
    function getCollectionFiles($collection_id, $ownerID)
    {
        $userRole = $this->getUserRoleVal($ownerID);
        $where = " where f.deleted=0 AND fc.deleted=0 AND fc.c_id = '$collection_id' AND (f.owner_id = '$ownerID' OR f.perms = 63 OR (ug.u_id ='$ownerID' and f.perms = 15))";
        if ($userRole == "admin") {
            $where = " where f.deleted=0 AND fc.deleted=0 AND fc.c_id = '$collection_id'";
        }

        $sql = "SELECT DISTINCT f.*
                    FROM $this->db.file f
                    INNER JOIN $this->db.file_collection fc ON f.id=fc.f_id
                    LEFT JOIN $this->db.user_group ug ON f.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    function getCollectionsOfFile($file_id, $ownerID)
    {
        $where = " where f.deleted=0 AND fc.deleted=0 AND fc.f_id = '$file_id' AND (f.owner_id = '$ownerID' OR f.perms = 63 OR (ug.u_id ='$ownerID' and f.perms = 15))";
        $sql = "SELECT DISTINCT fc.c_id
                    FROM $this->db.file f
                    INNER JOIN $this->db.file_collection fc ON f.id=fc.f_id
                    LEFT JOIN $this->db.user_group ug ON f.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }

    function getProjectPipelineInputs($project_pipeline_id, $ownerID)
    {
        $where = " where (c.deleted = 0 OR c.deleted IS NULL) AND ppi.deleted=0 AND ppi.project_pipeline_id = '$project_pipeline_id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63 OR (ug.u_id ='$ownerID' and ppi.perms = 15))";
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            $where = " where (c.deleted = 0 OR c.deleted IS NULL) AND ppi.deleted=0 AND ppi.project_pipeline_id = '$project_pipeline_id'";
        }

        $sql = "SELECT DISTINCT ppi.id, i.id as input_id, ppi.qualifier, i.name, ppi.given_name, ppi.g_num, ppi.collection_id, c.name as collection_name, i2.name as url, i3.name as urlzip, i4.name as checkpath
                    FROM $this->db.project_pipeline_input ppi
                    LEFT JOIN $this->db.input i ON (i.id = ppi.input_id)
                    LEFT JOIN $this->db.input i2 ON (i2.id = ppi.url)
                    LEFT JOIN $this->db.input i3 ON (i3.id = ppi.urlzip)
                    LEFT JOIN $this->db.input i4 ON (i4.id = ppi.checkpath)
                    LEFT JOIN $this->db.collection c ON c.id = ppi.collection_id
                    LEFT JOIN $this->db.user_group ug ON ppi.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    function getProjectPipelineInputsById($id, $ownerID)
    {
        $where = " where (c.deleted = 0 OR c.deleted IS NULL) AND ppi.deleted=0 AND ppi.id= '$id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63)";
        $sql = "SELECT ppi.id, ppi.qualifier, i.id as input_id, i.name, ppi.collection_id, c.name as collection_name, i2.name as url, i3.name as urlzip, i4.name as checkpath
                    FROM $this->db.project_pipeline_input ppi
                    LEFT JOIN $this->db.input i ON (i.id = ppi.input_id)
                    LEFT JOIN $this->db.input i2 ON (i2.id = ppi.url)
                    LEFT JOIN $this->db.input i3 ON (i3.id = ppi.urlzip)
                    LEFT JOIN $this->db.input i4 ON (i3.id = ppi.checkpath)
                    LEFT JOIN $this->db.collection c ON c.id = ppi.collection_id
                    $where";
        return self::queryTable($sql);
    }
    function getProjectPipelineInputIdByInputName($name, $project_pipeline_id, $ownerID)
    {
        $where = " where ppi.deleted=0 AND ppi.given_name= '$name' AND ppi.owner_id = '$ownerID' AND ppi.project_pipeline_id = '$project_pipeline_id'";
        $sql = "SELECT ppi.id, i.type as input_type, ppi.project_id
                    FROM $this->db.project_pipeline_input ppi
                    LEFT JOIN $this->db.input i ON (i.id = ppi.input_id)
                    $where";
        return self::queryTable($sql);
    }
    function insertProcessParameter($sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $test, $optional, $perms, $group_id, $ownerID)
    {
        $sql = "INSERT INTO $this->db.process_parameter(sname, process_id, parameter_id, type, closure, operator, reg_ex, test, optional, owner_id, date_created, date_modified, last_modified_user, perms, group_id)
                    VALUES ('$sname', '$process_id', '$parameter_id', '$type', '$closure', '$operator', '$reg_ex', '$test', '$optional', '$ownerID', now(), now(), '$ownerID', '$perms', '$group_id')";
        return self::insTable($sql);
    }

    function updateProcessParameter($id, $sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $test, $optional, $perms, $group_id, $ownerID)
    {
        $sql = "UPDATE $this->db.process_parameter SET sname='$sname', process_id='$process_id', parameter_id='$parameter_id', type='$type', closure='$closure', operator='$operator', reg_ex='$reg_ex', test='$test', optional='$optional', last_modified_user ='$ownerID', perms='$perms', group_id='$group_id'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function removeProcessParameter($id)
    {
        $sql = "DELETE FROM $this->db.process_parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function removeProcessParameterByParameterID($parameter_id)
    {
        $sql = "DELETE FROM $this->db.process_parameter WHERE parameter_id = '$parameter_id'";
        return self::runSQL($sql);
    }
    function removeProcessParameterByProcessGroupID($process_group_id)
    {
        $sql = "DELETE process_parameter
                    FROM $this->db.process_parameter
                    JOIN $this->db.process ON process.id = process_parameter.process_id
                    WHERE process.process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
    function removeProcessParameterByProcessID($process_id)
    {
        $sql = "DELETE FROM $this->db.process_parameter WHERE process_id = '$process_id'";
        return self::runSQL($sql);
    }
    //------- feedback ------
    function savefeedback($email, $message, $url, $ownerID)
    {
        $email = str_replace("'", "''", $email);
        $sql = "INSERT INTO $this->db.feedback(email, message, url, date_created, owner_id) 
                VALUES ('$email', '$message','$url', now(), '$ownerID')";
        return self::insTable($sql);
    }

    //Send Email to user if status is NextErr,NextSuc or Error
    function sendRunStatusEmail($status, $project_pipeline_id, $ownerID)
    {
        $userData = json_decode($this->getUserById($ownerID));
        $userRole = $this->getUserRoleVal($ownerID);
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, $userRole));

        if (!empty($userData) && !empty($userData[0]) && !empty($proPipeAll) && !empty($proPipeAll[0])) {
            $send = "false";
            $email = $userData[0]->{'email'};
            $name = $userData[0]->{'name'};
            $email_notif = $userData[0]->{'email_notif'};
            $project_pipeline_email_notif = $proPipeAll[0]->{'email_notif'};
            $project_pipeline_notif_check = $proPipeAll[0]->{'notif_check'};
            $notif_email_list = htmlspecialchars_decode($proPipeAll[0]->{'notif_email_list'}, ENT_QUOTES);

            error_log($notif_email_list);
            if ($project_pipeline_notif_check == "true" && $project_pipeline_email_notif == "true") {
                $send = "true";
            } else if ($project_pipeline_notif_check == "true" && $project_pipeline_email_notif == "false") {
                $send = "false";
            } else if ($project_pipeline_notif_check == "false" && $email_notif == "true") {
                $send = "true";
            }
            if ($send == "true") {
                $from = EMAIL_SENDER;
                $EMAIL_BODY_ADMIN = EMAIL_BODY_ADMIN;
                $from_name = "DolphinNext Team";
                $to =  $email;
                if (!empty($notif_email_list)) {
                    $notif_email_list = trim($notif_email_list);
                    $notif_email_list = str_replace(",", ";", $notif_email_list);
                    if (!empty($notif_email_list)) {
                        $to =  "$email;$notif_email_list";
                    }
                }
                error_log($to);
                $subject = "";
                $initialText = "";
                $profile_url = "{$this->base_path}/index.php?np=4&";

                $endText = "If you have any questions or issues please contact $EMAIL_BODY_ADMIN.";
                $run_url = "{$this->base_path}/index.php?np=3&id=" . $project_pipeline_id;
                $footerText = "<font size='1'>To unsubscribe from these e-mails <a href='$profile_url'> click here </a> and update the notification section.</font>";
                $runText = "Please click the following link for details of the run: <a href='$run_url'> $run_url </a> ";
                if ($status == "NextSuc") {
                    $subject = "RUN $project_pipeline_id in DolphinNext Completed";
                    $initialText = "Your DolphinNext run $project_pipeline_id successfully completed!";
                } else if ($status == "NextErr" || $status == "Error") {
                    $subject = "RUN $project_pipeline_id in DolphinNext Exited";
                    $initialText = "Your DolphinNext run $project_pipeline_id has failed.";
                } else if ($status == "NextRun") {
                    $subject = "RUN $project_pipeline_id in DolphinNext initiated.";
                    $initialText = "Your DolphinNext run $project_pipeline_id has started.";
                }

                $message = "Dear $name,<br><br>$initialText<br>$runText<br>$endText<br><br>Best Regards,<br><br>" . COMPANY_NAME . " DolphinNext Team<br>$footerText";
                $this->sendEmail($from, $from_name, $to, $subject, $message);
            }
        }
    }

    function sendEmail($from, $from_name, $to, $subject, $message)
    {

        $ret = array();
        if ($this->EMAIL_TYPE == "DEFAULT") {
            $message = str_replace("\n", "<br>", $message);
            $message = wordwrap($message, 70);
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: ' . $from_name . ' <' . $from . '>' . "\r\n";

            if (@mail($to, $subject, $message, $headers)) {
                $ret['status'] = "sent";
            } else {
                $ret['status'] = "failed";
            }
        } else if ($this->EMAIL_TYPE == "HTTP") {
            $message = wordwrap($message, 70);
            $url = "{$this->EMAIL_URL}";
            $header = array("Content-type: application/json");
            if (!empty($this->EMAIL_HEADER_KEY)) {
                $header = array(
                    "Content-type: application/json", "{$this->EMAIL_HEADER_KEY}: {$this->EMAIL_HEADER_VALUE}"
                );
            }
            $data = json_encode(array(
                "to" => $to,
                "subject" => $subject,
                "body" => $message,
            ));


            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_POST, true);
            // secure it:
            curl_setopt($curl, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to curl_error
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $body = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $msg = json_decode($body, true);

            if (curl_errno($curl) && $statusCode != 200) {
                $error_msg = curl_error($curl);
                $ret['status'] = "failed";
                $ret['log'] = $error_msg;
            } else {
                $ret['status'] = "sent";
            }
            curl_close($curl);
        } else if ($this->EMAIL_TYPE == "SMTP") {
            $emailer = new emailer();
            $ret = $emailer->sendSimpleEmail($from, $from_name, $to, $subject, $message);
        }

        return json_encode($ret);
    }
    // --------- Pipeline -----------
    function getPipelineGroup($ownerID)
    {
        $sql = "SELECT pg.id, pg.group_name
                      FROM $this->db.pipeline_group pg";
        return self::queryTable($sql);
    }
    function insertPipelineGroup($group_name, $ownerID)
    {
        $sql = "INSERT INTO $this->db.pipeline_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updatePipelineGroup($id, $group_name, $ownerID)
    {
        $sql = "UPDATE $this->db.pipeline_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function getEditDelPipelineGroups($ownerID)
    {
        $sql = "SELECT DISTINCT id, group_name
                      FROM $this->db.pipeline_group pg
                      Where pg.owner_id = '$ownerID' AND id not in (SELECT pipeline_group_id FROM $this->db.biocorepipe_save WHERE owner_id != '$ownerID' AND deleted = 0)";
        return self::queryTable($sql);
    }

    function getPublicPipelines()
    {
        $sql = "SELECT pip.id, pip.name, pip.summary, pip.pin, pip.pin_order, pip.script_pipe_header, pip.script_pipe_config, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id
                      FROM $this->db.biocorepipe_save pip
                      INNER JOIN (
                        SELECT pipeline_gid, MAX(rev_id) rev_id
                        FROM $this->db.biocorepipe_save
                        WHERE pin = 'true' AND perms = 63 AND deleted = 0
                        GROUP BY pipeline_gid
                        ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid AND pip.deleted = 0";
        return self::queryTable($sql);
    }
    function getProcessData($ownerID)
    {
        if ($ownerID == "") {
            $ownerID = "''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT p.id, p.process_group_id, p.name, p.summary, p.script, p.script_header, p.script_footer, p.script_mode, p.script_mode_header, p.test_env, p.test_work_dir, p.docker_check, p.docker_img, p.docker_opt, p.singu_check, p.singu_img, p.singu_opt, p.script_test, p.script_test_mode, p.rev_id, p.perms, p.group_id, p.publish, IF(p.owner_id='$ownerID',1,0) as own FROM $this->db.process p ";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.id, p.process_group_id, p.name, p.summary, p.script, p.script_header, p.script_footer, p.script_mode, p.script_mode_header, p.test_env, p.test_work_dir, p.docker_check, p.docker_img, p.docker_opt, p.singu_check, p.singu_img, p.singu_opt, p.script_test, p.script_test_mode, p.rev_id, p.perms, p.group_id, p.publish, IF(p.owner_id='$ownerID',1,0) as own
                        FROM $this->db.process p
                        LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                        WHERE p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
        return self::queryTable($sql);
    }
    function getLastProPipeByUUID($id, $type, $ownerID)
    {
        if ($type == "process") {
            $table = "process";
        } else if ($type == "pipeline") {
            $table = "biocorepipe_save";
        }
        if ($ownerID != '') {
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin") {
                $sql = "SELECT DISTINCT p.*, pg.group_name as process_group_name
                            FROM $this->db.$table p
                            INNER JOIN $this->db.{$type}_group pg ON p.{$type}_group_id = pg.id
                            INNER JOIN (
                              SELECT pr.{$type}_gid, MAX(pr.rev_id) rev_id
                              FROM $this->db.$table pr WHERE pr.deleted = 0
                              GROUP BY pr.{$type}_gid
                              ) b ON p.rev_id = b.rev_id AND p.{$type}_gid=b.{$type}_gid AND p.deleted = 0 AND p.{$type}_uuid = '$id'";
                return self::queryTable($sql);
            }
            $where_pg = "(pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15))";
            $where_pr = "(pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
        }
        $sql = "SELECT DISTINCT p.*, pg.group_name as {$type}_group_name
                          FROM $this->db.$table p
                          LEFT JOIN $this->db.user_group ug ON  p.group_id=ug.g_id
                          INNER JOIN $this->db.{$type}_group pg
                          ON p.{$type}_group_id = pg.id and p.{$type}_uuid = '$id' AND $where_pg
                          INNER JOIN (
                            SELECT pr.{$type}_gid, MAX(pr.rev_id) rev_id
                            FROM $this->db.$table pr
                            LEFT JOIN $this->db.user_group ug ON pr.group_id=ug.g_id where $where_pr
                            GROUP BY pr.{$type}_gid
                            ) b ON p.rev_id = b.rev_id AND p.{$type}_gid=b.{$type}_gid AND p.deleted = 0";

        return self::queryTable($sql);
    }
    function getProPipeDataByUUID($uuid, $rev_uuid, $type, $ownerID)
    {
        if ($type == "process") {
            $table = "process";
        } else if ($type == "pipeline") {
            $table = "biocorepipe_save";
        }
        if ($ownerID == "") {
            $ownerID = "''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as {$type}_group_name, IF(p.owner_id='$ownerID',1,0) as own
                                  FROM $this->db.$table p
                                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                                  INNER JOIN $this->db.{$type}_group pg ON p.{$type}_group_id = pg.id
                                  where p.deleted = 0 AND p.{$type}_rev_uuid = '$rev_uuid' AND p.{$type}_uuid = '$uuid' ";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as {$type}_group_name, IF(p.owner_id='$ownerID',1,0) as own
                            FROM $this->db.$table p
                            LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                            INNER JOIN $this->db.users u ON p.owner_id = u.id
                            INNER JOIN $this->db.{$type}_group pg ON p.{$type}_group_id = pg.id
                            where p.{$type}_rev_uuid = '$rev_uuid' AND p.{$type}_uuid = '$uuid' AND p.deleted = 0 AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }


    function getProcessDataById($id, $ownerID)
    {
        if ($ownerID == "") {
            $ownerID = "''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as process_group_name, IF(p.owner_id='$ownerID',1,0) as own, 1 as write_group_perm
                                  FROM $this->db.process p
                                  INNER JOIN $this->db.users u ON p.owner_id = u.id
                                  INNER JOIN $this->db.process_group pg ON p.process_group_id = pg.id
                                  where p.id = '$id'";
                    return self::queryTable($sql);
                }
            }
        }

        // get writePermQuery by checking all user groups for the ownerID
        $writePermQuery = " 0 as write_group_perm ";
        $getUserGroupsIDs = json_decode($this->getUserGroupsIDs($ownerID), true);
        for ($j = 0; $j < count($getUserGroupsIDs); $j++) {
            $group_id = $getUserGroupsIDs[$j]["id"];
            if ($j == 0) {
                $writePermQuery = "IF(FIND_IN_SET('" . $group_id . "',p.write_group_id) > 0 ";
            } else {
                $writePermQuery .= " OR FIND_IN_SET('" . $group_id . "',p.write_group_id) > 0 ";
            }
        }
        if (count($getUserGroupsIDs) > 0) {
            $writePermQuery .= " ,1,0) as write_group_perm ";
        }

        // IF(FIND_IN_SET('1',pip.write_group_id) > 0 OR FIND_IN_SET('0',pip.write_group_id) > 0,1,0) as write_perm
        // 0 as write_perm

        $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as process_group_name, IF(p.owner_id='$ownerID',1,0) as own, $writePermQuery
                            FROM $this->db.process p
                            LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                            INNER JOIN $this->db.users u ON p.owner_id = u.id
                            INNER JOIN $this->db.process_group pg ON p.process_group_id = pg.id
                            where p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getProcessRevision($process_gid, $ownerID)
    {
        if ($ownerID != "") {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT p.id, p.rev_id, p.rev_comment, p.last_modified_user, p.date_created, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
                                  FROM $this->db.process p
                                  WHERE p.process_gid = '$process_gid'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.id, p.rev_id, p.rev_comment, p.last_modified_user, p.date_created, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
                            FROM $this->db.process p
                            LEFT JOIN $this->db.user_group ug ON p.group_id=ug.g_id
                            WHERE p.process_gid = '$process_gid' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getPipelineRevision($pipeline_gid, $ownerID)
    {
        if ($ownerID != "") {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms FROM $this->db.biocorepipe_save pip WHERE pip.deleted = 0 AND pip.pipeline_gid = '$pipeline_gid'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms
                            FROM $this->db.biocorepipe_save pip
                            LEFT JOIN $this->db.user_group ug ON pip.group_id=ug.g_id
                            WHERE pip.deleted = 0 AND pip.pipeline_gid = '$pipeline_gid' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
        return self::queryTable($sql);
    }

    function getInputsPP($id)
    {
        $sql = "SELECT pp.parameter_id, pp.sname, pp.id, pp.operator, pp.closure, pp.reg_ex, pp.optional, pp.test, p.name, p.file_type, p.qualifier
                            FROM $this->db.process_parameter pp
                            INNER JOIN $this->db.parameter p ON pp.parameter_id = p.id
                            WHERE pp.process_id = '$id' and pp.type = 'input'";
        return self::queryTable($sql);
    }
    function checkPipeline($process_id, $ownerID)
    {
        $sql = "SELECT id, name FROM $this->db.biocorepipe_save WHERE deleted = 0  AND nodes LIKE '%\"$process_id\",\"%'";
        return self::queryTable($sql);
    }
    function checkInput($name, $type)
    {
        $sql = "SELECT id, name FROM $this->db.input WHERE name = BINARY '$name' AND type='$type'";
        return self::queryTable($sql);
    }
    function checkFile($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID)
    {
        $name = trim($name);
        $file_dir = trim($file_dir);
        $archive_dir = trim($archive_dir);
        $s3_archive_dir = trim($s3_archive_dir);
        $gs_archive_dir = trim($gs_archive_dir);
        $sql = "SELECT id, name FROM $this->db.file WHERE name = BINARY '$name' AND file_dir = BINARY '$file_dir' AND  file_type='$file_type' AND files_used = BINARY '$files_used' AND collection_type='$collection_type' AND archive_dir = BINARY '$archive_dir' AND s3_archive_dir = BINARY '$s3_archive_dir' AND gs_archive_dir = BINARY '$gs_archive_dir' AND run_env = BINARY '$run_env' AND owner_id='$ownerID' AND deleted=0";
        return self::queryTable($sql);
    }

    function checkProjectInput($project_id, $input_id)
    {
        $sql = "SELECT id FROM $this->db.project_input WHERE input_id = '$input_id' AND project_id = '$project_id'";
        return self::queryTable($sql);
    }
    function checkFileProject($project_id, $file_id)
    {
        $sql = "SELECT id FROM $this->db.file_project WHERE deleted=0 AND f_id = '$file_id' AND p_id = '$project_id'";
        return self::queryTable($sql);
    }
    function checkApp($type, $uuid, $location, $ownerID)
    {
        $sql = "SELECT id, status FROM $this->db.app WHERE deleted=0 AND run_log_uuid = '$uuid' AND owner_id = '$ownerID' AND location='$location' AND type = '$type' ";
        return self::queryTable($sql);
    }
    function checkProPipeInput($project_id, $input_id, $pipeline_id, $project_pipeline_id)
    {
        $sql = "SELECT id FROM $this->db.project_pipeline_input WHERE deleted =0 AND input_id = '$input_id' AND project_id = '$project_id' AND pipeline_id = '$pipeline_id' AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }

    // first check write_group_id and exclude from owner_id list
    // this will allow working on same revision by multuple users.
    function getExcludeWriteGroupText($id, $ownerID, $type)
    {
        $table = "";
        if ($type == "checkProjectPipelinePublic" || $type == "checkProjectPublic") {
            $table = "pp.";
        } else if ($type == "checkPipelinePublic") {
            $table = "";
        }
        $data = "";
        if ($type == "checkProjectPublic") {
            $data = json_decode($this->loadPipeline($id, $ownerID), true);
        } else if ($type == "checkProjectPipelinePublic" || $type == "checkPipelinePublic") {
            $data = json_decode($this->getProcessDataById($id, $ownerID), true);
        }
        $write_group_id = "";
        $write_group_text = "";
        if (!empty($data[0])) {
            $write_group_id = $data[0]["write_group_id"];
        }
        if (!empty($write_group_id)) {
            $group_ids = explode(",", $write_group_id);
            for ($j = 0; $j < count($group_ids); $j++) {
                $group_id = $group_ids[$j];
                $user_list = json_decode($this->viewGroupMembers($group_id), true);
                for ($k = 0; $k < count($user_list); $k++) {
                    $user_id = $user_list[$k]["id"];
                    $write_group_text .= " AND {$table}owner_id != '" . $user_id . "'  ";
                }
            }
        }
        return $write_group_text;
    }

    function checkPipelinePublic($process_id, $ownerID)
    {
        $write_group_text = $this->getExcludeWriteGroupText($process_id, $ownerID, "checkPipelinePublic");

        $sql = "SELECT id, name 
                FROM $this->db.biocorepipe_save 
                WHERE deleted = 0 AND (owner_id != '$ownerID' $write_group_text) AND 
                CONCAT(',', process_list , ',')  LIKE '%,$process_id,%'";
        return self::queryTable($sql);
    }
    function checkProjectPipelinePublic($process_id, $ownerID)
    {

        $write_group_text = $this->getExcludeWriteGroupText($process_id, $ownerID, "checkProjectPipelinePublic");

        $sql = "SELECT DISTINCT p.id, p.name, pp.id as pp_id
                FROM $this->db.biocorepipe_save p
                INNER JOIN $this->db.project_pipeline pp ON p.id = pp.pipeline_id
                WHERE p.deleted = 0 AND pp.deleted = 0 AND (pp.owner_id != '$ownerID' $write_group_text) 
                AND CONCAT(',', p.process_list , ',')  LIKE '%,$process_id,%'";
        return self::queryTable($sql);
    }
    function checkParameter($parameter_id, $ownerID)
    {
        $sql = "SELECT DISTINCT pp.id, p.name
                            FROM $this->db.process_parameter pp
                            INNER JOIN $this->db.process p ON pp.process_id = p.id
                            WHERE (pp.owner_id = '$ownerID') AND pp.parameter_id = '$parameter_id'";
        return self::queryTable($sql);
    }
    function checkMenuGr($id)
    {
        $sql = "SELECT DISTINCT pg.id, p.name
                            FROM $this->db.process p
                            INNER JOIN $this->db.process_group pg ON p.process_group_id = pg.id
                            WHERE pg.id = '$id'";
        return self::queryTable($sql);
    }
    function checkPipeMenuGr($id)
    {
        $sql = "SELECT DISTINCT pg.id, p.name
                            FROM $this->db.biocorepipe_save p
                            INNER JOIN $this->db.pipeline_group pg ON p.pipeline_group_id = pg.id
                            WHERE p.deleted = 0 AND pg.id = '$id'";
        return self::queryTable($sql);
    }
    function checkUserWritePermRun($project_pipeline_id, $ownerID)
    {
        $sql = "SELECT DISTINCT id, name
                FROM $this->db.project_pipeline
                WHERE deleted = 0 AND owner_id = '$ownerID' AND id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function checkProject($pipeline_id, $ownerID)
    {
        $sql = "SELECT DISTINCT pp.id, p.name
                FROM $this->db.project_pipeline pp
                INNER JOIN $this->db.project p ON pp.project_id = p.id
                WHERE pp.deleted = 0 AND pp.pipeline_id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    //Check if pipeline is ever used in projects that are group or public
    function checkProjectPublic($pipeline_id, $ownerID)
    {

        $write_group_text = $this->getExcludeWriteGroupText($pipeline_id, $ownerID, "checkProjectPublic");

        $sql = "SELECT DISTINCT pp.id, p.name, pip.pipeline_list
                FROM $this->db.project_pipeline pp
                INNER JOIN $this->db.project p ON pp.project_id = p.id
                INNER JOIN $this->db.biocorepipe_save pip ON pp.pipeline_id = pip.id
                WHERE pp.deleted = 0 AND ( pp.owner_id != '$ownerID' $write_group_text) AND (pp.pipeline_id = '$pipeline_id' OR CONCAT(',', pip.pipeline_list , ',')  LIKE '%,$pipeline_id,%' )";
        return self::queryTable($sql);
    }
    //Check if project is ever used by others (others add project_pipeline into group project)
    function checkSharedRunInProject($project_id, $ownerID)
    {
        $sql = "SELECT DISTINCT pp.id, p.name
                FROM $this->db.project_pipeline pp
                INNER JOIN $this->db.project p ON pp.project_id = p.id
                WHERE pp.deleted = 0 AND pp.owner_id != '$ownerID' AND p.id = '$project_id' ";
        return self::queryTable($sql);
    }

    function getMaxProcess_gid()
    {
        $sql = "SELECT MAX(process_gid) process_gid FROM $this->db.process";
        return self::queryTable($sql);
    }
    function getMaxPipeline_gid()
    {
        $sql = "SELECT MAX(pipeline_gid) pipeline_gid FROM $this->db.biocorepipe_save WHERE deleted = 0";
        return self::queryTable($sql);
    }
    function getProcess_gid($process_id)
    {
        $sql = "SELECT process_gid FROM $this->db.process WHERE id = '$process_id'";
        return self::queryTable($sql);
    }
    function getProcess_uuid($process_id)
    {
        $sql = "SELECT process_uuid FROM $this->db.process WHERE id = '$process_id'";
        return self::queryTable($sql);
    }
    function getPipeline_gid($pipeline_id)
    {
        $sql = "SELECT pipeline_gid FROM $this->db.biocorepipe_save WHERE id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    function getPipeline_uuid($pipeline_id)
    {
        $sql = "SELECT pipeline_uuid FROM $this->db.biocorepipe_save WHERE deleted = 0 AND id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    function getMaxRev_id($process_gid)
    {
        $sql = "SELECT MAX(rev_id) rev_id FROM $this->db.process WHERE process_gid = '$process_gid'";
        return self::queryTable($sql);
    }
    function getMaxPipRev_id($pipeline_gid)
    {
        $sql = "SELECT MAX(rev_id) rev_id FROM $this->db.biocorepipe_save WHERE deleted = 0 AND pipeline_gid = '$pipeline_gid'";
        return self::queryTable($sql);
    }
    function getOutputsPP($id)
    {
        $sql = "SELECT pp.parameter_id, pp.sname, pp.id, pp.operator, pp.closure, pp.reg_ex, pp.optional, pp.test, p.name, p.file_type, p.qualifier
                            FROM $this->db.process_parameter pp
                            INNER JOIN $this->db.parameter p ON pp.parameter_id = p.id
                            WHERE pp.process_id = '$id' and pp.type = 'output'";
        return self::queryTable($sql);
    }

    //update if user owns the project
    function updateProjectGroupPerm($id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.project p
                            INNER JOIN $this->db.project_pipeline pp ON p.id=pp.project_id
                            SET p.group_id='$group_id', p.perms='$perms', p.date_modified=now(), p.last_modified_user ='$ownerID'  WHERE p.id = '$id'";
        return self::runSQL($sql);
    }

    function updateProjectInputGroupPerm($id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.project_input pi
                            INNER JOIN $this->db.project_pipeline_input ppi ON pi.input_id=ppi.input_id
                            SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE ppi.deleted=0 AND ppi.project_pipeline_id = '$id' ";
        return self::runSQL($sql);
    }

    function updateProjectPipelineInputGroupPerm($id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.project_pipeline_input SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE deleted=0 AND project_pipeline_id = '$id' AND perms <= '$perms'";
        return self::runSQL($sql);
    }

    function updatePipelineGroupPermByPipeId($id, $group_id, $perms, $write_group_id, $ownerID)
    {
        error_log("updated pipeline_id:$id, perms:$perms, group_id:$group_id, write_group_id:$write_group_id");
        $write_group_id_text = "";
        if (!is_null($write_group_id)) {
            $write_group_id_text = "pi.write_group_id='{$write_group_id}',";
        }

        $sql = "UPDATE $this->db.biocorepipe_save pi
                SET pi.group_id='$group_id', pi.perms='$perms', $write_group_id_text pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE pi.deleted=0 AND pi.id = '$id'";
        return self::runSQL($sql);
    }

    //controlled by admin
    function updatePubliclySearchableById($id, $table, $publicly_searchable, $ownerID)
    {
        $sql = "UPDATE $this->db.$table SET publicly_searchable='$publicly_searchable',  date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateReleaseDateById($id, $table, $release_date, $ownerID)
    {
        $release_date = !empty($release_date) ? "'$release_date'" : "NULL";
        $sql = "UPDATE $this->db.$table SET release_date=$release_date,  date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    //3 -> user r+w
    //11-> user r+w, group r
    //15-> user r+w, group r+w 
    //43=> user r+w, group r,   and other r
    //47=> user r+w, group r+w, and other r
    //check if permission is needed to update:
    //return 1 if one of these conditions are met
    //expected(pipeline/run)       current(pipeline)
    //group A 11/15       group B 11/15 ->change only group_id
    //group A 11/15       3
    //everyone            3, group A 11/15
    function validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms)
    {
        $ret = 0;
        if ($curr_perms == 15 && $perms == 15 && $curr_group_id != $group_id && !empty($group_id)) {
            $ret = 1;
        } else if ($curr_perms == 3 && $perms == 15 && !empty($group_id)) {
            $ret = 1;
        } else if ($perms > 15 &&  $curr_perms < 16) {
            $ret = 1;
        }
        return $ret;
    }


    function checkUpdtNeed($type, $id, $group_id, $perms, $ownerID)
    {
        $ret = 0;
        $dataCheck = 0;
        if ($type == "pipeline") {
            $pipe = $this->loadPipeline($id, $ownerID);
            $pipeData = json_decode($pipe, true);
            if (!empty($pipeData[0])) {
                $dataCheck = 1;
                $curr_group_id = $pipeData[0]["group_id"];
                $curr_perms = $pipeData[0]["perms"];
            }
        }
        if (!empty($dataCheck)) {
            $ret = $this->validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms);
        }
        return $ret;
    }

    //update if user owns the process
    function updateProcessGroupPerm($id, $group_id, $perms, $write_group_id, $ownerID)
    {
        error_log("updated $this->db.process_id:$id, perms:$perms, group_id:$group_id, write_group_id:$write_group_id");
        $write_group_id_text = "";
        if (!is_null($write_group_id)) {
            $write_group_id_text = " write_group_id='{$write_group_id}',";
        }
        $sql = "UPDATE $this->db.process SET group_id='$group_id',  $write_group_id_text perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateProcessParameterGroupPerm($id, $group_id, $perms, $ownerID)
    {
        $sql = "UPDATE $this->db.process_parameter SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE process_id = '$id'";
        return self::runSQL($sql);
    }


    function checkUsed($table, $name, $id, $userID)
    {
        $ret = 0;
        $warn = "";
        if ($table == "biocorepipe_save") {
            $data = json_decode($this->checkProjectPublic($id, $userID));
            if (!empty($data[0])) {
                $ret = 1;
                $warn .= "Pipeline: $name ($id) already used in group/public projects.\n";
            }
        } else if ($table == "process") {
            $checkPipelinePublic = json_decode($this->checkPipelinePublic($id, $userID));
            $checkProjectPipelinePublic = json_decode($this->checkProjectPipelinePublic($id, $userID));
            if (!empty($checkPipelinePublic[0])) {
                $ret = 1;
                $warn .= "Process: $name ($id) already used in group/public pipelines.\n";
            } else if (!empty($checkProjectPipelinePublic[0])) {
                $ret = 1;
                $warn .= "Process: $name ($id) already used in group/public projects.\n";
            }
        } else if ($table == "project") {
            //$id -> project_id
            $data = json_decode($this->checkSharedRunInProject($id, $userID));
            // if project shared with group, then don't allow to share with another group.
            if (!empty($data[0])) {
                $ret = 1;
                $warn .= "Project: $name ($id) has group/public runs.\n";
            }
        }

        return array($ret, $warn);
    }

    //check if $userID allowed to $mode=r or w to $id from $table
    //3 -> user r+w
    //11-> user r+w, group r
    //15-> user r+w, group r+w 
    //43=> user r+w, group r,   and other r
    //47=> user r+w, group r+w, and other r
    //63=> user r+w, group r+w, and other r (depricated)
    function checkUserPermission($table, $id, $userID, $mode)
    {
        $ret = 0;
        $name = "";
        $releaseCheck = "";
        if ($table == "biocorepipe_save" || $table == "project_pipeline") {
            $releaseCheck = "AND (pip.release_date IS NULL OR pip.release_date <= CURDATE() )";
        }
        if ($mode == "w") {
            $where = "(pip.owner_id='$userID' OR (ug.u_id ='$userID' AND (pip.perms = 15 OR pip.perms = 47 OR pip.perms = 63)))";
        } else if ($mode == "r") {
            $where = "(pip.owner_id='$userID' OR ((pip.perms = 63 OR pip.perms = 43 OR pip.perms = 47) $releaseCheck) OR (ug.u_id ='$userID' AND (pip.perms = 15 OR pip.perms = 43 OR pip.perms = 47 OR pip.perms = 63))) ";
        }
        $sql = "SELECT DISTINCT pip.name, pip.perms, pip.group_id
                FROM $this->db.$table pip
                INNER JOIN $this->db.users u ON pip.owner_id = u.id
                LEFT JOIN $this->db.user_group ug ON  pip.group_id=ug.g_id
                WHERE pip.deleted = 0 AND pip.id = '$id' AND $where ";
        $data = json_decode(self::queryTable($sql));
        if (!empty($data[0])) {
            $ret = 1;
            $name = $data[0]->{'name'};
        }
        return array($ret, $name);
    }

    function checkPermGroupEq($curr_group_id, $curr_perms, $exp_group_id, $exp_perms, $write_group_id, $curr_write_group_id)
    {
        $ret = 0;
        settype($curr_group_id, "integer");
        settype($curr_perms, "integer");
        settype($exp_group_id, "integer");
        settype($exp_perms, "integer");
        if ($curr_group_id == $exp_group_id && $curr_perms == $exp_perms && $write_group_id == $curr_write_group_id) {
            $ret = 1;
        }
        return $ret;
    }

    function checkUserOwnPerm($curr_ownerID, $ownerID)
    {
        $ret = 0;
        settype($curr_ownerID, "integer");
        settype($ownerID, "integer");
        if ($curr_ownerID == $ownerID) {
            $ret = 1;
        }
        return $ret;
    }


    //$type.match(/greaterOrEqual/) only execute when $perms>=$curr_perms
    //$type.match(/dry-run/) prevents update
    //$type.match(/strict/) prevents skipping in case update is needed
    function permUpdtModule($listPermsDenied, $type, $table, $id, $curr_group_id, $curr_perms, $group_id, $perms, $curr_ownerID, $ownerID, $write_group_id, $curr_write_group_id, $getUserGroupsIDs)
    {
        // check if current value of the group and perm are same as expected values
        $checkEq = $this->checkPermGroupEq($curr_group_id, $curr_perms, $group_id, $perms, $write_group_id, $curr_write_group_id);
        if ($checkEq != 1) {
            // if user is not admin and user doesn't own the process then don't allow to change.
            $ownCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
            $userRole = $this->getUserRoleVal($ownerID);
            list($permCheck, $warnName) = $this->getWritePerm($ownerID, $id, $userRole, $table, $getUserGroupsIDs);
            list($checkUsed, $warn) = $this->checkUsed($table, $warnName, $id, $ownerID);
            error_log("$warnName permCheck:$permCheck checkUsed:$checkUsed perms:$perms>$curr_perms ownCheck:$ownCheck");
            if ((!empty($permCheck) || $userRole == "admin") && (empty($checkUsed) || $perms > $curr_perms || ($perms == $curr_perms && $curr_perms > 15) || ($perms == $curr_perms && empty($curr_group_id) && !empty($group_id))) && (!preg_match("/greaterOrEqual/i", $type) || (preg_match("/greaterOrEqual/i", $type) && $perms >= $curr_perms))) {
                // error_log("passed");
                if (!preg_match("/dry-run/i", $type)) {
                    if ($table == "biocorepipe_save") {
                        $this->updatePipelineGroupPermByPipeId($id, $group_id, $perms, $write_group_id, $ownerID);
                    } else if ($table == "process") {
                        $this->updateProcessGroupPerm($id, $group_id, $perms, $write_group_id, $ownerID);
                        $this->updateProcessParameterGroupPerm($id, $group_id, $perms, $ownerID);
                    } else if ($table == "project") {
                        $this->updateProjectGroupPerm($id, $group_id, $perms, $ownerID);
                        $this->updateProjectInputGroupPerm($id, $group_id, $perms, $ownerID);
                    }
                }
            } else {
                if (!empty($warn)) {
                    //validateUpdtNeed: allows skipping in case update not needed
                    if (!preg_match("/strict/i", $type)) {
                        $validateUpdtNeed = $this->validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms);
                        if (!empty($validateUpdtNeed)) {
                            $listPermsDenied[] = $warn;
                        }
                        // if user doesn't own the project and then don't allow to change permission/group of project
                        if ($table == "project" && empty($ownCheck)) {
                            $listPermsDenied[] = $warn;
                        }
                    } else {
                        $listPermsDenied[] = $warn;
                    }
                }
            }
        }
        return $listPermsDenied;
    }

    function pubSearchUpdtModule($table, $id, $publicly_searchable, $curr_publicly_searchable, $ownerID)
    {
        $userRole = $this->getUserRoleVal($ownerID);
        //only admin can update this section
        if ($userRole == "admin") {
            // check if current value of the publicly_searchable is the same as expected value
            if ($curr_publicly_searchable != $publicly_searchable) {
                list($checkUsed, $warn) = $this->checkUsed($table, "", $id, $ownerID);
                // only allow change from true to false when it's not used by others.
                if (empty($checkUsed) || $publicly_searchable == "true") {
                    if ($table == "biocorepipe_save" || $table == "process") {
                        $this->updatePubliclySearchableById($id, $table, $publicly_searchable, $ownerID);
                    }
                }
            }
        }
    }

    function releaseDateUpdtModule($table, $id, $release_date, $pipe_release_date, $curr_ownerID, $ownerID)
    {
        // check if current value of the $release_date is the same as expected value
        if ($pipe_release_date != $release_date) {
            // if user doesn't own the process then don't allow to change.
            $ownCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
            if (!empty($ownCheck)) {
                if ($table == "biocorepipe_save") {
                    $this->updateReleaseDateById($id, $table, $release_date, $ownerID);
                }
            }
        }
    }

    function recursivePermUpdtPipeline($type, $listPermsDenied, $pipeline_id, $group_id, $perms, $ownerID, $publicly_searchable, $release_date, $write_group_id)
    {
        settype($pipeline_id, "integer");
        $pipe = $this->loadPipeline($pipeline_id, $ownerID);
        $pipeData = json_decode($pipe, true);
        if (!empty($pipeData[0])) {
            $nodes = json_decode($pipeData[0]["nodes"]);
            $pipe_write_group_id = $pipeData[0]["write_group_id"];
            $pipe_group_id = $pipeData[0]["group_id"];
            $pipe_perms = $pipeData[0]["perms"];
            $pipe_owner_id = $pipeData[0]["owner_id"];
            $pipe_publicly_searchable = $pipeData[0]["publicly_searchable"];
            $pipe_release_date = $pipeData[0]["release_date"];
            $getUserGroupsIDs = json_decode($this->getUserGroupsIDs($ownerID), true);

            $listPermsDenied = $this->permUpdtModule($listPermsDenied, $type, "biocorepipe_save", $pipeline_id, $pipe_group_id, $pipe_perms, $group_id, $perms, $pipe_owner_id, $ownerID, $write_group_id, $pipe_write_group_id, $getUserGroupsIDs);
            if ($type == "default") {
                $this->pubSearchUpdtModule("biocorepipe_save", $pipeline_id, $publicly_searchable, $pipe_publicly_searchable, $ownerID);
                $this->releaseDateUpdtModule("biocorepipe_save", $pipeline_id, $release_date, $pipe_release_date, $pipe_owner_id, $ownerID);
            }
            if (!empty($nodes)) {
                foreach ($nodes as $item) :
                    if ($item[2] !== "inPro" && $item[2] !== "outPro") {
                        //pipeline modules
                        if (preg_match("/p(.*)/", $item[2], $matches)) {
                            $pipeModId = $matches[1];
                            if (!empty($pipeModId)) {
                                $listPermsDenied = $this->recursivePermUpdtPipeline($type, $listPermsDenied, $pipeModId, $group_id, $perms, $ownerID, $publicly_searchable, $release_date, $write_group_id);
                            }
                            //processes
                        } else {
                            $proId = $item[2];
                            $process_data = json_decode($this->getProcessDataById($proId, $ownerID), true);
                            if (!empty($process_data[0])) {
                                $pro_group_id = $process_data[0]["group_id"];
                                $pro_write_group_id = $process_data[0]["write_group_id"];
                                $pro_perms = $process_data[0]["perms"];
                                $pro_owner_id = $process_data[0]["owner_id"];
                                $pro_publicly_searchable = $process_data[0]["publicly_searchable"];
                                $listPermsDenied = $this->permUpdtModule($listPermsDenied, $type, "process", $proId, $pro_group_id, $pro_perms, $group_id, $perms, $pro_owner_id, $ownerID, $write_group_id, $pro_write_group_id, $getUserGroupsIDs);
                                if ($type == "default") {
                                    $this->pubSearchUpdtModule("process", $proId, $publicly_searchable, $pro_publicly_searchable, $ownerID);
                                }
                            }
                        }
                    }
                endforeach;
            }
        }
        $listPermsDeniedUniq = array_unique($listPermsDenied);
        sort($listPermsDeniedUniq);
        return $listPermsDeniedUniq;
    }

    function checkPermUpdtProject($type, $listPermsDenied, $project_id, $group_id, $perms, $ownerID)
    {
        settype($project_id, "integer");
        $proj = $this->getProjects($project_id, "default", $ownerID);
        $projData = json_decode($proj, true);
        if (!empty($projData[0])) {
            $proj_group_id = $projData[0]["group_id"];
            $proj_perms = $projData[0]["perms"];
            $proj_owner_id = $projData[0]["owner_id"];
            $getUserGroupsIDs = json_decode($this->getUserGroupsIDs($ownerID), true);
            $listPermsDenied = $this->permUpdtModule($listPermsDenied, $type, "project", $project_id, $proj_group_id, $proj_perms, $group_id, $perms, $proj_owner_id, $ownerID, null, null,  $getUserGroupsIDs);
        }
        $listPermsDeniedUniq = array_unique($listPermsDenied);
        sort($listPermsDeniedUniq);
        return $listPermsDeniedUniq;
    }

    function getProjectPipelineProcessOpts($project_pipeline_id, $ownerID)
    {
        $ret = "";
        $userRole = $this->getUserRoleVal($ownerID);
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id, "", $ownerID, $userRole));
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        // first get default pipeline process options 
        $ret = $this->getPipelineProcessOpts(array(), $pipeline_id, $ownerID, "", "");
        return $ret;
    }

    //inputText = "example" //* @textbox @description:"One inputbox is invented"
    //selectText = "sel1" //* @dropdown @options:"none","sel1","sel2" @description:"One text is invented"
    //checkBox = "true" //* @checkbox @description:"One checkbox is created"
    //arr = ["name1","name2"] //* @input
    function parseVarPart($varPart, $splitType)
    {
        $varName = null;
        $defaultVal = null;
        $varSplit = null;
        $varFormat = "";
        if (preg_match("/=/i", $varPart)) {
            if ($splitType === "condition") {
                $varSplit = explode("==", $varPart);
            } else {
                $varSplit = explode("=", $varPart);
            }
            if (count($varSplit) == 2) {
                $varName = trim($varSplit[0]);
                $defaultVal = trim($varSplit[1]);
                // if defaultVal starts and ends with single or double quote, remove these. (keep other quotes)
                if (substr($defaultVal, 0, 1) === "'" && substr($defaultVal, -1) === "'") {
                    $defaultVal = substr($defaultVal, 1, strlen($defaultVal) - 2);
                    $varFormat = "single";
                } else if (substr($defaultVal, 0, 1) === '"' && substr($defaultVal, -1) === '"') {
                    $defaultVal = substr($defaultVal, 1, strlen($defaultVal) - 2);
                    $varFormat = 'double';
                } else if (
                    substr($defaultVal, 0, 1) === "[" ||
                    substr($defaultVal, -1) === "]"
                ) {
                    $varFormat = 'square';
                    $content = substr($defaultVal, 1, strlen($defaultVal) - 2);
                    $content = preg_replace('/\"/', '', $content);
                    $content = preg_replace("/\'/", '', $content);
                    $defaultVal = explode(",", $content);
                }
            }
        } // if /=/ not exist then genericCond is defined
        else {
            if ($splitType === "condition") {
                $varName = trim($varPart);
                $defaultVal = null;
            }
        }
        return array($varName, $defaultVal, $varFormat);
    }
    //turn (a,b,c)  into (a|b|c) format
    function fixParentheses($outputName)
    {
        if (preg_match('/(.*)\((.*?),(.*?)\)(.*)/', $outputName, $match)) {
            $insideBrackets = $match[2] . "," . $match[3];
            $insideBrackets = preg_replace('/\,/', '|', $insideBrackets);
            $outputNameFix = $match[1] . "(" . $insideBrackets . ")" . $match[4];
            if (preg_match('/(.*)\((.*?),(.*?)\)(.*)/', $outputNameFix)) {
                return $this->fixParentheses($outputNameFix);
            } else {
                return $outputNameFix;
            }
        } else {
            return $outputName;
        }
    }

    //parse {var1, var2, var3}, {var5, var6} into array of [var1, var2, var3], [var5, var6]
    function parseBrackets($arr, $trim)
    {
        $finalArr = array();
        $arr = explode("{", $arr);

        if (count($arr) > 0) {
            for ($k = 0; $k < count($arr); $k++) {
                if (!empty($trim)) {
                    $arr[$k] = trim($arr[$k]);
                } else {
                    if (trim($arr[$k]) !== "") {
                        $arr[$k] = trim($arr[$k]);
                    }
                }
                $arr[$k] = preg_replace('/\"/', '', $arr[$k]);
                $arr[$k] = preg_replace("/^'|'$/", '', $arr[$k]);
                $arr[$k] = preg_replace('/\}/', '', $arr[$k]);

                $arr[$k] = $this->fixParentheses($arr[$k]); //turn (a,b,c) into (a|b|c) format for multiple options
                $allcolumn = explode(",", $arr[$k]);
                $allcolumnFinal = array();
                for ($j = 0; $j < count($allcolumn); $j++) {
                    $item = $allcolumn[$j];
                    if (!empty($trim)) {
                        $item = trim($item);
                    } else if (trim($item) !== "") {
                        $item = trim($item);
                    }
                    if (!empty($item)) {
                        $allcolumnFinal[] = $item;
                    }
                }
                if (!empty($allcolumnFinal[0])) {
                    $finalArr[] = $allcolumnFinal;
                }
            }
        }
        return $finalArr;
    }

    //parse main categories: @checkbox, @textbox, @input, @dropdown, @description, @options @title @autofill @show_settings
    //parse style categories: @multicolumn, @array, @condition
    function parseRegPart($regPart)
    {
        $type = null;
        $desc = null;
        $title = null;
        $tool = null;
        $showsett = null;
        $opt = null;
        $allOptCurly = null;
        $multiCol = null;
        $autoform = null;
        $arr = null;
        $cond = null;
        if (preg_match("/@/", $regPart)) {
            $regSplit = explode("@", $regPart);
            for ($i = 0; $i < count($regSplit); $i++) {

                // find type among following list:checkbox|textbox|input|dropdown
                preg_match('/^checkbox|^textbox|^input|^dropdown/i', $regSplit[$i], $typeCheck);
                // check if @autofill tag is defined //* @autofill:{var1="yes", "filling_text"}
                // for multiple options @autofill:{var1=("yes","no"), "filling_text"}
                // for dynamic filling @autofill:{var1=("yes","no"), _build+"filling_text"}
                preg_match("/^autofill:(.*)/i", $regSplit[$i], $autofillCheck);
                if (!empty($autofillCheck) && !empty($autofillCheck[1])) {
                    $autoContent = $autofillCheck[1];
                    if (!empty($autoform)) {
                        $autoform = array();
                    }
                    $autoform[] = $this->parseBrackets($autoContent, false);
                }
                // check if @multicolumn tag is defined //* @style @multicolumn:{var1, var2, var3}, {var5, var6}
                preg_match("/^multicolumn:(.*)/i", $regSplit[$i], $multiColCheck);
                if (!empty($multiColCheck) && !empty($multiColCheck[1])) {
                    $multiContent = $multiColCheck[1];
                    $multiCol = $this->parseBrackets($multiContent, true);
                }
                // check if @array tag is defined //* @style @array:{var1, var2}, {var4}
                preg_match("/^array:(.*)/i", $regSplit[$i], $arrayCheck);
                if (!empty($arrayCheck) && !empty($arrayCheck[1])) {
                    $arrContent = $arrayCheck[1];
                    $arr = $this->parseBrackets($arrContent, true);
                }
                // check if @condition tag is defined //* @style @condition:{var1="yes", var2}, {var1="no", var3, var4}
                preg_match("/^condition:(.*)/i", $regSplit[$i], $condCheck);
                if (!empty($condCheck) && !empty($condCheck[1])) {
                    $condContent = $condCheck[1];
                    if (!empty($cond)) {
                        $cond = array();
                    }
                    $cond[] = $this->parseBrackets($condContent, true);
                }
                if (!empty($typeCheck)) {
                    $type = strtolower($typeCheck[0]);
                }
                // find description
                preg_match('/^description:"(.*)"|^description:\'(.*)\'/i', $regSplit[$i], $descCheck);
                if (!empty($descCheck)) {
                    if (!empty($descCheck[1])) {
                        $desc = $descCheck[1];
                    } else if (!empty($descCheck[2])) {
                        $desc = $descCheck[2];
                    }
                }
                // find title
                preg_match('/^title:"(.*)"|^title:\'(.*)\'/i', $regSplit[$i], $titleCheck);
                if (!empty($titleCheck)) {
                    if (!empty($titleCheck[1])) {
                        $title = $titleCheck[1];
                    } else if (!empty($titleCheck[2])) {
                        $title = $titleCheck[2];
                    }
                }
                // find tooltip
                preg_match('/^tooltip:"(.*)"|^tooltip:\'(.*)\'/i', $regSplit[$i], $toolCheck);
                if (!empty($toolCheck)) {
                    if (!empty($toolCheck[1])) {
                        $tool = $toolCheck[1];
                    } else if (!empty($toolCheck[2])) {
                        $tool = $toolCheck[2];
                    }
                }
                // find show_settings
                preg_match('/^show_settings:"(.*)"|^show_settings:\'(.*)\'/i', $regSplit[$i], $show_settCheck);
                if (!empty($show_settCheck)) {
                    if (!empty($show_settCheck[1])) {
                        $showsett = $show_settCheck[1];
                    } else if (!empty($show_settCheck[2])) {
                        $showsett = $show_settCheck[2];
                    }
                    //seperate process names by comma
                    if (!empty($showsett)) {
                        $showsett = explode(",", $showsett);
                        if (count($showsett) > 0) {
                            for ($k = 0; $k < count($showsett); $k++) {
                                $showsett[$k] = trim($showsett[$k]);
                                $showsett[$k] = preg_replace('/\"/', '', $showsett[$k]);
                                $showsett[$k] = preg_replace("/\'/", '', $showsett[$k]);
                            }
                        }
                    }
                }
                // find options
                preg_match('/^options:\s*"(.*)"|^options:\s*\'(.*)\'|^options:\s*\{(.*)\}/i', $regSplit[$i], $optCheck);

                if (!empty($optCheck)) {
                    if (!empty($optCheck[1])) {
                        $allOpt = $optCheck[1];
                    } else if (!empty($optCheck[2])) {
                        $allOpt = $optCheck[2];
                    } else if (!empty($optCheck[3])) {
                        $allOpt = null;
                        preg_match('/^options:\s*(.*)/i', $regSplit[$i], $curlyOpt);
                        if (!empty($curlyOpt[1])) {
                            $opt = $this->parseBrackets($curlyOpt[1], true);
                        }
                    }
                    //seperate options by comma
                    if (!empty($allOpt)) {
                        $allOpt = explode(",", $allOpt);
                        if (count($allOpt) > 0) {
                            for ($k = 0; $k < count($allOpt); $k++) {
                                $allOpt[$k] = trim($allOpt[$k]);
                                $allOpt[$k] = preg_replace('/\"/', '', $allOpt[$k]);
                                $allOpt[$k] = preg_replace("/\'/", '', $allOpt[$k]);
                            }
                        }
                        $opt = $allOpt;
                    }
                }
            }
        }
        return array(
            $type,
            $desc,
            $tool,
            $opt,
            $multiCol,
            $arr,
            $cond,
            $title,
            $autoform,
            $showsett,
        );
    }

    //parse ProPipePanelScript and create panelObj
    //eg. {schema:[{ varName:"varName",
    //              defaultVal:"defaultVal",
    //              type:"type",
    //              desc:"desc",
    //              tool:"tool",
    //              opt:"opt"}],
    //      style:[{ multicol:[[var1, var2, var3], [var5, var6],
    //              array:[[var1, var2], [var4]]
    //              condi:[{var1:"yes", var2}, {var1:"no", var3, var4}]
    //              }]
    //     }
    function parseProPipePanelScript($script)
    {
        $panelObj = array();
        $panelObj["schema"] = array();
        $panelObj["style"] = array();
        $lines = explode("\n", $script);
        for ($i = 0; $i < count($lines); $i++) {
            $varName = null;
            $defaultVal = null;
            $type = null;
            $desc = null;
            $tool = null;
            $opt = null;
            $multiCol = null;
            $autoform = null;
            $showsett = null;
            $arr = null;
            $cond = null;
            $title = null;
            $varPart = "";
            $regPart = "";
            $parts = explode('//*', $lines[$i]);
            $varFormat = "";
            if (count($parts) > 1) {
                $varPart = $parts[0];
                $regPart = $parts[1];
            } else {
                $regPart = $parts[0];
            }
            if (!empty($varPart)) {
                list($varName, $defaultVal, $varFormat) = $this->parseVarPart($varPart, "");
            }
            if (!empty($regPart)) {
                list($type, $desc, $tool, $opt, $multiCol, $arr, $cond, $title, $autoform, $showsett) = $this->parseRegPart($regPart);
            }
            if (!empty($type) && !empty($varName)) {
                $panelObj["schema"][] = array(
                    "varName" => $varName,
                    "defaultVal" => $defaultVal,
                    "type" => $type,
                    "desc" => $desc,
                    "tool" => $tool,
                    "opt" => $opt,
                    "title" => $title,
                    "autoform" => $autoform,
                    "showsett" => $showsett,
                    "varFormat" => $varFormat
                );
            }
            if ($multiCol || $arr || $cond) {
                $panelObj["style"][] = array(
                    "multicol" => $multiCol,
                    "array" => $arr,
                    "condi" => $cond
                );
            }
        }
        return $panelObj;
    }

    //$ret parameter schema and style
    function getPipelineProcessOpts($ret, $pipeline_id, $ownerID, $prev_gnum, $prev_proName)
    {
        settype($pipeline_id, "integer");
        $pipe = $this->loadPipeline($pipeline_id, $ownerID);
        $pipeData = json_decode($pipe, true);
        if (!empty($pipeData[0])) {
            $allPipeScript = "";
            if (empty($prev_gnum)) {
                $pipeData[0]["script_pipe_config"] = htmlspecialchars_decode($pipeData[0]["script_pipe_config"], ENT_QUOTES);
                $pipeData[0]["script_pipe_header"] = htmlspecialchars_decode($pipeData[0]["script_pipe_header"], ENT_QUOTES);
                $allPipeScript = $pipeData[0]["script_pipe_config"] . "\n" . $pipeData[0]["script_pipe_header"];
                $ret['pipe'] = $this->parseProPipePanelScript($allPipeScript);
                $ret['pipe']["details"] = array();
            }


            $nodes = json_decode($pipeData[0]["nodes"]);
            if (!empty($nodes)) {
                foreach ($nodes as $gnum => $item) :
                    $gnum = preg_replace('/^g-/', '', $gnum);
                    $proName = $item[3];

                    if ($item[2] !== "inPro" && $item[2] !== "outPro") {
                        //pipeline modules
                        if (preg_match("/p(.*)/", $item[2], $matches)) {
                            $pipeModId = $matches[1];
                            if (!empty($pipeModId)) {
                                list($ret) = $this->getPipelineProcessOpts($ret, $pipeModId, $ownerID, $gnum, $proName);
                            }
                            //processes
                        } else {
                            $proId = $item[2];
                            $set_gnum = $gnum;
                            $set_proName = $proName;
                            if (!empty($prev_gnum)) {
                                $set_gnum = $prev_gnum . "_" . $gnum;
                                $set_proName = $prev_proName . "_" . $proName;
                            }
                            $process_data = json_decode($this->getProcessDataById($proId, $ownerID), true);
                            if (!empty($process_data[0])) {
                                $process_data[0]["script"] = htmlspecialchars_decode($process_data[0]["script"], ENT_QUOTES);
                                $process_data[0]["script_header"] = htmlspecialchars_decode($process_data[0]["script_header"], ENT_QUOTES);
                                $allProScript = $process_data[0]["script"] . "\n" . $process_data[0]["script_header"];
                                $ret[$set_gnum] = $this->parseProPipePanelScript($allProScript);
                                $ret[$set_gnum]["details"] = array();
                                $ret[$set_gnum]["details"]["longVarName"] = $set_proName;
                                $ret[$set_gnum]["details"]["processOrgName"] = $process_data[0]["name"];
                                $ret[$set_gnum]["details"]["processName"] = $proName;
                                $ret[$set_gnum]["details"]["onlyModuleName"] = $prev_proName;
                                $ret[$set_gnum]["details"]["gnum"] = $gnum;
                                $ret[$set_gnum]["details"]["prefix"] = $prev_gnum;
                            }
                        }
                    }
                endforeach;
            }
        }
        return array($ret);
    }

    function updateUUID($id, $type, $res)
    {
        $update = "";
        if ($type == "process") {
            $table = "process";
            if (!empty($res->uuid) && !empty($res->rev_uuid)) {
                $update = "process_uuid='$res->uuid', process_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "process_rev") {
            $table = "process";
            if (!empty($res->rev_uuid)) {
                $update = "process_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "pipeline") {
            $table = "biocorepipe_save";
            if (!empty($res->uuid) && !empty($res->rev_uuid)) {
                $update = "pipeline_uuid='$res->uuid', pipeline_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "pipeline_rev") {
            $table = "biocorepipe_save";
            if (!empty($res->rev_uuid)) {
                $update = "pipeline_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "run_log") {
            $table = "run_log";
            if (!empty($res->rev_uuid)) {
                $update = "run_log_uuid='$res->rev_uuid'";
                $targetDir = "{$this->tmp_path}/api";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
            }
        }
        $sql = "UPDATE $this->db.$table SET $update  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function getUUIDLocal($type)
    {
        $params = [];
        $params["type"] = $type;
        $myClass = new funcs();
        $res = (object)$myClass->getUUID($params);
        return $res;
    }

    function moveFile($type, $from, $to, $ownerID)
    {
        $res = false;
        if ($type == "pubweb") {
            $from = "{$this->run_path}/$from";
            $to = "{$this->run_path}/$to";
        }
        if (file_exists($from)) {
            $res = rename($from, $to);
        }

        return json_encode($res);
    }

    // $file: file content
    // $sName: sample Name @string
    // $sNameLoc: sample Name location [@options: "row", "column", "filename"]
    // $featureLoc: sample Name location [@options: "row", "column", "both"]
    function dmetaFileConvert($uuid, $filepaths, $sName, $sNameLoc, $featureLoc, $ownerID)
    {
        ini_set('memory_limit', '900M');
        $data = array();
        if ($sNameLoc == "filename") {
            for ($i = 0; $i < count($filepaths); $i++) {
                // check $filepath if it has the sample name:
                if (preg_match("/$sName/i", $filepaths[$i])) {
                    $partPubwebUrl = str_replace($this->base_path, "", $this->pubweb_url);
                    $fileURL = $partPubwebUrl . "/$uuid/" . $filepaths[$i];
                    $data[] = $fileURL;
                }
            }
        } else {
            // use only one file, if $sNameLoc != "filename"
            $file = json_decode($this->getFileContent($uuid, $filepaths[0], $ownerID));
            // add seperator detection algorithm
            $sep = "\t";
            $file = trim($file);
            $lines = explode("\n", $file);
            $rowheader = explode($sep, $lines[0]);
            //sample names at the first column
            if ($sNameLoc == "column" && $featureLoc == "row") {
                $samplePos = array_search($sName, $rowheader);
                for ($i = 1; $i < count($lines); $i++) {
                    $currentline = explode($sep, $lines[$i]);
                    $data[$currentline[0]] = $currentline[$samplePos];
                }
                //sample names at the first row
            } else if ($sNameLoc == "row" && $featureLoc == "column") {
                for ($i = 1; $i < count($lines); $i++) {
                    $currentline = explode($sep, $lines[$i]);
                    if ($currentline[0] == $sName) {
                        for ($k = 1; $k < count($currentline); $k++) {
                            $data[$rowheader[$k]] = $currentline[$k];
                        }
                    }
                }
            }
        }
        return $data;
    }

    // tsv to json converter
    function tsvCsvToJson($tsv)
    {
        ini_set('memory_limit', '900M');
        $tsv = trim($tsv);
        $lines = explode("\n", $tsv);
        $sep = ",";
        if (preg_match("/\t/", $lines[0])) {
            $sep = "\t";
        }
        $header = explode($sep, $lines[0]);
        $data = array();
        for ($i = 1; $i < count($lines); $i++) {
            $obj = new stdClass();
            $currentline = explode($sep, $lines[$i]);
            for ($j = 0; $j < count($header); $j++) {
                $name = trim($header[$j]);
                $obj->$name = trim($currentline[$j]);
            }
            $data[] = $obj;
        }
        return $data;
    }


    function callDebrowser($uuid, $dir, $filename)
    {
        $pubwebDir = "{$this->run_path}/$uuid/pubweb/";
        $targetDir = "{$this->run_path}/$uuid/pubweb/$dir";
        $targetFile = "{$targetDir}/{$filename}";
        $targetJson = "{$targetDir}/.{$filename}";
        $tsv = file_get_contents($targetFile);
        $array = $this->tsvCsvToJson($tsv);
        ini_set('memory_limit', '3000M');
        file_put_contents($targetJson, json_encode($array));
        $ret = array();
        $ret["count_file"] = "$dir/.{$filename}";
        // search for metadata file in pubweb directory
        $validMetadataFiles = ["debrowser_metadata.tsv", "debrowser_metadata.csv", "debrowser_metadata.txt"];
        $metadata = "";
        $it = new RecursiveDirectoryIterator($pubwebDir);
        foreach (new RecursiveIteratorIterator($it) as $file) {
            $pathAr = explode('/', $file);
            $onlymeta = array_pop($pathAr);
            $metaDir = substr($file, strlen($pubwebDir));
            if (in_array(strtolower($onlymeta), $validMetadataFiles)) {
                $subDir = preg_replace('/' . $onlymeta . '$/', '', $metaDir);
                $targetDir = "{$this->run_path}/$uuid/pubweb/$subDir";
                $targetFile = "{$targetDir}/{$onlymeta}";
                $targetJson = "{$targetDir}/.{$onlymeta}";
                $tsv = file_get_contents($targetFile);
                $array = $this->tsvCsvToJson($tsv);
                file_put_contents($targetJson, json_encode($array));
                $metadata = "{$subDir}/.{$onlymeta}";
                break;
            }
        }

        $ret["count_file"] = "$dir/.{$filename}";
        $ret["metadata_file"] = $metadata;
        return json_encode($ret);
    }
    function callRmarkdown($type, $uuid, $text, $dir, $filename)
    {
        //travis fix
        if (!headers_sent()) {
            ob_start();
            // send $data to user
            $targetDir = "{$this->run_path}/$uuid/pubweb/$dir/.tmp";
            $errorCheck = false;
            $errorText = "";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $format = "";
            if ($type == "rmdtext") {
                $format = "html";
            } else if ($type == "rmdpdf") {
                $format = "pdf";
            }
            $pUUID = uniqid();
            $log = "{$targetDir}/{$filename}.log{$pUUID}";
            $response = "{$targetDir}/{$filename}.curl{$pUUID}";
            $file = "{$targetDir}/{$filename}.{$format}{$pUUID}";
            $err = "{$targetDir}/{$filename}.{$format}.err{$pUUID}";
            $url =  OCPU_URL . "/ocpu/library/markdownapp/R/" . $type;
            $cmd = "(curl '$url' -H \"Content-Type: application/json\" -k -d '{\"text\":$text}' -o $response > $log 2>&1) & echo \$!";
            error_log($cmd);
            $pid = exec($cmd);
            $data = json_encode($pUUID);
            if (!headers_sent()) {
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/json');
                echo $data;
            } else {
                echo $data;
            }
            //function returned at this point for user
            $size = ob_get_length();
            header("Content-Encoding: none");
            header("Content-Length: {$size}");
            header("Connection: close");
            ob_end_flush();
            ob_flush();
            flush();
        }
        //server side keeps working
        if (!empty($pUUID)) {
            for ($i = 0; $i < 100; $i++) {
                sleep(1);
                $resText = $this->readFile($response);
                if (!empty($resText)) {
                    unlink($response);
                    break;
                }
                if ($i < 5) {
                    sleep(1);
                } else {
                    sleep(4);
                }
            }
            $ret = "";
            if (!empty($resText)) {
                $lines = explode("\n", $resText);
                foreach ($lines as $lin) :
                    if ($type == "rmdtext" && preg_match("/.*output.html/", $lin, $matches)) {
                        $ret = OCPU_URL . $lin;
                        break;
                    } else if ($type == "rmdpdf" && preg_match("/.*output.pdf/", $lin, $matches)) {
                        $ret = OCPU_URL . $lin;
                        break;
                    }
                endforeach;

                if (empty($ret)) {
                    $errorCheck = true;
                    $errorText = $resText;
                }
                if (!empty($ret)) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    if (file_exists($err)) {
                        unlink($err);
                    }
                    exec("curl '$ret' -o \"{$file}\" > /dev/null 2>&1 &", $res, $exit);
                } else {
                    $errorCheck = true;
                }
            } else {
                $errorCheck = true;
                $errorText = "Timeout error";
            }
            if ($errorCheck == true) {
                $fp = fopen($err, 'w');
                fwrite($fp, $errorText);
                fclose($fp);
            }
        }
    }

    function callApp($app_id, $uuid, $text, $dir, $filename, $container_id, $pUUID, $time, $ownerID)
    {
        $ret = array();
        $text = urldecode($text);
        $ret["app_id"] = $app_id;
        $appData = $this->getContainers($container_id, "", $ownerID);
        $appData = json_decode($appData, true);
        $app_name = $appData[0]["name"];
        $image_name = $appData[0]["image_name"];
        $container_type = $appData[0]["type"];

        $runDir = realpath("{$this->run_path}/$uuid/pubweb");
        $mountedPath = $this->MOUNTED_VOLUME;
        $runDirParentMachine = preg_replace('/^\/export/', $mountedPath, $runDir);
        $targetDir = "{$this->run_path}/$uuid/pubweb/$dir/.app";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        if (!file_exists("$runDir/.app")) {
            mkdir("$runDir/.app", 0777, true);
        }

        // Startup command
        // if filename ends with ipynb then copy file to $runDir/.${pUUID}_startup.ipynb
        $extension = substr(strrchr($filename, '.'), 1);
        if ($extension == "ipynb" && $filename == "startup.ipynb") {

            $startup_file = "{$runDir}/.app/${pUUID}_startup.ipynb";
            $this->writeFile($startup_file, $text, "w");
        }

        // docker command
        $log = "{$targetDir}/{$filename}.log{$pUUID}";
        $cmd = "";
        settype($time, 'integer');
        $time_in_sec = $time * 60;
        if ($container_type == "docker" && !empty($image_name)) {
            $container_name = "{$pUUID}_{$ownerID}";
            //docker run -d --privileged --rm -p 8888:8888  -v /Users/onuryukselen/projects/biocore/DolphinNext/github/dolphinnext/public/tmp/pub/1N1WP05kAZdaNrEGNtRs6xAXnCoJg5/pubweb:/home/jovyan/work  -u root jupyter:1.0 
            $cmd = "docker run --name {$container_name} -d --privileged -e DNEXT_APP_ID='{$pUUID}' -e DNEXT_RUN_TIME='{$time_in_sec}' --rm -p 8888:8888  -v {$runDirParentMachine}:/home/jovyan/work  -u root {$image_name}  >> {$log} 2>&1 & echo $! &";
        }
        $this->writeFile($log, $cmd, "w");
        $pid = shell_exec($cmd);
        $pid = trim($pid);
        error_log($pid);
        $this->writeFile($log, $pid, "a");
        if (!empty($pid)) {
            $this->updateAppPid($app_id, $pid, $ownerID);
        } else {
            $ret["message"] = "App PID not found.";
        }
        $ret["uid"] = $pUUID;
        return $ret;
    }


    function getUUIDAPI($data, $type, $id)
    {
        //travis fix
        if (!headers_sent()) {
            ob_start();
            // send $data to user
            if (!headers_sent()) {
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/json');
                echo $data;
            } else {
                echo $data;
            }
            //function returned at this point for user
            $size = ob_get_length();
            header("Content-Encoding: none");
            header("Content-Length: {$size}");
            header("Connection: close");
            ob_end_flush();
            ob_flush();
            flush();
        }
        //server side keeps working
        $targetDir = "{$this->tmp_path}/api";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $uuidPath = "{$targetDir}/{$type}{$id}.txt";
        $request = CENTRAL_API_URL . "/api/service.php?func=getUUID&type=$type";
        exec("curl '$request' -o $uuidPath > /dev/null 2>&1 &", $res, $exit);
        for ($i = 0; $i < 4; $i++) {
            sleep(5);
            $uuidFile = $this->readFile($uuidPath);
            if (!empty($uuidFile)) {
                $res = json_decode($uuidFile);
                unlink($uuidPath);
                break;
            }
        }
        if (!isset($res->rev_uuid)) {
            //call local functions to get uuid
            $params = [];
            $params["type"] = $type;
            $myClass = new funcs();
            $res = (object)$myClass->getUUID($params);
            if (isset($res->rev_uuid)) {
                $this->updateUUID($id, $type, $res);
            }
        } else {
            $this->updateUUID($id, $type, $res);
        }
    }


    function convert_array_to_obj_recursive($a)
    {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                if (is_integer($k)) {
                    // only need this if you want to keep the array indexes separate
                    // from the object notation: eg. $o->{1}
                    $a['index'][$k] = $this->convert_array_to_obj_recursive($v);
                } else {
                    $a[$k] = $this->convert_array_to_obj_recursive($v);
                }
            }

            return (object) $a;
        }

        // else maintain the type of $a
        return $a;
    }

    // get writePermQuery by checking all user groups for the ownerID
    function getWritePerm($ownerID, $id, $userRole, $table, $getUserGroupsIDs)
    {
        $ret = false;
        $name = "";
        if ($userRole == "admin") return array(true, $name);
        if (empty($id)) return array(true, $name);
        $writePermQuery = " IF(owner_id='$ownerID',1,0) as write_perm ";
        if ($table == "biocorepipe_save" || $table == "process") {

            for ($j = 0; $j < count($getUserGroupsIDs); $j++) {
                $group_id = $getUserGroupsIDs[$j]["id"];

                if ($j == 0) {
                    $writePermQuery = "IF(owner_id='$ownerID' OR FIND_IN_SET('" . $group_id . "',write_group_id) > 0 ";
                } else {
                    $writePermQuery .= " OR FIND_IN_SET('" . $group_id . "',write_group_id) > 0 ";
                }
            }
            if (count($getUserGroupsIDs) > 0) {
                $writePermQuery .= " ,1,0) as write_perm ";
            }
        }
        $data = json_decode(self::queryTable("SELECT name, $writePermQuery FROM $this->db.$table WHERE id='$id'"));
        if (!empty($data[0])) {
            $name = $data[0]->{'name'};
            $write_perm = $data[0]->{'write_perm'};
            if (!empty($write_perm)) $ret = true;
        }
        return array($ret, $name);
    }


    //if you add new field here, please consider import/export functionality(import.js - itemOrder)
    function saveAllPipeline($newObj, $userRole, $ownerID)
    {
        $name =  $newObj->{"name"};
        $id = $newObj->{"id"};
        $nodes = json_encode($newObj->{"nodes"});
        $mainG = "{\'mainG\':" . json_encode($newObj->{"mainG"}) . "}";
        $edges = "{\'edges\':" . json_encode($newObj->{"edges"}) . "}";
        $summary = addslashes(htmlspecialchars(urldecode($newObj->{"summary"}), ENT_QUOTES));
        $group_id = $newObj->{"group_id"};
        $write_group_id = $newObj->{"write_group_id"};
        $perms = $newObj->{"perms"};
        $pin = $newObj->{"pin"};
        $pin_order = $newObj->{"pin_order"};
        $publicly_searchable = isset($newObj->{"publicly_searchable"}) ? $newObj->{"publicly_searchable"} : "false";
        $script_pipe_header = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_header"}), ENT_QUOTES));
        $script_pipe_footer = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_footer"}), ENT_QUOTES));
        $script_pipe_config = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_config"}), ENT_QUOTES));
        $script_mode_header = $newObj->{"script_mode_header"};
        $script_mode_footer = $newObj->{"script_mode_footer"};
        $pipeline_group_id = $newObj->{"pipeline_group_id"};
        $process_list = $newObj->{"process_list"};
        $pipeline_list = $newObj->{"pipeline_list"};
        $app_list = $newObj->{"app_list"};
        $publish_web_dir = $newObj->{"publish_web_dir"};
        $publish_dmeta_dir = addslashes(htmlspecialchars(urldecode($newObj->{"publish_dmeta_dir"}), ENT_QUOTES));
        $pipeline_gid = isset($newObj->{"pipeline_gid"}) ? $newObj->{"pipeline_gid"} : "";
        if ($pipeline_gid == "") {
            $max_gid = json_decode($this->getMaxPipeline_gid(), true)[0]["pipeline_gid"];
            settype($max_gid, "integer");
            if (!empty($max_gid)) {
                $pipeline_gid = $max_gid + 1;
            } else {
                $pipeline_gid = 1;
            }
        }
        $rev_comment = isset($newObj->{"rev_comment"}) ? $newObj->{"rev_comment"} : "";
        $rev_id = isset($newObj->{"rev_id"}) ? $newObj->{"rev_id"} : "";
        $pipeline_uuid = isset($newObj->{"pipeline_uuid"}) ? $newObj->{"pipeline_uuid"} : "";
        $pipeline_rev_uuid = isset($newObj->{"pipeline_rev_uuid"}) ? $newObj->{"pipeline_rev_uuid"} : "";
        settype($pipeline_group_id, "integer");
        settype($rev_id, "integer");
        settype($pipeline_gid, "integer");
        settype($perms, "integer");
        settype($group_id, "integer");
        settype($pin_order, "integer");
        settype($id, 'integer');

        $admin_settings = "";
        $admin_items = "";
        if ($id > 0) {
            if ($userRole == "admin") {
                $admin_settings = "publicly_searchable='$publicly_searchable', pin='$pin', pin_order='$pin_order',";
            }
            $sql = "UPDATE $this->db.biocorepipe_save set name = '$name', edges = '$edges', summary = '$summary', mainG = '$mainG', nodes ='$nodes', date_modified = now(), group_id = '$group_id',  write_group_id = '$write_group_id', perms = '$perms', $admin_settings script_pipe_header = '$script_pipe_header', script_pipe_config = '$script_pipe_config', script_pipe_footer = '$script_pipe_footer', script_mode_header = '$script_mode_header', script_mode_footer = '$script_mode_footer', pipeline_group_id='$pipeline_group_id', process_list='$process_list', pipeline_list='$pipeline_list',  app_list='$app_list', publish_web_dir='$publish_web_dir', publish_dmeta_dir='$publish_dmeta_dir', last_modified_user = '$ownerID' where id = '$id'";
            $pipeline_gid = json_decode($this->getPipeline_gid($id), true)[0]["pipeline_gid"];
            $this->updateAllPipelineGroupByGid($pipeline_gid, $pipeline_group_id, $name, $ownerID);
        } else {
            if ($userRole == "admin") {
                $admin_settings = "'$pin', '$pin_order', '$publicly_searchable',";
                $admin_items = "pin, pin_order, publicly_searchable,";
            }
            $sql = "INSERT INTO $this->db.biocorepipe_save($admin_items owner_id, summary, edges, mainG, nodes, name, pipeline_gid, rev_comment, rev_id, date_created, date_modified, last_modified_user, write_group_id, group_id, perms, script_pipe_header, script_pipe_footer, script_mode_header, script_mode_footer,pipeline_group_id,process_list,pipeline_list, pipeline_uuid, pipeline_rev_uuid, publish_web_dir, publish_dmeta_dir, script_pipe_config, app_list) VALUES ($admin_settings '$ownerID', '$summary', '$edges', '$mainG', '$nodes', '$name', '$pipeline_gid', '$rev_comment', '$rev_id', now(), now(), '$ownerID','$write_group_id','$group_id', '$perms',  '$script_pipe_header', '$script_pipe_footer', '$script_mode_header', '$script_mode_footer', '$pipeline_group_id', '$process_list', '$pipeline_list', '$pipeline_uuid', '$pipeline_rev_uuid', '$publish_web_dir', '$publish_dmeta_dir','$script_pipe_config','$app_list')";
        }
        return self::insTable($sql);
    }
    function getSavedPipelines($ownerID)
    {
        if ($ownerID == "") {
            $ownerID = "''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id, pip.pipeline_gid
                                  FROM $this->db.biocorepipe_save pip
                                  INNER JOIN $this->db.users u ON pip.deleted=0 AND pip.owner_id = u.id";
                    return self::queryTable($sql);
                }
            }
        }
        $where = " where pip.deleted=0 AND pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15)";
        $sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id, pip.pipeline_gid
                            FROM $this->db.biocorepipe_save pip
                            INNER JOIN $this->db.users u ON pip.owner_id = u.id
                            LEFT JOIN $this->db.user_group ug ON pip.group_id=ug.g_id
                            $where";
        return self::queryTable($sql);
    }
    function loadPipeline($id, $ownerID)
    {
        if (!empty($ownerID)) {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])) {
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin") {
                    $sql = "select pip.*, u.username, pg.group_name as pipeline_group_name, IF(pip.owner_id='$ownerID',1,0) as own, 1 as write_group_perm
                                  FROM $this->db.biocorepipe_save pip
                                  INNER JOIN $this->db.users u ON pip.owner_id = u.id
                                  INNER JOIN $this->db.pipeline_group pg ON pip.pipeline_group_id = pg.id
                                  where pip.deleted=0 AND pip.id = '$id'";
                    return self::queryTable($sql);
                }
            }
        }

        // get writePermQuery by checking all user groups for the ownerID
        $writePermQuery = " 0 as write_group_perm ";
        $getUserGroupsIDs = json_decode($this->getUserGroupsIDs($ownerID), true);
        for ($j = 0; $j < count($getUserGroupsIDs); $j++) {
            $group_id = $getUserGroupsIDs[$j]["id"];
            if ($j == 0) {
                $writePermQuery = "IF(FIND_IN_SET('" . $group_id . "',pip.write_group_id) > 0 ";
            } else {
                $writePermQuery .= " OR FIND_IN_SET('" . $group_id . "',pip.write_group_id) > 0 ";
            }
        }
        if (count($getUserGroupsIDs) > 0) {
            $writePermQuery .= " ,1,0) as write_group_perm ";
        }

        // IF(FIND_IN_SET('1',pip.write_group_id) > 0 OR FIND_IN_SET('0',pip.write_group_id) > 0,1,0) as write_perm
        // 0 as write_perm
        $sql = "select pip.*, u.username, pg.group_name as pipeline_group_name, IF(pip.owner_id='$ownerID',1,0) as own, $writePermQuery
                            FROM $this->db.biocorepipe_save pip
                            INNER JOIN $this->db.users u ON pip.owner_id = u.id
                            INNER JOIN $this->db.pipeline_group pg ON pip.pipeline_group_id = pg.id
                            LEFT JOIN $this->db.user_group ug ON pip.group_id=ug.g_id
                            where pip.deleted=0 AND pip.id = '$id' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
        return self::queryTable($sql);
    }
    function removePipelineById($id)
    {
        $sql = "UPDATE $this->db.biocorepipe_save SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function savePipelineDetails($id, $summary, $group_id, $perms, $pin, $pin_order, $publicly_searchable, $pipeline_group_id, $userRole, $release_date, $ownerID)
    {
        $admin_settings = "";
        if ($userRole == "admin") {
            $admin_settings = "publicly_searchable='$publicly_searchable', pin='$pin', pin_order='$pin_order',";
        }

        $sql = "UPDATE $this->db.biocorepipe_save SET summary='$summary', group_id='$group_id',  perms='$perms', $admin_settings last_modified_user = '$ownerID', pipeline_group_id='$pipeline_group_id', release_date=" . ($release_date == NULL ? "NULL" : "'$release_date'") . " WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function exportPipeline($id, $ownerID, $type, $layer)
    {
        $layer += 1;
        $data = $this->loadPipeline($id, $ownerID);
        $new_obj = json_decode($data, true);
        $new_obj[0]["layer"] = $layer;
        $final_obj = [];
        if ($type == "main") {
            $final_obj["main_pipeline_{$id}"] = $new_obj[0];
        } else {
            $final_obj["pipeline_module_{$id}"] = $new_obj[0];
        }
        if (!empty($new_obj[0]["nodes"])) {
            $nodes = json_decode($new_obj[0]["nodes"]);
            foreach ($nodes as $item) :
                if ($item[2] !== "inPro" && $item[2] !== "outPro") {
                    //pipeline modules
                    if (preg_match("/p(.*)/", $item[2], $matches)) {
                        $pipeModId = $matches[1];
                        if (!empty($pipeModId)) {
                            $pipeModule = [];
                            settype($pipeModId, "integer");
                            $pipeModule = $this->exportPipeline($pipeModId, $ownerID, "pipeModule", $layer);
                            $pipeModuleDec = json_decode($pipeModule, true);
                            $final_obj = array_merge($pipeModuleDec, $final_obj);
                        }
                        //processes
                    } else {
                        $process_id = $item[2];
                        $pro_para_in = $this->getInputsPP($process_id);
                        $pro_para_out = $this->getOutputsPP($process_id);
                        $process_data = $this->getProcessDataById($process_id, $ownerID);
                        $final_obj["pro_para_inputs_$process_id"] = $pro_para_in;
                        $final_obj["pro_para_outputs_$process_id"] = $pro_para_out;
                        $final_obj["process_{$process_id}"] = $process_data;
                    }
                }
            endforeach;
        }
        return json_encode($final_obj);
    }
}
