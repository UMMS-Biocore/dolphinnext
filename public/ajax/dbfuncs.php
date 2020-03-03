<?php
require_once(__DIR__."/../api/funcs.php");
require_once(__DIR__."/../../config/config.php");



class dbfuncs {
    private $nf_path = __DIR__."/../../nf"; 
    private $dbhost = DBHOST;
    private $db = DB;
    private $dbuser = DBUSER;
    private $dbpass = DBPASS;
    private $dbport = DBPORT;
    private $run_path = RUNPATH;
    private $tmp_path = TEMPPATH;
    private $ssh_path = SSHPATH;
    private $base_path = BASE_PATH;
    private $ssh_settings = "-oStrictHostKeyChecking=no -q -oChallengeResponseAuthentication=no -oBatchMode=yes -oPasswordAuthentication=no -oConnectTimeout=3";
    private $amz_path = AMZPATH;
    private $goog_path = GOOGPATH;
    private $amazon = AMAZON;
    private $next_ver = NEXTFLOW_VERSION;
    private $test_profile_group_id = TEST_PROFILE_GROUP_ID;
    private static $link;

    function __construct() {
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
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: '. mysqli_connect_error());
        }
        $result=self::$link->query($sql);
        $link->close();

        if (!$result) {
            error_log($sql);
            trigger_error('Database Error: ' . self::$link->error);
        }
        if ($result && $result!="1")
        {
            return $result;
        }
        return json_encode (json_decode ("{}"));
    }
    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql))
        {
            while(($row=$res->fetch_assoc()))
            {
                if (isset($row['sname'])){
                    $row['sname'] = htmlspecialchars_decode($row['sname'], ENT_QUOTES);
                }
                $data[]=$row;
            }

            $res->close();
        }
        return json_encode($data);
    }

    function queryAVal($sql){
        $res = $this->runSQL($sql);
        if (is_object($res)){
            $num_rows =$res->num_rows;
            if (is_object($res) && $num_rows>0){
                $row=$res->fetch_array();
                return $row[0];
            }
        }
        return "0";
    }

    function insTable($sql)
    {
        $data = array();

        if ($res = $this->runSQL($sql))
        {
            $insertID = self::$link->insert_id;
            $data = array('id' => $insertID);
        }
        return json_encode($data);
    }

    function writeLog($uuid,$text,$mode, $filename){
        $file = fopen("{$this->run_path}/$uuid/run/$filename", $mode);
        fwrite($file, $text."\n");
        fclose($file);
    }
    //$img: path of image
    //$singu_save=true to overwrite on image
    function imageCmd($singu_cache, $img, $singu_save, $type, $profileType,$profileId, $runType, $dolphin_publish_real, $ownerID){
        $cmd = "";
        $imgPath = "";
        $downPath = '$NXF_SINGULARITY_CACHEDIR';
        //full path
        if (substr($img,0,1) == "/") {
            $imgPath = $img;
        } else if (preg_match("/http:/i",$img) || preg_match("/https:/i",$img) || preg_match("/ftp:/i",$img)){
            if ($profileType == "amazon"){
                $amzData=$this->getProfileCloudbyID($profileId, $profileType, $ownerID);
                $amzDataArr=json_decode($amzData,true);
                $downPath = $amzDataArr[0]["shared_storage_mnt"]."/.dolphinnext/singularity"; // /mnt/efs
            }
            if (!empty($singu_cache)){
                $downPath = $singu_cache;
            }
            $imageNameAr = explode('/',$img);
            $imageName=$imageNameAr[count($imageNameAr)-1];
            $imgPath ="$downPath/$imageName";
            $wgetCmd = "if [ ! -f $downPath/$imageName ]; then wget $img; fi";
            if ($singu_save == "true"){
                $cmd = "mkdir -p $downPath && cd $downPath && rm -f ".$imageName." && $wgetCmd";
            } else {
                $cmd = "mkdir -p $downPath && cd $downPath && $wgetCmd";
            }
        } else if ($type == 'singularity'){
            $prefix = "";
            preg_match("/(shub|docker):\/\/(.*)/", $img, $matches);
            //docker or singularity image
            if (!empty($matches[2])){
                $prefix = str_replace("/","-",$matches[2]);
                //docker image if doesn't start with shub|docker
            } else if (substr($img,0,1) != "/") {
                $prefix = str_replace("/","-",$img);
                $prefix = str_replace(":","-",$prefix);
                $img = "docker://$img";
            } 
            if (!empty($prefix)){
                if ($profileType == "amazon"){
                    $amzData=$this->getProfileCloudbyID($profileId, $profileType, $ownerID);
                    $amzDataArr=json_decode($amzData,true);
                    $downPath = $amzDataArr[0]["shared_storage_mnt"]."/.dolphinnext/singularity"; // /mnt/efs
                }
                if (!empty($singu_cache)){
                    $downPath = $singu_cache;
                }

                $imageName = "$prefix.simg";
                $imgPath ="$downPath/$imageName";
                $shubCmd = "if [ ! -f $downPath/{$imageName} ]; then singularity pull --name {$imageName} $img; fi";
                if ($singu_save == "true"){
                    $cmd = "mkdir -p $downPath && cd $downPath && rm -f {$imageName} && $shubCmd";
                } else {
                    $cmd = "mkdir -p $downPath && cd $downPath && $shubCmd";
                }
            }
        } 

        if (!empty($cmd) && $runType == "initial"){
            $cmd.= " && find $downPath -type f -regex '.*UMMS-Biocore-initialrun.*img' -not -name '{$imageName}' -delete";
        }
        // nextflow google cloud doesn't support gs://singularity.images. error: Unsupported transport type: gs
        //        if (!empty($cmd) && $profileType == 'google'){
        //            $cmd.= " && gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -o GSUtil:parallel_composite_upload_threshold=150M cp -n $imageName $dolphin_publish_real/$imageName";
        //            $imgPath ="$dolphin_publish_real/$imageName";
        //        }
        return array($cmd, $imgPath);
    }

    //type:w creates new file
    function createDirFile ($pathDir, $fileName, $type, $text){
        if ($pathDir != ""){
            if (!file_exists($pathDir)) {
                mkdir($pathDir, 0755, true);
            }
        }
        if ($fileName != ""){
            $file = fopen("$pathDir/$fileName", $type);
            fwrite($file, $text);
            fclose($file);
            chmod("$pathDir/$fileName", 0755);
        }
    }

    //if logArray not exist than send empty ""
    function runCommand ($cmd, $logName, $logArray) {
        $pid_command = popen($cmd, 'r');
        $pid = fread($pid_command, 2096);
        pclose($pid_command);
        if (empty($logArray)){
            $log_array = array($logName => $pid);
        } else {
            $log_array[$logName] = $pid;
        }
        return $log_array;
    }

    //full path for file
    function readFile($path){
        $content = "";
        if (file_exists($path)){
            $handle = fopen($path, 'r');
            if (filesize($path) > 0){
                $content = fread($handle, filesize($path));
            }
            fclose($handle);
            return $content;
        } else {
            return null;
        }
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function getCloudConfig ($project_pipeline_id, $attempt, $ownerID){
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $configFileDir = "";
        $amazon_cre_id_Ar = array();
        $google_cre_id_Ar = array();
        foreach ($allinputs as $inputitem):
        $collection_id = $inputitem->{'collection_id'};
        if (!empty($collection_id)){
            $allfiles= json_decode($this->getCollectionFiles($collection_id, $ownerID));
            foreach ($allfiles as $fileData):
            $file_dir = $fileData->{'file_dir'};
            $s3_archive_dir = $fileData->{'s3_archive_dir'};
            $gs_archive_dir = $fileData->{'gs_archive_dir'};
            if (preg_match("/s3:/i",$file_dir) || preg_match("/gs:/i",$file_dir)){
                $strData = explode("\t", $file_dir);
                $cre_id = trim($strData[1]);
                if (preg_match("/s3:/i",$file_dir)){
                    if (!in_array($cre_id, $amazon_cre_id_Ar)){ $amazon_cre_id_Ar[] = $cre_id; }
                } else if (preg_match("/gs:/i",$file_dir)){
                    if (!in_array($cre_id, $google_cre_id_Ar)){ $google_cre_id_Ar[] = $cre_id; }
                }
            } 
            if (preg_match("/s3:/i",$s3_archive_dir)){
                $strData = explode("\t", $s3_archive_dir);
                $cre_id = trim($strData[1]);
                if (!in_array($cre_id, $amazon_cre_id_Ar)){ $amazon_cre_id_Ar[] = $cre_id; }
            }
            if (preg_match("/gs:/i",$gs_archive_dir)){
                $strData = explode("\t", $gs_archive_dir);
                $cre_id = trim($strData[1]);
                if (!in_array($cre_id, $google_cre_id_Ar)){ $google_cre_id_Ar[] = $cre_id; }
            }
            endforeach;
        }
        endforeach;

        foreach ($amazon_cre_id_Ar as $amazon_cre_id):
        if (!empty($amazon_cre_id)){
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach($amz_data as $d){
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
            $file = fopen($s3tmpFile, 'w');//creates new file
            fwrite($file, $confText);
            fclose($file);
            chmod($s3tmpFile, 0700);
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
    function getInitialRunImg($docker_check){
        $initialrun_img = "https://galaxyweb.umassmed.edu/pub/dolphinnext_singularity/UMMS-Biocore-initialrun-07.01.2020.simg"; 
        if ($docker_check == "true"){
            $initialrun_img = "ummsbiocore/initialrun-docker:1.0";
        }
        return $initialrun_img;
    }


    function getConfigHostnameVariable($profileId, $profileType, $ownerID) {
        $hostname ="";
        $variable ="";
        if ($profileType == 'cluster'){
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $hostname = $cluDataArr[0]["hostname"];
            $variable = $cluDataArr[0]["variable"];
        } else if ($profileType == 'amazon'){
            $cluData=$this->getProfileCloudbyID($profileId, $profileType, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $hostname = $cluDataArr[0]["shared_storage_id"];
            $variable = $cluDataArr[0]["variable"];
        }
        return array($hostname,$variable);
    }

    function getCluAmzData($profileId, $profileType, $ownerID) {
        if ($profileType == 'cluster'){
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["username"]."@".$cluDataArr[0]["hostname"];
        } else if ($profileType == 'amazon' || $profileType == 'google'){
            $cluData=$this->getProfileCloudbyID($profileId, $profileType, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["ssh"];
        }
        $ssh_port = !empty($cluDataArr[0]["port"]) ? " -p ".$cluDataArr[0]["port"] : "";
        $scp_port = !empty($cluDataArr[0]["port"]) ? " -P ".$cluDataArr[0]["port"] : "";
        return array($connect, $ssh_port, $scp_port, $cluDataArr);
    }


    function getReportDir($proPipeAll){
        $project_pipeline_id = $proPipeAll[0]->{'id'};
        $reportDir = $proPipeAll[0]->{'output_dir'}."/report".$project_pipeline_id;
        $publish_dir = isset($proPipeAll[0]->{'publish_dir'}) ? $proPipeAll[0]->{'publish_dir'} : "";
        $publish_dir_check = isset($proPipeAll[0]->{'publish_dir_check'}) ? $proPipeAll[0]->{'publish_dir_check'} : "";
        if ($publish_dir_check == "true" && !empty($publish_dir)){
            $reportDir = $publish_dir."/report".$project_pipeline_id;
        }
        $reportDir = trim($reportDir);
        return $reportDir;
    }
    //should end with && if not empty
    function getCleanReportCmd($proPipeAll){
        $cmd = "";
        $repDir = $this->getReportDir($proPipeAll);
        if (!empty($repDir)){
            if (substr($repDir,0,1) == "/") {
                $cmd = "rm -rf $repDir &&";
            } else if (preg_match("/gs:/i", $repDir)){
                $cmd = "gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $repDir 2> /dev/null || true &&";
            } else if (preg_match("/s3:/i", $repDir)){
                $cmd = "aws s3 rm $repDir --recursive 2> /dev/null || true &&";
            }
        }
        return $cmd;
    }

    function getServerRunPath($uuid){
        $run_path_real = "{$this->run_path}/$uuid/run";
        return $run_path_real;
    }

    function getDolphinPathReal($proPipeAll){
        $project_pipeline_id = $proPipeAll[0]->{'id'};
        $outdir = $proPipeAll[0]->{'output_dir'};
        $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
        $publish_dir = !empty($proPipeAll[0]->{'publish_dir'}) ? $proPipeAll[0]->{'publish_dir'} : "";
        $publish_dir_check = isset($proPipeAll[0]->{'publish_dir_check'}) ? $proPipeAll[0]->{'publish_dir_check'} : "";
        $dolphin_publish_real = "";
        if ($publish_dir_check == "true" && !empty($publish_dir)){
            $dolphin_publish_real = "$publish_dir/run{$project_pipeline_id}";
        }
        return array($dolphin_path_real,$dolphin_publish_real);
    }

    function initialRunParams($proPipeAll, $project_pipeline_id, $attempt, $profileId, $profileType, $ownerID){
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $params = "";
        $checkGeoFiles = "false";
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
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
        foreach ($allinputs as $inputitem):
        $collection_id = $inputitem->{'collection_id'};
        if (!empty($collection_id)){
            $allfiles= json_decode($this->getCollectionFiles($collection_id, $ownerID));
            foreach ($allfiles as $fileData):
            $collection[] = $collection_id;
            $file_name[] = $fileData->{'name'};
            $file_dir[] = $fileData->{'file_dir'};
            $file_type[] = $fileData->{'file_type'};
            $files_used[] = $fileData->{'files_used'};
            $archive_dir[] = $fileData->{'archive_dir'};
            $s3_archive_dir[] = $fileData->{'s3_archive_dir'};
            $gs_archive_dir[] = $fileData->{'gs_archive_dir'};
            $collection_type[] = $fileData->{'collection_type'};
            if (empty($fileData->{'file_dir'})){
                $checkGeoFiles = "true";
            }
            endforeach;
        }
        if (!empty($inputitem->{'url'}) || !empty($inputitem->{'urlzip'})){
            $given_name[] = $inputitem->{'given_name'};
            $input_name[] = $inputitem->{'name'};
            $url[] = $inputitem->{'url'};
            $urlzip[] = $inputitem->{'urlzip'};
            $checkpath[] = $inputitem->{'checkpath'};
        }
        endforeach;



        if (!empty($file_name) || !empty($url) || !empty($urlzip)) {
            $params  = "params {\n";
            $params .= "  attempt = '".$attempt."'\n";
            $params .= "  run_dir = '".$dolphin_path_real."'\n";
            $params .= "  cloud_run_dir = '".$dolphin_publish_real."'\n";
            //if $profile eq "amazon" then allow s3 backupdir download.
            $params .= "  profile = '".$profileType."'\n";
            $paramNameAr = array("given_name", "input_name", "url", "urlzip", "checkpath", "collection", "file_name", "file_dir", "file_type", "files_used", "archive_dir", "s3_archive_dir", "gs_archive_dir", "collection_type");
            $paramAr = array($given_name, $input_name, $url, $urlzip, $checkpath, $collection, $file_name, $file_dir, $file_type, $files_used, $archive_dir, $s3_archive_dir, $gs_archive_dir, $collection_type);

            for ($i=0; $i<count($paramNameAr); $i++) {
                if (!empty($paramAr[$i])){
                    $params.= "  ".$paramNameAr[$i]." = '\"".implode('","', $paramAr[$i])."\"'\n"; 
                }

            }
            $params .= "}\n";
        }
        return array($params,$checkGeoFiles);
    }

    //get nextflow input parameters
    function getMainRunInputs ($project_pipeline_id, $dolphin_path_real, $ownerID ){
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $next_inputs="";
        if (!empty($allinputs)){
            $next_inputs="params {\n";
            foreach ($allinputs as $inputitem):
            $inputName = $inputitem->{'name'};
            $collection_id = $inputitem->{'collection_id'};
            if (!empty($collection_id)){
                $inputsPath = "$dolphin_path_real/inputs/$collection_id";
                $allfiles= json_decode($this->getCollectionFiles($collection_id, $ownerID));
                $file_type = $allfiles[0]->{'file_type'};
                $collection_type = $allfiles[0]->{'collection_type'};
                if ($collection_type == "single"){
                    $inputName = "$inputsPath/*.$file_type";
                } else if ($collection_type == "pair"){
                    $inputName = "$inputsPath/*.{R1,R2}.$file_type";
                }
            }
            $next_inputs.="  ".$inputitem->{'given_name'}." = '".$inputName."'\n";
            endforeach;
            $next_inputs.="}\n";
        }
        return $next_inputs;

    }


    function getDownCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType){
        $cacheCmd = "";
        if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
            $cacheCmd = "if [ ! -d $dolphin_path_real/.nextflow ]; then mkdir -p $dolphin_path_real && gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -o GSUtil:parallel_composite_upload_threshold=150M -m cp -r $dolphin_publish_real/.nextflow $dolphin_path_real 2> /dev/null || true; fi";
        }
        return $cacheCmd;
    }

    function getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType){
        $cacheCmd = "";
        if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
            $cacheCmd = "if [ -d $dolphin_path_real/.nextflow ]; then gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $dolphin_publish_real/.nextflow 2> /dev/null || true && gsutil -o GSUtil:parallel_composite_upload_threshold=150M -m cp -r $dolphin_path_real/.nextflow $dolphin_publish_real; fi";
        }
        return $cacheCmd;
    }



    function getInitialImageCmdPath($proPipeAll, $profileType,$profileId, $ownerID){
        $initImageCmd  = "";
        $initImagePath = "";
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $singu_cache = $cluDataArr[0]["singu_cache"];

        $runType = "initial";
        $containerType = 'singularity'; //default
        if ($docker_check == "true"){
            $containerType = 'docker';
        }
        $initialrun_img= $this->getInitialRunImg($docker_check);
        list($initImageCmd,$initImagePath) = $this->imageCmd($singu_cache, $initialrun_img, "", $containerType, $profileType,$profileId, $runType, $dolphin_publish_real, $ownerID);

        return array($initImageCmd, $initImagePath);
    }

    function getImageCmdPath($proPipeAll, $profileType,$profileId, $ownerID){
        $imageCmd  = "";
        $imagePath = "";
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $singu_cache = $cluDataArr[0]["singu_cache"];

        if ($singu_check == "true"){
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
    function getNextExecParam($proPipeAll, $project_pipeline_id,$profileType,$profileId, $initialRunParams, $ownerID){
        $proPipeCmd = $proPipeAll[0]->{'cmd'};
        $jobname = html_entity_decode($proPipeAll[0]->{'pp_name'},ENT_QUOTES);
        //get dolphin paths in target location
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        list($imageCmd, $imagePath) = $this->getImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        $initImageCmd = "";
        if (!empty($initialRunParams)){
            list($initImageCmd, $initImagePath) = $this->getInitialImageCmdPath($proPipeAll, $profileType,$profileId, $ownerID);
        }
        //get report options
        $reportOptions = "";
        $withReport = $proPipeAll[0]->{'withReport'};
        $withTrace = $proPipeAll[0]->{'withTrace'};
        $withTimeline = $proPipeAll[0]->{'withTimeline'};
        $withDag = $proPipeAll[0]->{'withDag'};
        if ($withReport == "true"){
            $reportOptions .= " -with-report";
        }
        if ($withTrace == "true"){
            $reportOptions .= " -with-trace";
        }
        if ($withTimeline == "true"){
            $reportOptions .= " -with-timeline";
        }
        if ($withDag == "true"){
            $reportOptions .= " -with-dag dag.html";
        }
        return array($dolphin_path_real, $dolphin_publish_real, $proPipeCmd, $jobname, $imageCmd, $initImageCmd, $reportOptions);
    }


    //get username and hostname and exec info for connection
    function getNextConnectExec($profileId,$ownerID, $profileType){
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
        return array($connect, $next_path, $profileCmd, $executor,$next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job,$ssh_id, $ssh_port);
    }

    function getPostCmd($proPipeAll, $dolphin_path_real, $dolphin_publish_real, $profileType){
        $interdel = $proPipeAll[0]->{'interdel'};
        $interdelCmd = "";
        if ($interdel == "true"){
            if ($profileType == "google" && !empty($dolphin_publish_real) && preg_match("/gs:/i", $dolphin_publish_real)) {
                $nxf_work = "$dolphin_publish_real/work"; 
                $interdelCmd = "gcloud auth activate-service-account --key-file=\$GOOGLE_APPLICATION_CREDENTIALS && gsutil -m rm -rf $nxf_work 2> /dev/null || true";
            } else if (!empty($dolphin_path_real)) {
                $nxf_work = "$dolphin_path_real/work";
                $interdelCmd = "rm -rf $nxf_work";
            }
        }
        // ### combine post-run cmd
        // should start with && and end without &&
        $arr = array($interdelCmd);
        $postCmd="";
        for ($i=0; $i<count($arr); $i++) {
            if (!empty($arr[$i])){
                $postCmd .= " && ";
            }
            $postCmd .= $arr[$i];
        }


        // ### combine fail commands
        //copy .nextflow folder to cloud anycase
        $upCacheCmd = $this->getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType); 
        // $failCmd should start with ; and use && inbetween and end without &&
        $failarr = array($upCacheCmd);
        $failCmd="";
        for ($i=0; $i<count($failarr); $i++) {
            if (!empty($failarr[$i]) && !empty($failCmd)){
                $failCmd .= " && ";
            } else if (!empty($failarr[$i]) && empty($failCmd)){
                $failCmd .= " ; ";
            }
            $failCmd .= $failarr[$i];
        }


        return $postCmd.$failCmd;
    }

    function getPreCmd($profileType,$profileCmd,$proPipeCmd, $imageCmd, $initImageCmd, $downCacheCmd){
        $profile_def = "";
        if ($profileType == "amazon" || $profileType == "google"){
            $profile_def = "source /etc/profile && source ~/.bash_profile";
        } 
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)){
            $nextVerText = "export NXF_VER=$nextVer";
        }
        $nextANSILog = "export NXF_ANSI_LOG=false";
        // set NXF_SINGULARITY_CACHEDIR as $HOME/.dolphinnext/singularity, if it is not defined.
        $singu_cachedir = 'NXF_SINGULARITY_CACHEDIR="${NXF_SINGULARITY_CACHEDIR:-$HOME/.dolphinnext/singularity}" && export NXF_SINGULARITY_CACHEDIR=$NXF_SINGULARITY_CACHEDIR';
        // combine pre-run cmd
        // should start without && and end with &&

        $arr = array($profile_def, $nextVerText, $nextANSILog, $profileCmd, $proPipeCmd, $singu_cachedir, $imageCmd , $initImageCmd, $downCacheCmd);
        $preCmd="";
        for ($i=0; $i<count($arr); $i++) {
            if (!empty($arr[$i]) && !empty($preCmd)){
                $preCmd .= " && ";
            }
            $preCmd .= $arr[$i];
        }
        if (!empty($preCmd)){
            $preCmd .= " && ";
        }

        return $preCmd;
    }

    function getNextPathReal($next_path){
        if (!empty($next_path)){
            $next_path_real = "$next_path/nextflow";
        } else {
            $next_path_real  = "nextflow";
        }
        return $next_path_real;
    }

    function convertToHoursMins($time) {
        $format = '%d:%s';
        settype($time, 'integer');
        $hours = floor($time/60);
        $minutes = $time%60;
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($hours < 10) {
            $hours = '0' . $hours;
        }
        return sprintf($format, $hours, $minutes);
    }
    function cleanName($name, $limit){
        $name = str_replace("/","_",$name);
        $name = str_replace(" ","",$name);
        $name = str_replace("(","_",$name);
        $name = str_replace(")","_",$name);
        $name = str_replace("\'","_",$name);
        $name = str_replace("\"","_",$name);
        $name = str_replace("\\","_",$name);
        $name = str_replace("&","_",$name);
        $name = str_replace("<","_",$name);
        $name = str_replace(">","_",$name);
        $name = str_replace("-","_",$name);
        $name = substr($name, 0, $limit);
        return $name;
    }

    function getMemory($next_memory, $executor){
        $memoryText = "";
        if (!empty($next_memory)){
            if ($executor == "lsf"){
                //convert gb to mb
                settype($next_memory, 'integer');
                $next_memory = $next_memory*1000;
                $memoryText = "#BSUB -R rusage[mem=".$next_memory."]\\n";
            } else if ($executor == "sge"){
                $memoryText = "#$ -l h_vmem=".$next_memory."G\\n";
            } else if ($executor == "slurm") {
                //#SBATCH --mem 100 # memory pool for all cores default GB
                $memoryText = "#SBATCH --mem=".$next_memory."G\\n";
            }
        } 
        return $memoryText;
    }
    function getJobName($jobname, $executor){
        $jobname = $this->cleanName($jobname, 9);
        $jobNameText = "";
        if (!empty($jobname)){
            if ($executor == "lsf"){
                $jobNameText = "#BSUB -J $jobname\\n";
            } else if ($executor == "sge"){
                $jobNameText = "#$ -N $jobname\\n";
            } else if ($executor == "slurm"){
                $jobNameText = "#SBATCH --job-name=$jobname\\n";
            }
        } 
        return $jobNameText;
    }
    function getTime($next_time, $executor){
        $timeText = "";
        if (!empty($next_time)){
            if ($executor == "lsf"){
                //$next_time is in minutes convert into hours and minutes.
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#BSUB -W $next_time\\n";
            } else if ($executor == "sge"){
                //$next_time is in minutes convert into hours and minutes.
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#$ -l h_rt=$next_time:00\\n";
            } else if ($executor == "slurm"){
                //#SBATCH -t hours:minutes:seconds
                $next_time = $this->convertToHoursMins($next_time);
                $timeText = "#SBATCH -t $next_time:00\\n";
            }
        }
        return $timeText;
    }
    function getQueue($next_queue, $executor){
        $queueText = "";
        if (!empty($next_queue)){
            if ($executor == "lsf"){
                $queueText = "#BSUB -q $next_queue\\n";
            }  else if ($executor == "sge"){
                $queueText = "#$ -q $next_queue\\n";
            }  else if ($executor == "slurm"){
                //#SBATCH --partition=defq
                $queueText = "#SBATCH --partition=$next_queue\\n";
            }
        } 
        return $queueText;
    }
    function getNextCluOpt($next_clu_opt, $executor){
        $next_clu_optText = "";
        if (!empty($next_clu_opt)){
            if ($executor == "sge"){
                $next_clu_optText = "#$ $next_clu_opt\\n";
            } else if ($executor == "slurm"){
                $next_clu_optText = "#SBATCH $next_clu_opt\\n";
            }
        }
        return $next_clu_optText;
    }
    function getCPU($next_cpu, $executor){
        $cpuText = "";
        if (!empty($next_cpu)){
            if ($executor == "lsf"){
                $cpuText = "#BSUB -n $next_cpu\\n";
            } else if ($executor == "sge"){
                $cpuText = "#$ -l slots=$next_cpu\\n";
            } else if ($executor == "slurm"){
                $cpuText = "#SBATCH --nodes=$next_cpu\\n#SBATCH --ntasks=$next_cpu\\n";
            }
        }
        return $cpuText;
    }

    //get all nextflow executor text
    function getExecNextAll($proPipeAll, $executor, $dolphin_path_real, $dolphin_publish_real, $next_path_real, $next_queue, $next_cpu,$next_time,$next_memory,$jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId, $profileType, $logName, $initialRunParams, $postCmd, $preCmd, $ownerID) {
        $cleanReportCmd = "";
        if ($runType == "resumerun"){
            $runType = "-resume";
        } else {
            $runType = "";
            $cleanReportCmd = $this->getCleanReportCmd($proPipeAll);
        }
        $initialRunCmd = "";
        $igniteExec = "-process.executor ignite";
        if ($profileType == "google"){
            $igniteExec = "";
        }
        $igniteCmd = "";
        $nxf_work  = "";
        $timeouts = "-cluster.failureDetectionTimeout 100000000 -cluster.clientFailureDetectionTimeout 100000000 -cluster.tcp.socketTimeout 100000000";
        //      $timeouts = " -cluster.tcp.reconnectCount 100000000 -cluster.tcp.networkTimeout 100000000 -cluster.tcp.ackTimeout 100000000 -cluster.tcp.maxAckTimeout 100000000  -cluster.tcp.joinTimeout 100000000";
        if ($executor == "local" && $executor_job == 'ignite' ){
            $nxf_work  = "-w $dolphin_path_real/work";
            $igniteCmd = "$igniteExec $timeouts";
        }
        if ($profileType == "google"){
            $nxf_work = "-w $dolphin_publish_real/work";
        }

        if (!empty($initialRunParams)){
            $nxf_work_init = "";
            if ($executor == "local" && $executor_job == 'ignite'){
                $nxf_work_init = "-w $dolphin_path_real/initialrun/work";
            }
            if ($profileType == "google"){
                $nxf_work_init = "-w $dolphin_publish_real/initialrun/work";
            }
            $initialRunCmd = "cd $dolphin_path_real/initialrun && $next_path_real $dolphin_path_real/initialrun/nextflow.nf $nxf_work_init $igniteCmd $runType $reportOptions > $dolphin_path_real/initialrun/initial.log && ";
        }
        $mainNextCmd = "$preCmd $cleanReportCmd $initialRunCmd cd $dolphin_path_real && $next_path_real $dolphin_path_real/nextflow.nf $nxf_work $igniteCmd $runType $reportOptions > $dolphin_path_real/$logName $postCmd";

        //for lsf "bsub -q short -n 1  -W 100 -R rusage[mem=32024]";
        if ($executor == "local"){
            $exec_next_all = "$mainNextCmd ";
        } else if ($executor == "lsf"){
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            $lsfRunFile = "printf '#!/bin/bash \\n".$queueText.$jobnameText.$cpuText.$timeText.$memoryText."$mainNextCmd"."'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "bsub -e $dolphin_path_real/err.log $next_clu_opt < $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $lsfRunFile && $exec_string";
        } else if ($executor == "sge"){
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            //-j y ->Specifies whether or not the standard error stream of the job is merged into the standard output stream.
            $sgeRunFile= "printf '#!/bin/bash \\n#$ -j y\\n#$ -V\\n#$ -notify\\n#$ -wd $dolphin_path_real\\n#$ -o $dolphin_path_real/.dolphinnext.log\\n".$jobnameText.$memoryText.$timeText.$queueText.$clu_optText.$cpuText."$mainNextCmd"."'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "qsub -e $dolphin_path_real/err.log $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $sgeRunFile && $exec_string";
        } else if ($executor == "slurm"){
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
            $errText = "#SBATCH -e $dolphin_path_real/err.log\\n";
            $outText = "#SBATCH -o $dolphin_path_real/.dolphinnext.log\\n";
            $runFile= "printf '#!/bin/bash \\n".$outText.$errText.$jobnameText.$memoryText.$timeText.$queueText.$clu_optText.$cpuText."$mainNextCmd"."'> $dolphin_path_real/.dolphinnext.run";
            $exec_string = "sbatch $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $runFile && $exec_string";
        } 
        return $exec_next_all;
    }

    function getContainerRunOpt($proPipeAll,$profileType, $profileId, $runType, $ownerID){
        $configText = "";
        $docker_opt = $proPipeAll[0]->{'docker_opt'};
        $singu_opt = $proPipeAll[0]->{'singu_opt'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $docker_check = $proPipeAll[0]->{'docker_check'};

        if ($docker_check == "true") {
            if (!empty($docker_opt)) {
                $configText .= "docker.runOptions = '".$docker_opt."'\n";
            }
        }
        if ($singu_check == "true") {
            if (!empty($singu_opt)) {
                $configText .= "singularity.runOptions = '".$singu_opt."'\n";
            }
        }
        return $configText;
    }

    //$runType == "main" or "initial"
    function getContainerConfig($proPipeAll,$profileType, $profileId, $runType, $ownerID){
        $configText = "";
        $docker_check = $proPipeAll[0]->{'docker_check'};
        $docker_img = $proPipeAll[0]->{'docker_img'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        $singu_img = $proPipeAll[0]->{'singu_img'};
        if ($runType == "main"){
            list($imageCmd, $imagePath) = $this->getImageCmdPath($proPipeAll, $profileType, $profileId, $ownerID);
        } else if ($runType == "initial"){
            list($imageCmd, $imagePath) = $this->getInitialImageCmdPath($proPipeAll, $profileType,$profileId, $ownerID);
        }
        //escape $ in the nexflow config
        $imagePath = str_replace('$','//$',$imagePath); 

        if ($docker_check == "true") {
            $configText .= "process.container = '".$imagePath."'\n";
            $configText .= "docker.enabled = true\n";
        } else if ($singu_check == "true") {
            $configText .= "process.container = '".$imagePath."'\n";
            $configText .= "singularity.enabled = true\n";
        }
        return $configText;
    }

    function getMainRunConfig($proPipeAll, $configText,$project_pipeline_id, $profileId, $profileType, $proVarObj, $ownerID){
        $containerConfig = $this->getContainerConfig($proPipeAll, $profileType, $profileId, "main", $ownerID);
        $containerRunOpt = $this->getContainerRunOpt($proPipeAll, $profileType, $profileId, "main", $ownerID);

        $configText = "// Process Config:\n\n{$containerConfig}{$containerRunOpt}{$configText}";
        // get outputdir
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        $pipeline_id = $proPipeAll[0]->{'pipeline_id'};
        if ($profileType == "google" && !empty($dolphin_publish_real)){
            $dolphin_path_real = $dolphin_publish_real;
        }

        //get nextflow.config from pipeline.
        $pipe = $this->loadPipeline($pipeline_id,$ownerID);
        $pipe_obj = json_decode($pipe,true);
        $script_pipe_configRaw = isset($pipe_obj[0]["script_pipe_config"]) ? $pipe_obj[0]["script_pipe_config"] : "";
        $script_pipe_config = htmlspecialchars_decode($script_pipe_configRaw , ENT_QUOTES);
        $configText .= "\n// Pipeline Config:\n\n";
        list($hostVar,$variableRaw) = $this->getConfigHostnameVariable($profileId, $profileType, $ownerID);
        $variable = htmlspecialchars_decode($variableRaw , ENT_QUOTES);

        $configText .= "\$HOSTNAME='".$hostVar."'\n";
        $configText .= "$variable\n";
        $configText .= "$script_pipe_config\n";
        //get main run input parameters
        $mainRunParams = $this->getMainRunInputs($project_pipeline_id, $dolphin_path_real, $ownerID);
        $configText .= "\n// Run Parameters:\n\n".$mainRunParams;
        //get main run local variable parameters:
        $configText = $this->getProcessParams($proVarObj, $configText);
        return $configText;
    }

    function getProcessParams($proVarObj, $configText){
        $checkVarObj = (array)$proVarObj;
        if (!empty($checkVarObj)) {
            $configText .= "\n\n// Process Parameters:\n";
            foreach ($proVarObj as $processName => $varObj):
            $configText .= "\n// Process Parameters for $processName:\n";
            foreach ($varObj as $varname => $line):
            $configText .= "$line\n";
            endforeach;
            endforeach;
        }
        return $configText;
    }

    function getInitialRunConfig($proPipeAll, $project_pipeline_id, $attempt, $profileType,$profileId, $docker_check, $initRunOptions, $ownerID){
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $containerConfig = $this->getContainerConfig($proPipeAll, $profileType, $profileId, "initial", $ownerID);
        $configText = "// Process Config:\n\n{$containerConfig}{$initRunOptions}\n";

        //get initial run input paramaters
        list($initialRunParams,$checkGeoFiles) = $this->initialRunParams($proPipeAll, $project_pipeline_id, $attempt, $profileId, $profileType, $ownerID);
        if (isset($checkGeoFiles)){
            $executor = $cluDataArr[0]['executor'];
            $executor_job = $cluDataArr[0]['executor_job'];
            if ($checkGeoFiles == "true" && (($executor == "local" && $executor_job == "local") || $profileType == "amazon")){
                $configText .= "\n//parallel download limit for GEO files on local executor:\n";
                $configText .= "executor.queueSize = 4 \n";
            } 
        }
        $configText .= "\n//Initial Run Parameters\n".$initialRunParams;
        return array($configText,$initialRunParams);
    }

    function createUUIDCmd($dolphin_path_real,$uuid){
        $uuidCmd = "";
        if (!empty($dolphin_path_real)){
            $uuidCmd = "mkdir -p $dolphin_path_real/.dolphinnext/uuid && touch $dolphin_path_real/.dolphinnext/uuid/$uuid &&";
        }
        return $uuidCmd;

    }

    function getRenameCmd($dolphin_path_real,$attempt){
        $renameLog = "";
        $pathArr = array($dolphin_path_real, "$dolphin_path_real/initialrun");
        foreach ($pathArr as $path):
        if ($path == $dolphin_path_real){
            $renameArr= array("log.txt", "timeline.html", "trace.txt", "dag.html", "report.html", ".nextflow.log", "err.log");
        } else {
            $renameArr= array("initial.log", "timeline.html", "trace.txt", "dag.html", "report.html", ".nextflow.log", "err.log");
        }
        foreach ($renameArr as $item):
        if ($item == "log.txt" || $item == "initial.log"){
            $renameLog .= "cp $path/$item $path/$item.$attempt 2>/dev/null || true && >$path/$item && ";
        } else {
            $renameLog .= "mv $path/$item $path/$item.$attempt 2>/dev/null || true && ";
        }
        endforeach;
        endforeach;
        return $renameLog;
    }

    function tarGzDirectory($dir,$targz_file){
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

    function zipDirectory($dir,$zip_file){
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
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
        if (!$zip->status == ZIPARCHIVE::ER_OK){
            $ret = "Failed to write files to zip\n";
        }
        return $ret;
    }

    function execute_cmd($cmd, $logObj, $log_name, $cmd_name){
        $log = shell_exec($cmd);
        $logObj[$cmd_name]=$cmd;
        $logObj[$log_name]=$log;
        return $logObj;
    }

    function execute_cmd_logfile($cmd, $logObj, $log_name, $cmd_name, $logfile, $mode){
        $log = shell_exec($cmd);
        $logObj[$cmd_name]=$cmd;
        $logObj[$log_name]=$log;
        $file = fopen($logfile, $mode);
        fwrite($file, $cmd."\n".$log);
        fclose($file);
        return $logObj;
    }

    function updatePipelineGithub($pipeline_id, $username, $repo, $branch, $commit, $ownerID){
        $obj=array();
        $obj["username"]=$username;
        $obj["repository"]=$repo;
        $obj["branch"]=$branch;
        $obj["commit"]=$commit;
        $github = json_encode($obj);
        $sql = "UPDATE biocorepipe_save SET github='$github', last_modified_user ='$ownerID', date_modified=now() WHERE deleted = 0 AND id = '$pipeline_id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }

    function checkNewRelease($version, $ownerID){
        $ret = array();
        $newVer = "false";
        if (!empty($ownerID)){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $release_cmd = "curl -s https://api.github.com/repos/umms-biocore/dolphinnext/releases/latest 2>&1";
                $ret = $this->execute_cmd($release_cmd, $ret, "release_cmd_log", "release_cmd");
                $rel = json_decode($ret["release_cmd_log"]);
                if (!empty($rel)) {
                    $obj=array();
                    if (isset($rel->tag_name)){ 
                        $obj["tag_name"]=$rel->tag_name; 
                        //new version check
                        if (version_compare($obj["tag_name"], $version) > 0) {
                            $newVer = "true";
                            if (isset($rel->html_url)){ $obj["html_url"]=$rel->html_url; }
                            if (isset($rel->published_at)){ $obj["published_at"]=$rel->published_at; }
                            if (isset($rel->body)){ $obj["body"]=$rel->body; }
                            $ret["release_cmd_log"] = $obj;
                            $scriptsPath = realpath(__DIR__."/../../scripts");
                            $ret["scripts_path"] = $scriptsPath;
                        } 
                    }
                }
                if ($newVer == "false"){
                    $ret["release_cmd_log"] = "";
                }
            }
        }
        return json_encode($ret);
    }



    //$type: "downPack" or "pushGithub"
    function initGitRepo ($description, $pipeline_id, $pipeline_name, $username_id, $github_repo, $github_branch, $configText, $nfData, $dnData, $type, $ownerID){
        $ret = array();
        $dir_name = !empty($username_id) ? "{$github_repo}_{$github_branch}" : $pipeline_name;
        //create git folder
        $gitDir= "{$this->tmp_path}/git/$ownerID";
        $repoDir= "{$this->tmp_path}/git/$ownerID/$dir_name";
        $zip_file= "{$this->tmp_path}/git/$ownerID/$dir_name.zip";
        $zip_file_public= "{$this->base_path}/tmp/git/$ownerID/$dir_name.zip";
        if (!file_exists($gitDir)) {
            mkdir($gitDir, 0755, true);
        }
        system('rm -f ' . escapeshellarg("$zip_file"), $retval);
        //create empty git repo folder
        if (!file_exists($repoDir)) {
            mkdir($repoDir, 0755, true);
        } else{
            system('rm -rf ' . escapeshellarg("$repoDir"), $retval);
            if ($retval == 0){
                $ret["clean_repo_dir_log"]=$repoDir." successfully deleted";
                mkdir($repoDir, 0755, true);
            } else {
                $ret["clean_repo_dir_log"]=$repoDir." could not deleted->$retval";
                return $ret;
            }
        }

        if ($type == "pushGithub"){
            $git_data = json_decode($this->getGithubbyID($username_id, $ownerID));
            $password = trim($this->amazonDecode($git_data[0]->password));
            $username= $git_data[0]->username;
            $email= $git_data[0]->email;
            $check_repo_cmd = "curl https://api.github.com/repos/$username/$github_repo 2>&1";
            $ret = $this->execute_cmd($check_repo_cmd, $ret, "check_repo_cmd_log", "check_repo_cmd");
            $repo_found = "";
            if (preg_match('/"message": "Not Found"/',$ret["check_repo_cmd_log"])){
                $repo_found = "false";
            }
            $git_init_cmd = "";
            if ($repo_found == "false"){
                //repo not found, create with curl
                $init_cmd = "curl -u '$username:$password' https://api.github.com/user/repos -d '{\"name\":\"$github_repo\"}' && cd $repoDir && git init 2>&1";
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
            $this->createMultiConfig ($repoDir, $configText);
            $this->createDirFile ($repoDir, "main.nf", 'w', $nfData);
            $this->createDirFile ($repoDir, "main.dn", 'w', $dnData);
            $this->createDirFile ($repoDir, "README.md", 'w', $description);
            $date = date("d-m-Y H:i:s", time());

            //push to github
            $push_cmd = "cd $repoDir && git config --local user.name \"$username\" && git config --local user.email \"$email\" && git add . && git commit -m \"$date\" && git  push --porcelain https://{$username}:{$password}@github.com/$username/{$github_repo}.git $github_branch 2>&1";
            $ret = $this->execute_cmd($push_cmd, $ret, "push_cmd_log", "push_cmd");
            //parse commit_id
            //[master 407d677] 01-08-2019 21:08:45
            if (preg_match('/Done/',$ret["push_cmd_log"])){
                if (preg_match("/\[$github_branch(.*)\] $date/",$ret["push_cmd_log"])){
                    preg_match("/\[$github_branch(.*)\] $date/",$ret["push_cmd_log"], $match);
                    $block = explode(" ", trim($match[1]));
                    $part_of_commit_id = end($block);
                    if (!empty($part_of_commit_id)){
                        $get_commit_id_cmd = "cd $repoDir && git config --local user.name \"$username\" && git config --local user.email \"$email\" && git log -1 $part_of_commit_id | head -1 2>&1";
                        $ret = $this->execute_cmd($get_commit_id_cmd, $ret, "get_commit_id_cmd_log", "get_commit_id_cmd");
                        preg_match("/commit(.*)/",$ret["get_commit_id_cmd_log"], $commit_log);
                        if (!empty($commit_log[1])){
                            $commit_id=$commit_log[1];
                            $ret["commit_id"]=trim($commit_id);
                            $this->updatePipelineGithub ($pipeline_id, $username, $github_repo, $github_branch, trim($commit_id), $ownerID);
                        }
                    }
                }
            }
        }
        if ($type == "downPack"){
            $this->createMultiConfig ($repoDir, $configText);
            $this->createDirFile ($repoDir, "main.nf", 'w', $nfData);
            $this->createDirFile ($repoDir, "main.dn", 'w', $dnData);
            $this->createDirFile ($repoDir, "README.md", 'w', $description);
            $ret["zip_log"] = $this->zipDirectory($repoDir,$zip_file);
            $ret["zip_file"]= $zip_file_public;
        }
        system('rm -rf ' . escapeshellarg("$repoDir"), $retval);
        foreach($ret as $key => $val){
            if (!empty($password)){
                $valClean = str_replace($password,"****",$val);  
                $ret[$key] = $valClean;
            }
        }
        return json_encode($ret);
    }

    function getAmazonConfig($amazon_cre_id,$ownerID){
        $configText = "";
        if ($amazon_cre_id != "" ){
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach($amz_data as $d){
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

    //nextflow config tag and label separated: \n//~@:~\n@~:"filename"\n//~@:~\ntext
    //Use createMultiConfig function to parse and save into run folder
    function createMultiConfig($dir, $allConf){
        //if empty or null, then show as empty nextflow.config
        $filename = "nextflow.config";
        $this->createDirFile ($dir, $filename, "w", "");
        if (!empty($allConf)){
            $sep    = "\n//~@:~\n";
            $lines = explode($sep, $allConf);
            $filename = "";
            $checkLabel = "false";
            for ($i = 0; $i < count($lines); $i++) {
                if (preg_match("/@~:\"(.*)\"/",$lines[$i])){
                    //initiate sub config
                    preg_match("/@~:\"(.*)\"/",$lines[$i], $match);
                    if (!empty($match[1]) && isset($lines[$i+1])){
                        $publishDir = $dir."/".$match[1];
                        $block = explode("/", $publishDir);
                        $filename = end($block);
                        //remove last item and join with "/"
                        array_pop($block);
                        $publishDir = join("/",$block);
                        $writeType = "w";
                        if ($filename == "nextflow.config"){
                            $writeType = "a";
                        }
                        $this->createDirFile ($publishDir, $filename, $writeType, $lines[$i+1]); //empty file
                        $checkLabel = "true";
                        continue;
                    }
                } else {
                    //getMainRunConfig function might append or prepend nextflow.config text.
                    if ($i == 0 || $i == count($lines)-1 ){
                        $filename = "nextflow.config";
                        $this->createDirFile ($dir, $filename, "a", $lines[$i]);
                    }
                }
            }
            //if header info is not found, then show as nextflow.config
            if ($checkLabel == "false"){
                $filename = "nextflow.config";
                $this->createDirFile ($dir, $filename, "w", $allConf); 
            }
        }
    }

    function recurse_copy($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 

    function triggerRunErr($message, $uuid,$project_pipeline_id,$ownerID){
        $this->writeLog($uuid,$message,'a','serverlog.txt');
        $this->updateRunLog($project_pipeline_id, "Error", "", $ownerID);
        $this->updateRunStatus($project_pipeline_id, "Error", $ownerID);
        die(json_encode($message));
    }
    function createReadmeMD($uuid){
        $this -> createDirFile ("{$this->run_path}/$uuid/pubweb/_Description", "README.md", 'w', "#### **Run Description**\n\nYou can use this space for adding notes about your run such as its aims, experimental context, and any other ideas that you’d like to share with your group members. We support <a style=\"color:#1479cc;\" href=\"https://guides.github.com/features/mastering-markdown/\" target=\"_blank\">Markdown</a> for styling and formatting your notes.\n\nTo start editing this text, click **Edit Markdown** <i style=\"font-size: 14px;\" class=\"fa fa-pencil-square-o\"></i> icon on the right.\n\nIf you need to upload other files, please click the **Add File** <i style=\"font-size: 14px;\" class=\"fa fa-plus\"></i> icon on the left.");
    }

    function initRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $amzConfigText, $attempt, $runType, $ownerID){
        //create files and folders
        $this -> createDirFile ("{$this->run_path}/$uuid/run", "nextflow.nf", 'w', $nextText );
        //create Run Description
        $this -> createReadmeMD($uuid);
        //separate nextflow config (by using @config tag).
        $this -> createMultiConfig ("{$this->run_path}/$uuid/run", $mainConfigText);
        //create clean serverlog.txt 
        $this -> writeLog($uuid,'','w','serverlog.txt');
        $run_path_real = "{$this->run_path}/$uuid/run";
        if (!empty($initialRunParams)){
            $this->createDirFile ("{$this->run_path}/$uuid/run/initialrun", "nextflow.config", 'w', $initialConfigText );
            copy("{$this->nf_path}/initialrun.nf", "{$this->run_path}/$uuid/run/initialrun/nextflow.nf");
        }
        if (!file_exists($run_path_real."/nextflow.nf")) {
            $this->triggerRunErr('ERROR: Nextflow file is not found in server!', $uuid,$project_pipeline_id,$ownerID);
        }
        if (!file_exists($run_path_real."/nextflow.config")) {
            $this->triggerRunErr('ERROR: Nextflow config file is not found!', $uuid,$project_pipeline_id,$ownerID);
        }

        //get nextflow executor parameters
        list($dolphin_path_real, $dolphin_publish_real, $proPipeCmd, $jobname, $imageCmd, $initImageCmd, $reportOptions) = $this->getNextExecParam($proPipeAll, $project_pipeline_id,$profileType, $profileId, $initialRunParams, $ownerID);

        //get username and hostname and exec info for connection
        list($connect, $next_path, $profileCmd, $executor, $next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job, $ssh_id, $ssh_port)=$this->getNextConnectExec($profileId,$ownerID, $profileType);
        //get cmd before run
        $downCacheCmd = $this->getDownCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType); 
        $preCmd = $this->getPreCmd($profileType,$profileCmd,$proPipeCmd, $imageCmd, $initImageCmd, $downCacheCmd); 
        $next_path_real = $this->getNextPathReal($next_path); //eg. /project/umw_biocore/bin
        $postCmd = $this->getPostCmd($proPipeAll, $dolphin_path_real, $dolphin_publish_real, $profileType); 


        //get command for renaming previous log file
        $renameLog = $this->getRenameCmd($dolphin_path_real, $attempt);
        $createUUID = $this->createUUIDCmd($dolphin_path_real, $uuid);
        $exec_next_all = $this->getExecNextAll($proPipeAll, $executor, $dolphin_path_real, $dolphin_publish_real, $next_path_real, $next_queue,$next_cpu,$next_time,$next_memory, $jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId, $profileType, "log.txt", $initialRunParams, $postCmd, $preCmd, $ownerID);
        $amzCmd = "";
        //temporarily copy s3/gs config file into initialrun folder 
        if (!empty($getCloudConfigFileDir)){
            $this->recurse_copy($getCloudConfigFileDir, $run_path_real."/initialrun");
        }
        //.aws_cred file to export credentials to remote machine
        if (!empty($amzConfigText)){
            $this->createDirFile ($run_path_real, ".aws_cred", 'w', $amzConfigText );
            $amzCmd = "source $dolphin_path_real/.aws_cred && rm $dolphin_path_real/.aws_cred && ";
        }
        //create run cmd file (.dolphinnext.init)
        $runCmdAll = "$amzCmd $renameLog $createUUID $exec_next_all";
        $this->createDirFile ($run_path_real, ".dolphinnext.init", 'w', $runCmdAll);

        // compress run folder
        $targz_file= $run_path_real.".tar.gz";
        $this->tarGzDirectory($run_path_real,$targz_file);
        // remove credentials from run folder after compressing run folder
        if (file_exists($run_path_real."/initialrun")) {
            system('rm -rf ' . escapeshellarg($run_path_real."/initialrun") . "/.conf*", $retval);
        }
        // remove .aws_cred file after compressing run folder
        if (file_exists($run_path_real."/.aws_cred")) {
            unlink($run_path_real."/.aws_cred");
        }
        return array($targz_file, $dolphin_path_real, $runCmdAll);
    }

    function runCmd($project_pipeline_id, $profileType, $profileId, $uuid, $targz_file, $dolphin_path_real, $runCmdAll, $ownerID){
        $ret = array();
        // get scp port
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
        if (!file_exists($userpky)) {
            $this->triggerRunErr('ERROR: Private key is not found!', $uuid,$project_pipeline_id,$ownerID);
        }
        $run_path_real = $this->getServerRunPath($uuid);
        // 1. Mkdir $dolphin_path_real
        $mkdir_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"mkdir -p $dolphin_path_real && echo 'INFO: Run directory created.'\" 2>&1";
        $ret = $this->execute_cmd_logfile($mkdir_cmd, $ret, "mkdir_cmd_log", "mkdir_cmd", "$run_path_real/serverlog.txt", "a");
        if (!preg_match("/INFO: Run directory created\./", $ret["mkdir_cmd_log"])){
            $this->triggerRunErr('ERROR: Run directory cannot be created.\nLOG: '.$ret["mkdir_cmd_log"], $uuid,$project_pipeline_id,$ownerID);
        }
        // 2. rsync $targz_file
        $rsync_cmd = "rsync -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $targz_file $connect:$dolphin_path_real  2>&1";
        $ret = $this->execute_cmd_logfile($rsync_cmd, $ret, "scp_cmd_log", "scp_cmd", "$run_path_real/serverlog.txt", "a");
        // 3. remove local $targz_file after transfer (if this command couldn't executed, cronjob will remove it->cleanTempDir)
        if (file_exists($targz_file)) {
            unlink($targz_file);
        }
        // 4. check $targz_file
        $package_exist_cmd= "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \" test  -f '$dolphin_path_real/run.tar.gz' && echo 'INFO: Run package exists.'\" 2>&1";
        $ret = $this->execute_cmd_logfile($package_exist_cmd, $ret, "package_exist_cmd_log", "package_exist_cmd", "$run_path_real/serverlog.txt", "a");
        if (!preg_match("/INFO: Run package exists\./", $ret["package_exist_cmd_log"])){
            $this->triggerRunErr('ERROR: Run directory cannot be transfered.\nLOG: '.$ret["package_exist_cmd_log"], $uuid,$project_pipeline_id,$ownerID);
        }
        // 4. extract and execute 
        $exec_cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"source /etc/profile && tar xf $dolphin_path_real/run.tar.gz -C $dolphin_path_real && rm $dolphin_path_real/run.tar.gz && bash $dolphin_path_real/.dolphinnext.init\" >> $run_path_real/serverlog.txt 2>&1 & echo $! &";
        $this->writeLog($uuid,$exec_cmd,'a','serverlog.txt');
        $this->writeLog($uuid,$runCmdAll,'a','serverlog.txt');
        $next_submit_pid= shell_exec($exec_cmd); //"Job <203477> is submitted to queue <long>.\n"
        if (!$next_submit_pid) {
            $this->triggerRunErr('ERROR: Connection failed! Please check your connection profile or internet connection.', $uuid,$project_pipeline_id,$ownerID);
        }
        $ret['next_submit_pid'] = $next_submit_pid;
        $this->updateRunLog($project_pipeline_id, "Waiting", "", $ownerID);
        $this->updateRunStatus($project_pipeline_id, "Waiting", $ownerID);
        return json_encode($ret);
    }

    function getManualRunCmd($targz_file, $uuid, $dolphin_path_real){
        $ret = array();
        if (!empty($targz_file)){
            $targz_file_public= "{$this->base_path}/tmp/pub/$uuid/run.tar.gz";
            $ret["manualRunCmd"] = "mkdir -p $dolphin_path_real && cd $dolphin_path_real && rm -f run.tar.gz && wget $targz_file_public && tar xf run.tar.gz && rm run.tar.gz && bash .dolphinnext.init";
            $this->writeLog($uuid,"RUN COMMAND:\n".$ret["manualRunCmd"],'a','serverlog.txt');
        }
        return json_encode($ret);
    }

    public function updateRunAttemptLog($status, $project_pipeline_id, $uuid, $ownerID){
        //check if $project_pipeline_id already exits un run table
        $checkRun = $this->getRun($project_pipeline_id,$ownerID);
        $checkarray = json_decode($checkRun,true);
        $attempt = isset($checkarray[0]["attempt"]) ? $checkarray[0]["attempt"] : "";
        settype($attempt, 'integer');
        if (empty($attempt)){
            $attempt = 0;
        }
        $attempt += 1;
        if (isset($checkarray[0])) {
            $this->updateRunAttempt($project_pipeline_id, $attempt, $ownerID);
            $this->updateRunStatus($project_pipeline_id, $status, $ownerID);
        } else {
            $this->insertRun($project_pipeline_id, $status, "1", $ownerID);
        }
        $data = $this->insertRunLog($project_pipeline_id, $uuid, $status, $ownerID);
    }

    function parseKeyLine($txt, $key){
        $txt = trim($txt);
        $lines = explode("\n", $txt);
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match("/$key/i",$lines[$i])){
                return $lines[$i];
            }
        }
        return "";
    }

    function validateSSH($connect, $ssh_id, $ssh_port, $type, $cmd, $path, $ownerID) {
        $ret = array();
        if (!empty($cmd)){
            $cmd = $cmd." && ";
        }
        $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
        $ssh_port = !empty($ssh_port) ? " -p ".$ssh_port : "";
        $subcmd = "";
        $precmd = "source /etc/profile "; 
        if ($type == "ssh"){
            $subcmd = "$precmd && ls ";
        } else if ($type == "java"){
            $subcmd = "$precmd && which java ";
        } else if ($type == "nextflow"){
            if (!empty($path)){
                $subcmd = "$precmd && which $path/nextflow ";
            } else {
                $subcmd = "$precmd && which nextflow ";
            }
        } else if ($type == "docker"){
            $subcmd = "$precmd && docker --version ";
        } else if ($type == "singularity"){
            $subcmd = "$precmd && singularity --version ";
        } 
        $runcmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$cmd $subcmd && echo 'query_validated'\" 2>&1 &";
        $ret = $this->execute_cmd($runcmd, $ret, "cmd_log", "cmd");
        $ret["validation"] = "success";
        $ret["version"] = "";
        if (empty($ret["cmd_log"])){
            $ret["validation"] = "failed";
        } else if (!preg_match("/query_validated/i", $ret["cmd_log"])){
            $ret["validation"] = "failed";
        } else if (preg_match("/command not found/i", $ret["cmd_log"])){
            $ret["validation"] = "failed";
        } else {
            if ($type == "java" || $type == "nextflow" || $type == "docker" || $type == "singularity"){
                $ret["version"] = $this->parseKeyLine($ret["cmd_log"], "version");
            } 
        }

        return json_encode($ret);
    }

    function generateKeys($ownerID) {
        $cmd = "rm -rf {$this->ssh_path}/.tmp$ownerID && mkdir -p {$this->ssh_path}/.tmp$ownerID && cd {$this->ssh_path}/.tmp$ownerID && ssh-keygen -C @dolphinnext -f tkey -t rsa -N '' > logTemp.txt";
        $resText = shell_exec("$cmd");
        $keyPubPath ="{$this->ssh_path}/.tmp$ownerID/tkey.pub";
        $keyPriPath ="{$this->ssh_path}/.tmp$ownerID/tkey";
        $keyPub = $this->readFile($keyPubPath);
        $keyPri = $this->readFile($keyPriPath);
        $log_array = array('$keyPub' => $keyPub);
        $log_array['$keyPri'] = $keyPri;
        //remove the directory after reading files.
        $cmd = "rm -rf {$this->ssh_path}/.tmp$ownerID 2>&1 & echo $! &";
        $log_remove = $this->runCommand ($cmd, 'remove_key', '');
        return json_encode($log_array);
    }

    function insertGoogKey($id, $key_name, $ownerID){
        $suffix = "goog.json";
        $targetFile = "{$this->goog_path}/{$ownerID}_{$id}_{$suffix}";
        $tmpFile = "{$this->goog_path}/uploads/{$ownerID}/{$ownerID}_tmpkey";
        if (empty($key_name)){
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

    function insertKey($id, $key, $type, $ownerID){
        if (!file_exists("{$this->ssh_path}")) {
            mkdir("{$this->ssh_path}", 0700, true);
        }
        if ($type == 'clu'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}.pky", 'w');//new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}.pky", 0600);
        } else if ($type == 'amz_pri'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w'); //new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'amz_pub'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w');//creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'ssh_pub'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w');//creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        } else if ($type == 'ssh_pri'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w');//creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 0600);
        }
    }
    function readKey($id, $type, $ownerID)
    {
        if ($type == 'clu'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}.pky";
        } else if ($type == 'amz_pub' || $type == 'amz_pri'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        } else if ($type == 'ssh_pub' || $type == 'ssh_pri'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        }
        $handle = fopen($filename, 'r');//creates new file
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return $content;
    }
    function delKey($id, $type, $ownerID){
        if ($type == 'clu'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}.pky";
        } else if ($type == 'amz_pub' || $type == 'amz_pri'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        } else if ($type == 'ssh_pri' || $type == 'ssh_pub'){
            $filename = "{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky";
        }
        unlink($filename);
    }

    function amazonEncode($a_key){
        $encrypted_string=openssl_encrypt($a_key,"AES-128-ECB",$this->amazon);
        return $encrypted_string;
    }
    function amazonDecode($a_key){
        $decrypted_string=openssl_decrypt($a_key,"AES-128-ECB",$this->amazon);
        return $decrypted_string;
    }
    function keyAsterisk($key){
        if (strlen($key) >3){
            $key=str_repeat('*', strlen($key) - 4) . substr($key, -4);
        } 
        return $key;
    }
    function getCloudName($profileName, $cloud){
        $profileName = str_replace("_","",$profileName); 
        $cloudName = "$profileName{$cloud}";
        return $cloudName;
    }

    function startProCloud($id, $cloud, $ownerID, $username){
        $text = "";
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
        if ($cloud == "amazon"){
            $main_path = $this->amz_path;
            $amazon_cre_id = $data[0]->{'amazon_cre_id'};
            $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
            foreach($amz_data as $d){
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


            $text.= "aws{\n";
            $text.= "   accessKey = '$access_key'\n";
            $text.= "   secretKey = '$secret_key'\n";
            $text.= "   region = '$default_region'\n";
            $text.= "}\n";

        } else if ($cloud == "google"){
            $main_path = $this->goog_path;
            $google_cre_id = $data[0]->{'google_cre_id'};
            $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
            $project_id = $goog_data[0]->{'project_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";

            $zone = $data[0]->{'zone'};
            $text.= "google{\n";
            $text.= "   project = '$project_id'\n";
            $text.= "   zone = '$zone'\n";
            $text.= "}\n";
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
        $text.= "cloud { \n";
        if (!empty($username)){ $text.= "   userName = '$username'\n"; }
        if (!empty($image_id)){ $text.= "   imageId = '$image_id'\n"; }
        if (!empty($instance_type)){ $text.= "   instanceType = '$instance_type'\n"; }
        if ($cloud == "google"){ $text.= "   driver = 'google'\n"; }
        if (!empty($security_group) && ($cloud == "amazon")){ $text.= "   securityGroup = '$security_group'\n"; }
        if (!empty($subnet_id) && ($cloud == "amazon")){ $text.= "   subnetId = '$subnet_id'\n"; }
        if (!empty($shared_storage_id) && ($cloud == "amazon")){ $text.= "   sharedStorageId = '$shared_storage_id'\n"; }
        if (!empty($shared_storage_mnt) && ($cloud == "amazon")){ $text.= "   sharedStorageMount = '$shared_storage_mnt'\n"; }
        $text.= "   keyFile = '$keyFile'\n";
        if ($autoscale_check == "true"){
            $text.= "   autoscale {\n";
            $text.= "       enabled = true \n";
            $text.= "       terminateWhenIdle = true\n";
            if (!empty($autoscale_maxIns)){
                $text.= "       maxInstances = $autoscale_maxIns\n";
            }
            $text.= "   }\n";
        }
        $text.= "}\n";

        $this->createDirFile ("{$main_path}/pro_{$profileName}", "nextflow.config", 'w', $text );
        $nodeText = "";
        if ($nodes >1){
            $nodeText = "-c $nodes";
        }
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)){
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google"){
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }

        //start cloud cluster
        $cmd = "cd {$main_path}/pro_{$profileName} && $nextVerText $nextModeText yes | nextflow cloud create $cloudName $nodeText > logStart.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'start_cloud', '');
        $log_array['start_cloud_cmd'] = $cmd;
        //xxx save pid of nextflow cloud create cluster job
        if (preg_match("/([0-9]+)(.*)/", $log_array['start_cloud'])){
            $this->updateCloudProStatus($id, "waiting", $cloud, $ownerID);
        }else {
            $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
        }
        return json_encode($log_array);
    }

    function stopProCloud($id,$ownerID,$username, $cloud){
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        if ($cloud == "amazon"){
            $main_path = $this->amz_path;
        } else if ($cloud == "google"){
            $main_path = $this->goog_path;
        }
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)){
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google"){
            $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
            $google_cre_id = $data[0]->{'google_cre_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }
        //stop cluster
        $cmd = "cd {$main_path}/pro_{$profileName} && $nextVerText $nextModeText yes | nextflow cloud shutdown $cloudName > logStop.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'stop_cloud', '');
        return json_encode($log_array);
    }
    function triggerShutdown ($id, $cloud, $ownerID, $type){
        $cloudDataJS=$this->getProfileCloudbyID($id, $cloud, $ownerID);
        $cloudData=json_decode($cloudDataJS,true)[0];
        $username = $cloudData["username"];
        if (!empty($username)){
            $usernameCl = str_replace(".","__",$username); 
        }
        $autoshutdown_date = $cloudData["autoshutdown_date"];
        $autoshutdown_active = $cloudData["autoshutdown_active"];
        $autoshutdown_check = $cloudData["autoshutdown_check"];
        // get list of active runs using this profile 
        $activeRun=json_decode($this->getActiveRunbyProID($id, $cloud, $ownerID),true);
        if (count($activeRun) > 0){ return "Active run is found"; }
        //if process comes to this checkpoint it has to be activated
        if ($autoshutdown_check == "true" && $autoshutdown_active == "true"){
            error_log("Active run not found->checking autoshutdown.");
            //if timer not set then set timer
            if (empty($autoshutdown_date)){
                $autoshutdown_date = strtotime("+10 minutes");
                $mysqltime = date ("Y-m-d H:i:s", $autoshutdown_date);
                $this->updateCloudShutdownDate($id, $mysqltime, $cloud, $ownerID);
                return "Timer set to: $mysqltime";
            } else {
                //if timer is set then check if time elapsed -> stopProCloud
                $expected_date = strtotime($autoshutdown_date);
                $remaining = $expected_date - time();
                error_log("expected_date:".$expected_date);
                error_log("time:".time());
                error_log("remaining:".$remaining);
                if ($remaining < 1){
                    error_log("autoshutdown triggered");
                    $newStatus = "";
                    $stopProCloud = $this->stopProCloud($id,$ownerID,$usernameCl, $cloud);
                    //track termination of instance
                    if ($type == "slow"){
                        for ($i = 0; $i < 10; $i++) {
                            $runAmzCloudCheck = $this->runCloudCheck($id,$cloud, $ownerID, $usernameCl);
                            sleep(15);

                            $checkCloudStatus = $this->checkCloudStatus($id,$ownerID,$usernameCl, $cloud);
                            $newStatus = json_decode($checkCloudStatus)->{'status'};
                            if ($newStatus == "terminated"){
                                break;
                            }
                        }
                    }
                    return json_encode("Shutdown Triggered:".$stopProCloud." New Status:".$newStatus);
                } else {
                    return "$remaining seconds left to shutdown.";
                }
            }
        } else {
            return "Shutdown feature has not been activated.";
        }
    }


    //read both start and list files
    function readCloudListStart($id,$username, $cloud){
        if ($cloud == "amazon"){
            $main_path = $this->amz_path;
        } else if ($cloud == "google"){
            $main_path = $this->goog_path;
        }
        $profileName = "{$username}_{$id}";
        //read logCloudList.txt
        $logPath ="{$main_path}/pro_{$profileName}/logCloudList.txt";
        $logCloudList = $this->readFile($logPath);
        $log_array = array('logCloudList' => $logCloudList);
        //read logStart.txt
        $logPathStart ="{$main_path}/pro_{$profileName}/logStart.txt";
        $logStart = $this->readFile($logPathStart);
        $log_array['logStart'] = $logStart;
        return $log_array;
    }
    //available status: waiting, initiated, terminated, running
    function checkCloudStatus($id, $ownerID, $username, $cloud) {
        $profileName = "{$username}_{$id}";
        //check status
        $cloudStat = json_decode($this->getCloudStatus($id, $cloud, $ownerID));
        $status = $cloudStat[0]->{'status'};
        $node_status = $cloudStat[0]->{'node_status'};
        if ($status == "waiting"){
            //check cloud list
            $log_array = $this->readCloudListStart($id,$username, $cloud);
            if (preg_match("/running/i", $log_array['logCloudList'])){
                $this->updateCloudProStatus($id, "initiated", $cloud, $ownerID);
                $log_array['status'] = "initiated";
                return json_encode($log_array);
            } else if (!preg_match("/STATUS/", $log_array['logCloudList']) && (preg_match("/Missing/i", $log_array['logCloudList']) || preg_match("/denied/i", $log_array['logCloudList']) || preg_match("/ERROR/", $log_array['logCloudList']))){
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
                //Downloading dependency com.google.errorprone:error_prone
            }else if (preg_match("/Unknow cloud/i", $log_array['logStart']) || preg_match("/Invalid/i", $log_array['logStart']) || preg_match("/Missing/i", $log_array['logStart']) || preg_match("/denied/i", $log_array['logStart']) || (preg_match("/ERROR/", $log_array['logStart']) && !preg_match("/WARN: One or more errors/i", $log_array['logStart'])) || preg_match("/couldn't/i", $log_array['logStart'])  || preg_match("/help/i", $log_array['logStart']) || preg_match("/wrong/i", $log_array['logStart'])){
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            }else {
                //error
                $log_array['status'] = "waiting";
                return json_encode($log_array);
            }
        } else if ($status == "initiated"){
            //check cloud list
            $log_array = $this->readCloudListStart($id,$username, $cloud);
            if (preg_match("/running/i",$log_array['logCloudList']) && preg_match("/STATUS/",$log_array['logCloudList'])){
                $startLog = $log_array['logStart'];
                if (preg_match("/ssh -i(.*)/",$startLog)){
                    preg_match("/ssh -i <(.*)> (.*)/",$startLog, $match);
                    $sshText = $match[2];
                    $log_array['sshText'] = $sshText;
                    $log_array['status'] = "running";
                    $this->updateCloudProStatus($id, "running", $cloud, $ownerID);
                    $this->updateCloudProSSH($id, $sshText, $cloud, $ownerID);
                    //parse child nodes
                    $cluData=$this->getProfileCloudbyID($id, $cloud, $ownerID);
                    $cluDataArr=json_decode($cluData,true);
                    $numNodes = $cluDataArr[0]["nodes"];
                    settype($numNodes, "integer");
                    $username = $cluDataArr[0]["username"];
                    if ($numNodes >1){
                        $log_array['nodes'] = $numNodes;
                        if (preg_match("/.*Launching worker node.*/",$startLog)){
                            preg_match("/.*Launching worker node.*ready\.(.*)Launching master node --/s",$startLog, $matchNodes);
                            if (!empty($matchNodes[1])){
                                preg_match_all("/[ ]+[^ ]+[ ]+(.*\.com)\n.*/sU",$matchNodes[1], $matchNodesAll);
                                $log_array['childNodes'] = $matchNodesAll[1];
                            }
                        }
                    }
                    return json_encode($log_array);
                } else {
                    $log_array['status'] = "initiated";
                    return json_encode($log_array);
                }
            } else if (!preg_match("/running/i",$log_array['logCloudList']) && preg_match("/STATUS/",$log_array['logCloudList'])){
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        } else if ($status == "running"){
            //check cloud list
            $log_array = $this->readCloudListStart($id,$username, $cloud);
            if (preg_match("/running/i",$log_array['logCloudList']) && preg_match("/STATUS/",$log_array['logCloudList'])){
                $log_array['status'] = "running";
                $sshTextArr = json_decode($this->getCloudProSSH($id, $cloud, $ownerID));
                $sshText = $sshTextArr[0]->{'ssh'};
                $log_array['sshText'] = $sshText;
                return json_encode($log_array);
            } else if (!preg_match("/running/i",$log_array['logCloudList']) && !preg_match("/stopping/i",$log_array['logCloudList']) && preg_match("/STATUS/",$log_array['logCloudList'])){
                $this->updateCloudProStatus($id, "terminated", $cloud, $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        }
        else if ($status == "terminated"){
            $log_array = $this->readCloudListStart($id,$username, $cloud);
            $log_array['status'] = "terminated";
            if (preg_match("/running/i",$log_array['logCloudList']) && preg_match("/STATUS/",$log_array['logCloudList'])){
                $log_array['status'] = "running";
                $this->updateCloudProStatus($id, "running", $cloud, $ownerID);
                $sshTextArr = json_decode($this->getCloudProSSH($id, $cloud, $ownerID));
                $sshText = $sshTextArr[0]->{'ssh'};
                $log_array['sshText'] = $sshText;
                return json_encode($log_array);
            }
            return json_encode($log_array);
        } else if ($status == "" ){
            $log_array = array('status' => 'inactive');
            return json_encode($log_array);
        }else if ($status == "inactive"){
            $log_array = array('status' => 'inactive');
            return json_encode($log_array);
        }
    }

    //check cloud list
    function runCloudCheck($id, $cloud, $ownerID,$username){
        if ($cloud == "amazon"){
            $main_path = $this->amz_path;
        } else if ($cloud == "google"){
            $main_path = $this->goog_path;
        }
        $profileName = "{$username}_{$id}";
        $cloudName = $this->getCloudName($profileName, $cloud);
        $nextVer = $this->next_ver;
        $nextVerText = "";
        if (!empty($nextVer)){
            $nextVerText = "export NXF_VER=$nextVer &&";
        }
        $nextModeText = "";
        if ($cloud == "google"){
            $data = json_decode($this->getProfileCloudbyID($id, $cloud, $ownerID));
            $google_cre_id = $data[0]->{'google_cre_id'};
            $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            $nextModeText = "export NXF_MODE=$cloud && export GOOGLE_APPLICATION_CREDENTIALS=$credFile && nextflow info &&";
        }
        $cmd = "cd {$main_path}/pro_$profileName && rm -f logCloudList.txt && $nextVerText $nextModeText nextflow cloud list $cloudName >> logCloudList.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'cloudlist', '');
        return json_encode($log_array);
    }

    function getLastRunData($project_pipeline_id){
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.date_modified, pp.owner_id, r.run_status
            FROM project_pipeline pp
            INNER JOIN run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid AND pp.deleted=0 AND pp.id='$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function cleanlist($list, $ret){
        if (!empty($list)){
            for ($i = 0; $i < count($list); $i++) {
                $dir = trim($list[$i]);
                if (!empty($dir) && file_exists($dir) && $dir != "{$this->tmp_path}" && $dir != "{$this->tmp_path}/uploads/" && $dir != "{$this->tmp_path}/uploads" && $dir != "{$this->tmp_path}/pub" && $dir != "{$this->tmp_path}/pub/" ){
                    system('rm -rf ' . escapeshellarg($dir), $retval);
                    if ($retval == 0){
                        $ret.=$dir." ";
                    } else {
                        $ret.=$dir." not deleted->$retval";
                    }
                }
            }
        }
        return $ret;
    }

    function execCleanCmd ($ret, $cmd){
        $dirlist = array();
        exec($cmd, $dirlist, $exit);
        $ret = $this->cleanlist($dirlist, $ret);
        return $ret;
    }

    function cleanTempDir(){
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

    function savePubWeb($project_pipeline_id,$profileType,$profileId,$pipeline_id, $ownerID){
        $data = json_encode("pubweb is not defined");
        $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
        //get pubWebDir
        $pipeData = json_decode($this->loadPipeline($pipeline_id,$ownerID));
        $pubWebDir = $pipeData[0]->{'publish_web_dir'};
        if (!empty($pubWebDir)){
            // get outputdir
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
            list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
            $reportDir = $this->getReportDir($proPipeAll);
            $down_file_list = explode(',', $pubWebDir);
            foreach ($down_file_list as &$value) {
                $value = $reportDir."/".$value;
            }
            unset($value);
            $data = $this->saveNextflowLog($down_file_list,  $uuid, "pubweb", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);
        } 
        return $data;
    }

    function updateProPipeStatus ($project_pipeline_id, $loadtype, $ownerID){
        // get active runs //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted, Manual
        // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. 
        $out = array();
        $duration = ""; //run duration
        $newRunStatus = "";
        $saveNextLog = "";
        $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
        //fix for old runs 
        if (empty($uuid)){
            //old run folder format may exist (runID)
            $runStat = json_decode($this->getRunStatus($project_pipeline_id, $ownerID));
            if (!empty($runStat)){
                $runStatus = $runStat[0]->{"run_status"};
                $last_run_uuid = "run".$project_pipeline_id;
                $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
                $output_dir = $proPipeAll[0]->{'output_dir'};
                $profile = $proPipeAll[0]->{'profile'};
                $subRunLogDir = "";
            }
        } else {
            // latest last_uuid format exist
            $runDataJS = $this->getLastRunData($project_pipeline_id);
            if (!empty(json_decode($runDataJS,true))){
                $runData = json_decode($runDataJS,true)[0];
                $runStatus = $runData["run_status"];
                $last_run_uuid = $runData["last_run_uuid"];
                $output_dir = $runData["output_dir"];
                $profile = $runData["profile"];
                $subRunLogDir = "run";
            }

        }
        if (!empty($profile)){
            $profileAr = explode("-", $profile);
            $profileType = $profileAr[0];
            $profileId = $profileAr[1];
            if (!empty($last_run_uuid)){
                $dolphin_path_real = "$output_dir/run{$project_pipeline_id}";
                $down_file_list=array("log.txt",".nextflow.log","report.html", "timeline.html", "trace.txt","dag.html","err.log", "initialrun/initial.log");
                foreach ($down_file_list as &$value) {
                    $value = $dolphin_path_real."/".$value;
                }
                unset($value);
                //wait for the downloading logs
                if ($loadtype == "slow"){
                    $saveNextLog = $this -> saveNextflowLog($down_file_list, $last_run_uuid, "run", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);
                    sleep(5);
                    $out["saveNextLog"] = $saveNextLog;
                }
                $serverLog = json_decode($this -> getFileContent($last_run_uuid, "run/serverlog.txt", $ownerID));

                $errorLog = json_decode($this -> getFileContent($last_run_uuid, "$subRunLogDir/err.log", $ownerID));
                $initialLog = json_decode($this -> getFileContent($last_run_uuid, "$subRunLogDir/initial.log", $ownerID));
                $nextflowLog = json_decode($this -> getFileContent($last_run_uuid, "$subRunLogDir/log.txt", $ownerID));
                $dotNextflowLog = json_decode($this -> getFileContent($last_run_uuid, "$subRunLogDir/.nextflow.log", $ownerID));
                $serverLog = isset($serverLog) ? trim($serverLog) : "";
                $errorLog = isset($errorLog) ? trim($errorLog) : "";
                $initialLog = isset($initialLog) ? trim($initialLog) : "";
                $nextflowLog = isset($nextflowLog) ? trim($nextflowLog) : "";
                $dotNextflowLog = isset($dotNextflowLog) ? trim($dotNextflowLog) : "";
                if (!empty($errorLog)) { $serverLog = $serverLog . "\n" . $errorLog; }
                if (!empty($initialLog)) { $nextflowLog = $initialLog . "\n" . $nextflowLog; }
                $out["serverLog"] = $serverLog;
                $out["nextflowLog"] = $nextflowLog;

                if ($runStatus === "Terminated" || $runStatus === "NextSuc" || $runStatus === "Error" || $runStatus === "NextErr" || $runStatus === "Manual") {
                    // when run hasn't finished yet and connection is down
                } else if ($loadtype == "slow" && $saveNextLog == "logNotFound" && ($runStatus != "Waiting" && $runStatus !== "init")) {
                    //log file might be deleted or couldn't read the log file
                    $newRunStatus = "Aborted";
                } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i",$serverLog) || preg_match("/command not found/i",$serverLog)) {
                    error_log("err1");
                    $newRunStatus = "Error";
                    // otherwise parse nextflow file to get status
                } else if (!empty($nextflowLog)){
                    if (preg_match("/N E X T F L O W/",$nextflowLog)){
                        //run completed with error
                        if (preg_match("/##Success: failed/",$nextflowLog) && preg_match("/DEBUG nextflow.script.ScriptRunner - > Execution complete/",$dotNextflowLog)){
                            preg_match("/##Duration:(.*)\n/",$nextflowLog, $matchDur);
                            $duration = !empty($matchDur[1]) ? $matchDur[1] : "";
                            $newRunStatus = "NextErr";
                            //run completed with success
                        } else if (preg_match("/##Success: OK/",$nextflowLog) && preg_match("/DEBUG nextflow.script.ScriptRunner - > Execution complete/",$dotNextflowLog)){
                            preg_match("/##Duration:(.*)/",$nextflowLog, $matchDur);
                            $duration = !empty($matchDur[1]) ? $matchDur[1] : "";
                            $newRunStatus = "NextSuc";
                            // run error
                            //"WARN: Failed to publish file" gives error
                            //|| preg_match("/failed/i",$nextflowLog) removed 
                        } else if (preg_match("/[\n\r\s]error[\n\r\s:=]/i",$nextflowLog) || preg_match("/\n -- Check script /",$nextflowLog)){
                            $confirmErr=true;
                            if (preg_match("/-- Execution is retried/i",$nextflowLog) || preg_match("/WARN: One or more errors/i", $nextflowLog)){
                                //if only process retried, status shouldn't set as error.
                                $confirmErr = false;
                                $txt = trim($nextflowLog);
                                $lines = explode("\n", $txt);
                                for ($i = 0; $i < count($lines); $i++) {
                                    if (preg_match("/error/i",$lines[$i]) && !preg_match("/-- Execution is retried/i",$lines[$i]) && !preg_match("/WARN: One or more errors/i", $lines[$i])){
                                        error_log("WARN: One or more errors");
                                        error_log($lines[$i]);
                                        $confirmErr = true;
                                        break;
                                    }
                                }
                            }
                            if ($confirmErr == true){
                                $newRunStatus = "NextErr";
                            } else {
                                $newRunStatus = "NextRun";
                            }
                        } else {
                            //update status as running  
                            $newRunStatus = "NextRun";
                        }
                        //Nextflow log file exist but /N E X T F L O W/ not printed yet
                    } else {
                        $newRunStatus = "Waiting";
                    }
                } else{
                    //"Nextflow log is not exist yet."
                    $newRunStatus = "Waiting";
                }
                if (!empty($newRunStatus)){
                    $setStatus = $this -> updateRunStatus($project_pipeline_id, $newRunStatus, $ownerID);
                    $setLog = $this -> updateRunLog($project_pipeline_id, $newRunStatus, $duration, $ownerID); 
                    $out["runStatus"] = $newRunStatus;
                    if (($newRunStatus == "NextErr" || $newRunStatus == "NextSuc" || $newRunStatus == "Error") && ($profileType == "amazon" || $profileType == "google") ){
                        error_log("triggerShutdown fast1");
                        $triggerShutdown = $this -> triggerShutdown($profileId, $profileType, $ownerID, "fast");
                    }
                } else {
                    $out["runStatus"] = $runStatus;
                }
            }
            return json_encode($out);
        }
    }


    //------------- SideBar Funcs --------
    public function getParentSideBar($ownerID){
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $sql= "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                  FROM process_group pg ";
                return self::queryTable($sql);
            }
            $sql= "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                FROM process_group pg
                LEFT JOIN user_group ug ON  pg.group_id=ug.g_id
                where pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15) ";
        } else {
            $sql= "SELECT DISTINCT group_name name, id FROM process_group where perms = 63";
        }
        return self::queryTable($sql);
    }
    public function getParentSideBarPipeline($ownerID){
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $sql= "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                  FROM pipeline_group pg ";
                return self::queryTable($sql);
            }
            $sql= "SELECT DISTINCT pg.group_name name, pg.id, pg.perms, pg.group_id
                FROM pipeline_group pg
                LEFT JOIN user_group ug ON  pg.group_id=ug.g_id
                where pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15) ";
        } else {
            $sql= "SELECT DISTINCT group_name name, id FROM pipeline_group where perms = 63";
        }
        return self::queryTable($sql);
    }



    public function getPipelineSideBar($ownerID){
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $where = " WHERE p.deleted = 0";
            } else {
                $where = " WHERE p.deleted = 0 AND (p.owner_id='$ownerID' OR (p.perms = 63 AND p.pin = 'true') OR (ug.u_id ='$ownerID' and p.perms = 15)) ";
            }
            $sql= "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin, pip.rev_id, pip.summary, pip.date_modified, u.username, pip.pipeline_group_id, pip.pipeline_gid
                FROM biocorepipe_save pip
                INNER JOIN users u ON pip.owner_id = u.id
                LEFT JOIN user_group ug ON  pip.group_id=ug.g_id
                INNER JOIN (
                  SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                  FROM biocorepipe_save p
                  INNER JOIN users u ON p.owner_id = u.id
                  LEFT JOIN user_group ug ON p.group_id=ug.g_id
                  $where
                  GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.deleted = 0 AND pip.pipeline_gid=b.pipeline_gid";

        } else {
            $sql= "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin, pip.rev_id,  pip.summary, pip.date_modified, u.username, pip.pipeline_group_id, pip.pipeline_gid
                FROM biocorepipe_save pip
                INNER JOIN users u ON pip.owner_id = u.id
                INNER JOIN (
                  SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                  FROM biocorepipe_save p
                  INNER JOIN users u ON pip.owner_id = u.id
                  WHERE p.perms = 63 AND p.deleted=0
                  GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid AND pip.pin = 'true' AND pip.deleted = 0";
        }
        return self::queryTable($sql);
    }

    public function getSubMenuFromSideBar($parent, $ownerID){
        $admin_only = "";
        $admin_only_group_by = "";
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            $where_pr = "pr.deleted=0 AND (pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            if ($userRole == "admin"){
                $admin_only = ", p.owner_id, p.publish, MIN(IF((p.owner_id='$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)),0,1)) as admin_only";
                $where_pr = "pr.deleted=0";
                $admin_only_group_by = " GROUP BY p.id";
            }
        } else {
            $where_pr = "pr.deleted=0 AND pr.perms = 63";
        }
        $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id $admin_only
              FROM process p
              LEFT JOIN user_group ug ON  p.group_id=ug.g_id
              INNER JOIN process_group pg
              ON p.process_group_id = pg.id and pg.group_name='$parent'
              INNER JOIN (
                SELECT pr.process_gid, MAX(pr.rev_id) rev_id
                FROM process pr
                LEFT JOIN user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.process_gid
              ) b ON p.rev_id = b.rev_id AND p.process_gid=b.process_gid AND p.deleted = 0
              $admin_only_group_by";

        return self::queryTable($sql);
    }
    //new
    public function getSubMenuFromSideBarPipe($parent, $ownerID){
        $admin_only = "";
        $admin_only_group_by = "";
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            $where_pr = "pr.deleted=0 AND (pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            if ($userRole == "admin"){
                $admin_only = ", p.owner_id, p.publish, MIN(IF((p.owner_id='$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)),0,1)) as admin_only";
                $where_pr = "pr.deleted=0";
                $admin_only_group_by = " GROUP BY p.id";
            }
        } else {
            $where_pr = "pr.deleted=0 AND pr.perms = 63";
        }
        $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id, p.pin $admin_only
              FROM biocorepipe_save p
              LEFT JOIN user_group ug ON  p.group_id=ug.g_id
              INNER JOIN pipeline_group pg
              ON p.pipeline_group_id = pg.id and pg.group_name='$parent'
              INNER JOIN (
                SELECT DISTINCT pr.pipeline_gid, MAX(pr.rev_id) rev_id
                FROM biocorepipe_save pr
                LEFT JOIN user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.pipeline_gid
              ) b ON p.rev_id = b.rev_id AND p.pipeline_gid=b.pipeline_gid AND p.deleted = 0
              $admin_only_group_by";


        return self::queryTable($sql);
    }

    public function getParentSideBarProject($ownerID){
        $sql= "SELECT DISTINCT pp.name, pp.id
              FROM project pp
              LEFT JOIN user_group ug ON pp.group_id=ug.g_id
              where pp.deleted =0 AND pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15)";
        return self::queryTable($sql);
    }
    public function getSubMenuFromSideBarProject($parent, $ownerID){
        $where = "pp.deleted = 0 AND (pp.project_id='$parent' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15)))";
        $sql="SELECT DISTINCT pp.id, pp.name, pj.owner_id, pp.project_id
              FROM project_pipeline pp
              LEFT JOIN user_group ug ON pp.group_id=ug.g_id
              INNER JOIN project pj ON pp.project_id = pj.id and $where ";
        return self::queryTable($sql);
    }


    //    ---------------  Users ---------------
    public function getUserByGoogleId($google_id) {
        $sql = "SELECT * FROM users WHERE google_id = '$google_id' AND deleted=0";
        return self::queryTable($sql);
    }
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = '$id' AND deleted=0";
        return self::queryTable($sql);
    }
    public function getUserByEmail($email) {
        $email = str_replace("'", "''", $email);
        $sql = "SELECT * FROM users WHERE email = '$email' AND deleted=0";
        return self::queryTable($sql);
    }
    public function getUserByEmailorUsername($emailusername) {
        $emailusername = strtolower(str_replace("'", "''", $emailusername));
        $sql = "SELECT * FROM users WHERE (email = '$emailusername' OR username = '$emailusername' ) AND deleted=0";
        return self::queryTable($sql);
    }
    public function updateUserManual($id, $name, $email, $username, $institute, $lab, $logintype, $ownerID) {
        $email = str_replace("'", "''", $email);
        $sql = "UPDATE users SET name='$name', institute='$institute', username='$username', lab='$lab', logintype='$logintype', email='$email', last_modified_user='$ownerID' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateUserPassword($id, $pass_hash, $ownerID) {
        $sql = "UPDATE users SET pass_hash='$pass_hash', last_modified_user='$ownerID' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function insertUserManual($name, $email, $username, $institute, $lab, $logintype, $role, $active, $pass_hash, $verify, $google_id) {
        $email = str_replace("'", "''", $email);
        $sql = "INSERT INTO users(name, email, username, institute, lab, logintype, role, active, memberdate, date_created, date_modified, perms, pass_hash, verification, google_id) VALUES ('$name', '$email', '$username', '$institute', '$lab', '$logintype','$role', $active, now() , now(), now(), '3', '$pass_hash', '$verify', '$google_id')";
        return self::insTable($sql);
    }
    public function checkExistUser($id,$username,$email) {
        $email = str_replace("'", "''", $email);
        $error = array();
        if (!empty($id)){//update
            //check if username or e-mail is altered
            $userData = json_decode($this->getUserById($id))[0];
            $usernameDB = $userData->{'username'};
            $emailDB = $userData->{'email'};
            if ($usernameDB != $username){
                $checkUsername = $this->queryAVal("SELECT id FROM users WHERE deleted=0 AND username = LCASE('" .$username. "')");
            }
            if ($emailDB != $email){
                $checkEmail = $this->queryAVal("SELECT id FROM users WHERE deleted=0 AND email = LCASE('" .$email. "')");
            }
        } else { //insert
            $checkUsername = $this->queryAVal("SELECT id FROM users WHERE deleted=0 AND username = LCASE('" .$username. "')");
            $checkEmail = $this->queryAVal("SELECT id FROM users WHERE deleted=0 AND email = LCASE('" .$email. "')");
        }
        if (!empty($checkUsername)){
            $error['username'] ="This username already exists.";
        }
        if (!empty($checkEmail)){
            $error['email'] ="This e-mail already exists.";
        }
        return $error;
    }

    public function changeActiveUser($user_id, $type) {
        if ($type == "activate" || $type == "activateSendUser"){
            $active = 1;
            $verify = "verification=NULL,";
        } else {
            $active = "NULL";
            $verify = "";
        }
        $sql = "UPDATE users SET $verify active=$active, last_modified_user='$user_id' WHERE id = '$user_id'";
        return self::runSQL($sql);
    }
    function changeRoleUser($user_id, $type, $ownerID) {
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin"){
            $sql = "UPDATE users SET role='$type', last_modified_user='$ownerID' WHERE id = '$user_id'";
            return self::runSQL($sql);
        }
    }

    //    ------------- Profiles   ------------
    function insertSSH($name, $hide, $check_userkey, $check_ourkey, $ownerID) {
        $sql = "INSERT INTO ssh(name, hide, check_userkey, check_ourkey, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$hide', '$check_userkey', '$check_ourkey', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateSSH($id, $name, $hide, $check_userkey, $check_ourkey, $ownerID) {
        $sql = "UPDATE ssh SET name='$name', hide='$hide', check_userkey='$check_userkey', check_ourkey='$check_ourkey', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertAmz($name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID) {
        $sql = "INSERT INTO amazon_credentials (name, amz_def_reg, amz_acc_key, amz_suc_key, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$amz_def_reg', '$amz_acc_key', '$amz_suc_key', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateAmz($id, $name, $amz_def_reg,$amz_acc_key,$amz_suc_key, $ownerID) {
        $sql = "UPDATE amazon_credentials SET name='$name', amz_def_reg='$amz_def_reg', amz_acc_key='$amz_acc_key', amz_suc_key='$amz_suc_key', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function getWizardAll($ownerID) {
        $sql = "SELECT id, name, status, deleted FROM wizard WHERE owner_id ='$ownerID' ";
        return self::queryTable($sql);
    }
    function checkActiveWizard($ownerID) {
        $sql = "SELECT id, name, status FROM wizard WHERE owner_id ='$ownerID' AND status = 'active' AND deleted = 0 ";
        return self::queryTable($sql);
    }
    function getWizardByID($id, $ownerID) {
        $sql = "SELECT * FROM wizard WHERE id = '$id' AND owner_id ='$ownerID' AND deleted = 0";
        return self::queryTable($sql);
    }
    function insertWizard($name, $data, $status, $ownerID) {
        $sql = "INSERT INTO wizard (name, data, status, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$name', '$data', '$status', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateWizard($id, $name, $data, $status, $ownerID) {
        $sql = "UPDATE wizard SET name='$name', data='$data', status='$status', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertGoogle($name, $project_id, $key_name, $ownerID) {
        $sql = "INSERT INTO google_credentials (name, project_id, key_name, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
        ('$name', '$project_id', '$key_name', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }   
    function updateGoogle($id, $name, $project_id, $key_name, $ownerID) {
        $sql = "UPDATE google_credentials SET name='$name', project_id='$project_id', key_name='$key_name', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertGithub($username, $email, $password, $ownerID) {
        $sql = "INSERT INTO github (username, email, password, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
              ('$username', '$email', '$password', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    function updateGithub($id, $username, $email, $password, $ownerID) {
        $sql = "UPDATE github SET username='$username', email='$email', password='$password', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function getGithub($ownerID) {
        $sql = "SELECT id, username, email, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM github WHERE owner_id = '$ownerID' AND deleted=0";
        return self::queryTable($sql);
    }
    function getGithubbyID($id,$ownerID) {
        $sql = "SELECT * FROM github WHERE owner_id = '$ownerID' and id = '$id' AND deleted=0";
        return self::queryTable($sql);
    }
    function getAmz($ownerID) {
        $sql = "SELECT id, name, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM amazon_credentials WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getGoogle($ownerID) {
        $sql = "SELECT id, name, owner_id, group_id, perms, date_created, date_modified, last_modified_user FROM google_credentials WHERE deleted = 0 AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getGooglebyID($id,$ownerID) {
        $sql = "SELECT * FROM google_credentials WHERE deleted = 0 AND owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    function getAmzbyID($id,$ownerID) {
        $sql = "SELECT * FROM amazon_credentials WHERE owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    function getSSH($userRole, $admin_id, $type, $ownerID) {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id) || $type == "hidden"){
            $hide="";
        }
        $sql = "SELECT * FROM ssh WHERE $hide owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getSSHbyID($id, $userRole, $admin_id, $ownerID) {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id)){
            $hide="";
        }
        $sql = "SELECT * FROM ssh WHERE $hide owner_id = '$ownerID' AND id = '$id'";
        return self::queryTable($sql);
    }
    function getSSHbyName($name, $userRole, $admin_id, $ownerID) {
        $hide = " hide = 'false' AND";
        if ($userRole == "admin" || !empty($admin_id)){
            $hide="";
        }
        $sql = "SELECT * FROM ssh WHERE $hide owner_id = '$ownerID' AND name = BINARY '$name'";
        return self::queryTable($sql);
    }
    function getProfileClusterbyID($id, $ownerID) {
        $sql = "SELECT p.* 
                FROM profile_cluster p
                INNER JOIN users u ON p.owner_id = u.id
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                WHERE (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' AND p.perms = 15)) AND p.id = '$id'";
        return self::queryTable($sql);
    }
    function getProfileCluster($ownerID) {
        $sql = "SELECT * FROM profile_cluster WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getRunProfileCluster($ownerID) {
        $sql = "SELECT DISTINCT p.* FROM profile_cluster p 
                INNER JOIN users u ON p.owner_id = u.id
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getCollections($ownerID) {
        $sql = "SELECT id, name FROM collection WHERE owner_id = '$ownerID' AND deleted = 0";
        return self::queryTable($sql);
    }
    function getCollectionById($id,$ownerID) {
        $sql = "SELECT id, name FROM collection WHERE id = '$id' AND owner_id = '$ownerID' AND deleted = 0";
        return self::queryTable($sql);
    }
    function getFiles($ownerID) {
        $sql = "SELECT DISTINCT f.id, f.name, f.files_used, f.file_dir, f.collection_type, f.archive_dir, f.s3_archive_dir, f.gs_archive_dir, f.date_created, f.date_modified, f.last_modified_user, f.file_type, f.run_env, 
              GROUP_CONCAT( DISTINCT fp.p_id order by fp.p_id) as p_id,
              GROUP_CONCAT( DISTINCT p.name order by p.name) as project_name,
              GROUP_CONCAT( DISTINCT c.name order by c.name) as collection_name,
              GROUP_CONCAT( DISTINCT c.id order by c.id) as collection_id
              FROM file f
              LEFT JOIN file_collection fc  ON f.id = fc.f_id
              LEFT JOIN file_project fp ON f.id = fp.f_id
              LEFT JOIN collection c on fc.c_id = c.id
              LEFT JOIN project p on fp.p_id = p.id
              WHERE f.owner_id = '$ownerID' AND f.deleted = 0 AND (fc.deleted = 0 OR fc.deleted IS NULL) AND (fp.deleted = 0 OR fp.deleted IS NULL) AND (p.deleted = 0 OR p.deleted IS NULL) AND (c.deleted = 0 OR c.deleted IS NULL)
              GROUP BY f.id, f.name, f.files_used, f.file_dir, f.collection_type, f.archive_dir, f.s3_archive_dir, f.gs_archive_dir, f.date_created, f.date_modified, f.last_modified_user, f.file_type, f.run_env";
        return self::queryTable($sql);
    }
    function getPublicProfileCluster($ownerID) {
        $sql = "SELECT * FROM profile_cluster WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getProfileAmazon($ownerID) {
        $sql = "SELECT * FROM profile_amazon WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getProfileGoogle($ownerID) {
        $sql = "SELECT * FROM profile_google WHERE (public != '1' OR public IS NULL) AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getPublicProfileAmazon($ownerID) {
        $sql = "SELECT * FROM profile_amazon WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getPublicProfileGoogle($ownerID) {
        $sql = "SELECT * FROM profile_google WHERE public = '1'";
        return self::queryTable($sql);
    }
    function getRunProfileAmazon($ownerID) {
        $sql = "SELECT DISTINCT p.* FROM profile_amazon p 
                INNER JOIN users u ON p.owner_id = u.id
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getRunProfileGoogle($ownerID) {
        $sql = "SELECT DISTINCT p.* FROM profile_google p 
                INNER JOIN users u ON p.owner_id = u.id
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                WHERE (p.public != '1' OR p.public IS NULL) AND (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    function getProfileCloudbyID($id, $cloud, $ownerID) {
        $sql = "SELECT p.*, u.username
                FROM profile_{$cloud} p
                INNER JOIN users u ON p.owner_id = u.id
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                WHERE (p.owner_id = '$ownerID' OR (ug.u_id ='$ownerID' AND p.perms = 15)) AND p.id = '$id'";
        return self::queryTable($sql);
    }
    function getActiveRunbyProID($id, $cloud, $ownerID) {
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.date_modified, pp.owner_id, r.run_status
            FROM project_pipeline pp
            INNER JOIN run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid AND pp.deleted=0 AND pp.owner_id = '$ownerID' AND pp.profile = '$cloud-$id' AND (r.run_status = 'init' OR r.run_status = 'Waiting' OR r.run_status = 'NextRun' OR r.run_status = 'Aborted')";
        return self::queryTable($sql);
    }
    function insertProfileLocal($name, $executor,$next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID) {
        $sql = "INSERT INTO profile_local (name, executor, next_path, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$name', '$executor','$next_path', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileLocal($id, $name, $executor,$next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID) {
        $sql = "UPDATE profile_local SET name='$name', executor='$executor', next_path='$next_path', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu',  last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function insertProfileCluster($name, $executor,$next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $group_id, $auto_workdir, $perms, $ownerID) {
        $sql = "INSERT INTO profile_cluster(name, executor, next_path, port, singu_cache, username, hostname, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, next_clu_opt, job_clu_opt, public, variable, group_id, auto_workdir, owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$username', '$hostname', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ssh_id', '$next_clu_opt','$job_clu_opt', '$public', '$variable', '$group_id', '$auto_workdir', '$ownerID', '$perms', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileCluster($id, $name, $executor,$next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $group_id, $auto_workdir, $perms, $ownerID) {
        $sql = "UPDATE profile_cluster SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', username='$username', hostname='$hostname', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', job_clu_opt='$job_clu_opt', next_clu_opt='$next_clu_opt', ssh_id='$ssh_id', public='$public', variable='$variable', group_id='$group_id', auto_workdir='$auto_workdir', last_modified_user ='$ownerID', perms='$perms'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertProfileAmazon($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id,$shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID) {
        $sql = "INSERT INTO profile_amazon(name, executor, next_path, port, singu_cache, instance_type, image_id, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, subnet_id, shared_storage_id, shared_storage_mnt, ssh_id, amazon_cre_id, next_clu_opt, job_clu_opt, public, security_group, variable, group_id, auto_workdir, def_publishdir, def_workdir,  owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$ins_type', '$image_id', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$subnet_id','$shared_storage_id','$shared_storage_mnt','$ssh_id','$amazon_cre_id', '$next_clu_opt', '$job_clu_opt', '$public', '$security_group', '$variable', '$group_id', '$auto_workdir', '$def_publishdir', '$def_workdir', '$ownerID', '$perms', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileAmazon($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID) {
        $sql = "UPDATE profile_amazon SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', instance_type='$ins_type', image_id='$image_id', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', subnet_id='$subnet_id', shared_storage_id='$shared_storage_id', shared_storage_mnt='$shared_storage_mnt', ssh_id='$ssh_id', next_clu_opt='$next_clu_opt', job_clu_opt='$job_clu_opt', amazon_cre_id='$amazon_cre_id', public='$public', security_group='$security_group', variable='$variable', group_id='$group_id', auto_workdir='$auto_workdir', def_publishdir='$def_publishdir', def_workdir='$def_workdir', last_modified_user ='$ownerID', perms='$perms' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertProfileGoogle($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID) {
        $sql = "INSERT INTO profile_google(name, executor, next_path, port, singu_cache, instance_type, image_id, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, google_cre_id, next_clu_opt, job_clu_opt, public, zone, variable, group_id, auto_workdir, def_publishdir, def_workdir, owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$port', '$singu_cache', '$ins_type', '$image_id', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ssh_id', '$google_cre_id', '$next_clu_opt', '$job_clu_opt', '$public', '$zone', '$variable', '$group_id', '$auto_workdir', '$def_publishdir', '$def_workdir', '$ownerID', '$perms', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProfileGoogle($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID) {
        $sql = "UPDATE profile_google SET name='$name', executor='$executor', next_path='$next_path', port='$port', singu_cache='$singu_cache', instance_type='$ins_type', image_id='$image_id', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu',  ssh_id='$ssh_id', next_clu_opt='$next_clu_opt', job_clu_opt='$job_clu_opt', google_cre_id='$google_cre_id', public='$public', zone='$zone', variable='$variable', group_id='$group_id', auto_workdir='$auto_workdir', def_publishdir='$def_publishdir', def_workdir='$def_workdir', last_modified_user ='$ownerID', perms='$perms' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateProfileCloudOnStart($id, $nodes, $autoscale_check, $autoscale_maxIns, $autoscale_minIns, $autoshutdown_date, $autoshutdown_active, $autoshutdown_check, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET nodes='$nodes', autoscale_check='$autoscale_check', autoscale_maxIns='$autoscale_maxIns', autoscale_minIns='$autoscale_minIns',  autoshutdown_date=".($autoshutdown_date==NULL ? "NULL" : "'$autoshutdown_date'").", autoshutdown_active='$autoshutdown_active', autoshutdown_check='$autoshutdown_check', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownDate($id, $autoshutdown_date, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET autoshutdown_date=".($autoshutdown_date==NULL ? "NULL" : "'$autoshutdown_date'").", last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownActive($id, $autoshutdown_active, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET autoshutdown_active='$autoshutdown_active', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudShutdownCheck($id, $autoshutdown_check, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET autoshutdown_check='$autoshutdown_check', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudProStatus($id, $status, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAmazonProNodeStatus($id, $node_status, $ownerID) {
        $sql = "UPDATE profile_amazon SET node_status='$node_status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAmazonProPid($id, $pid, $ownerID) {
        $sql = "UPDATE profile_amazon SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateCloudProSSH($id, $sshText, $cloud, $ownerID) {
        $sql = "UPDATE profile_{$cloud} SET ssh='$sshText', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function getCloudProSSH($id, $cloud, $ownerID) {
        $sql = "SELECT ssh FROM profile_{$cloud} WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function removeAmz($id) {
        $sql = "DELETE FROM amazon_credentials WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeSSH($id) {
        $sql = "DELETE FROM ssh WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProLocal($id) {
        $sql = "DELETE FROM profile_local WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProCluster($id) {
        $sql = "DELETE FROM profile_cluster WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProAmazon($id) {
        $sql = "DELETE FROM profile_amazon WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProGoogle($id) {
        $sql = "DELETE FROM profile_google WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //    ------------- Parameters ------------
    function getAllParameters($ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.id, p.file_type, p.qualifier, p.name, p.group_id, p.perms FROM parameter p";
                    return self::queryTable($sql);
                }
            }
        }

        $sql = "SELECT DISTINCT p.id, p.file_type, p.qualifier, p.name, p.group_id, p.perms
              FROM parameter p
              LEFT JOIN user_group ug ON p.group_id=ug.g_id
              WHERE p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
        return self::queryTable($sql);
    }
    function getEditDelParameters($ownerID) {
        $sql = "SELECT DISTINCT * FROM parameter p
              WHERE p.owner_id = '$ownerID' AND id not in (select parameter_id from process_parameter WHERE owner_id != '$ownerID')";
        return self::queryTable($sql);
    }

    function insertParameter($name, $qualifier, $file_type, $ownerID) {
        $sql = "INSERT INTO parameter(name, qualifier, file_type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
              ('$name', '$qualifier', '$file_type', '$ownerID', 63, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }

    function updateParameter($id, $name, $qualifier, $file_type, $ownerID) {
        $sql = "UPDATE parameter SET name='$name', qualifier='$qualifier', last_modified_user ='$ownerID', file_type='$file_type'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateProPipe_ProjectID($project_pipeline_id, $new_project_id, $ownerID) {
        $sql = "UPDATE project_pipeline SET project_id='$new_project_id', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateProPipeInput_ProjectID($project_pipeline_id, $new_project_id, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET project_id='$new_project_id', last_modified_user ='$ownerID', date_modified=now()  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateProjectInput_ProjectID($project_pipeline_id, $new_project_id, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET project_id='$new_project_id', last_modified_user ='$ownerID', date_modified=now()  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }

    function insertProcessGroup($group_name, $ownerID) {
        $sql = "INSERT INTO process_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updateProcessGroup($id, $group_name, $ownerID) {
        $sql = "UPDATE process_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function updateAllProcessGroupByGid($process_gid, $process_group_id,$ownerID) {
        $sql = "UPDATE process SET process_group_id='$process_group_id', last_modified_user ='$ownerID', date_modified=now()  WHERE process_gid = '$process_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateAllProcessNameByGid($process_gid, $name, $ownerID) {
        $sql = "UPDATE process SET name='$name', last_modified_user ='$ownerID', date_modified=now()  WHERE process_gid = '$process_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function updateAllPipelineGroupByGid($pipeline_gid, $pipeline_group_id,$ownerID) {
        $sql = "UPDATE biocorepipe_save SET pipeline_group_id='$pipeline_group_id', last_modified_user ='$ownerID', date_modified=now() WHERE deleted = 0 AND pipeline_gid = '$pipeline_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function removeParameter($id) {
        $sql = "DELETE FROM parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcessGroup($id) {
        $sql = "DELETE FROM process_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removePipelineGroup($id) {
        $sql = "DELETE FROM pipeline_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
    // --------- Process -----------
    function getAllProcessGroups($ownerID) {
        $sql = "SELECT DISTINCT pg.id, pg.group_name
              FROM process_group pg";
        return self::queryTable($sql);
    }
    function getProcessGroupById($id) {
        $sql = "SELECT DISTINCT pg.group_name
              FROM process_group pg
              WHERE pg.id = '$id'";
        return self::queryTable($sql);
    }
    function getProcessGroupByName($group_name) {
        $sql = "SELECT DISTINCT pg.id
              FROM process_group pg
              WHERE pg.group_name = '$group_name'";
        return self::queryTable($sql);
    }
    function getCollectionByName($col_name, $owner_id) {
        $sql = "SELECT DISTINCT c.id
              FROM collection c
              WHERE c.deleted = 0 AND c.name = '$col_name' AND owner_id='$owner_id'";
        return self::queryTable($sql);
    }
    function getPipelineGroupByName($group_name) {
        $sql = "SELECT DISTINCT pg.id
              FROM pipeline_group pg
              WHERE pg.group_name = '$group_name'";
        return self::queryTable($sql);
    }
    function getParameterByName($name, $qualifier, $file_type) {
        $sql = "SELECT DISTINCT id FROM parameter
              WHERE name = '$name' AND qualifier = '$qualifier' AND file_type = '$file_type'";
        return self::queryTable($sql);
    }
    function getEditDelProcessGroups($ownerID) {
        $sql = "SELECT DISTINCT id, group_name
              FROM process_group pg
              Where pg.owner_id = '$ownerID' AND id not in (select process_group_id from process Where owner_id != '$ownerID')";
        return self::queryTable($sql);
    }
    //$table="process" or "biocorepipe_save"
    function getSharedItemByUser($table, $u_id, $g_id) {
        $sql = "SELECT id, name
                FROM $table 
                WHERE owner_id = '$u_id' AND group_id = '$g_id' AND (perms=11 OR perms=15)";
        return self::queryTable($sql);
    }

    function insertProcess($name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $rev_id, $rev_comment, $group, $perms, $publish, $script_mode, $script_mode_header, $process_uuid, $process_rev_uuid, $ownerID) {
        $sql = "INSERT INTO process(name, process_gid, summary, process_group_id, script, script_header, script_footer, rev_id, rev_comment, owner_id, date_created, date_modified, last_modified_user, perms, group_id, publish, script_mode, script_mode_header, process_uuid, process_rev_uuid) VALUES ('$name', '$process_gid', '$summary', '$process_group_id', '$script', '$script_header', '$script_footer', '$rev_id','$rev_comment', '$ownerID', now(), now(), '$ownerID', '$perms', '$group', '$publish','$script_mode', '$script_mode_header', '$process_uuid', '$process_rev_uuid')";
        return self::insTable($sql);
    }

    function updateProcess($id, $name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $group, $perms, $publish, $script_mode, $script_mode_header, $ownerID) {
        $sql = "UPDATE process SET name= '$name', process_gid='$process_gid', summary='$summary', process_group_id='$process_group_id', script='$script', script_header='$script_header',  script_footer='$script_footer', last_modified_user='$ownerID', group_id='$group', perms='$perms', publish='$publish', script_mode='$script_mode', date_modified = now(), script_mode_header='$script_mode_header' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcess($id) {
        $sql = "DELETE FROM process WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProject($id) {
        $sql = "UPDATE project SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function checkGroupItem($table, $g_id,$ownerID) {
        $checkGroupItem = $this->queryAVal("SELECT id, name 
                                    FROM $table 
                                    WHERE deleted = 0 AND owner_id != '$ownerID' AND group_id ='$g_id'");
        if (!empty($checkGroupItem)){
            return "It is not allowed to remove your group because your group has been used by other members. For details, please contact with admin.";
        } else {
            return "";
        }
    }

    function removeGroup($g_id,$ownerID) {
        $ret = array();
        $checkUserOwnGroup = $this->queryAVal("SELECT id FROM groups WHERE owner_id = '$ownerID' AND id = '$g_id'");
        if (empty($checkUserOwnGroup)){
            $ret["error"] = "You don't have permission to remove this group.";
            return json_encode($ret);
        } else {
            // check if group is used in any processes/pipeline that are shared
            $warn = $this->checkGroupItem("process", $g_id, $ownerID);
            if (empty($warn)){
                $warn = $this->checkGroupItem("biocorepipe_save", $g_id, $ownerID);
            }
            if (empty($warn)){
                $this -> removeUserGroup($g_id);
                $sql = "DELETE FROM groups WHERE id = '$g_id'";
                return self::runSQL($sql);
            } else {
                $ret["error"] = $warn;
                return json_encode($ret);
            }
        }
    }

    function checkUsersSharedItem($table, $u_id, $g_id){
        $item_ids = json_decode($this->getSharedItemByUser($table, $u_id, $g_id));
        for ($i = 0; $i < count($item_ids); $i++) {
            $item_id = $item_ids[$i]->{"id"};
            $warnName = $item_ids[$i]->{"name"};
            list($checkUsed,$warn) = $this->checkUsed($table, $warnName, $item_id, $u_id);
            if (!empty($checkUsed)){
                return "It is not allowed to remove user from your group because user has shared processes/pipelines that are used by other group members. For details, please contact with admin.";
            }
        }
        return "";
    }

    function removeUserFromGroup($u_id, $g_id, $ownerID) {
        $ret = array();
        $checkUserOwnGroup = $this->queryAVal("SELECT id FROM groups WHERE owner_id = '$ownerID' AND id = '$g_id'");
        if (empty($checkUserOwnGroup)){
            $ret["error"] = "You don't have permission to remove user from group.";
            return json_encode($ret);
        } else {
            // check if user has any process/pipeline shared within group and used within in pipeline that owner is not the user
            //get all shared process and pipeline of the user.
            $warn = $this->checkUsersSharedItem("process",$u_id, $g_id);
            if (empty($warn)){
                $warn = $this->checkUsersSharedItem("biocorepipe_save",$u_id, $g_id);
            }
            if (empty($warn)){
                $sql = "DELETE FROM user_group WHERE g_id = '$g_id' AND u_id='$u_id'";
                return self::runSQL($sql);
            } else {
                $ret["error"] = $warn;
                return json_encode($ret);
            }
        }
    }
    function removeUserGroup($id) {
        $sql = "DELETE FROM user_group WHERE g_id = '$id'";
        return self::runSQL($sql);
    }
    function removeUser($id, $ownerID) {
        $sql = "UPDATE users SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeGithub($id, $ownerID) {
        $sql = "UPDATE github SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeGoogle($id, $ownerID) {
        $sql = "UPDATE google_credentials SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function removeWizard($id, $ownerID) {
        $sql = "UPDATE wizard SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    function removeProjectPipeline($id) {
        $sql = "UPDATE project_pipeline SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeRun($id) {
        $sql = "UPDATE run SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    function removeInput($id) {
        $sql = "DELETE FROM input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeFile($id, $ownerID) {
        $sql = "UPDATE file SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeCollection($id, $ownerID) {
        $sql = "UPDATE collection SET deleted = 1, date_modified = now() WHERE id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeFileProject($id, $ownerID) {
        $sql = "UPDATE file_project SET deleted = 1, date_modified = now() WHERE f_id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeFileCollection($id, $ownerID) {
        $sql = "UPDATE file_collection SET deleted = 1, date_modified = now() WHERE f_id = '$id' AND owner_id='$ownerID'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInput($id) {
        $sql = "UPDATE project_pipeline_input SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputByPipe($id) {
        $sql = "UPDATE project_pipeline_input SET deleted = 1, date_modified = now() WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputByCollection($id) {
        $sql = "UPDATE project_pipeline_input SET deleted = 1, date_modified = now() WHERE collection_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectInput($id) {
        $sql = "DELETE FROM project_input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelinebyProjectID($id) {
        $sql = "UPDATE project_pipeline SET deleted = 1, date_modified = now() WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeRunByProjectID($id) {
        $sql = "UPDATE run
              JOIN project_pipeline ON project_pipeline.id = run.project_pipeline_id
              SET run.deleted = 1 WHERE project_pipeline.project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectPipelineInputbyProjectID($id) {
        $sql = "UPDATE project_pipeline_input SET deleted = 1, date_modified = now() WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProjectInputbyProjectID($id) {
        $sql = "DELETE FROM project_input WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    function removeProcessByProcessGroupID($process_group_id) {
        $sql = "DELETE FROM process WHERE process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
    //    ------ Groups -------
    function getAllGroups($ownerID) {
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin"){
            $sql = "SELECT id, name FROM groups";
            return self::queryTable($sql);
        }
    }
    function getAllAvailableGroups($user_id, $ownerID){
        $userRole = $this->getUserRoleVal($ownerID);
        if ($userRole == "admin"){
            $sql = "SELECT DISTINCT id, name 
                    FROM groups
                    WHERE name NOT IN (SELECT DISTINCT g.name 
                    FROM groups g
                    INNER JOIN user_group ug ON g.id = ug.g_id 
                    WHERE ug.u_id = '$user_id')";
            return self::queryTable($sql);
        }
    }
    function getGroups($id,$ownerID) {
        $where = " where u.deleted = 0";
        if ($id != ""){
            $where = " where u.deleted = 0 AND g.id = '$id'";
        }
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.date_modified
              FROM groups g
              INNER JOIN users u ON g.owner_id = u.id $where";
        return self::queryTable($sql);
    }
    function viewGroupMembers($g_id) {
        $sql = "SELECT id, name, username, email
              FROM users
              WHERE deleted = 0 AND id in (
                SELECT u_id
                FROM user_group
                WHERE g_id = '$g_id')";
        return self::queryTable($sql);
    }
    function saveGroupMemberByEmail($email, $g_id, $ownerID) {
        $ret = array();
        $email = str_replace("'", "''", $email);
        $checkEmail = $this->queryAVal("SELECT id FROM users WHERE deleted=0 AND email = LCASE('" .$email. "')");
        if (!empty($checkEmail)){
            $u_id= $checkEmail;
            $checkGroup = $this->getUserGroupsById($g_id,$u_id);
            if (empty(json_decode($checkGroup))){
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
    function getAllUsers($ownerID) {
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])){
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if ($userRole == "admin"){
                $sql = "SELECT *
                      FROM users
                      WHERE id <> '$ownerID' AND deleted=0";
                return self::queryTable($sql);
            }
        }
    }


    function getUserGroups($ownerID) {
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.owner_id, ug.u_id
                  FROM groups g
                  INNER JOIN user_group ug ON  ug.g_id =g.id
                  INNER JOIN users u ON u.id = g.owner_id
                  where u.deleted = 0 AND ug.u_id = '$ownerID'";
        return self::queryTable($sql);
    }
    function getUserGroupsById($id, $ownerID) {
        $sql = "SELECT g.id, g.name, g.date_created, u.username, g.owner_id, ug.u_id
                  FROM groups g
                  INNER JOIN user_group ug ON  ug.g_id =g.id
                  INNER JOIN users u ON u.id = g.owner_id
                  where u.deleted = 0 AND ug.u_id = '$ownerID' AND g.id = '$id'";
        return self::queryTable($sql);
    }
    function getUserRole($ownerID) {
        $sql = "SELECT role
                  FROM users
                  where id = '$ownerID' AND deleted=0";
        return self::queryTable($sql);
    }

    function getUserRoleVal($ownerID) {
        $role = "";
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])){
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if (!empty($userRole)){
                $role = $userRole;
            }
        }
        return $role;
    }

    function insertGroup($name, $ownerID) {
        $sql = "INSERT INTO groups(name, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }

    function saveTestGroup($ownerID) {
        $g_id = $this->test_profile_group_id;
        $checkTestGroup = $this->getUserGroupsById($g_id,$ownerID);
        if (empty(json_decode($checkTestGroup))){
            return $this->insertUserGroup($g_id, $ownerID, $ownerID);
        } else {
            return $checkTestGroup;
        }
    }
    function insertUserGroup($g_id, $u_id, $ownerID) {
        $sql = "INSERT INTO user_group (g_id, u_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$g_id', '$u_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateGroup($id, $name, $ownerID) {
        $sql = "UPDATE groups SET name= '$name', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //    ----------- Projects   ---------
    function getProjects($id,$ownerID) {
        $where = " where u.deleted=0 AND p.deleted=0 AND p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
        if ($id != ""){
            $where = " where u.deleted=0 AND p.deleted=0 AND p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        }
        $sql = "SELECT DISTINCT p.id, p.name, p.summary, p.date_created, u.username, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
                  FROM project p
                  INNER JOIN users u ON p.owner_id = u.id
                  LEFT JOIN user_group ug ON p.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function insertProject($name, $summary, $ownerID) {
        $sql = "INSERT INTO project(name, summary, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$summary', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateProject($id, $name, $summary, $ownerID) {
        $sql = "UPDATE project SET name= '$name', summary= '$summary', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    //    ----------- Runs     ---------
    function insertRun($project_pipeline_id, $status, $attempt, $ownerID) {
        $sql = "INSERT INTO run (project_pipeline_id, run_status, attempt, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$project_pipeline_id', '$status', '$attempt', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function insertRunLog($project_pipeline_id, $uuid, $status, $ownerID) {
        $sql = "INSERT INTO run_log (project_pipeline_id, run_log_uuid, run_status, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$project_pipeline_id', '$uuid', '$status', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    //get maximum of $project_pipeline_id
    function updateRunLog($project_pipeline_id, $status, $duration, $ownerID) {
        $sql = "UPDATE run_log SET run_status='$status', duration='$duration', date_ended= now(), date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id' ORDER BY id DESC LIMIT 1";
        return self::runSQL($sql);
    }
    function getRunLog($project_pipeline_id) {
        $sql = "SELECT * FROM run_log WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function updateRunStatus($project_pipeline_id, $status, $ownerID) {
        $sql = "UPDATE run SET run_status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateRunAttempt($project_pipeline_id, $attempt, $ownerID) {
        $sql = "UPDATE run SET attempt= '$attempt', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function updateRunPid($project_pipeline_id, $pid, $ownerID) {
        $sql = "UPDATE run SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function getRunPid($project_pipeline_id) {
        $sql = "SELECT pid FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function getRunAttempt($project_pipeline_id) {
        $sql = "SELECT attempt FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }

    function getUpload($name,$ownerID) {
        $filename= "{$this->tmp_path}/uploads/$ownerID/$name";
        // get contents of a file into a string
        $handle = fopen($filename, "r");
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return json_encode($content);
    }
    function removeUpload($name,$ownerID) {
        $filename= "{$this->tmp_path}/uploads/$ownerID/$name";
        unlink($filename);
        return json_encode("file deleted");
    }
    public function getRun($project_pipeline_id,$ownerID) {
        $sql = "SELECT * FROM run WHERE deleted = 0 AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    public function getRunStatus($project_pipeline_id,$ownerID) {
        $sql = "SELECT run_status FROM run WHERE deleted = 0 AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    public function getCloudStatus($id, $cloud, $ownerID) {
        if ($cloud == "amazon"){
            $sql = "SELECT status, node_status FROM profile_amazon WHERE id = '$id'";
        } else if ($cloud == "google"){
            $sql = "SELECT status, node_status FROM profile_google WHERE id = '$id'";
        }
        return self::queryTable($sql);
    }
    function getAmazonPid($id,$ownerID) {
        $sql = "SELECT pid FROM profile_amazon WHERE id = '$id'";
        return self::queryTable($sql);
    }
    function sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $ownerID) {
        $ret = array();
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $executor = $cluDataArr[0]['executor'];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";

        //get $preSSH to load prerequisites and run qstat qdel
        $preSSH = "source /etc/profile && ";
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
        list($dolphin_path_real,$dolphin_publish_real) = $this->getDolphinPathReal($proPipeAll);
        $upCacheCmd = $this->getUploadCacheCmd($dolphin_path_real, $dolphin_publish_real, $profileType); 
        if (!empty($upCacheCmd)){
            $upCacheCmd = str_replace('$', '\$', $upCacheCmd);
            $upCacheCmd = "; $upCacheCmd";
        }
        if ($executor == "lsf" && $commandType == "checkRunPid"){
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH bjobs\" 2>&1 &");
            if (preg_match("/$pid/",$check_run)){
                return json_encode('running');
            } else {
                return json_encode('done');
            }
        } else if ($executor == "sge" && $commandType == "checkRunPid"){
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH qstat -j $pid\" 2>&1 &");
            if (preg_match("/job_number:/",$check_run)){
                return json_encode('running');
            } else {
                $this->updateRunPid($project_pipeline_id, "0", $ownerID);
                return json_encode('done');
            }
        } else if ($executor == "slurm" && $commandType == "checkRunPid"){
            $check_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH squeue --job $pid\" 2>&1 &");
            if (preg_match("/$pid/",$check_run)){
                return json_encode('running');
            } else {
                $this->updateRunPid($project_pipeline_id, "0", $ownerID);
                return json_encode('done');
            }
        } else if ($executor == "sge" && $commandType == "terminateRun"){
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH qdel $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "lsf" && $commandType == "terminateRun"){
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH bkill $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "slurm" && $commandType == "terminateRun"){
            $terminate_run = shell_exec("ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH scancel $pid $upCacheCmd\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "local" && $commandType == "terminateRun"){
            $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH ps -ef |grep nextflow.*/run$project_pipeline_id/ |grep -v grep | awk '{print \\\"kill \\\"\\\$2}' |bash $upCacheCmd\" 2>&1 &";
            $uuid = $this->getProPipeLastRunUUID($project_pipeline_id);
            $run_path_real = $this->getServerRunPath($uuid);
            $ret = $this->execute_cmd_logfile($cmd, $ret, "terminate_cmd_log", "terminate_cmd", "$run_path_real/serverlog.txt", "a");
            return json_encode('terminateCommandExecuted');
        } else if ($commandType == "getRemoteFileList"){
            $target_dir = $pid;
            $cmd = "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"$preSSH ls $target_dir \" 2>&1 & echo $! &";
            $file_list = shell_exec($cmd);
            return json_encode($file_list);
        }

    }

    function file_get_contents_utf8($fn) {
        $content = file_get_contents($fn);
        return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
    function getFileContent($uuid, $filename, $ownerID) {
        $file = "{$this->run_path}/$uuid/$filename";
        $content = "";
        if (file_exists($file)) {
            $content = $this->file_get_contents_utf8($file);
        }
        return json_encode($content);
    }
    function saveFileContent($text, $uuid, $filename, $ownerID) {
        if (preg_match("/\//i",$filename)){
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
    function deleteFile($uuid,$filename,$ownerID) {
        $file = "{$this->run_path}/$uuid/$filename";
        if (file_exists($file)) {
            unlink($file);
            return json_encode("file deleted.");
        }
        return json_encode("file not found.");
    }

    //$last_server_dir is last directory in $uuid folder: eg. run, pubweb
    function saveNextflowLog($files,$uuid, $last_server_dir, $profileType,$profileId,$project_pipeline_id,$dolphin_path_real,$ownerID) {
        $nextflow_log = "";
        $ret = array();
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        if (!empty($cluDataArr)){
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            if (!file_exists("{$this->run_path}/$uuid/$last_server_dir")) {
                mkdir("{$this->run_path}/$uuid/$last_server_dir", 0755, true);
            }
            // check uuid_file before downloading file
            $uuid_exist_cmd= "ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \" test  -f '$dolphin_path_real/.dolphinnext/uuid/$uuid' && echo 'INFO: Run package for $uuid exists.'\" 2>&1";
            $ret = $this->execute_cmd($uuid_exist_cmd, $ret, "uuid_exist_cmd_log", "uuid_exist_cmd");  
            if (preg_match("/INFO: Run package for $uuid exists\./", $ret["uuid_exist_cmd_log"])){
                if (preg_match("/s3:/i", $files[0])){
                    $fileList="";
                    foreach ($files as $item):
                    $fileList.="$item ";
                    endforeach;
                    $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
                    $amazon_cre_id = $proPipeAll[0]->{'amazon_cre_id'};
                    if (!empty($amazon_cre_id)){
                        $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
                        foreach($amz_data as $d){
                            $access = $d->amz_acc_key;
                            $d->amz_acc_key = trim($this->amazonDecode($access));
                            $secret = $d->amz_suc_key;
                            $d->amz_suc_key = trim($this->amazonDecode($secret));
                        }
                        $access_key = $amz_data[0]->{'amz_acc_key'};
                        $secret_key = $amz_data[0]->{'amz_suc_key'};
                        $cmd="s3cmd sync --access_key $access_key  --secret_key $secret_key $fileList {$this->run_path}/$uuid/$last_server_dir/ 2>&1 &";
                    }
                } else if (preg_match("/gs:/i", $files[0])){
                    $fileList="";
                    foreach ($files as $item):
                    $fileList.="$item ";
                    endforeach;
                    $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
                    $google_cre_id = $proPipeAll[0]->{'google_cre_id'};
                    if (!empty($google_cre_id)){
                        $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
                        $project_id = $goog_data[0]->{'project_id'};
                        $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
                        $cmd = "gcloud auth activate-service-account --project=$project_id --key-file=$credFile && gsutil cp -rcn $fileList {$this->run_path}/$uuid/$last_server_dir/ 2>&1 &";
                    }
                } else {
                    $fileList="";
                    foreach ($files as $item):
                    $fileList.="$connect:$item ";
                    endforeach;
                    $cmd="rsync -avzu -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $fileList {$this->run_path}/$uuid/$last_server_dir/ 2>&1 &"; 
                }
                $nextflow_log = shell_exec($cmd);
            }
        }
        if (!is_null($nextflow_log) && !empty($nextflow_log)){
            return json_encode("nextflow log saved");
        } else {
            return json_encode("logNotFound");
        }
    }

    function rsyncTransfer($localFile,$fileName, $target_dir, $upload_dir, $profileId, $profileType, $ownerID){
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $cmd_log = "";
        if (!empty($cluDataArr)){
            $fileName = str_replace(" ", "\\ ", $fileName);
            $localFile = str_replace(" ", "\\ ", $localFile);
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            $cmd="rsync --info=progress2 --partial-dir='$target_dir/.tmp_$fileName' -avzu --rsync-path='mkdir -p $target_dir && rsync' -e 'ssh {$this->ssh_settings} $ssh_port -i $userpky' $localFile $connect:$target_dir/ > $upload_dir/.$fileName 2>&1 & echo $! &"; 
            $cmd_log = shell_exec($cmd);
            if (!empty($cmd_log)){
                $cmd_log = trim($cmd_log);
                $pidFile = $upload_dir."/.".$fileName.".rsyncPid";
                file_put_contents($pidFile, $cmd_log);
            }
        }
        return $cmd_log;
    }

    function getRsyncStatus($fileName, $ownerID){
        $log = "";
        $tmp_path = $this->tmp_path;
        $upload_dir = "$tmp_path/uploads/{$ownerID}";
        $logfile = $upload_dir."/.".$fileName;
        if (file_exists($logfile)){
            $log = $this->readFile($logfile);
        }
        return json_encode($log);
    }

    function resetUpload($fileName, $ownerID){
        $tmp_path = $this->tmp_path;
        $upload_dir = "$tmp_path/uploads/{$ownerID}";
        $rsyncPidFile = $upload_dir."/.".$fileName.".rsyncPid";
        if (file_exists($rsyncPidFile)){
            $rsyncPid = $this->readFile($rsyncPidFile);
            if (!empty($rsyncPid)){
                $rsyncPid = trim($rsyncPid);
                exec("kill $rsyncPid",$res,$err);
                return json_encode($res);
            }
        }
        $res = "";
        return json_encode($res);
    }

    function retryRsync($fileName, $target_dir, $run_env, $email, $ownerID){
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
        $data = $this->rsyncTransfer($localFile,$fileName, $target_dir, $upload_dir, $profileId, $profileType, $ownerID);
        return json_encode($data);
    }

    function retrieve_remote_file_size($url){
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    public function getLsDir($dir, $profileType, $profileId, $amazon_cre_id, $google_cre_id, $project_pipeline_id, $ownerID) {
        $dir = trim($dir);
        $log = "";
        if (preg_match("/s3:/i", $dir)){
            if (!empty($amazon_cre_id)){
                $amz_data = json_decode($this->getAmzbyID($amazon_cre_id, $ownerID));
                foreach($amz_data as $d){
                    $access = $d->amz_acc_key;
                    $d->amz_acc_key = trim($this->amazonDecode($access));
                    $secret = $d->amz_suc_key;
                    $d->amz_suc_key = trim($this->amazonDecode($secret));
                }
                $access_key = $amz_data[0]->{'amz_acc_key'};
                $secret_key = $amz_data[0]->{'amz_suc_key'};
            }
            $lastChar = substr($dir, -1);
            if ($lastChar != "/"){
                $dir = $dir."/";
            }
            $cmd = "s3cmd ls --access_key $access_key  --secret_key $secret_key $dir 2>&1 &";
            $log = shell_exec($cmd);
            // For google storage queries
        } else if (preg_match("/gs:/i", $dir)){
            if (!empty($google_cre_id)){
                $goog_data = json_decode($this->getGooglebyID($google_cre_id, $ownerID));
                $project_id = $goog_data[0]->{'project_id'};
                $credFile = "{$this->goog_path}/{$ownerID}_{$google_cre_id}_goog.json";
            }
            $lastChar = substr($dir, -1);
            if ($lastChar != "/"){
                $dir = $dir."/";
            }
            $cmd = "gcloud auth activate-service-account --project=$project_id --key-file=$credFile && gsutil ls $dir 2>&1 &";
            $log = shell_exec($cmd);
            // For https http ftp queries
        } else if (preg_match("/:\/\//i", $dir)){
            $log = $this->retrieve_remote_file_size($dir);
            // if $log > -1 then file has been searched, it should be directory
            if ($log > 100){
                $subs = substr($dir, 0,strrpos($dir, '/')+1);
                $log = "Query failed! Please search directory: $subs instead of the file: $dir";
            } else {
                $lastChar = substr($dir, -1);
                if ($lastChar != "/"){
                    $dir = $dir."/";
                }
                $html = shell_exec("curl -s -k $dir 2>&1 &");
                $log = "";
                //ftp connection:
                if (preg_match("/ftp:\/\//i", $dir)){
                    $count = explode("\n", $html);
                    for ($i = 0; $i < count($count); ++$i) {
                        $block = explode(" ", $count[$i]);
                        $last = end($block);
                        $log .= $last."\n";
                    }
                    //http connection:
                } else {
                    //<a href="control_rep3.2.gz">control_rep3.2.gz</a>
                    $count = preg_match_all('/<a.*href="(.*)">.*<\/a>/i', $html, $files);
                    for ($i = 0; $i < $count; ++$i) {
                        if (!preg_match("/\//i", $files[1][$i]) && !preg_match("/;/i", $files[1][$i])){
                            $log .= $files[1][$i]."\n";
                        }
                    }
                }
            }
        } else {
            list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $perms = $cluDataArr[0]["perms"];
            $auto_workdir = $cluDataArr[0]["auto_workdir"];
            if (!empty($perms)){
                if ($perms == "15"){
                    if (empty($auto_workdir)){
                        return json_encode("Query failed! Generic work directory not defined in shared run environment.");
                    }
                    $rundir = $auto_workdir."/id".$project_pipeline_id;
                    $rundir = preg_replace('/(\/+)/','/',$rundir);
                    $dir = preg_replace('/(\/+)/','/',$dir);
                    if (strpos($dir, $rundir) === false) {
                        return json_encode("Query failed! You don't have permission to access this directory.");
                    }
                }
            }
            $ssh_own_id = $cluDataArr[0]["owner_id"];
            $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            $cmd="ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"ls -1 $dir\" 2>&1 &";
            $log = shell_exec($cmd);
        }
        if (!is_null($log) && isset($log)){
            return json_encode($log);
        } else {
            return json_encode("Query failed! Please check your query, connection profile or internet connection");
        }
    }

    function chkRmDirWritable($dir, $profileType, $profileId, $ownerID) {
        $dir = trim($dir);
        $log = "";
        list($connect, $ssh_port, $scp_port, $cluDataArr) = $this->getCluAmzData($profileId, $profileType, $ownerID);
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $ssh_own_id = $cluDataArr[0]["owner_id"];
        $userpky = "{$this->ssh_path}/{$ssh_own_id}_{$ssh_id}_ssh_pri.pky";
        if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
        $cmd="ssh {$this->ssh_settings} $ssh_port -i $userpky $connect \"mkdir -p $dir && [ -w $dir ] && echo 'writeable' || echo 'write permission denied'\" 2>&1 &";
        $log = shell_exec($cmd);
        //writeable\n will be successful $log
        if (!is_null($log) && isset($log)){
            return json_encode($log);
        } else {
            return json_encode("Query failed! Please check your query, connection profile or internet connection");
        }
    }

    function getSRRDataENA($srr_id) {
        $obj = new stdClass();
        if (preg_match("/SRR/i", $srr_id)){
            //use www.ebi.ac.uk api which is faster and accurate if exist
            $check_cmd = "curl -s 'https://www.ebi.ac.uk/ena/data/warehouse/filereport?result=read_run&fields=fastq_ftp&accession=$srr_id' 2>&1";
            $log = shell_exec($check_cmd);
            if (!empty($log)){
                $log = trim($log);
                $lines = explode("\n", $log);
                if (count($lines) == 2){
                    if (preg_match("/fastq_ftp/i", $lines[0]) && preg_match("/fastq/i", $lines[1])){
                        $obj->srr_id = trim($srr_id);
                        if (preg_match("/;/i", $lines[1])){
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
    function getSRRData($srr_id) {
        $obj = new stdClass();
        $command = "esearch -db sra -query $srr_id |efetch -format runinfo";
        $resText = shell_exec("$command 2>&1 & echo $! &");
        if (!empty($resText)){
            $resText = trim($resText);
            $lines = explode("\n", $resText);
            if (count($lines) == 3){
                $header = explode(",", $lines[1]);
                $vals = explode(",", $lines[2]);
                for ($i = 0; $i < count($header); $i++) {
                    $col = $header[$i];
                    if ($col == "Run"){
                        $obj->srr_id = trim($vals[$i]);
                        $retObj = $this->getSRRDataENA($obj->srr_id);
                        if (isset($retObj->collection_type)){
                            return $retObj;
                        }
                    } else if ($col == "LibraryLayout"){
                        if (trim($vals[$i]) == "PAIRED"){
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
    function getGeoData($geo_id, $ownerID) {
        $data = array();
        if (preg_match("/SRR/i", $geo_id) || preg_match("/GSM/i", $geo_id) || preg_match("/SRX/i", $geo_id)){
            $obj = $this->getSRRData($geo_id);
            $data[] = $obj;
        } else if (preg_match("/GSE/i", $geo_id)){
            $command = "esearch -db gds -query $geo_id | esummary | xtract -pattern DocumentSummary -element title Accession";
            $resText = shell_exec("$command 2>&1 & echo $! &");
            if (!empty($resText)){
                $resText = trim($resText);
                $lines = explode("\n", $resText);
                for ($i = 0; $i < count($lines); $i++) {
                    $cols = explode("\t", $lines[$i]);
                    if (count($cols) == 2){
                        $obj = $this->getSRRData($cols[1]);
                        $obj->name = trim(str_replace(" ","_",$cols[0]));
                        $data[] = $obj;
                        usleep(400000); //wait ncbi api limit 3query/sec
                    }
                }
            }
        }
        return json_encode($data);
    }
    public function readFileSubDir($path) {
        $scanned_directory = array_diff(scandir($path), array('..', '.'));
        foreach ($scanned_directory as $fileItem) {
            // skip '.' and '..' and .tmp hidden directories
            if ($fileItem[0] == '.')  continue;
            $fileItem = rtrim($path,'/') . '/' . $fileItem;
            // if dir found call again recursively
            if (is_dir($fileItem)) {
                foreach ($this->readFileSubDir($fileItem) as $childFileItem) {
                    yield $childFileItem;
                }
            } else {
                yield $fileItem;
            }
        }
    }

    //$last_server_dir is last directory in $uuid folder: eg. run, pubweb
    //$opt = "onlyfile", "filedir"
    public function getFileList($uuid, $last_server_dir, $opt) {
        $path= "{$this->run_path}/$uuid/$last_server_dir";
        $scanned_directory = array();
        if (file_exists($path)) {
            if ($opt == "filedir"){
                $scanned_directory = array_diff(scandir($path), array('..', '.'));
            } else if ($opt == "onlyfile"){
                //recursive read of all subdirectories
                foreach ($this->readFileSubDir($path) as $fileItem) {
                    //remove initial part of the path
                    $fileItemRet = preg_replace('/^' . preg_quote($path.'/', '/') . '/', '', $fileItem);
                    $scanned_directory[]=$fileItemRet;
                }
            }
        }
        return json_encode($scanned_directory);
    }

    function checkDescriptionBox($data, $uuid, $path){
        $name = "_Description"; //folder name
        $module = "run_description";
        $id = $module;
        $fileList = array_values((array)json_decode($this->getFileList($uuid, "$path/$name", "onlyfile")));
        $fileList = array_filter($fileList);
        if (empty($fileList)){
            $targetDir = "{$this->run_path}/$uuid/pubweb/_Description";
            //if not _Description not created before, create only for once
            if (!file_exists($targetDir)) {
                $this -> createReadmeMD($uuid);
                $fileList = array_values((array)json_decode($this->getFileList($uuid, "$path/$name", "onlyfile")));
                $fileList = array_filter($fileList);
            }
        }
        $out["fileList"] = $fileList;
        $out["name"] = $name;
        $out["pubWeb"] = $module;
        $out["id"] = $id."_".$module;
        array_unshift($data , $out); //push to the top of the array
        return $data;
    }

    //    ----------- Inputs, Project Inputs   ---------
    function getInputs($id,$ownerID) {
        $where = "";
        if ($id != ""){
            $where = " where i.id = '$id' ";
        }
        $sql = "SELECT DISTINCT i.id, i.name, IF(i.owner_id='$ownerID',1,0) as own
                  FROM input i
                  LEFT JOIN user_group ug ON i.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectInputs($project_id,$ownerID) {
        $where = " where pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM project_input pi
                  INNER JOIN input i ON i.id = pi.input_id
                  LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectFiles($project_id,$ownerID) {
        $where = " where (i.type = 'file' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM project_input pi
                  INNER JOIN input i ON i.id = pi.input_id
                  LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getPublicInputs($id) {
        $where = " WHERE i.perms = 63";
        if ($id != ""){
            $where = " where i.id = '$id' AND i.perms = 63";
        }
        $sql = "SELECT i.*, u.username
                  FROM input i
                  INNER JOIN users u ON i.owner_id = u.id
                  $where";
        return self::queryTable($sql);
    }
    function getPublicFiles($host) {
        $sql = "SELECT id as input_id, name, date_modified FROM input WHERE type = 'file' AND host = '$host' AND perms = 63 ";
        return self::queryTable($sql);
    }
    function getPublicValues($host) {
        $sql = "SELECT id as input_id, name, date_modified FROM input WHERE type = 'val' AND host = '$host' AND perms = 63 ";
        return self::queryTable($sql);
    }
    function getProjectValues($project_id,$ownerID) {
        $where = " where (i.type = 'val' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
        $sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                  FROM project_input pi
                  INNER JOIN input i ON i.id = pi.input_id
                  LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                  $where";
        return self::queryTable($sql);
    }
    function getProjectInput($id,$ownerID) {
        $where = " where pi.id = '$id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63)" ;
        $sql = "SELECT pi.id, i.id as input_id, i.name
                  FROM project_input pi
                  INNER JOIN input i ON i.id = pi.input_id
                  $where";
        return self::queryTable($sql);
    }
    function insertProjectInput($project_id, $input_id, $ownerID) {
        $sql = "INSERT INTO project_input(project_id, input_id, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$project_id', '$input_id', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function insertFile($name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID) {
        $sql = "INSERT INTO file(name, file_dir, file_type, files_used, collection_type, archive_dir, s3_archive_dir, gs_archive_dir, run_env, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$name', '$file_dir', '$file_type', '$files_used', '$collection_type', '$archive_dir', '$s3_archive_dir', '$gs_archive_dir', '$run_env', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function insertCollection($name, $ownerID) {
        $sql = "INSERT INTO collection (name, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$name', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateCollection($id, $name, $ownerID) {
        $sql = "UPDATE collection SET name='$name', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertFileCollection($f_id, $c_id, $ownerID) {
        $sql = "INSERT INTO file_collection (f_id, c_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$f_id', '$c_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function insertFileProject($f_id, $p_id, $ownerID) {
        $sql = "INSERT INTO file_project (f_id, p_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$f_id', '$p_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function checkInsertUrlInput($name, $type, $ownerID) {
        $url_id = "";
        if (!empty($name)) {
            $checkUrl = $this->checkInput($name,$type);
            $checkUrlData = json_decode($checkUrl,true);
            if (isset($checkUrlData[0])){
                $url_id = $checkUrlData[0]["id"];
            } else {
                //insert into input table
                $insertIn = $this->insertInput($name, $type, $ownerID);
                $insertInData = json_decode($insertIn,true);
                $url_id = $insertInData["id"];
            }
        }
        return $url_id;
    }
    function insertInput($name, $type, $ownerID) {
        $sql = "INSERT INTO input(name, type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
                  ('$name', '$type', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateInput($id, $name, $type, $ownerID) {
        $sql = "UPDATE input SET name='$name', type='$type', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    function insertPublicInput($name, $type, $host, $ownerID) {
        $sql = "INSERT INTO input(name, type, host, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$type', '$host', '$ownerID', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updatePublicInput($id, $name, $type, $host, $ownerID) {
        $sql = "UPDATE input SET name= '$name', type= '$type', host= '$host', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }

    // ------- Project Pipelines  ------
    function insertProjectPipeline($name, $project_id, $pipeline_id, $summary, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $ownerID) {
        $sql = "INSERT INTO project_pipeline(name, project_id, pipeline_id, summary, output_dir, profile, interdel, cmd, exec_each, exec_all, exec_all_settings, exec_each_settings, docker_check, docker_img, singu_check, singu_save, singu_img, exec_next_settings, docker_opt, singu_opt, amazon_cre_id, google_cre_id, publish_dir, publish_dir_check, withReport, withTrace, withTimeline, withDag, process_opt, onload, owner_id, date_created, date_modified, last_modified_user, perms)
                  VALUES ('$name', '$project_id', '$pipeline_id', '$summary', '$output_dir', '$profile', '$interdel', '$cmd', '$exec_each', '$exec_all', '$exec_all_settings', '$exec_each_settings', '$docker_check', '$docker_img', '$singu_check', '$singu_save', '$singu_img', '$exec_next_settings', '$docker_opt', '$singu_opt', '$amazon_cre_id', '$google_cre_id', '$publish_dir','$publish_dir_check', '$withReport', '$withTrace', '$withTimeline', '$withDag', '$process_opt', '$onload', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    function updateProjectPipeline($id, $name, $summary, $output_dir, $perms, $profile, $interdel, $cmd, $group_id, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $ownerID) {
        $sql = "UPDATE project_pipeline SET name='$name', summary='$summary', output_dir='$output_dir', perms='$perms', profile='$profile', interdel='$interdel', cmd='$cmd', group_id='$group_id', exec_each='$exec_each', exec_all='$exec_all', exec_all_settings='$exec_all_settings', exec_each_settings='$exec_each_settings', docker_check='$docker_check', docker_img='$docker_img', singu_check='$singu_check', singu_save='$singu_save', singu_img='$singu_img', exec_next_settings='$exec_next_settings', docker_opt='$docker_opt', singu_opt='$singu_opt', amazon_cre_id='$amazon_cre_id', google_cre_id='$google_cre_id', publish_dir='$publish_dir', publish_dir_check='$publish_dir_check', date_modified= now(), last_modified_user ='$ownerID', withReport='$withReport', withTrace='$withTrace', withTimeline='$withTimeline', withDag='$withDag',  process_opt='$process_opt', onload='$onload' WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function getProPipeLastRunUUID($project_pipeline_id) {
        return $this->queryAVal("SELECT last_run_uuid FROM project_pipeline WHERE id='$project_pipeline_id'");
    }
    function updateProPipeLastRunUUID($project_pipeline_id, $uuid) {
        $sql = "UPDATE project_pipeline SET last_run_uuid='$uuid' WHERE id='$project_pipeline_id'";
        return self::runSQL($sql);
    }
    function getProjectPipelines($id,$project_id,$ownerID,$userRole) {
        if ($id != ""){
            if ($userRole == "admin"){
                $where = " where pp.id = '$id' AND pip.deleted = 0 AND pp.deleted = 0";
            } else {
                $where = " where pp.id = '$id' AND pip.deleted = 0 AND pp.deleted = 0 AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
            }

            $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.project_id, pp.pipeline_id, pp.date_created, pp.date_modified, pp.owner_id, p.name as project_name, pp.output_dir, pp.profile, pp.interdel, pp.group_id, pp.exec_each, pp.exec_all, pp.exec_all_settings, pp.exec_each_settings, pp.perms, pp.docker_check, pp.docker_img, pp.singu_check, pp.singu_save, pp.singu_img, pp.exec_next_settings, pp.cmd, pp.singu_opt, pp.docker_opt, pp.amazon_cre_id, pp.google_cre_id, pp.publish_dir, pp.publish_dir_check, pp.withReport, pp.withTrace, pp.withTimeline, pp.withDag, pp.process_opt, pp.onload, IF(pp.owner_id='$ownerID',1,0) as own
                      FROM project_pipeline pp
                      INNER JOIN users u ON pp.owner_id = u.id
                      INNER JOIN project p ON pp.project_id = p.id
                      INNER JOIN biocorepipe_save pip ON pip.id = pp.pipeline_id
                      LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                      $where";
        } else {
            //for sidebar menu 
            if ($project_id != ""){
                $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.date_modified, IF(pp.owner_id='$ownerID',1,0) as own
                      FROM project_pipeline pp
                      INNER JOIN biocorepipe_save pip ON pip.id = pp.pipeline_id
                      INNER JOIN users u ON pp.owner_id = u.id
                      LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                      WHERE pp.deleted = 0 AND pip.deleted = 0 AND pp.project_id = '$project_id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
                //for run status page 
            } else {
                if ($userRole == "admin"){
                    $where = " WHERE pp.deleted = 0";
                } else {
                    $where = " WHERE pp.deleted = 0 AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
                }
                $sql = "SELECT DISTINCT rr.date_created as run_date_created, rr.run_status,  b.project_pipeline_id, b.name, b.summary, b.output_dir, b.run_log_id, b.pp_date_created, pip.name as pipeline_name, pip.rev_id as pipeline_rev, pip.id as pipeline_id, u.email, u.username, b.owner_id, b.own
                    FROM run_log rr
                    RIGHT JOIN (
                        SELECT DISTINCT pp.id as project_pipeline_id, pp.name,  pp.summary, max(r.id) as run_log_id,  pp.date_created as pp_date_created, pp.output_dir, pp.owner_id, IF(pp.owner_id='$ownerID',1,0) as own, pp.pipeline_id, pp.group_id
                        FROM project_pipeline pp
                        LEFT JOIN run_log r  ON r.project_pipeline_id=pp.id
                        LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                        $where
                        GROUP BY pp.id 
                    ) b ON rr.id = b.run_log_id
                        INNER JOIN biocorepipe_save pip ON pip.id = b.pipeline_id
                        INNER JOIN users u ON b.owner_id = u.id";
            }
        }
        return self::queryTable($sql);
    }
    function getExistProjectPipelines($pipeline_id,$ownerID) {
        $where = " where pp.deleted = 0 AND pp.pipeline_id = '$pipeline_id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
        $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, u.username, pp.date_modified, p.name as project_name
                    FROM project_pipeline pp
                    INNER JOIN users u ON pp.owner_id = u.id
                    INNER JOIN project p ON pp.project_id = p.id
                    LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    // ------- Project Pipeline Inputs  ------
    function insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url, $urlzip, $checkpath, $ownerID) {
        $sql = "INSERT INTO project_pipeline_input(collection_id, project_pipeline_id, input_id, project_id, pipeline_id, g_num, given_name, qualifier, url, urlzip, checkpath, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$collection_id', '$project_pipeline_id', '$input_id', '$project_id', '$pipeline_id', '$g_num', '$given_name', '$qualifier', '$url', '$urlzip', '$checkpath', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    function updateProPipeInput($id, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url, $urlzip, $checkpath,$ownerID) {
        $sql = "UPDATE project_pipeline_input SET collection_id='$collection_id', url='$url', urlzip='$urlzip', checkpath='$checkpath', project_pipeline_id='$project_pipeline_id', input_id='$input_id', project_id='$project_id', pipeline_id='$pipeline_id', g_num='$g_num', given_name='$given_name', qualifier='$qualifier', last_modified_user ='$ownerID'  WHERE id = $id";
        return self::runSQL($sql);
    }
    function duplicateProjectPipelineInput($new_id,$old_id,$ownerID) {
        $sql = "INSERT INTO project_pipeline_input(url, urlzip, checkpath, input_id, project_id, pipeline_id, g_num, given_name, qualifier, collection_id, project_pipeline_id, owner_id, perms, date_created, date_modified, last_modified_user)
                    SELECT url, urlzip, checkpath, input_id, project_id, pipeline_id, g_num, given_name, qualifier, collection_id, '$new_id', '$ownerID', '3', now(), now(),'$ownerID'
                    FROM project_pipeline_input
                    WHERE deleted=0 AND project_pipeline_id='$old_id'";
        return self::insTable($sql);
    }
    function duplicateProcess($new_process_gid, $new_name, $old_id, $ownerID) {
        $sql = "INSERT INTO process(process_uuid, process_rev_uuid, process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, owner_id, perms, date_created, date_modified, last_modified_user, rev_id, process_gid)
                    SELECT '', '', process_group_id, '$new_name', summary, script, script_header, script_footer, script_mode, script_mode_header, '$ownerID', '3', now(), now(),'$ownerID', '0', '$new_process_gid'
                    FROM process
                    WHERE id='$old_id'";
        return self::insTable($sql);
    }
    function createProcessRev($new_process_gid, $rev_comment, $rev_id, $old_id, $ownerID) {
        $sql = "INSERT INTO process(process_uuid, process_rev_uuid, process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, owner_id, perms, date_created, date_modified, last_modified_user, rev_id, process_gid, rev_comment)
                    SELECT process_uuid, '', process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, '$ownerID', '3', now(), now(),'$ownerID', '$rev_id', '$new_process_gid', '$rev_comment'
                    FROM process
                    WHERE id='$old_id'";
        return self::insTable($sql);
    }
    function duplicateProcessParameter($new_pro_id, $old_id, $ownerID){
        $sql = "INSERT INTO process_parameter(process_id, parameter_id, type, sname, operator, closure, reg_ex, optional, owner_id, perms, date_created, date_modified, last_modified_user)
                    SELECT '$new_pro_id', parameter_id, type, sname, operator, closure, reg_ex, optional, '$ownerID', '3', now(), now(),'$ownerID'
                    FROM process_parameter
                    WHERE process_id='$old_id'";
        return self::insTable($sql);
    }
    function getCollectionFiles($collection_id,$ownerID) {
        $where = " where f.deleted=0 AND fc.deleted=0 AND fc.c_id = '$collection_id' AND (f.owner_id = '$ownerID' OR f.perms = 63 OR (ug.u_id ='$ownerID' and f.perms = 15))";
        $sql = "SELECT DISTINCT f.*
                    FROM file f
                    INNER JOIN file_collection fc ON f.id=fc.f_id
                    LEFT JOIN user_group ug ON f.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    function getCollectionsOfFile($file_id,$ownerID) {
        $where = " where f.deleted=0 AND fc.deleted=0 AND fc.f_id = '$file_id' AND (f.owner_id = '$ownerID' OR f.perms = 63 OR (ug.u_id ='$ownerID' and f.perms = 15))";
        $sql = "SELECT DISTINCT fc.c_id
                    FROM file f
                    INNER JOIN file_collection fc ON f.id=fc.f_id
                    LEFT JOIN user_group ug ON f.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }

    function getProjectPipelineInputs($project_pipeline_id,$ownerID) {
        $where = " where (c.deleted = 0 OR c.deleted IS NULL) AND ppi.deleted=0 AND ppi.project_pipeline_id = '$project_pipeline_id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63 OR (ug.u_id ='$ownerID' and ppi.perms = 15))";
        $sql = "SELECT DISTINCT ppi.id, i.id as input_id, i.name, ppi.given_name, ppi.g_num, ppi.collection_id, c.name as collection_name, i2.name as url, i3.name as urlzip, i4.name as checkpath
                    FROM project_pipeline_input ppi
                    LEFT JOIN input i ON (i.id = ppi.input_id)
                    LEFT JOIN input i2 ON (i2.id = ppi.url)
                    LEFT JOIN input i3 ON (i3.id = ppi.urlzip)
                    LEFT JOIN input i4 ON (i4.id = ppi.checkpath)
                    LEFT JOIN collection c ON c.id = ppi.collection_id
                    LEFT JOIN user_group ug ON ppi.group_id=ug.g_id
                    $where";
        return self::queryTable($sql);
    }
    function getProjectPipelineInputsById($id,$ownerID) {
        $where = " where (c.deleted = 0 OR c.deleted IS NULL) AND ppi.deleted=0 AND ppi.id= '$id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63)" ;
        $sql = "SELECT ppi.id, ppi.qualifier, i.id as input_id, i.name, ppi.collection_id, c.name as collection_name, i2.name as url, i3.name as urlzip, i4.name as checkpath
                    FROM project_pipeline_input ppi
                    LEFT JOIN input i ON (i.id = ppi.input_id)
                    LEFT JOIN input i2 ON (i2.id = ppi.url)
                    LEFT JOIN input i3 ON (i3.id = ppi.urlzip)
                    LEFT JOIN input i4 ON (i3.id = ppi.checkpath)
                    LEFT JOIN collection c ON c.id = ppi.collection_id
                    $where";
        return self::queryTable($sql);
    }
    function insertProcessParameter($sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $optional, $perms, $group_id, $ownerID) {
        $sql = "INSERT INTO process_parameter(sname, process_id, parameter_id, type, closure, operator, reg_ex, optional, owner_id, date_created, date_modified, last_modified_user, perms, group_id)
                    VALUES ('$sname', '$process_id', '$parameter_id', '$type', '$closure', '$operator', '$reg_ex', '$optional', '$ownerID', now(), now(), '$ownerID', '$perms', '$group_id')";
        return self::insTable($sql);
    }

    function updateProcessParameter($id, $sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $optional, $perms, $group_id, $ownerID) {
        $sql = "UPDATE process_parameter SET sname='$sname', process_id='$process_id', parameter_id='$parameter_id', type='$type', closure='$closure', operator='$operator', reg_ex='$reg_ex', optional='$optional', last_modified_user ='$ownerID', perms='$perms', group_id='$group_id'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function removeProcessParameter($id) {
        $sql = "DELETE FROM process_parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function removeProcessParameterByParameterID($parameter_id) {
        $sql = "DELETE FROM process_parameter WHERE parameter_id = '$parameter_id'";
        return self::runSQL($sql);
    }
    function removeProcessParameterByProcessGroupID($process_group_id) {
        $sql = "DELETE process_parameter
                    FROM process_parameter
                    JOIN process ON process.id = process_parameter.process_id
                    WHERE process.process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
    function removeProcessParameterByProcessID($process_id) {
        $sql = "DELETE FROM process_parameter WHERE process_id = '$process_id'";
        return self::runSQL($sql);
    }
    //------- feedback ------
    function savefeedback($email,$message,$url) {
        $email = str_replace("'", "''", $email);
        $sql = "INSERT INTO feedback(email, message, url, date_created) VALUES
                      ('$email', '$message','$url', now())";
        return self::insTable($sql);
    }

    function sendEmail($from, $from_name, $to, $subject, $message) {
        $message = str_replace("\n","<br>",$message);
        $message = wordwrap($message, 70);
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: '.$from_name.' <'.$from.'>' . "\r\n";
        $ret = array();
        if(@mail($to, $subject, $message, $headers)){
            $ret['status'] = "sent";
        } else{
            $ret['status'] = "failed";
        }
        return json_encode($ret);
    }
    // --------- Pipeline -----------
    function getPipelineGroup($ownerID) {
        $sql = "SELECT pg.id, pg.group_name
                      FROM pipeline_group pg";
        return self::queryTable($sql);
    }
    function insertPipelineGroup($group_name, $ownerID) {
        $sql = "INSERT INTO pipeline_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    function updatePipelineGroup($id, $group_name, $ownerID) {
        $sql = "UPDATE pipeline_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function getEditDelPipelineGroups($ownerID) {
        $sql = "SELECT DISTINCT id, group_name
                      FROM pipeline_group pg
                      Where pg.owner_id = '$ownerID' AND id not in (SELECT pipeline_group_id FROM biocorepipe_save WHERE owner_id != '$ownerID' AND deleted = 0)";
        return self::queryTable($sql);
    }

    function getPublicPipelines() {
        $sql= "SELECT pip.id, pip.name, pip.summary, pip.pin, pip.pin_order, pip.script_pipe_header, pip.script_pipe_config, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id
                      FROM biocorepipe_save pip
                      INNER JOIN (
                        SELECT pipeline_gid, MAX(rev_id) rev_id
                        FROM biocorepipe_save
                        WHERE pin = 'true' AND perms = 63
                        GROUP BY pipeline_gid
                        ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid AND pip.deleted = 0";
        return self::queryTable($sql);
    }
    function getProcessData($ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.id, p.process_group_id, p.name, p.summary, p.script, p.script_header, p.script_footer, p.script_mode, p.script_mode_header, p.rev_id, p.perms, p.group_id, p.publish, IF(p.owner_id='$ownerID',1,0) as own FROM process p ";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.id, p.process_group_id, p.name, p.summary, p.script, p.script_header, p.script_footer, p.script_mode, p.script_mode_header, p.rev_id, p.perms, p.group_id, p.publish, IF(p.owner_id='$ownerID',1,0) as own
                        FROM process p
                        LEFT JOIN user_group ug ON p.group_id=ug.g_id
                        WHERE p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
        return self::queryTable($sql);
    }
    function getLastProPipeByUUID($id, $type, $ownerID) {
        if ($type == "process"){
            $table = "process";
        } else if ($type == "pipeline"){
            $table = "biocorepipe_save";
        }
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $sql="SELECT DISTINCT p.*, pg.group_name as process_group_name
                            FROM $table p
                            INNER JOIN {$type}_group pg ON p.{$type}_group_id = pg.id
                            INNER JOIN (
                              SELECT pr.{$type}_gid, MAX(pr.rev_id) rev_id
                              FROM $table pr WHERE pr.deleted = 0
                              GROUP BY pr.{$type}_gid
                              ) b ON p.rev_id = b.rev_id AND p.{$type}_gid=b.{$type}_gid AND p.deleted = 0 AND p.{$type}_uuid = '$id'";
                return self::queryTable($sql);
            }
            $where_pg = "(pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15))";
            $where_pr = "(pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
        }
        $sql="SELECT DISTINCT p.*, pg.group_name as {$type}_group_name
                          FROM $table p
                          LEFT JOIN user_group ug ON  p.group_id=ug.g_id
                          INNER JOIN {$type}_group pg
                          ON p.{$type}_group_id = pg.id and p.{$type}_uuid = '$id' AND $where_pg
                          INNER JOIN (
                            SELECT pr.{$type}_gid, MAX(pr.rev_id) rev_id
                            FROM $table pr
                            LEFT JOIN user_group ug ON pr.group_id=ug.g_id where $where_pr
                            GROUP BY pr.{$type}_gid
                            ) b ON p.rev_id = b.rev_id AND p.{$type}_gid=b.{$type}_gid AND p.deleted = 0";

        return self::queryTable($sql);
    }
    public function getProPipeDataByUUID($uuid, $rev_uuid, $type, $ownerID) {
        if ($type == "process"){
            $table = "process";
        } else if ($type == "pipeline"){
            $table = "biocorepipe_save";
        }
        if ($ownerID == ""){
            $ownerID ="''";
        }else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as {$type}_group_name, IF(p.owner_id='$ownerID',1,0) as own
                                  FROM $table p
                                  INNER JOIN users u ON p.owner_id = u.id
                                  INNER JOIN {$type}_group pg ON p.{$type}_group_id = pg.id
                                  where p.deleted = 0 AND p.{$type}_rev_uuid = '$rev_uuid' AND p.{$type}_uuid = '$uuid' ";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as {$type}_group_name, IF(p.owner_id='$ownerID',1,0) as own
                            FROM $table p
                            LEFT JOIN user_group ug ON p.group_id=ug.g_id
                            INNER JOIN users u ON p.owner_id = u.id
                            INNER JOIN {$type}_group pg ON p.{$type}_group_id = pg.id
                            where p.{$type}_rev_uuid = '$rev_uuid' AND p.{$type}_uuid = '$uuid' AND p.deleted = 0 AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }


    function getProcessDataById($id, $ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        }else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as process_group_name, IF(p.owner_id='$ownerID',1,0) as own
                                  FROM process p
                                  INNER JOIN users u ON p.owner_id = u.id
                                  INNER JOIN process_group pg ON p.process_group_id = pg.id
                                  where p.id = '$id'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.*, u.username, pg.group_name as process_group_name, IF(p.owner_id='$ownerID',1,0) as own
                            FROM process p
                            LEFT JOIN user_group ug ON p.group_id=ug.g_id
                            INNER JOIN users u ON p.owner_id = u.id
                            INNER JOIN process_group pg ON p.process_group_id = pg.id
                            where p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    public function getProcessRevision($process_gid,$ownerID) {
        if ($ownerID != ""){
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.id, p.rev_id, p.rev_comment, p.last_modified_user, p.date_created, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
                                  FROM process p
                                  WHERE p.process_gid = '$process_gid'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT p.id, p.rev_id, p.rev_comment, p.last_modified_user, p.date_created, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
                            FROM process p
                            LEFT JOIN user_group ug ON p.group_id=ug.g_id
                            WHERE p.process_gid = '$process_gid' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
        return self::queryTable($sql);
    }
    public function getPipelineRevision($pipeline_gid,$ownerID) {
        if ($ownerID != ""){
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms FROM biocorepipe_save pip WHERE pip.deleted = 0 AND pip.pipeline_gid = '$pipeline_gid'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms
                            FROM biocorepipe_save pip
                            LEFT JOIN user_group ug ON pip.group_id=ug.g_id
                            WHERE pip.deleted = 0 AND pip.pipeline_gid = '$pipeline_gid' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
        return self::queryTable($sql);
    }

    public function getInputsPP($id) {
        $sql = "SELECT pp.parameter_id, pp.sname, pp.id, pp.operator, pp.closure, pp.reg_ex, pp.optional, p.name, p.file_type, p.qualifier
                            FROM process_parameter pp
                            INNER JOIN parameter p ON pp.parameter_id = p.id
                            WHERE pp.process_id = '$id' and pp.type = 'input'";
        return self::queryTable($sql);
    }
    public function checkPipeline($process_id, $ownerID) {
        $sql = "SELECT id, name FROM biocorepipe_save WHERE deleted = 0 AND owner_id = '$ownerID' AND nodes LIKE '%\"$process_id\",\"%'";
        return self::queryTable($sql);
    }
    public function checkInput($name,$type) {
        $sql = "SELECT id, name FROM input WHERE name = BINARY '$name' AND type='$type'";
        return self::queryTable($sql);
    }
    public function checkProjectInput($project_id, $input_id) {
        $sql = "SELECT id FROM project_input WHERE input_id = '$input_id' AND project_id = '$project_id'";
        return self::queryTable($sql);
    }
    function checkFileProject($project_id, $file_id) {
        $sql = "SELECT id FROM file_project WHERE deleted=0 AND f_id = '$file_id' AND p_id = '$project_id'";
        return self::queryTable($sql);
    }
    function checkProPipeInput($project_id, $input_id, $pipeline_id, $project_pipeline_id) {
        $sql = "SELECT id FROM project_pipeline_input WHERE deleted =0 AND input_id = '$input_id' AND project_id = '$project_id' AND pipeline_id = '$pipeline_id' AND project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function checkPipelinePublic($process_id, $ownerID) {
        $sql = "SELECT id, name 
                FROM biocorepipe_save 
                WHERE deleted = 0 AND owner_id != '$ownerID' AND 
                CONCAT(',', process_list , ',')  LIKE '%,$process_id,%'";
        return self::queryTable($sql);
    }
    function checkProjectPipelinePublic($process_id, $ownerID) {
        $sql = "SELECT DISTINCT p.id, p.name
                FROM biocorepipe_save p
                INNER JOIN project_pipeline pp ON p.id = pp.pipeline_id
                WHERE p.deleted = 0 AND pp.deleted = 0 AND (pp.owner_id != '$ownerID') 
                AND CONCAT(',', p.process_list , ',')  LIKE '%,$process_id,%'";
        return self::queryTable($sql);
    }
    function checkParameter($parameter_id, $ownerID) {
        $sql = "SELECT DISTINCT pp.id, p.name
                            FROM process_parameter pp
                            INNER JOIN process p ON pp.process_id = p.id
                            WHERE (pp.owner_id = '$ownerID') AND pp.parameter_id = '$parameter_id'";
        return self::queryTable($sql);
    }
    function checkMenuGr($id) {
        $sql = "SELECT DISTINCT pg.id, p.name
                            FROM process p
                            INNER JOIN process_group pg ON p.process_group_id = pg.id
                            WHERE pg.id = '$id'";
        return self::queryTable($sql);
    }
    function checkPipeMenuGr($id) {
        $sql = "SELECT DISTINCT pg.id, p.name
                            FROM biocorepipe_save p
                            INNER JOIN pipeline_group pg ON p.pipeline_group_id = pg.id
                            WHERE p.deleted = 0 AND pg.id = '$id'";
        return self::queryTable($sql);
    }
    function checkUserWritePermRun($project_pipeline_id, $ownerID) {
        $sql = "SELECT DISTINCT id, name
                FROM project_pipeline
                WHERE deleted = 0 AND owner_id = '$ownerID' AND id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    function checkProject($pipeline_id, $ownerID) {
        $sql = "SELECT DISTINCT pp.id, p.name
                FROM project_pipeline pp
                INNER JOIN project p ON pp.project_id = p.id
                WHERE pp.deleted = 0 AND pp.owner_id = '$ownerID' AND pp.pipeline_id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    //Check if pipeline is ever used in projects that are group or public
    function checkProjectPublic($pipeline_id, $ownerID) {
        $sql = "SELECT DISTINCT pp.id, p.name, pip.pipeline_list
                FROM project_pipeline pp
                INNER JOIN project p ON pp.project_id = p.id
                INNER JOIN biocorepipe_save pip ON pp.pipeline_id = pip.id
                WHERE pp.deleted = 0 AND pp.owner_id != '$ownerID' AND (pp.pipeline_id = '$pipeline_id' OR CONCAT(',', pip.pipeline_list , ',')  LIKE '%,$pipeline_id,%' )";
        return self::queryTable($sql);
    }

    function getMaxProcess_gid() {
        $sql = "SELECT MAX(process_gid) process_gid FROM process";
        return self::queryTable($sql);
    }
    function getMaxPipeline_gid() {
        $sql = "SELECT MAX(pipeline_gid) pipeline_gid FROM biocorepipe_save WHERE deleted = 0";
        return self::queryTable($sql);
    }
    function getProcess_gid($process_id) {
        $sql = "SELECT process_gid FROM process WHERE id = '$process_id'";
        return self::queryTable($sql);
    }
    function getProcess_uuid($process_id) {
        $sql = "SELECT process_uuid FROM process WHERE id = '$process_id'";
        return self::queryTable($sql);
    }
    function getPipeline_gid($pipeline_id) {
        $sql = "SELECT pipeline_gid FROM biocorepipe_save WHERE id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    function getPipeline_uuid($pipeline_id) {
        $sql = "SELECT pipeline_uuid FROM biocorepipe_save WHERE deleted = 0 AND id = '$pipeline_id'";
        return self::queryTable($sql);
    }
    function getMaxRev_id($process_gid) {
        $sql = "SELECT MAX(rev_id) rev_id FROM process WHERE process_gid = '$process_gid'";
        return self::queryTable($sql);
    }
    function getMaxPipRev_id($pipeline_gid) {
        $sql = "SELECT MAX(rev_id) rev_id FROM biocorepipe_save WHERE deleted = 0 AND pipeline_gid = '$pipeline_gid'";
        return self::queryTable($sql);
    }
    function getOutputsPP($id) {
        $sql = "SELECT pp.parameter_id, pp.sname, pp.id, pp.operator, pp.closure, pp.reg_ex, pp.optional, p.name, p.file_type, p.qualifier
                            FROM process_parameter pp
                            INNER JOIN parameter p ON pp.parameter_id = p.id
                            WHERE pp.process_id = '$id' and pp.type = 'output'";
        return self::queryTable($sql);
    }
    //update if user owns the project
    function updateProjectGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project p
                            INNER JOIN project_pipeline pp ON p.id=pp.project_id
                            SET p.group_id='$group_id', p.perms='$perms', p.date_modified=now(), p.last_modified_user ='$ownerID'  WHERE pp.id = '$id' AND p.perms <= '$perms'";
        return self::runSQL($sql);
    }

    function updateProjectInputGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project_input pi
                            INNER JOIN project_pipeline_input ppi ON pi.input_id=ppi.input_id
                            SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE ppi.deleted=0 AND ppi.project_pipeline_id = '$id' and pi.perms <= '$perms'";
        return self::runSQL($sql);
    }

    function updateProjectPipelineInputGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE deleted=0 AND project_pipeline_id = '$id' AND perms <= '$perms'";
        return self::runSQL($sql);
    }

    function updatePipelineGroupPermByPipeId($id, $group_id, $perms, $ownerID) {
        error_log("updated pipeline_id:$id, perms:$perms, group_id:$group_id");
        $sql = "UPDATE biocorepipe_save pi
                            SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE pi.deleted=0 AND pi.id = '$id'";
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
    function validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms){
        $ret = 0;
        if ($curr_perms == 15 && $perms == 15 && $curr_group_id != $group_id && !empty($group_id)){
            $ret = 1;
        } else if ($curr_perms == 3 && $perms == 15 && !empty($group_id)){
            $ret = 1;
        } else if ($perms >15 &&  $curr_perms < 16){
            $ret = 1;
        } 
        return $ret;
    }


    function checkUpdtNeed($type, $id, $group_id, $perms, $ownerID){
        $ret = 0;
        $dataCheck = 0;
        if ($type == "pipeline"){
            $pipe = $this->loadPipeline($id,$ownerID);
            $pipeData = json_decode($pipe,true);
            if (!empty($pipeData[0])){
                $dataCheck = 1;
                $curr_group_id = $pipeData[0]["group_id"];
                $curr_perms = $pipeData[0]["perms"];
            } 
        }
        if (!empty($dataCheck)){
            $ret = $this->validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms);
        }
        return $ret;
    }

    //update if user owns the process
    function updateProcessGroupPerm($id, $group_id, $perms, $ownerID) {
        error_log("updated process_id:$id, perms:$perms, group_id:$group_id");
        $sql = "UPDATE process SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    function updateProcessParameterGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE process_parameter SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE process_id = '$id'";
        return self::runSQL($sql);
    }


    function checkUsed($table, $name, $id, $userID){
        $ret = 0;
        $warn = "";
        if ($table == "biocorepipe_save"){
            $data = json_decode($this->checkProjectPublic($id, $userID));
            if (!empty($data[0])){
                $ret = 1;
                $warn .= "Pipeline: $name already used in group/public projects.\n";
            } 
        } else if ($table == "process"){
            $checkPipelinePublic = json_decode($this->checkPipelinePublic($id, $userID));
            $checkProjectPipelinePublic = json_decode($this->checkProjectPipelinePublic($id, $userID));
            if (!empty($checkPipelinePublic[0])){
                $ret = 1;
                $warn .= "Process: $name already used in group/public pipelines.\n";
            } else if (!empty($checkProjectPipelinePublic[0])){
                $ret = 1;
                $warn .= "Process: $name already used in group/public projects.\n";
            }
        }
        return array($ret,$warn);
    }

    //check if $userID allowed to $mode=r or w to $id from $table
    //3 -> user r+w
    //11-> user r+w, group r
    //15-> user r+w, group r+w 
    //43=> user r+w, group r,   and other r
    //47=> user r+w, group r+w, and other r
    //63=> user r+w, group r+w, and other r (depricated)
    function checkUserPermission($table, $id, $userID, $mode){
        $ret = 0;
        $name = "";
        if ($mode == "w"){
            $where = "(pip.owner_id='$userID' OR (ug.u_id ='$userID' and pip.perms = 15))";
        } else if ($mode == "r"){
            $where = "(pip.owner_id='$userID' OR pip.perms = 63 OR pip.perms = 43 OR pip.perms = 47 OR (ug.u_id ='$userID' and pip.perms = 15))";
        }
        $sql = "SELECT DISTINCT pip.name, pip.perms, pip.group_id
                FROM $table pip
                INNER JOIN users u ON pip.owner_id = u.id
                LEFT JOIN user_group ug ON  pip.group_id=ug.g_id
                WHERE pip.deleted = 0 AND pip.id = '$id' AND $where ";
        $data = json_decode(self::queryTable($sql));
        if (!empty($data[0])){
            $ret = 1; 
            $name = $data[0]->{'name'};
        }
        return array($ret, $name);
    }

    function checkPermGroupEq($curr_group_id, $curr_perms, $exp_group_id, $exp_perms){
        $ret = 0;
        settype($curr_group_id, "integer");
        settype($curr_perms, "integer");
        settype($exp_group_id, "integer");
        settype($exp_perms, "integer");
        if ($curr_group_id == $exp_group_id && $curr_perms == $exp_perms){
            $ret = 1;
        }
        return $ret;
    }

    function checkUserOwnPerm($curr_ownerID, $ownerID){
        $ret = 0;
        settype($curr_ownerID, "integer");
        settype($ownerID, "integer");
        if ($curr_ownerID == $ownerID){
            $ret = 1;
        }
        return $ret;
    }


    //$type.match(/strict/) prevents skipping in case update is needed
    function permUpdtModule($listPermsDenied, $type, $table, $id, $curr_group_id, $curr_perms, $group_id, $perms, $curr_ownerID, $ownerID){
        // check if current value of the group and perm are same as expected values
        $checkEq = $this->checkPermGroupEq($curr_group_id, $curr_perms, $group_id, $perms);
        if ($checkEq != 1){
            // if user doesn't own the process then only allow to change its permission.
            $ownCheck = $this->checkUserOwnPerm($curr_ownerID, $ownerID);
            list($permCheck,$warnName) = $this->checkUserPermission($table, $id, $ownerID, "w");
            list($checkUsed,$warn) = $this->checkUsed($table, $warnName, $id, $ownerID);

            if (!empty($permCheck) && (empty($checkUsed) || $perms>$curr_perms) && !empty($ownCheck)){
                if ($type != "dry-run" && $type != "dry-run-strict"){
                    if ($table == "biocorepipe_save"){
                        $this->updatePipelineGroupPermByPipeId($id, $group_id, $perms, $ownerID);
                    } else if ($table == "process"){
                        $this->updateProcessGroupPerm($id, $group_id, $perms, $ownerID);
                        $this->updateProcessParameterGroupPerm($id, $group_id, $perms, $ownerID);
                    }
                }
            } else {
                if (!empty($warn)){
                    //allows skipping in case update not needed
                    if ($type != "strict" && $type != "dry-run-strict"){
                        $validateUpdtNeed = $this->validateUpdtNeed($curr_group_id, $curr_perms, $group_id, $perms);
                        if (!empty($validateUpdtNeed)){
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

    function recursivePermUpdtPipeline($type, $listPermsDenied, $pipeline_id, $group_id, $perms, $ownerID) {
        settype($pipeline_id, "integer");
        $pipe = $this->loadPipeline($pipeline_id,$ownerID);
        $pipeData = json_decode($pipe,true);
        if (!empty($pipeData[0])){
            $nodes = json_decode($pipeData[0]["nodes"]);
            $pipe_group_id = $pipeData[0]["group_id"];
            $pipe_perms = $pipeData[0]["perms"];
            $pipe_owner_id = $pipeData[0]["owner_id"];
            $listPermsDenied = $this->permUpdtModule($listPermsDenied, $type, "biocorepipe_save", $pipeline_id, $pipe_group_id, $pipe_perms, $group_id, $perms, $pipe_owner_id, $ownerID);
            if (!empty($nodes)){
                foreach ($nodes as $item):
                if ($item[2] !== "inPro" && $item[2] !== "outPro" ){
                    //pipeline modules
                    if (preg_match("/p(.*)/", $item[2], $matches)){
                        $pipeModId = $matches[1];
                        if (!empty($pipeModId)){
                            $listPermsDenied = $this->recursivePermUpdtPipeline($type, $listPermsDenied, $pipeModId, $group_id, $perms, $ownerID);
                        }
                        //processes
                    } else {
                        $proId = $item[2];
                        $process_data = json_decode($this->getProcessDataById($proId, $ownerID),true);
                        if (!empty($process_data[0])){
                            $pro_group_id = $process_data[0]["group_id"];
                            $pro_perms = $process_data[0]["perms"];
                            $pro_owner_id = $process_data[0]["owner_id"];
                            $listPermsDenied = $this->permUpdtModule($listPermsDenied, $type, "process", $proId, $pro_group_id, $pro_perms, $group_id, $perms, $pro_owner_id, $ownerID);
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

    public function updateUUID ($id, $type, $res){
        $update = "";
        if ($type == "process"){
            $table = "process";
            if (!empty($res->uuid) && !empty($res->rev_uuid)){
                $update = "process_uuid='$res->uuid', process_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "process_rev"){
            $table = "process";
            if (!empty($res->rev_uuid)){
                $update = "process_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "pipeline"){
            $table = "biocorepipe_save";
            if (!empty($res->uuid) && !empty($res->rev_uuid)){
                $update = "pipeline_uuid='$res->uuid', pipeline_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "pipeline_rev"){
            $table = "biocorepipe_save";
            if (!empty($res->rev_uuid)){
                $update = "pipeline_rev_uuid='$res->rev_uuid'";
            }
        } else if ($type == "run_log"){
            $table = "run_log";
            if (!empty($res->rev_uuid)){
                $update = "run_log_uuid='$res->rev_uuid'";
                $targetDir = "{$this->tmp_path}/api";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
            }
        }
        $sql = "UPDATE $table SET $update  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    public function getUUIDLocal($type){
        $params=[];
        $params["type"]=$type;
        $myClass = new funcs();
        $res= (object)$myClass->getUUID($params);
        return $res;
    }

    function moveFile($type, $from, $to, $ownerID){
        $res = false;
        if ($type == "pubweb"){
            $from = "{$this->run_path}/$from";
            $to = "{$this->run_path}/$to";
        }
        if (file_exists($from)) {
            $res = rename($from, $to);
        }

        return json_encode($res);
    }

    public function tsvConvert($tsv, $format){
        ini_set('memory_limit','900M');
        $tsv = trim($tsv);
        $lines = explode("\n", $tsv);
        $header = explode("\t", $lines[0]);
        $data = array();
        for ($i = 1; $i < count($lines); $i++) {
            $obj = new stdClass();
            $currentline = explode("\t", $lines[$i]);
            for ($j = 0; $j < count($header); $j++) {
                $name = $header[$j];
                $obj->$name = $currentline[$j];
            }
            $data[] = $obj;
        }
        return $data;
    }


    public function callDebrowser($uuid, $dir, $filename){
        $targetDir = "{$this->run_path}/$uuid/pubweb/$dir";
        $targetFile = "{$targetDir}/{$filename}";
        $targetJson = "{$targetDir}/.{$filename}";
        $tsv= file_get_contents($targetFile);
        $array = $this->tsvConvert($tsv, "json");
        file_put_contents($targetJson, json_encode($array));
        return json_encode("$dir/.{$filename}");
    }
    public function callRmarkdown($type, $uuid, $text, $dir, $filename){
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
            if ($type == "rmdtext"){
                $format = "html";
            } else if ($type == "rmdpdf"){
                $format = "pdf";
            }
            $pUUID = uniqid();
            $log = "{$targetDir}/{$filename}.log{$pUUID}";
            $response = "{$targetDir}/{$filename}.curl{$pUUID}";
            $file = "{$targetDir}/{$filename}.{$format}{$pUUID}";
            $err = "{$targetDir}/{$filename}.{$format}.err{$pUUID}";
            $url =  OCPU_URL."/ocpu/library/markdownapp/R/".$type;
            $cmd = "(curl '$url' -H \"Content-Type: application/json\" -k -d '{\"text\":$text}' -o $response > $log 2>&1) & echo \$!";
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
        if (!empty($pUUID)){
            for( $i= 0 ; $i < 100 ; $i++ ){
                sleep(1);
                $resText = $this->readFile($response);
                if (!empty($resText)){
                    unlink($response);
                    break;
                }
                if ($i <5){
                    sleep(1);
                } else {
                    sleep(4);
                }
            }
            $ret = "";
            if (!empty($resText)){
                $lines = explode("\n", $resText);
                foreach ($lines as $lin):
                if ($type == "rmdtext" && preg_match("/.*output.html/", $lin, $matches)){
                    $ret = OCPU_URL.$lin;
                    break;
                } else if ($type == "rmdpdf" && preg_match("/.*output.pdf/", $lin, $matches)){
                    $ret = OCPU_URL.$lin;
                    break;
                }
                endforeach;

                if (empty($ret)){
                    $errorCheck =true;
                    $errorText = $resText;
                }
                if (!empty($ret)){
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    if (file_exists($err)) {
                        unlink($err);
                    }
                    exec("curl '$ret' -o \"{$file}\" > /dev/null 2>&1 &", $res, $exit);
                } else {
                    $errorCheck =true;
                }
            } else {
                $errorCheck =true;
                $errorText = "Timeout error";
            }
            if ($errorCheck == true){
                $fp = fopen($err, 'w');
                fwrite($fp, $errorText);
                fclose($fp);
            }
        }
    }

    public function getUUIDAPI($data,$type,$id){
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
        $request = CENTRAL_API_URL."/api/service.php?func=getUUID&type=$type";
        exec("curl '$request' -o $uuidPath > /dev/null 2>&1 &", $res, $exit);
        for( $i= 0 ; $i < 4 ; $i++ ){
            sleep(5);
            $uuidFile = $this->readFile($uuidPath);
            if (!empty($uuidFile)){
                $res = json_decode($uuidFile);
                unlink($uuidPath);
                break;
            }
        }
        if (!isset($res->rev_uuid)){
            //call local functions to get uuid
            $params=[];
            $params["type"]=$type;
            $myClass = new funcs();
            $res= (object)$myClass->getUUID($params);
            if (isset($res->rev_uuid)){
                $this->updateUUID($id, $type, $res);
            }
        } else {
            $this->updateUUID($id, $type, $res);
        }
    }


    public function convert_array_to_obj_recursive($a) {
        if (is_array($a) ) {
            foreach($a as $k => $v) {
                if (is_integer($k)) {
                    // only need this if you want to keep the array indexes separate
                    // from the object notation: eg. $o->{1}
                    $a['index'][$k] = $this->convert_array_to_obj_recursive($v);
                }
                else {
                    $a[$k] = $this->convert_array_to_obj_recursive($v);
                }
            }

            return (object) $a;
        }

        // else maintain the type of $a
        return $a;
    }


    //if you add new field here, please consider import/export functionality(import.js - itemOrder)
    function saveAllPipeline($newObj,$ownerID) {
        $name =  $newObj->{"name"};
        $id = $newObj->{"id"};
        $nodes = json_encode($newObj->{"nodes"});
        $mainG = "{\'mainG\':".json_encode($newObj->{"mainG"})."}";
        $edges = "{\'edges\':".json_encode($newObj->{"edges"})."}";
        $summary = addslashes(htmlspecialchars(urldecode($newObj->{"summary"}), ENT_QUOTES));
        $group_id = $newObj->{"group_id"};
        $perms = $newObj->{"perms"};
        $pin = $newObj->{"pin"};
        $pin_order = $newObj->{"pin_order"};
        $publish = $newObj->{"publish"};
        $script_pipe_header = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_header"}), ENT_QUOTES));
        $script_pipe_footer = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_footer"}), ENT_QUOTES));
        $script_pipe_config = addslashes(htmlspecialchars(urldecode($newObj->{"script_pipe_config"}), ENT_QUOTES));
        $script_mode_header = $newObj->{"script_mode_header"};
        $script_mode_footer = $newObj->{"script_mode_footer"};
        $pipeline_group_id = $newObj->{"pipeline_group_id"};
        $process_list = $newObj->{"process_list"};
        $pipeline_list = $newObj->{"pipeline_list"};
        $publish_web_dir = $newObj->{"publish_web_dir"};
        $pipeline_gid = isset($newObj->{"pipeline_gid"}) ? $newObj->{"pipeline_gid"} : "";
        if (empty($pipeline_gid)) {
            $max_gid = json_decode($this->getMaxPipeline_gid(),true)[0]["pipeline_gid"];
            settype($max_gid, "integer");
            if (!empty($max_gid) && $max_gid != 0) {
                $pipeline_gid = $max_gid +1;
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
        settype($publish, "integer");
        settype($pin_order, "integer");
        settype($id, 'integer');

        if ($id > 0){
            $sql = "UPDATE biocorepipe_save set name = '$name', edges = '$edges', summary = '$summary', mainG = '$mainG', nodes ='$nodes', date_modified = now(), group_id = '$group_id', perms = '$perms', pin = '$pin', publish = '$publish', script_pipe_header = '$script_pipe_header', script_pipe_config = '$script_pipe_config', script_pipe_footer = '$script_pipe_footer', script_mode_header = '$script_mode_header', script_mode_footer = '$script_mode_footer', pipeline_group_id='$pipeline_group_id', process_list='$process_list', pipeline_list='$pipeline_list', publish_web_dir='$publish_web_dir', pin_order = '$pin_order', last_modified_user = '$ownerID' where id = '$id'";
        }else{
            $sql = "INSERT INTO biocorepipe_save(owner_id, summary, edges, mainG, nodes, name, pipeline_gid, rev_comment, rev_id, date_created, date_modified, last_modified_user, group_id, perms, pin, pin_order, publish, script_pipe_header, script_pipe_footer, script_mode_header, script_mode_footer,pipeline_group_id,process_list,pipeline_list, pipeline_uuid, pipeline_rev_uuid, publish_web_dir, script_pipe_config) VALUES ('$ownerID', '$summary', '$edges', '$mainG', '$nodes', '$name', '$pipeline_gid', '$rev_comment', '$rev_id', now(), now(), '$ownerID', '$group_id', '$perms', '$pin', '$pin_order', $publish, '$script_pipe_header', '$script_pipe_footer', '$script_mode_header', '$script_mode_footer', '$pipeline_group_id', '$process_list', '$pipeline_list', '$pipeline_uuid', '$pipeline_rev_uuid', '$publish_web_dir', '$script_pipe_config')";
        }
        return self::insTable($sql);

    }
    public function getSavedPipelines($ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        } else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id, pip.pipeline_gid
                                  FROM biocorepipe_save pip
                                  INNER JOIN users u ON pip.deleted=0 AND pip.owner_id = u.id";
                    return self::queryTable($sql);
                }
            }
        }
        $where = " where pip.deleted=0 AND pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15)";
        $sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id, pip.pipeline_gid
                            FROM biocorepipe_save pip
                            INNER JOIN users u ON pip.owner_id = u.id
                            LEFT JOIN user_group ug ON pip.group_id=ug.g_id
                            $where";
        return self::queryTable($sql);
    }
    public function loadPipeline($id,$ownerID) {
        if ($ownerID != ""){
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "select pip.*, u.username, pg.group_name as pipeline_group_name, IF(pip.owner_id='$ownerID',1,0) as own
                                  FROM biocorepipe_save pip
                                  INNER JOIN users u ON pip.owner_id = u.id
                                  INNER JOIN pipeline_group pg ON pip.pipeline_group_id = pg.id
                                  where pip.deleted=0 AND pip.id = '$id'";
                    return self::queryTable($sql);
                }
            }
        }
        $sql = "select pip.*, u.username, pg.group_name as pipeline_group_name, IF(pip.owner_id='$ownerID',1,0) as own
                            FROM biocorepipe_save pip
                            INNER JOIN users u ON pip.owner_id = u.id
                            INNER JOIN pipeline_group pg ON pip.pipeline_group_id = pg.id
                            LEFT JOIN user_group ug ON pip.group_id=ug.g_id
                            where pip.deleted=0 AND pip.id = '$id' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
        return self::queryTable($sql);
    }
    public function removePipelineById($id) {
        $sql = "UPDATE biocorepipe_save SET deleted = 1, date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function savePipelineDetails($id, $summary,$group_id, $perms, $pin, $pin_order, $publish, $pipeline_group_id, $ownerID) {
        $sql = "UPDATE biocorepipe_save SET summary='$summary', group_id='$group_id', publish='$publish', perms='$perms', pin='$pin', pin_order='$pin_order', last_modified_user = '$ownerID', pipeline_group_id='$pipeline_group_id'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function exportPipeline($id, $ownerID, $type, $layer) {
        $layer += 1;
        $data = $this->loadPipeline($id,$ownerID);
        $new_obj = json_decode($data,true);
        $new_obj[0]["layer"] = $layer;
        $final_obj = [];
        if ($type == "main"){
            $final_obj["main_pipeline_{$id}"]=$new_obj[0];
        } else {
            $final_obj["pipeline_module_{$id}"]=$new_obj[0];
        }
        if (!empty($new_obj[0]["nodes"])){
            $nodes = json_decode($new_obj[0]["nodes"]);
            foreach ($nodes as $item):
            if ($item[2] !== "inPro" && $item[2] !== "outPro"){
                //pipeline modules
                if (preg_match("/p(.*)/", $item[2], $matches)){
                    $pipeModId = $matches[1];
                    if (!empty($pipeModId)){
                        $pipeModule = [];
                        settype($pipeModId, "integer");
                        $pipeModule = $this->exportPipeline($pipeModId, $ownerID, "pipeModule",$layer);
                        $pipeModuleDec = json_decode($pipeModule,true);
                        $final_obj = array_merge($pipeModuleDec, $final_obj);
                    }
                    //processes
                } else {
                    $process_id = $item[2];
                    $pro_para_in = $this->getInputsPP($process_id);
                    $pro_para_out = $this->getOutputsPP($process_id);
                    $process_data = $this->getProcessDataById($process_id, $ownerID);
                    $final_obj["pro_para_inputs_$process_id"]=$pro_para_in;
                    $final_obj["pro_para_outputs_$process_id"]=$pro_para_out;
                    $final_obj["process_{$process_id}"]=$process_data;
                }
            }
            endforeach;
        }
        return json_encode($final_obj);
    }

}
