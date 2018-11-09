<?php
require_once(__DIR__."/../config/config.php");
class dbfuncs {

    private $dbhost = DBHOST;
    private $db = DB;
    private $dbuser = DBUSER;
    private $dbpass = DBPASS;
    private $dbport = DBPORT;
    private $run_path = RUNPATH;
    private $ssh_path = SSHPATH;
    private $ssh_settings = "-oStrictHostKeyChecking=no -q -oChallengeResponseAuthentication=no -oBatchMode=yes -oPasswordAuthentication=no -oConnectTimeout=3";
    private $amz_path = AMZPATH;
    private $amazon = AMAZON;
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
            } else if (isset($row['process_parameter_name'])){
            $row['process_parameter_name'] = htmlspecialchars_decode($row['process_parameter_name'], ENT_QUOTES);
            }
            $data[]=$row;
        }

        $res->close();
     }
     return json_encode($data);
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

    function writeLog($project_pipeline_id,$text,$mode, $filename){
        $file = fopen("../{$this->run_path}/run{$project_pipeline_id}/$filename", $mode);
        fwrite($file, $text."\n");
        fclose($file);
    }
    //$img: path of image
    function imageCmd($img, $singu_save, $type, $profileType,$profileId,$ownerID){
        if ($type == 'singularity'){
            preg_match("/shub:\/\/(.*)/", $img, $matches);
              if ($matches[1] != ''){
                  $singuPath = '~';
                  if ($profileType == "amazon"){
                    $amzData=$this->getProfileAmazonbyID($profileId, $ownerID);
                    $amzDataArr=json_decode($amzData,true);
                    $singuPath = $amzDataArr[0]["shared_storage_mnt"]; // /mnt/efs
                  }
                  $imageName = str_replace("/","-",$matches[1]);
                  $image = $singuPath +'/.dolphinnext/singularity/' + $imageName;
                  if ($singu_save == "true"){
                    $cmd = "mkdir -p $singuPath/.dolphinnext/singularity && cd $singuPath/.dolphinnext/singularity && [ -e ".$imageName.".simg ] && rm ".$imageName.".simg && singularity pull --name ".$imageName.".simg ".$img;
                  } else {
                    $cmd = "mkdir -p $singuPath/.dolphinnext/singularity && cd $singuPath/.dolphinnext/singularity && singularity pull --name ".$imageName.".simg ".$img;
                  }
                return $cmd;
              }
        } 
    }

    //type:w creates new file
    function createDirFile ($pathDir, $fileName, $type, $text){
        if ($pathDir != ""){
            mkdir("$pathDir", 0755, true);
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
        $handle = fopen($path, 'r');
        $content = fread($handle, filesize($path));
        fclose($handle);
        return $content;
    }

     //get nextflow input parameters
    function getNextInputs ($executor, $project_pipeline_id, $ownerID ){
        $allinputs = json_decode($this->getProjectPipelineInputs($project_pipeline_id, $ownerID));
        $next_inputs="";
        if ($executor === "local"){
            foreach ($allinputs as $inputitem):
                $next_inputs.="--".$inputitem->{'given_name'}." \\\"".$inputitem->{'name'}."\\\" ";
            endforeach;
        } else if ($executor !== "local"){
            if (!empty($allinputs)){
                foreach ($allinputs as $inputitem):
                    $next_inputs.="--".$inputitem->{'given_name'}." \\\\\\\"".$inputitem->{'name'}."\\\\\\\" ";
                endforeach;
            }
        }
        return $next_inputs;

    }

    //get nextflow executor parameters
    function getNextExecParam($project_pipeline_id,$profileType,$profileId, $ownerID){
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID));
        $outdir = $proPipeAll[0]->{'output_dir'};
        $proPipeCmd = $proPipeAll[0]->{'cmd'};
        $jobname = $proPipeAll[0]->{'pp_name'};
        $singu_check = $proPipeAll[0]->{'singu_check'};
        if ($singu_check == "true"){
            $singu_img = $proPipeAll[0]->{'singu_img'};
            $singu_save = $proPipeAll[0]->{'singu_save'};
            $imageCmd = $this->imageCmd($singu_img, $singu_save, 'singularity', $profileType,$profileId,$ownerID);
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
            $reportOptions .= " -with-dag";
        }
        return array($outdir, $proPipeCmd, $jobname, $singu_check, $singu_img, $imageCmd, $reportOptions);
    }
    

    //get username and hostname and exec info for connection
    function getNextConnectExec($profileId,$ownerID, $profileType){
        if ($profileType == "cluster"){
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["username"]."@".$cluDataArr[0]["hostname"];
        } else if ($profileType == "amazon"){
            $cluData=$this->getProfileAmazonbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["ssh"];
        }
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
        return array($connect, $next_path, $profileCmd, $executor,$next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job,$ssh_id);
    }
    
    function getPreCmd ($profileType,$profileCmd,$proPipeCmd, $imageCmd){
    $profile_def = "";
    if ($profileType == "amazon"){
        $profile_def = "source /etc/profile && source ~/.bash_profile";
    } 
    //combine pre-run cm
    $arr = array($profile_def, $profileCmd, $proPipeCmd, $imageCmd);
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
        if ($time >= 1440) {
            $time = 1440;
        }
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
    function cleanName($name){
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
        $name = substr($name, 0, 9);
        return $name;
    }
	
	function getMemory($next_memory, $executor){
		if ($executor == "sge"){
			if (!empty($next_memory)){
				$memoryText = "#$ -l h_vmem=".$next_memory."G\\n";
			} else {
				$memoryText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $memoryText;
    }
	function getJobName($jobname, $executor){
        $jobname = $this->cleanName($jobname);
		if ($executor == "sge"){
			if (!empty($jobname)){
				$jobNameText = "#$ -N $jobname\\n";
			} else {
				$jobNameText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $jobNameText;
    }
	function getTime($next_time, $executor){
		if ($executor == "sge"){
			if (!empty($next_time)){
            	//$next_time is in minutes convert into hours and minutes.
        		$next_time = $this->convertToHoursMins($next_time);
				$timeText = "#$ -l h_rt=$next_time:00\\n";
			} else {
				$timeText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $timeText;
    }
	function getQueue($next_queue, $executor){
		if ($executor == "sge"){
			if (!empty($next_queue)){
				$queueText = "#$ -q $next_queue\\n";
			} else {
				$queueText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $queueText;
    }
	function getNextCluOpt($next_clu_opt, $executor){
		if ($executor == "sge"){
			if (!empty($next_clu_opt)){
				$next_clu_optText = "#$ $next_clu_opt\\n";
			} else {
				$next_clu_optText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $next_clu_optText;
    }
	function getCPU($next_cpu, $executor){
		if ($executor == "sge"){
			if (!empty($next_cpu)){
				$cpuText = "#$ -l slots=$next_cpu\\n";
			} else {
				$cpuText = "";
			}
		} else if ($executor == "lsf"){
		}
        return $cpuText;
    }

    //get all nextflow executor text
    function getExecNextAll($executor, $dolphin_path_real, $next_path_real, $next_inputs, $next_queue, $next_cpu,$next_time,$next_memory,$jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId,$ownerID) {
		if ($runType == "resumerun"){
			$runType = "-resume";
		} else {
			$runType = "";
		}
		
    //for lsf "bsub -q short -n 1  -W 100 -R rusage[mem=32024]";
        if ($executor == "local"){
            if ($executor_job == 'ignite'){
                $exec_next_all = "cd $dolphin_path_real && $next_path_real $dolphin_path_real/nextflow.nf -w $dolphin_path_real/work -process.executor ignite $next_inputs $runType $reportOptions > $dolphin_path_real/log.txt ";
            }else {
                $exec_next_all = "cd $dolphin_path_real && $next_path_real $dolphin_path_real/nextflow.nf $next_inputs $runType $reportOptions > $dolphin_path_real/log.txt ";
            }
        } else if ($executor == "lsf"){
            //convert gb to mb
            settype($next_memory, 'integer');
            $next_memory = $next_memory*1000;
            //-J $jobname
            $jobname = $this->cleanName($jobname);
            $exec_string = "bsub $next_clu_opt -q $next_queue -J $jobname -n $next_cpu -W $next_time -R rusage[mem=$next_memory]";
            $exec_next_all = "cd $dolphin_path_real && $exec_string \\\"$next_path_real $dolphin_path_real/nextflow.nf $next_inputs $runType $reportOptions > $dolphin_path_real/log.txt\\\"";
        } else if ($executor == "sge"){
            $jobnameText = $this->getJobName($jobname, $executor);
            $memoryText = $this->getMemory($next_memory, $executor);
            $timeText = $this->getTime($next_time, $executor);
            $queueText = $this->getQueue($next_queue, $executor);
            $clu_optText = $this->getNextCluOpt($next_clu_opt, $executor);
            $cpuText = $this->getCPU($next_cpu, $executor);
			//-j y ->Specifies whether or not the standard error stream of the job is merged into the standard output stream.
			$sgeRunFile= "printf '#!/bin/bash \\n#$ -j y\\n#$ -V\\n#$ -notify\\n#$ -wd $dolphin_path_real\\n#$ -o $dolphin_path_real/.dolphinnext.log\\n".$jobnameText.$memoryText.$timeText.$queueText.$clu_optText.$cpuText."$next_path_real $dolphin_path_real/nextflow.nf $next_inputs $runType $reportOptions > $dolphin_path_real/log.txt"."'> $dolphin_path_real/.dolphinnext.run";
            
			$exec_string = "qsub $dolphin_path_real/.dolphinnext.run";
            $exec_next_all = "cd $dolphin_path_real && $sgeRunFile && $exec_string";
        } else if ($executor == "slurm"){
        } else if ($executor == "ignite"){
        }
    return $exec_next_all;
    }


    function initRun($project_pipeline_id, $configText, $nextText, $profileType, $profileId, $amazon_cre_id, $ownerID){
        //if  $amazon_cre_id is defined append the aws credentials into nextflow.config
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
            $configText.= "aws{\n";
            $configText.= "   accessKey = '$access_key'\n";
            $configText.= "   secretKey = '$secret_key'\n";
            $configText.= "   region = '$default_region'\n";
            $configText.= "}\n";
        }
        //create folders
        mkdir("../{$this->run_path}/run{$project_pipeline_id}", 0755, true);
        $file = fopen("../{$this->run_path}/run{$project_pipeline_id}/nextflow.log", 'w');//creates new file
        fclose($file);
        chmod("../{$this->run_path}/run{$project_pipeline_id}/nextflow.log", 0755);
        $file = fopen("../{$this->run_path}/run{$project_pipeline_id}/nextflow.nf", 'w');//creates new file
        fwrite($file, $nextText);
        fclose($file);
        chmod("../{$this->run_path}/run{$project_pipeline_id}/nextflow.nf", 0755);
        $file = fopen("../{$this->run_path}/run{$project_pipeline_id}/nextflow.config", 'w');//creates new file
        fwrite($file, $configText);
        fclose($file);
        chmod("../{$this->run_path}/run{$project_pipeline_id}/nextflow.config", 0755);
        if ($profileType == "cluster") {
            // get outputdir
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID));
            $outdir = $proPipeAll[0]->{'output_dir'};
            // get username and hostname for connection
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["username"]."@".$cluDataArr[0]["hostname"];
            $ssh_id = $cluDataArr[0]["ssh_id"];
            //get userpky
            $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
            //check $userpky file exist
            if (!file_exists($userpky)) {
            $this->writeLog($project_pipeline_id,'Private key is not found!','a','log.txt');
            $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
            die(json_encode('Private key is not found!'));
            }
            $run_path_real = "../{$this->run_path}/run{$project_pipeline_id}";
            if (!file_exists($run_path_real."/nextflow.nf")) {
            $this->writeLog($project_pipeline_id,'Nextflow file is not found!','a','log.txt');
            $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
            die(json_encode('Nextflow file is not found!'));
            }
            if (!file_exists($run_path_real."/nextflow.config")) {
            $this->writeLog($project_pipeline_id,'Nextflow config file is not found!','a','log.txt');
            $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
            die(json_encode('Nextflow config file is not found!'));
            }
            $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
            //mkdir and copy nextflow file to run directory in cluster
            $cmd = "ssh {$this->ssh_settings}  -i $userpky $connect \"mkdir -p $dolphin_path_real\" > $run_path_real/log.txt 2>&1 && scp {$this->ssh_settings} -i $userpky $run_path_real/nextflow.nf $run_path_real/nextflow.config $connect:$dolphin_path_real >> $run_path_real/log.txt 2>&1";
            $mkdir_copynext_pid =shell_exec($cmd);
            $this->writeLog($project_pipeline_id,$cmd,'a','log.txt');
            $log_array = array('mkdir_copynext_pid' => $mkdir_copynext_pid);
            return $log_array;
        } else if ($profileType == "amazon") {
            // get outputdir
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID));
            $outdir = $proPipeAll[0]->{'output_dir'};
            // get username and hostname for connection
            $amzData=$this->getProfileAmazonbyID($profileId, $ownerID);
            $amzDataArr=json_decode($amzData,true);
            $connect = $amzDataArr[0]["ssh"];
            $ssh_id = $amzDataArr[0]["ssh_id"];
            //get userpky
            $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
            //check $userpky file exist
            if (!file_exists($userpky)) {
                $this->writeLog($project_pipeline_id,'Private key is not found!','a','log.txt');
                $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                die(json_encode('Private key is not found!'));
            }
            $run_path_real = "../{$this->run_path}/run{$project_pipeline_id}";
            if (!file_exists($run_path_real."/nextflow.nf")) {
                $this->writeLog($project_pipeline_id,'Nextflow file is not found!','a','log.txt');
                $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                die(json_encode('Nextflow file is not found!'));
            }
            if (!file_exists($run_path_real."/nextflow.config")) {
                $this->writeLog($project_pipeline_id,'Nextflow config file is not found!','a','log.txt');
                $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                die(json_encode('Nextflow config file is not found!'));
            }
            $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
            //mkdir and copy nextflow file to run directory in cluster
            $cmd = "ssh {$this->ssh_settings}  -i $userpky $connect \"mkdir -p $dolphin_path_real\" > $run_path_real/log.txt 2>&1 && scp {$this->ssh_settings} -i $userpky $run_path_real/nextflow.nf $run_path_real/nextflow.config $connect:$dolphin_path_real >> $run_path_real/log.txt 2>&1";
            $mkdir_copynext_pid =shell_exec($cmd);
            $this->writeLog($project_pipeline_id,$cmd,'a','log.txt');
            $log_array = array('mkdir_copynext_pid' => $mkdir_copynext_pid);
            return $log_array;
        }
    }

    function runCmd($project_pipeline_id, $profileType, $profileId, $log_array, $runType, $ownerID)
    {
            //get nextflow executor parameters
            list($outdir, $proPipeCmd, $jobname, $singu_check, $singu_img, $imageCmd, $reportOptions) = $this->getNextExecParam($project_pipeline_id,$profileType, $profileId, $ownerID);
            //get username and hostname and exec info for connection
            list($connect, $next_path, $profileCmd, $executor, $next_time, $next_queue, $next_memory, $next_cpu, $next_clu_opt, $executor_job, $ssh_id)=$this->getNextConnectExec($profileId,$ownerID, $profileType);
            //get nextflow input parameters
            $next_inputs = $this->getNextInputs($executor, $project_pipeline_id,$ownerID);
            //get cmd before run
            $preCmd = $this->getPreCmd ($profileType,$profileCmd,$proPipeCmd, $imageCmd);
            //eg. /project/umw_biocore/bin
            $next_path_real = $this->getNextPathReal($next_path);
        
            //get userpky
            $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) {
                $this->writeLog($project_pipeline_id,'Private key is not found!','a','log.txt');
                $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                die(json_encode('Private key is not found!'));
            }
            $run_path_real = "../{$this->run_path}/run{$project_pipeline_id}";
            $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
            //get command for renaming previous log file
            $attemptData = json_decode($this->getRunAttempt($project_pipeline_id));
            $attempt = $attemptData[0]->{'attempt'};
            if (empty($attempt) || $attempt == 0 || $attempt == "0"){
                $attempt = "0";
            }
            $renameLog = "cp $dolphin_path_real/log.txt $dolphin_path_real/log$attempt.txt 2>/dev/null || true && >$dolphin_path_real/log.txt &&";
            
            //check if files are exist
            $next_exist_cmd= "ssh {$this->ssh_settings} -i $userpky $connect test  -f \"$dolphin_path_real/nextflow.nf\"  && echo \"Nextflow file exists\" || echo \"Nextflow file not exists\" 2>&1 & echo $! &";
            $next_exist = shell_exec($next_exist_cmd);
            $this->writeLog($project_pipeline_id,$next_exist_cmd,'a','log.txt');
            preg_match("/(.*)Nextflow file(.*)exists(.*)/", $next_exist, $matches);
            $log_array['next_exist'] = $next_exist;
            if ($matches[2] == " ") {
                $exec_next_all = $this->getExecNextAll($executor, $dolphin_path_real, $next_path_real, $next_inputs, $next_queue,$next_cpu,$next_time,$next_memory, $jobname, $executor_job, $reportOptions, $next_clu_opt, $runType, $profileId,$ownerID);
            
                $cmd="ssh {$this->ssh_settings}  -i $userpky $connect \"$renameLog $preCmd $exec_next_all\" >> $run_path_real/log.txt 2>&1 & echo $! &";
                $next_submit_pid= shell_exec($cmd); //"Job <203477> is submitted to queue <long>.\n"
                $this->writeLog($project_pipeline_id,$cmd,'a','log.txt');
                if (!$next_submit_pid) {
                    $this->writeLog($project_pipeline_id,'ERROR: Connection failed! Please check your connection profile or internet connection','a','log.txt');
                    $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                    die(json_encode('ERROR: Connection failed. Please check your connection profile or internet connection'));
                    }
                $log_array['next_submit_pid'] = $next_submit_pid;
                return json_encode($log_array);

            }else if ($matches[2] == " not "){
                for( $i= 0 ; $i < 3 ; $i++ ){
                    sleep(3);
                    $next_exist = shell_exec($next_exist_cmd);
                    preg_match("/(.*)Nextflow file(.*)exists(.*)/", $next_exist, $matches);
                    $log_array['next_exist'] = $next_exist;
                    if ($matches[2] == " ") {
                        $next_submit_pid= shell_exec($cmd); //"Job <203477> is submitted to queue <long>.\n"
                        if (!$next_submit_pid) {
                            $this->writeLog($project_pipeline_id,'ERROR: Connection failed. Please check your connection profile or internet connection','a','log.txt');
                            $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                            die(json_encode('ERROR: Connection failed. Please check your connection profile or internet connection'));
                        }
                        $log_array['next_submit_pid'] = $next_submit_pid;
                            return json_encode($log_array);
                    }
                }
                    $this->writeLog($project_pipeline_id,'ERROR: Connection failed. Please check your connection profile or internet connection','a','log.txt');
                    $this->updateRunAttemptLog("Error", $project_pipeline_id, $ownerID);
                    die(json_encode('ERROR: Connection failed. Please check your connection profile or internet connection'));
            } 
    }

    public function updateRunAttemptLog($status, $project_pipeline_id, $ownerID){
        //check if $project_pipeline_id already exits
        $checkRun = $this->getRun($project_pipeline_id,$ownerID);
        $checkarray = json_decode($checkRun,true);
        $ppId = $checkarray[0]["project_pipeline_id"];
        $attempt = $checkarray[0]["attempt"];
        settype($attempt, 'integer');
        if (empty($attempt)){
            $attempt = 0;
        }
        $attempt = $attempt +1;
        if (!empty($ppId)) {
            $this->updateRunAttempt($project_pipeline_id, $attempt, $ownerID);
            $this->updateRunStatus($project_pipeline_id, $status, $ownerID);
            $this->insertRunLog($project_pipeline_id, $status, $ownerID);
        } else {
            $this->insertRun($project_pipeline_id, $status, "1", $ownerID);
            $this->insertRunLog($project_pipeline_id, $status, $ownerID);
        }
     }

    public function generateKeys($ownerID) {
        $cmd = "rm -rf {$this->ssh_path}/.tmp$ownerID && mkdir -p {$this->ssh_path}/.tmp$ownerID && cd {$this->ssh_path}/.tmp$ownerID && ssh-keygen -C @dolphinnext -f tkey -t rsa -N '' > logTemp.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'create_key', '');
        if (preg_match("/([0-9]+)(.*)/", $log_array['create_key'])){
             $log_array['create_key_status'] = "active";
        }else {
             $log_array['create_key_status'] = "error";
        }
        return json_encode($log_array);
    }
     public function readGenerateKeys($ownerID) {
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
    function insertKey($id, $key, $type, $ownerID){
            mkdir("{$this->ssh_path}", 0700, true);
        if ($type == 'clu'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}.pky", 'w');//creates new file
            fwrite($file, $key);
            fclose($file);
            chmod("{$this->ssh_path}/{$ownerID}_{$id}.pky", 0600);
        } else if ($type == 'amz_pri'){
            $file = fopen("{$this->ssh_path}/{$ownerID}_{$id}_{$type}.pky", 'w');//creates new file
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
    function startProAmazon($id,$ownerID, $username){
        $profileName = "{$username}_{$id}";
        $data = json_decode($this->getProfileAmazonbyID($id, $ownerID));
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
        $name = $data[0]->{'name'};
        $ssh_id = $data[0]->{'ssh_id'};
        $username = $data[0]->{'username'};
        $image_id = $data[0]->{'image_id'};
        $instance_type = $data[0]->{'instance_type'};
        $subnet_id = $data[0]->{'subnet_id'};
        $shared_storage_id = $data[0]->{'shared_storage_id'};
        $shared_storage_mnt = $data[0]->{'shared_storage_mnt'};
        $keyFile = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pub.pky";
        $nodes = $data[0]->{'nodes'};
        settype($nodes, "integer");
        $autoscale_check = $data[0]->{'autoscale_check'};
        $autoscale_maxIns = $data[0]->{'autoscale_maxIns'};
        $autoscale_minIns = $nodes;
        $text= "cloud { \n";
        $text.= "   userName = '$username'\n";
        $text.= "   imageId = '$image_id'\n";
        $text.= "   instanceType = '$instance_type'\n";
        $text.= "   subnetId = '$subnet_id'\n";
        $text.= "   sharedStorageId = '$shared_storage_id'\n";
        $text.= "   sharedStorageMount = '$shared_storage_mnt'\n";
        $text.= "   keyFile = '$keyFile'\n";
        if ($autoscale_check == "true"){
        $text.= "   autoscale {\n";
        $text.= "       enabled = true \n";
        $text.= "       terminateWhenIdle = true\n";
        if (!empty($autoscale_minIns)){
        $text.= "       minInstances = $autoscale_minIns\n";
        }
        if (!empty($autoscale_maxIns)){
        $text.= "       maxInstances = $autoscale_maxIns\n";
        }
        $text.= "   }\n";
        }
        $text.= "}\n";
        $text.= "aws{\n";
        $text.= "   accessKey = '$access_key'\n";
        $text.= "   secretKey = '$secret_key'\n";
        $text.= "   region = '$default_region'\n";
        $text.= "}\n";
        $this->createDirFile ("{$this->amz_path}/pro_{$profileName}", "nextflow.config", 'w', $text );
        $nodeText = "";
        if ($nodes >1){
            $nodeText = "-c $nodes";
        } 
        //start amazon cluster
        $cmd = "cd {$this->amz_path}/pro_{$profileName} && yes | nextflow cloud create $profileName $nodeText > logAmzStart.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'start_cloud', '');
        $log_array['start_cloud_cmd'] = $cmd;
        //xxx save pid of nextflow cloud create cluster job
        if (preg_match("/([0-9]+)(.*)/", $log_array['start_cloud'])){
            $this->updateAmazonProStatus($id, "waiting", $ownerID);
        }else {
            $this->updateAmazonProStatus($id, "terminated", $ownerID);
        }
        return json_encode($log_array);
    }

    function stopProAmazon($id,$ownerID,$username){
        $profileName = "{$username}_{$id}";
        //stop amazon cluster
        $cmd = "cd {$this->amz_path}/pro_{$profileName} && yes | nextflow cloud shutdown $profileName > logAmzStop.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'stop_cloud', '');
        return json_encode($log_array);
    }

     function checkAmzStopLog($id,$ownerID,$username){
        $profileName = "{$username}_{$id}";
        //read logAmzStop.txt
        $logPath ="{$this->amz_path}/pro_{$profileName}/logAmzStop.txt";
        $logAmzStop = $this->readFile($logPath);
        $log_array = array('logAmzStop' => $logAmzStop);
        return json_encode($log_array);
    }
     //read both start and list files
    function readAmzCloudListStart($id,$username){
        $profileName = "{$username}_{$id}";
        //read logAmzCloudList.txt
        $logPath ="{$this->amz_path}/pro_{$profileName}/logAmzCloudList.txt";
        $logAmzCloudList = $this->readFile($logPath);
        $log_array = array('logAmzCloudList' => $logAmzCloudList);
        //read logAmzStart.txt
        $logPathStart ="{$this->amz_path}/pro_{$profileName}/logAmzStart.txt";
        $logAmzStart = $this->readFile($logPathStart);
        $log_array['logAmzStart'] = $logAmzStart;
        return $log_array;
    }
    public function checkAmazonStatus($id,$ownerID,$username) {
        $profileName = "{$username}_{$id}";
        //check status
        $amzStat = json_decode($this->getAmazonStatus($id,$ownerID));
        $status = $amzStat[0]->{'status'};
        $node_status = $amzStat[0]->{'node_status'};
        if ($status == "waiting"){
            //check cloud list
            $log_array = $this->readAmzCloudListStart($id,$username);
            if (preg_match("/running/", $log_array['logAmzCloudList'])){
                $this->updateAmazonProStatus($id, "initiated", $ownerID);
                $log_array['status'] = "initiated";
                return json_encode($log_array);
            } else if (!preg_match("/STATUS/", $log_array['logAmzCloudList']) && (preg_match("/Missing/i", $log_array['logAmzCloudList']) || preg_match("/denied/i", $log_array['logAmzCloudList']) || preg_match("/ERROR/i", $log_array['logAmzCloudList']))){
                $this->updateAmazonProStatus($id, "terminated", $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            }else if (preg_match("/Missing/i", $log_array['logAmzStart']) || preg_match("/denied/i", $log_array['logAmzStart']) || preg_match("/ERROR/i", $log_array['logAmzStart'])  || preg_match("/couldn't/i", $log_array['logAmzStart'])  || preg_match("/help/i", $log_array['logAmzStart']) || preg_match("/wrong/i", $log_array['logAmzStart'])){
                $this->updateAmazonProStatus($id, "terminated", $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            }else {
                //error
                $log_array['status'] = "waiting";
                return json_encode($log_array);
            }
        } else if ($status == "initiated"){
            //check cloud list
            $log_array = $this->readAmzCloudListStart($id,$username);
            if (preg_match("/running/",$log_array['logAmzCloudList']) && preg_match("/STATUS/",$log_array['logAmzCloudList'])){
                //read logAmzStart.txt
                $amzStartPath ="{$this->amz_path}/pro_{$profileName}/logAmzStart.txt";
                $amzStartLog = $this->readFile($amzStartPath);
                $log_array['$amzStartLog'] = $amzStartLog;
                if (preg_match("/ssh -i(.*)/",$amzStartLog)){
                    preg_match("/ssh -i <(.*)> (.*)/",$amzStartLog, $match);
                    $sshText = $match[2];
                    $log_array['sshText'] = $sshText;
                    $log_array['status'] = "running";
                    $this->updateAmazonProStatus($id, "running", $ownerID);
                    $this->updateAmazonProSSH($id, $sshText, $ownerID);
                    //parse child nodes
                    $cluData=$this->getProfileAmazonbyID($id, $ownerID);
                    $cluDataArr=json_decode($cluData,true);
                    $numNodes = $cluDataArr[0]["nodes"];
                    settype($numNodes, "integer");
                    $username = $cluDataArr[0]["username"];
                    if ($numNodes >1){
                        $log_array['nodes'] = $numNodes;
                        if (preg_match("/.*Launching worker node.*/",$amzStartLog)){
                            preg_match("/.*Launching worker node.*ready\.(.*)Launching master node --/s",$amzStartLog, $matchNodes);
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
            } else if (!preg_match("/running/",$log_array['logAmzCloudList']) && preg_match("/STATUS/",$log_array['logAmzCloudList'])){
                $this->updateAmazonProStatus($id, "terminated", $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        } else if ($status == "running"){
            //check cloud list
            $log_array = $this->readAmzCloudListStart($id,$username);
            if (preg_match("/running/",$log_array['logAmzCloudList']) && preg_match("/STATUS/",$log_array['logAmzCloudList'])){
                $log_array['status'] = "running";
                $sshTextArr = json_decode($this->getAmazonProSSH($id, $ownerID));
                $sshText = $sshTextArr[0]->{'ssh'};
                $log_array['sshText'] = $sshText;
                
                
                
                
                return json_encode($log_array);
            } else if (!preg_match("/running/",$log_array['logAmzCloudList']) && preg_match("/STATUS/",$log_array['logAmzCloudList'])){
                $this->updateAmazonProStatus($id, "terminated", $ownerID);
                $log_array['status'] = "terminated";
                return json_encode($log_array);
            } else {
                $log_array['status'] = "retry";
                return json_encode($log_array);
            }
        }
        else if ($status == "terminated"){
                $log_array = $this->readAmzCloudListStart($id,$username);
                $log_array['status'] = "terminated";
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
    public function runAmazonCloudCheck($id,$ownerID,$username){
        $profileName = "{$username}_{$id}";
        $cmd = "cd {$this->amz_path}/pro_$profileName && rm -f logAmzCloudList.txt && nextflow cloud list $profileName >> logAmzCloudList.txt 2>&1 & echo $! &";
        $log_array = $this->runCommand ($cmd, 'cloudlist', '');
        return json_encode($log_array);
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
              $where = " ";
          } else {
              $where = " WHERE (p.owner_id='$ownerID' OR (p.perms = 63 AND p.pin = 'true') OR (ug.u_id ='$ownerID' and p.perms = 15)) ";
          }
          $sql= "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin
               FROM biocorepipe_save pip
               LEFT JOIN user_group ug ON  pip.group_id=ug.g_id
               INNER JOIN (
                SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                FROM biocorepipe_save p
                LEFT JOIN user_group ug ON p.group_id=ug.g_id
                $where
                GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid";

        } else {
          $sql= "SELECT DISTINCT pip.id, pip.name, pip.perms, pip.group_id, pip.pin
               FROM biocorepipe_save pip
               INNER JOIN (
                SELECT p.pipeline_gid, MAX(p.rev_id) rev_id
                FROM biocorepipe_save p
                WHERE p.perms = 63
                GROUP BY p.pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid AND pip.pin = 'true' ";
        }
        return self::queryTable($sql);
    }

    public function getSubMenuFromSideBar($parent, $ownerID){
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id, p.owner_id, p.publish
                FROM process p
                INNER JOIN process_group pg
                ON p.process_group_id = pg.id and pg.group_name='$parent'
                INNER JOIN (
                SELECT pr.process_gid, MAX(pr.rev_id) rev_id
                FROM process pr
                GROUP BY pr.process_gid
                ) b ON p.rev_id = b.rev_id AND p.process_gid=b.process_gid  ";
            return self::queryTable($sql);
            }
            $where_pg = "(pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15))";
            $where_pr = "(pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            } else {
                $where_pg = "pg.perms = 63";
                $where_pr = "pr.perms = 63";
            }
       $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id
             FROM process p
             LEFT JOIN user_group ug ON  p.group_id=ug.g_id
             INNER JOIN process_group pg
             ON p.process_group_id = pg.id and pg.group_name='$parent' and $where_pg
             INNER JOIN (
                SELECT pr.process_gid, MAX(pr.rev_id) rev_id
                FROM process pr
                LEFT JOIN user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.process_gid
                ) b ON p.rev_id = b.rev_id AND p.process_gid=b.process_gid";

      return self::queryTable($sql);
    }
	//new
	public function getSubMenuFromSideBarPipe($parent, $ownerID){
        if ($ownerID != ''){
            $userRoleArr = json_decode($this->getUserRole($ownerID));
            $userRole = $userRoleArr[0]->{'role'};
            if ($userRole == "admin"){
                $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id, p.owner_id, p.publish
                FROM biocorepipe_save p
                INNER JOIN pipeline_group pg
                ON p.pipeline_group_id = pg.id and pg.group_name='$parent'
                INNER JOIN (
                SELECT pr.pipeline_gid, MAX(pr.rev_id) rev_id
                FROM biocorepipe_save pr
                GROUP BY pr.pipeline_gid
                ) b ON p.rev_id = b.rev_id AND p.pipeline_gid=b.pipeline_gid  ";
            return self::queryTable($sql);
            }
            $where_pg = "(pg.owner_id='$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15))";
            $where_pr = "(pr.owner_id='$ownerID' OR pr.perms = 63 OR (ug.u_id ='$ownerID' and pr.perms = 15))";
            } else {
                $where_pg = "pg.perms = 63";
                $where_pr = "pr.perms = 63";
            }
       $sql="SELECT DISTINCT p.id, p.name, p.perms, p.group_id
             FROM biocorepipe_save p
             LEFT JOIN user_group ug ON  p.group_id=ug.g_id
             INNER JOIN pipeline_group pg
             ON p.pipeline_group_id = pg.id and pg.group_name='$parent' and $where_pg
             INNER JOIN (
                SELECT pr.pipeline_gid, MAX(pr.rev_id) rev_id
                FROM biocorepipe_save pr
                LEFT JOIN user_group ug ON pr.group_id=ug.g_id where $where_pr
                GROUP BY pr.pipeline_gid
                ) b ON p.rev_id = b.rev_id AND p.pipeline_gid=b.pipeline_gid";

      return self::queryTable($sql);
    }
	
    public function getParentSideBarProject($ownerID){
        $sql= "SELECT DISTINCT pp.name, pp.id
        FROM project pp
        LEFT JOIN user_group ug ON pp.group_id=ug.g_id
        where pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15)";
        return self::queryTable($sql);
    }
    public function getSubMenuFromSideBarProject($parent, $ownerID){
        $where = "(pp.project_id='$parent' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15)))";
        $sql="SELECT DISTINCT pp.id, pp.name, pj.owner_id, pp.project_id
             FROM project_pipeline pp
             LEFT JOIN user_group ug ON pp.group_id=ug.g_id
             INNER JOIN project pj ON pp.project_id = pj.id and $where ";
        return self::queryTable($sql);
    }


//    ---------------  Users ---------------
    public function getUser($google_id) {
        $sql = "SELECT * FROM users WHERE google_id = '$google_id'";
        return self::queryTable($sql);
    }
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = '$id'";
        return self::queryTable($sql);
    }
    public function getUserLess($google_id) {
        $sql = "SELECT username, name, email, google_image FROM users WHERE google_id = '$google_id'";
        return self::queryTable($sql);
    }
    public function insertUser($google_id, $name, $email, $google_image, $username) {
        $sql = "INSERT INTO users(google_id, name, email, google_image, username, institute, lab, memberdate, date_created, date_modified, perms) VALUES
			('$google_id', '$name', '$email', '$google_image', '$username', '', '', now() , now(), now(), '3')";
        return self::insTable($sql);
    }

    public function updateUser($id, $google_id, $name, $email, $google_image, $username) {
        $sql = "UPDATE users SET id='$id', google_id='$google_id', name='$name', email='$email', google_image='$google_image', username='$username', last_modified_user='$id' WHERE id = '$id'";
        return self::runSQL($sql);
    }
//    ------------- Profiles   ------------
    public function insertSSH($name, $check_userkey, $check_ourkey, $ownerID) {
        $sql = "INSERT INTO ssh(name, check_userkey, check_ourkey, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
			('$name', '$check_userkey', '$check_ourkey', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    public function updateSSH($id, $name, $check_userkey, $check_ourkey, $ownerID) {
        $sql = "UPDATE ssh SET name='$name', check_userkey='$check_userkey', check_ourkey='$check_ourkey', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
      public function insertAmz($name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID) {
        $sql = "INSERT INTO amazon_credentials (name, amz_def_reg, amz_acc_key, amz_suc_key, date_created, date_modified, last_modified_user, perms, owner_id) VALUES
			('$name', '$amz_def_reg', '$amz_acc_key', '$amz_suc_key', now() , now(), '$ownerID', '3', '$ownerID')";
        return self::insTable($sql);
    }
    public function updateAmz($id, $name, $amz_def_reg,$amz_acc_key,$amz_suc_key, $ownerID) {
        $sql = "UPDATE amazon_credentials SET name='$name', amz_def_reg='$amz_def_reg', amz_acc_key='$amz_acc_key', amz_suc_key='$amz_suc_key', date_modified = now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function getAmz($ownerID) {
        $sql = "SELECT * FROM amazon_credentials WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    public function getAmzbyID($id,$ownerID) {
        $sql = "SELECT * FROM amazon_credentials WHERE owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    public function getSSH($ownerID) {
        $sql = "SELECT * FROM ssh WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    public function getSSHbyID($id,$ownerID) {
        $sql = "SELECT * FROM ssh WHERE owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    public function getProfileClusterbyID($id, $ownerID) {
        $sql = "SELECT * FROM profile_cluster WHERE owner_id = '$ownerID' and id = '$id'";
        return self::queryTable($sql);
    }
    public function getProfileCluster($ownerID) {
        $sql = "SELECT * FROM profile_cluster WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    public function getProfileAmazon($ownerID) {
        $sql = "SELECT * FROM profile_amazon WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
    public function getProfileAmazonbyID($id, $ownerID) {
        $sql = "SELECT p.*, u.username
        FROM profile_amazon p
        INNER JOIN users u ON p.owner_id = u.id
        WHERE p.owner_id = '$ownerID' and p.id = '$id'";
        return self::queryTable($sql);
    }
    public function insertProfileLocal($name, $executor,$next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID) {
        $sql = "INSERT INTO profile_local (name, executor, next_path, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$name', '$executor','$next_path', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function updateProfileLocal($id, $name, $executor,$next_path, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ownerID) {
        $sql = "UPDATE profile_local SET name='$name', executor='$executor', next_path='$next_path', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu',  last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    public function insertProfileCluster($name, $executor,$next_path, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $ownerID) {
        $sql = "INSERT INTO profile_cluster(name, executor, next_path, username, hostname, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, ssh_id, next_clu_opt, job_clu_opt, owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$username', '$hostname', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$ssh_id', '$next_clu_opt','$job_clu_opt', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function updateProfileCluster($id, $name, $executor,$next_path, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $ownerID) {
        $sql = "UPDATE profile_cluster SET name='$name', executor='$executor', next_path='$next_path', username='$username', hostname='$hostname', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', job_clu_opt='$job_clu_opt', next_clu_opt='$next_clu_opt', ssh_id='$ssh_id', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function insertProfileAmazon($name, $executor, $next_path, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id,$shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $ownerID) {
        $sql = "INSERT INTO profile_amazon(name, executor, next_path, instance_type, image_id, cmd, next_memory, next_queue, next_time, next_cpu, executor_job, job_memory, job_queue, job_time, job_cpu, subnet_id, shared_storage_id, shared_storage_mnt, ssh_id, amazon_cre_id, next_clu_opt, job_clu_opt, owner_id, perms, date_created, date_modified, last_modified_user) VALUES('$name', '$executor', '$next_path', '$ins_type', '$image_id', '$cmd', '$next_memory', '$next_queue', '$next_time', '$next_cpu', '$executor_job', '$job_memory', '$job_queue', '$job_time', '$job_cpu', '$subnet_id','$shared_storage_id','$shared_storage_mnt','$ssh_id','$amazon_cre_id', '$next_clu_opt', '$job_clu_opt', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function updateProfileAmazon($id, $name, $executor, $next_path, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $ownerID) {
        $sql = "UPDATE profile_amazon SET name='$name', executor='$executor', next_path='$next_path', instance_type='$ins_type', image_id='$image_id', cmd='$cmd', next_memory='$next_memory', next_queue='$next_queue', next_time='$next_time', next_cpu='$next_cpu', executor_job='$executor_job', job_memory='$job_memory', job_queue='$job_queue', job_time='$job_time', job_cpu='$job_cpu', subnet_id='$subnet_id', shared_storage_id='$shared_storage_id', shared_storage_mnt='$shared_storage_mnt', ssh_id='$ssh_id', next_clu_opt='$next_clu_opt', job_clu_opt='$job_clu_opt', amazon_cre_id='$amazon_cre_id', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateProfileAmazonNode($id, $nodes, $autoscale_check, $autoscale_maxIns, $autoscale_minIns, $ownerID) {
        $sql = "UPDATE profile_amazon SET nodes='$nodes', autoscale_check='$autoscale_check', autoscale_maxIns='$autoscale_maxIns', autoscale_minIns='$autoscale_minIns', last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateAmazonProStatus($id, $status, $ownerID) {
        $sql = "UPDATE profile_amazon SET status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateAmazonProNodeStatus($id, $node_status, $ownerID) {
        $sql = "UPDATE profile_amazon SET node_status='$node_status', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateAmazonProPid($id, $pid, $ownerID) {
        $sql = "UPDATE profile_amazon SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function updateAmazonProSSH($id, $sshText, $ownerID) {
        $sql = "UPDATE profile_amazon SET ssh='$sshText', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
		return self::runSQL($sql);
    }
    public function getAmazonProSSH($id, $ownerID) {
        $sql = "SELECT ssh FROM profile_amazon WHERE id = '$id' AND owner_id = '$ownerID'";
		return self::queryTable($sql);
    }
         public function removeAmz($id) {
        $sql = "DELETE FROM amazon_credentials WHERE id = '$id'";
        return self::runSQL($sql);
    }
     public function removeSSH($id) {
        $sql = "DELETE FROM ssh WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProLocal($id) {
        $sql = "DELETE FROM profile_local WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProCluster($id) {
        $sql = "DELETE FROM profile_cluster WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProAmazon($id) {
        $sql = "DELETE FROM profile_amazon WHERE id = '$id'";
        return self::runSQL($sql);
    }
//    ------------- Parameters ------------
    public function getAllParameters($ownerID) {
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
    public function getEditDelParameters($ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        }
        $sql = "SELECT * FROM parameter WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }

    public function insertParameter($name, $qualifier, $file_type, $ownerID) {
        $sql = "INSERT INTO parameter(name, qualifier, file_type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
			('$name', '$qualifier', '$file_type', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }

    public function updateParameter($id, $name, $qualifier, $file_type, $ownerID) {
        $sql = "UPDATE parameter SET name='$name', qualifier='$qualifier', last_modified_user ='$ownerID', file_type='$file_type'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    
    public function insertProcessGroup($group_name, $ownerID) {
        $sql = "INSERT INTO process_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    
    public function updateProcessGroup($id, $group_name, $ownerID) {
        $sql = "UPDATE process_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    
	public function updateAllProcessGroupByGid($process_gid, $process_group_id,$ownerID) {
        $sql = "UPDATE process SET process_group_id='$process_group_id', last_modified_user ='$ownerID', date_modified=now()  WHERE process_gid = '$process_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }
    
	public function updateAllPipelineGroupByGid($pipeline_gid, $pipeline_group_id,$ownerID) {
        $sql = "UPDATE biocorepipe_save SET pipeline_group_id='$pipeline_group_id', last_modified_user ='$ownerID', date_modified=now() WHERE pipeline_gid = '$pipeline_gid' AND owner_id = '$ownerID'";
        return self::runSQL($sql);
    }

    public function removeParameter($id) {
        $sql = "DELETE FROM parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }

    public function removeProcessGroup($id) {
        $sql = "DELETE FROM process_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
	public function removePipelineGroup($id) {
        $sql = "DELETE FROM pipeline_group WHERE id = '$id'";
        return self::runSQL($sql);
    }
    // --------- Process -----------
    public function getAllProcessGroups($ownerID) {
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])){
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if ($userRole == "admin"){
                $sql = "SELECT DISTINCT pg.id, pg.group_name
                FROM process_group pg";
                return self::queryTable($sql);
            }
        }
        $sql = "SELECT DISTINCT pg.id, pg.group_name
        FROM process_group pg
        LEFT JOIN user_group ug ON pg.group_id=ug.g_id
        WHERE pg.owner_id = '$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15)";
        return self::queryTable($sql);
    }
    public function getEditDelProcessGroups($ownerID) {
        $sql = "SELECT id, group_name FROM process_group WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
	
    public function insertProcess($name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $rev_id, $rev_comment, $group, $perms, $publish, $script_mode, $script_mode_header, $ownerID) {
        $sql = "INSERT INTO process(name, process_gid, summary, process_group_id, script, script_header, script_footer, rev_id, rev_comment, owner_id, date_created, date_modified, last_modified_user, perms, group_id, publish, script_mode, script_mode_header) VALUES ('$name', '$process_gid', '$summary', '$process_group_id', '$script', '$script_header', '$script_footer', '$rev_id','$rev_comment', '$ownerID', now(), now(), '$ownerID', '$perms', '$group', '$publish','$script_mode', '$script_mode_header')";
        return self::insTable($sql);
    }

    public function updateProcess($id, $name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $group, $perms, $publish, $script_mode, $script_mode_header, $ownerID) {
        $sql = "UPDATE process SET name= '$name', process_gid='$process_gid', summary='$summary', process_group_id='$process_group_id', script='$script', script_header='$script_header',  script_footer='$script_footer', last_modified_user='$ownerID', group_id='$group', perms='$perms', publish='$publish', script_mode='$script_mode', date_modified = now(), script_mode_header='$script_mode_header' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProcess($id) {
        $sql = "DELETE FROM process WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProject($id) {
        $sql = "DELETE FROM project WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeGroup($id) {
        $sql = "DELETE FROM groups WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeUserGroup($id) {
        $sql = "DELETE FROM user_group WHERE g_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectPipeline($id) {
        $sql = "DELETE FROM project_pipeline WHERE id = '$id'";
        return self::runSQL($sql);
    }
	public function removeRun($id) {
        $sql = "DELETE FROM run WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeInput($id) {
        $sql = "DELETE FROM input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectPipelineInput($id) {
        $sql = "DELETE FROM project_pipeline_input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectPipelineInputByPipe($id) {
        $sql = "DELETE FROM project_pipeline_input WHERE project_pipeline_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectInput($id) {
        $sql = "DELETE FROM project_input WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectPipelinebyProjectID($id) {
        $sql = "DELETE FROM project_pipeline WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
	public function removeRunByProjectID($id) {
        $sql = "DELETE run 
				FROM run
                JOIN project_pipeline ON project_pipeline.id = run.project_pipeline_id
                WHERE project_pipeline.project_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectPipelineInputbyProjectID($id) {
        $sql = "DELETE FROM project_pipeline_input WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProjectInputbyProjectID($id) {
        $sql = "DELETE FROM project_input WHERE project_id = '$id'";
        return self::runSQL($sql);
    }
    public function removeProcessByProcessGroupID($process_group_id) {
        $sql = "DELETE FROM process WHERE process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
//    ------ Groups -------
    public function getAllGroups() {
        $sql = "SELECT id, name FROM groups";
        return self::queryTable($sql);
    }
    public function getGroups($id,$ownerID) {
        $where = "";
		if ($id != ""){
			$where = " where g.id = '$id'";
		}
		$sql = "SELECT g.id, g.name, g.date_created, u.username, g.date_modified
                FROM groups g
                INNER JOIN users u ON g.owner_id = u.id $where";
		return self::queryTable($sql);
    }
    public function viewGroupMembers($g_id) {
        $sql = "SELECT id, username
	           FROM users
	           WHERE id in (
		          SELECT u_id
		          FROM user_group
		          WHERE g_id = '$g_id')";
        return self::queryTable($sql);
    }
    public function getMemberAdd($g_id) {
        $sql = "SELECT id, username
	           FROM users
	           WHERE id NOT IN (
		          SELECT u_id
		          FROM user_group
		          WHERE g_id = '$g_id')";
        return self::queryTable($sql);
    }
    public function getAllUsers($ownerID) {
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])){
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if ($userRole == "admin"){
                $sql = "SELECT id, username
                        FROM users
                        WHERE id <> '$ownerID'";
                return self::queryTable($sql);
            }
        }
    }

    public function getUserGroups($ownerID) {
		$sql = "SELECT g.id, g.name, g.date_created, u.username, g.owner_id, ug.u_id
                FROM groups g
                INNER JOIN user_group ug ON  ug.g_id =g.id
                INNER JOIN users u ON u.id = g.owner_id
                where ug.u_id = '$ownerID'";
		return self::queryTable($sql);
    }
    public function getUserRole($ownerID) {
		$sql = "SELECT role
                FROM users
                where id = '$ownerID'";
		return self::queryTable($sql);
    }
    public function insertGroup($name, $ownerID) {
        $sql = "INSERT INTO groups(name, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    public function insertUserGroup($g_id, $u_id, $ownerID) {
        $sql = "INSERT INTO user_group (g_id, u_id, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$g_id', '$u_id', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    public function updateGroup($id, $name, $ownerID) {
        $sql = "UPDATE groups SET name= '$name', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
//    ----------- Projects   ---------
    public function getProjects($id,$ownerID) {
        $where = " where p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)";
		if ($id != ""){
			$where = " where p.id = '$id' AND (p.owner_id = '$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15))";
		}
		$sql = "SELECT DISTINCT p.id, p.name, p.summary, p.date_created, u.username, p.date_modified, IF(p.owner_id='$ownerID',1,0) as own
        FROM project p
        INNER JOIN users u ON p.owner_id = u.id
        LEFT JOIN user_group ug ON p.group_id=ug.g_id
        $where";
		return self::queryTable($sql);
    }
    public function insertProject($name, $summary, $ownerID) {
        $sql = "INSERT INTO project(name, summary, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$summary', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    public function updateProject($id, $name, $summary, $ownerID) {
        $sql = "UPDATE project SET name= '$name', summary= '$summary', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
//    ----------- Runs     ---------
    public function insertRun($project_pipeline_id, $status, $attempt, $ownerID) {
        $sql = "INSERT INTO run (project_pipeline_id, run_status, attempt, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
			('$project_pipeline_id', '$status', '$attempt', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function insertRunLog($project_pipeline_id, $status, $ownerID) {
        $sql = "INSERT INTO run_log (project_pipeline_id, run_status, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
			('$project_pipeline_id', '$status', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    //get maximum of $project_pipeline_id
    public function updateRunLog($project_pipeline_id, $status, $duration, $ownerID) {
         $sql = "UPDATE run_log SET run_status='$status', duration='$duration', date_ended= now(), date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id' ORDER BY id DESC LIMIT 1";
        return self::runSQL($sql);
    }
    public function updateRunStatus($project_pipeline_id, $status, $ownerID) {
        $sql = "UPDATE run SET run_status='$status', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    public function updateRunAttempt($project_pipeline_id, $attempt, $ownerID) {
        $sql = "UPDATE run SET attempt= '$attempt', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    public function updateRunPid($project_pipeline_id, $pid, $ownerID) {
        $sql = "UPDATE run SET pid='$pid', date_modified= now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::runSQL($sql);
    }
    public function getRunPid($project_pipeline_id) {
        $sql = "SELECT pid FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    public function getRunAttempt($project_pipeline_id) {
        $sql = "SELECT attempt FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
    public function getServerLog($project_pipeline_id,$ownerID) {
        $path= "../{$this->run_path}/run$project_pipeline_id";
        // get contents of a file into a string
        $filename = "$path/log.txt";
        $handle = fopen($filename, "r");
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return json_encode($content);
    }
    public function getRun($project_pipeline_id,$ownerID) {
        $sql = "SELECT * FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
		return self::queryTable($sql);
    }
    public function getRunStatus($project_pipeline_id,$ownerID) {
        $sql = "SELECT run_status FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
		return self::queryTable($sql);
    }
    public function getAmazonStatus($id,$ownerID) {
        $sql = "SELECT status, node_status FROM profile_amazon WHERE id = '$id'";
		return self::queryTable($sql);
    }
    public function getAmazonPid($id,$ownerID) {
        $sql = "SELECT pid FROM profile_amazon WHERE id = '$id'";
		return self::queryTable($sql);
    }
    public function sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $ownerID) {
        if ($profileType == 'cluster'){
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["username"]."@".$cluDataArr[0]["hostname"];
        } else if ($profileType == 'amazon'){
            $cluData=$this->getProfileAmazonbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["ssh"];
        }
        $ssh_id = $cluDataArr[0]["ssh_id"];
        $executor = $cluDataArr[0]['executor'];
        $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
			
//get preCmd to load prerequisites (eg: source /etc/bashrc) (to run qstat qdel)
        $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID));
        $proPipeCmd = $proPipeAll[0]->{'cmd'};
        $profileCmd = $cluDataArr[0]["cmd"];
        $imageCmd = "";
        $preCmd = $this->getPreCmd($profileType, $profileCmd, $proPipeCmd, $imageCmd);
			
        if ($executor == "lsf" && $commandType == "checkRunPid"){
        	$check_run = shell_exec("ssh {$this->ssh_settings} -i $userpky $connect \"$preCmd bjobs\" 2>&1 &");
            if (preg_match("/$pid/",$check_run)){
                return json_encode('running');
            } else {
            	return json_encode('done');
            }
        } else if ($executor == "sge" && $commandType == "checkRunPid"){
            $check_run = shell_exec("ssh {$this->ssh_settings} -i $userpky $connect \"$preCmd qstat -j $pid\" 2>&1 &");
            if (preg_match("/job_number:/",$check_run)){
                return json_encode('running');
            } else {
				$this->updateRunPid($project_pipeline_id, "0", $ownerID);
                return json_encode('done');
            } 
        } else if ($executor == "sge" && $commandType == "terminateRun"){
            $terminate_run = shell_exec("ssh {$this->ssh_settings} -i $userpky $connect \"$preCmd qdel $pid\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "lsf" && $commandType == "terminateRun"){
            $terminate_run = shell_exec("ssh {$this->ssh_settings} -i $userpky $connect \"$preCmd bkill $pid\" 2>&1 &");
            return json_encode('terminateCommandExecuted');
        } else if ($executor == "local" && $commandType == "terminateRun"){
            $cmd = "ssh {$this->ssh_settings} -i $userpky $connect \"$preCmd ps -ef |grep nextflow.*/run$project_pipeline_id/ |grep -v grep | awk '{print \\\"kill \\\"\\\$2}' |bash \" 2>&1 &";
        	$terminate_run = shell_exec($cmd);
            return json_encode('terminateCommandExecuted');
        } 
    	
	}
	public function terminateRun($pid, $project_pipeline_id, $ownerID) {
        $sql = "SELECT attempt FROM run WHERE project_pipeline_id = '$project_pipeline_id'";
        return self::queryTable($sql);
    }
	
    public function getNextflowLog($project_pipeline_id,$profileType,$profileId,$ownerID) {
        $path= "../{$this->run_path}/run$project_pipeline_id";
        // get contents of a file into a string
        $filename = "$path/nextflow.log";
        $handle = fopen($filename, "r");
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return json_encode($content);
    }
    
    public function saveNextflowLog($project_pipeline_id,$profileType,$profileId,$ownerID) {
         if ($profileType == 'cluster'){
            $cluData=$this->getProfileClusterbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["username"]."@".$cluDataArr[0]["hostname"];
         } else if ($profileType == 'amazon'){
            $cluData=$this->getProfileAmazonbyID($profileId, $ownerID);
            $cluDataArr=json_decode($cluData,true);
            $connect = $cluDataArr[0]["ssh"];
         }
            $ssh_id = $cluDataArr[0]["ssh_id"];
            $userpky = "{$this->ssh_path}/{$ownerID}_{$ssh_id}_ssh_pri.pky";
            if (!file_exists($userpky)) die(json_encode('Private key is not found!'));
            // get outputdir
            $proPipeAll = json_decode($this->getProjectPipelines($project_pipeline_id,"",$ownerID));
            $outdir = $proPipeAll[0]->{'output_dir'};
            $dolphin_path_real = "$outdir/run{$project_pipeline_id}";
            $nextflow_log = shell_exec("ssh {$this->ssh_settings} -i $userpky $connect 'cat $dolphin_path_real/log.txt 2>/dev/null' 2>&1 &");
            //if workdir not exist, create it
            if (!file_exists("../{$this->run_path}/run{$project_pipeline_id}")) {
                mkdir("../{$this->run_path}/run{$project_pipeline_id}", 0755, true);
            }
            // save $nextflow_log to a file 
            if ($nextflow_log != "" && !empty($nextflow_log)){
                $this->writeLog($project_pipeline_id,$nextflow_log,'w','nextflow.log');
            }
            return json_encode("nextflow log saved");
    }
//    ----------- Inputs, Project Inputs   ---------
    public function getInputs($id,$ownerID) {
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
    public function getProjectInputs($project_id,$ownerID) {
        $where = " where pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
		$sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                FROM project_input pi
                INNER JOIN input i ON i.id = pi.input_id
                LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                $where";
		return self::queryTable($sql);
    }
     public function getProjectFiles($project_id,$ownerID) {
        $where = " where (i.type = 'file' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
		$sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                FROM project_input pi
                INNER JOIN input i ON i.id = pi.input_id
                LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                $where";
		return self::queryTable($sql);
    }
    public function getPublicInputs($id) {
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
    public function getPublicFiles($host) {
        $sql = "SELECT id as input_id, name, date_modified FROM input WHERE type = 'file' AND host = '$host' AND perms = 63 ";
		return self::queryTable($sql);
    }
    public function getPublicValues($host) {
		$sql = "SELECT id as input_id, name, date_modified FROM input WHERE type = 'val' AND host = '$host' AND perms = 63 ";
		return self::queryTable($sql);
    }
    public function getProjectValues($project_id,$ownerID) {
        $where = " where (i.type = 'val' OR i.type IS NULL) AND pi.project_id = '$project_id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63 OR (ug.u_id ='$ownerID' and pi.perms = 15))" ;
		$sql = "SELECT DISTINCT pi.id, i.id as input_id, i.name, pi.date_modified,  IF(pi.owner_id='$ownerID',1,0) as own
                FROM project_input pi
                INNER JOIN input i ON i.id = pi.input_id
                LEFT JOIN user_group ug ON pi.group_id=ug.g_id
                $where";
		return self::queryTable($sql);
    }
    public function getProjectInput($id,$ownerID) {
        $where = " where pi.id = '$id' AND (pi.owner_id = '$ownerID' OR pi.perms = 63)" ;
		$sql = "SELECT pi.id, i.id as input_id, i.name
                FROM project_input pi
                INNER JOIN input i ON i.id = pi.input_id
                $where";
		return self::queryTable($sql);
    }
    public function insertProjectInput($project_id, $input_id, $ownerID) {
        $sql = "INSERT INTO project_input(project_id, input_id, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
			('$project_id', '$input_id', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function insertInput($name, $type, $ownerID) {
        $sql = "INSERT INTO input(name, type, owner_id, perms, date_created, date_modified, last_modified_user) VALUES
			('$name', '$type', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function updateInput($id, $name, $type, $ownerID) {
        $sql = "UPDATE input SET name='$name', type='$type', date_modified= now(), last_modified_user ='$ownerID'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function insertPublicInput($name, $type, $host, $ownerID) {
        $sql = "INSERT INTO input(name, type, host, owner_id, date_created, date_modified, last_modified_user, perms) VALUES ('$name', '$type', '$host', '$ownerID', now(), now(), '$ownerID', 63)";
        return self::insTable($sql);
    }
    public function updatePublicInput($id, $name, $type, $host, $ownerID) {
        $sql = "UPDATE input SET name= '$name', type= '$type', host= '$host', last_modified_user = '$ownerID', date_modified = now() WHERE id = '$id'";
        return self::runSQL($sql);
    }
    
     // ------- Project Pipelines  ------
    public function insertProjectPipeline($name, $project_id, $pipeline_id, $summary, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $ownerID) {
        $sql = "INSERT INTO project_pipeline(name, project_id, pipeline_id, summary, output_dir, profile, interdel, cmd, exec_each, exec_all, exec_all_settings, exec_each_settings, docker_check, docker_img, singu_check, singu_save, singu_img, exec_next_settings, docker_opt, singu_opt, amazon_cre_id, publish_dir, publish_dir_check, withReport, withTrace, withTimeline, withDag, process_opt, owner_id, date_created, date_modified, last_modified_user, perms)
                VALUES ('$name', '$project_id', '$pipeline_id', '$summary', '$output_dir', '$profile', '$interdel', '$cmd', '$exec_each', '$exec_all', '$exec_all_settings', '$exec_each_settings', '$docker_check', '$docker_img', '$singu_check', '$singu_save', '$singu_img', '$exec_next_settings', '$docker_opt', '$singu_opt', '$amazon_cre_id', '$publish_dir','$publish_dir_check', '$withReport', '$withTrace', '$withTimeline', '$withDag', '$process_opt', '$ownerID', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    public function updateProjectPipeline($id, $name, $summary, $output_dir, $perms, $profile, $interdel, $cmd, $group_id, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $ownerID) {
        $sql = "UPDATE project_pipeline SET name='$name', summary='$summary', output_dir='$output_dir', perms='$perms', profile='$profile', interdel='$interdel', cmd='$cmd', group_id='$group_id', exec_each='$exec_each', exec_all='$exec_all', exec_all_settings='$exec_all_settings', exec_each_settings='$exec_each_settings', docker_check='$docker_check', docker_img='$docker_img', singu_check='$singu_check', singu_save='$singu_save', singu_img='$singu_img', exec_next_settings='$exec_next_settings', docker_opt='$docker_opt', singu_opt='$singu_opt', amazon_cre_id='$amazon_cre_id', publish_dir='$publish_dir', publish_dir_check='$publish_dir_check', date_modified= now(), last_modified_user ='$ownerID', withReport='$withReport', withTrace='$withTrace', withTimeline='$withTimeline', withDag='$withDag',  process_opt='$process_opt' WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function getProjectPipelines($id,$project_id,$ownerID) {
		if ($id != ""){
			$where = " where pp.id = '$id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
            $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.project_id, pp.pipeline_id, pp.date_created, pp.date_modified, pp.owner_id, p.name as project_name, pp.output_dir, pp.profile, pp.interdel, pp.group_id, pp.exec_each, pp.exec_all, pp.exec_all_settings, pp.exec_each_settings, pp.perms, pp.docker_check, pp.docker_img, pp.singu_check, pp.singu_save, pp.singu_img, pp.exec_next_settings, pp.cmd, pp.singu_opt, pp.docker_opt, pp.amazon_cre_id, pp.publish_dir, pp.publish_dir_check, pp.withReport, pp.withTrace, pp.withTimeline, pp.withDag, pp.process_opt, IF(pp.owner_id='$ownerID',1,0) as own
                    FROM project_pipeline pp
                    INNER JOIN users u ON pp.owner_id = u.id
                    INNER JOIN project p ON pp.project_id = p.id
                    INNER JOIN biocorepipe_save pip ON pip.id = pp.pipeline_id
                    LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                    $where";
		} else {
            $where = " where pp.project_id = '$project_id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))" ;
            $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, pip.id as pip_id, pip.rev_id, pip.name, u.username, pp.summary, pp.date_modified, IF(pp.owner_id='$ownerID',1,0) as own
                    FROM project_pipeline pp
                    INNER JOIN biocorepipe_save pip ON pip.id = pp.pipeline_id
                    INNER JOIN users u ON pp.owner_id = u.id
                    LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                    $where";
        }
		return self::queryTable($sql);
    }
    public function getExistProjectPipelines($pipeline_id,$ownerID) {
			$where = " where pp.pipeline_id = '$pipeline_id' AND (pp.owner_id = '$ownerID' OR pp.perms = 63 OR (ug.u_id ='$ownerID' and pp.perms = 15))";
            $sql = "SELECT DISTINCT pp.id, pp.name as pp_name, u.username, pp.date_modified, p.name as project_name
                    FROM project_pipeline pp
                    INNER JOIN users u ON pp.owner_id = u.id
                    INNER JOIN project p ON pp.project_id = p.id
                    LEFT JOIN user_group ug ON pp.group_id=ug.g_id
                    $where";
		return self::queryTable($sql);
    }
     // ------- Project Pipeline Inputs  ------
    public function insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $ownerID) {
        $sql = "INSERT INTO project_pipeline_input(project_pipeline_id, input_id, project_id, pipeline_id, g_num, given_name, qualifier, owner_id, perms, date_created, date_modified, last_modified_user) VALUES ('$project_pipeline_id', '$input_id', '$project_id', '$pipeline_id', '$g_num', '$given_name', '$qualifier', '$ownerID', 3, now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
    public function updateProPipeInput($id, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET project_pipeline_id='$project_pipeline_id', input_id='$input_id', project_id='$project_id', pipeline_id='$pipeline_id', g_num='$g_num', given_name='$given_name', qualifier='$qualifier', last_modified_user ='$ownerID'  WHERE id = $id";
        return self::runSQL($sql);
    }
	public function renameProjectPipelineInputByGnum($id, $given_name, $g_num, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET given_name='$given_name', last_modified_user ='$ownerID', date_modified = now() WHERE pipeline_id = '$id' AND g_num = '$g_num'";
        return self::runSQL($sql);
    }
    public function duplicateProjectPipelineInput($new_id,$old_id,$ownerID) {
        $sql = "INSERT INTO project_pipeline_input(input_id, project_id, pipeline_id, g_num, given_name, qualifier, project_pipeline_id, owner_id, perms, date_created, date_modified, last_modified_user)
                SELECT input_id, project_id, pipeline_id, g_num, given_name, qualifier, '$new_id', '$ownerID', '3', now(), now(),'$ownerID'
                FROM project_pipeline_input
                WHERE project_pipeline_id='$old_id'";
        return self::insTable($sql);
    }
    public function duplicateProcess($new_process_gid, $new_name, $old_id, $ownerID) {
        $sql = "INSERT INTO process(process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, owner_id, perms, date_created, date_modified, last_modified_user, rev_id, process_gid)
                SELECT process_group_id, '$new_name', summary, script, script_header, script_footer, script_mode, script_mode_header, '$ownerID', '3', now(), now(),'$ownerID', '0', '$new_process_gid'
                FROM process
                WHERE id='$old_id'";
        return self::insTable($sql);
    }
    public function createProcessRev($new_process_gid, $rev_comment, $rev_id, $old_id, $ownerID) {
        $sql = "INSERT INTO process(process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, owner_id, perms, date_created, date_modified, last_modified_user, rev_id, process_gid, rev_comment)
                SELECT process_group_id, name, summary, script, script_header, script_footer, script_mode, script_mode_header, '$ownerID', '3', now(), now(),'$ownerID', '$rev_id', '$new_process_gid', '$rev_comment'
                FROM process
                WHERE id='$old_id'";
        return self::insTable($sql);
    }
    public function duplicateProcessParameter($new_pro_id, $old_id, $ownerID){
        $sql = "INSERT INTO process_parameter(process_id, parameter_id, type, sname, operator, closure, reg_ex, owner_id, perms, date_created, date_modified, last_modified_user)
                SELECT '$new_pro_id', parameter_id, type, sname, operator, closure, reg_ex, '$ownerID', '3', now(), now(),'$ownerID'
                FROM process_parameter
                WHERE process_id='$old_id'";
        return self::insTable($sql);
    }
    public function getProjectPipelineInputs($project_pipeline_id,$ownerID) {
        $where = " where ppi.project_pipeline_id = '$project_pipeline_id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63 OR (ug.u_id ='$ownerID' and ppi.perms = 15))";
		$sql = "SELECT DISTINCT ppi.id, i.id as input_id, i.name, ppi.given_name, ppi.g_num
                FROM project_pipeline_input ppi
                INNER JOIN input i ON i.id = ppi.input_id
                LEFT JOIN user_group ug ON ppi.group_id=ug.g_id
                $where";
		return self::queryTable($sql);
    }
    public function getProjectPipelineInputsByGnum($g_num, $project_pipeline_id,$ownerID) {
        $where = " where ppi.g_num= '$g_num' AND ppi.project_pipeline_id = '$project_pipeline_id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63 OR (ug.u_id ='$ownerID' and ppi.perms = 15))";
		$sql = "SELECT DISTINCT ppi.id, i.id as input_id, i.name, ppi.given_name, ppi.g_num
                FROM project_pipeline_input ppi
                INNER JOIN input i ON i.id = ppi.input_id
                LEFT JOIN user_group ug ON ppi.group_id=ug.g_id
                $where";
		return self::queryTable($sql);
    }
    public function getProjectPipelineInputsById($id,$ownerID) {
        $where = " where ppi.id= '$id' AND (ppi.owner_id = '$ownerID' OR ppi.perms = 63)" ;
		$sql = "SELECT ppi.id, ppi.qualifier, i.id as input_id, i.name
                FROM project_pipeline_input ppi
                INNER JOIN input i ON i.id = ppi.input_id
                $where";
		return self::queryTable($sql);
    }
    public function insertProcessParameter($sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $perms, $group_id, $ownerID) {
        $sql = "INSERT INTO process_parameter(sname, process_id, parameter_id, type, closure, operator, reg_ex, owner_id, date_created, date_modified, last_modified_user, perms, group_id)
                VALUES ('$sname', '$process_id', '$parameter_id', '$type', '$closure', '$operator', '$reg_ex', '$ownerID', now(), now(), '$ownerID', '$perms', '$group_id')";
        return self::insTable($sql);
    }

    public function updateProcessParameter($id, $sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $perms, $group_id, $ownerID) {
        $sql = "UPDATE process_parameter SET sname='$sname', process_id='$process_id', parameter_id='$parameter_id', type='$type', closure='$closure', operator='$operator', reg_ex='$reg_ex', last_modified_user ='$ownerID', perms='$perms', group_id='$group_id'  WHERE id = '$id'";
        return self::runSQL($sql);
    }

    public function removeProcessParameter($id) {
        $sql = "DELETE FROM process_parameter WHERE id = '$id'";
        return self::runSQL($sql);
    }

    public function removeProcessParameterByParameterID($parameter_id) {
        $sql = "DELETE FROM process_parameter WHERE parameter_id = '$parameter_id'";
        return self::runSQL($sql);
    }
    public function removeProcessParameterByProcessGroupID($process_group_id) {
        $sql = "DELETE process_parameter
                FROM process_parameter
                JOIN process ON process.id = process_parameter.process_id
                WHERE process.process_group_id = '$process_group_id'";
        return self::runSQL($sql);
    }
    public function removeProcessParameterByProcessID($process_id) {
        $sql = "DELETE FROM process_parameter WHERE process_id = '$process_id'";
        return self::runSQL($sql);
    }
    //------- feedback ------
    public function savefeedback($email,$message,$url) {
        $sql = "INSERT INTO feedback(email, message, url, date_created) VALUES
			('$email', '$message','$url', now())";
        return self::insTable($sql);
    }
// --------- Pipeline -----------
	  public function getPipelineGroup($ownerID) {
        $userRoleCheck = $this->getUserRole($ownerID);
        if (isset(json_decode($userRoleCheck)[0])){
            $userRole = json_decode($userRoleCheck)[0]->{'role'};
            if ($userRole == "admin"){
                $sql = "SELECT DISTINCT pg.id, pg.group_name
                FROM pipeline_group pg";
                return self::queryTable($sql);
            }
        }
        $sql = "SELECT DISTINCT pg.id, pg.group_name
        FROM pipeline_group pg
        LEFT JOIN user_group ug ON pg.group_id=ug.g_id
        WHERE pg.owner_id = '$ownerID' OR pg.perms = 63 OR (ug.u_id ='$ownerID' and pg.perms = 15)";
        return self::queryTable($sql);
    }
	
	public function insertPipelineGroup($group_name, $ownerID) {
        $sql = "INSERT INTO pipeline_group (owner_id, group_name, date_created, date_modified, last_modified_user, perms) VALUES ('$ownerID', '$group_name', now(), now(), '$ownerID', 3)";
        return self::insTable($sql);
    }
    public function updatePipelineGroup($id, $group_name, $ownerID) {
        $sql = "UPDATE pipeline_group SET group_name='$group_name', last_modified_user ='$ownerID', date_modified=now()  WHERE id = '$id'";
        return self::runSQL($sql);
    }
	public function getEditDelPipelineGroups($ownerID) {
        $sql = "SELECT id, group_name FROM pipeline_group WHERE owner_id = '$ownerID'";
        return self::queryTable($sql);
    }
	
    public function getPublicPipelines() {
        $sql= "SELECT pip.id, pip.name, pip.summary, pip.pin, pip.pin_order, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id
               FROM biocorepipe_save pip
               INNER JOIN (
                SELECT pipeline_gid, MAX(rev_id) rev_id
                FROM biocorepipe_save
                WHERE pin = 'true' AND perms = 63
                GROUP BY pipeline_gid
                ) b ON pip.rev_id = b.rev_id AND pip.pipeline_gid=b.pipeline_gid ";
     return self::queryTable($sql);
   }
	public function getProcessData($ownerID) {
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
    public function getProcessDataById($id, $ownerID) {
        if ($ownerID == ""){
            $ownerID ="''";
        }else {
            $userRoleCheck = $this->getUserRole($ownerID);
            if (isset(json_decode($userRoleCheck)[0])){
                $userRole = json_decode($userRoleCheck)[0]->{'role'};
                if ($userRole == "admin"){
                    $sql = "SELECT DISTINCT p.*, u.username, IF(p.owner_id='$ownerID',1,0) as own 
					FROM process p 
					INNER JOIN users u ON p.owner_id = u.id
					where p.id = '$id'";
                    return self::queryTable($sql);
                }
            }
		}
		$sql = "SELECT DISTINCT p.*, u.username, IF(p.owner_id='$ownerID',1,0) as own
        FROM process p
        LEFT JOIN user_group ug ON p.group_id=ug.g_id
		INNER JOIN users u ON p.owner_id = u.id
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
                        $sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms FROM biocorepipe_save pip WHERE pip.pipeline_gid = '$pipeline_gid'";
                        return self::queryTable($sql);
                    }
                }
        }
		$sql = "SELECT DISTINCT pip.id, pip.rev_id, pip.rev_comment, pip.last_modified_user, pip.date_created, pip.date_modified, IF(pip.owner_id='$ownerID',1,0) as own, pip.perms
        FROM biocorepipe_save pip
        LEFT JOIN user_group ug ON pip.group_id=ug.g_id
        WHERE pip.pipeline_gid = '$pipeline_gid' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
		return self::queryTable($sql);
	}

    public function getProcessGID($id) {
		$sql = "SELECT  process_gid FROM process WHERE id = '$id'";
		return self::queryTable($sql);
	}
    public function getPipelineGID($id) {
		$sql = "SELECT pipeline_gid FROM biocorepipe_save WHERE id = '$id'";
		return self::queryTable($sql);
	}
	public function getInputsPP($id) {
		$sql = "SELECT parameter_id, sname, id, operator, closure, reg_ex FROM process_parameter where process_id = '$id' and type = 'input'";
		return self::queryTable($sql);
	}
	public function checkPipeline($process_id, $ownerID) {
		$sql = "SELECT id, name FROM biocorepipe_save WHERE (owner_id = '$ownerID') AND nodes LIKE '%\"$process_id\",\"%'";
		return self::queryTable($sql);
	}
    public function checkInput($name,$type) {
		$sql = "SELECT id, name FROM input WHERE name = '$name' AND type='$type'";
		return self::queryTable($sql);
	}
    public function checkProjectInput($project_id, $input_id) {
		$sql = "SELECT id FROM project_input WHERE input_id = '$input_id' AND project_id = '$project_id'";
		return self::queryTable($sql);
	}
    public function checkProPipeInput($project_id, $input_id, $pipeline_id, $project_pipeline_id) {
		$sql = "SELECT id FROM project_pipeline_input WHERE input_id = '$input_id' AND project_id = '$project_id' AND pipeline_id = '$pipeline_id' AND project_pipeline_id = '$project_pipeline_id'";
		return self::queryTable($sql);
    }
    public function checkPipelinePublic($process_id, $ownerID) {
		$sql = "SELECT id, name FROM biocorepipe_save WHERE (owner_id != '$ownerID') AND nodes LIKE '%\"$process_id\",\"%'";
		return self::queryTable($sql);
	}
	public function checkProjectPipelinePublic($process_id, $ownerID) {
		$sql = "SELECT DISTINCT p.id, p.name 
				FROM biocorepipe_save p
			    INNER JOIN project_pipeline pp ON p.id = pp.pipeline_id
				WHERE (pp.owner_id != '$ownerID') AND p.nodes LIKE '%\"$process_id\",\"%'";
		return self::queryTable($sql);
	}
    public function checkPipelinePerm($process_id) {
		$sql = "SELECT id, name FROM biocorepipe_save WHERE perms>3 AND nodes LIKE '%\"$process_id\",\"%'";
		return self::queryTable($sql);
	}
    public function checkProjectPipePerm($pipeline_id) {
		$sql = "SELECT id, name FROM project_pipeline WHERE perms>3 AND pipeline_id='$pipeline_id'";
		return self::queryTable($sql);
	}
    public function checkParameter($parameter_id, $ownerID) {
		$sql = "SELECT DISTINCT pp.id, p.name
        FROM process_parameter pp
        INNER JOIN process p ON pp.process_id = p.id
        WHERE (pp.owner_id = '$ownerID') AND pp.parameter_id = '$parameter_id'";
		return self::queryTable($sql);
	}
    public function checkMenuGr($id) {
		$sql = "SELECT DISTINCT pg.id, p.name
        FROM process p
        INNER JOIN process_group pg ON p.process_group_id = pg.id
        WHERE pg.id = '$id'";
		return self::queryTable($sql);
	}
	public function checkPipeMenuGr($id) {
		$sql = "SELECT DISTINCT pg.id, p.name
        FROM biocorepipe_save p
        INNER JOIN pipeline_group pg ON p.pipeline_group_id = pg.id
        WHERE pg.id = '$id'";
		return self::queryTable($sql);
	}
    public function checkProject($pipeline_id, $ownerID) {
		$sql = "SELECT DISTINCT pp.id, p.name
        FROM project_pipeline pp
        INNER JOIN project p ON pp.project_id = p.id
        WHERE (pp.owner_id = '$ownerID') AND pp.pipeline_id = '$pipeline_id'";
		return self::queryTable($sql);
	}
    public function checkProjectPublic($pipeline_id, $ownerID) {
		$sql = "SELECT DISTINCT pp.id, p.name
        FROM project_pipeline pp
        INNER JOIN project p ON pp.project_id = p.id
        WHERE (pp.owner_id != '$ownerID') AND pp.pipeline_id = '$pipeline_id'";
		return self::queryTable($sql);
	}
    public function getMaxProcess_gid() {
		$sql = "SELECT MAX(process_gid) process_gid FROM process";
		return self::queryTable($sql);
	}
    public function getMaxPipeline_gid() {
		$sql = "SELECT MAX(pipeline_gid) pipeline_gid FROM biocorepipe_save";
		return self::queryTable($sql);
	}
    public function getProcess_gid($process_id) {
		$sql = "SELECT process_gid FROM process WHERE id = '$process_id'";
		return self::queryTable($sql);
	}
    public function getPipeline_gid($pipeline_id) {
		$sql = "SELECT pipeline_gid FROM biocorepipe_save WHERE id = '$pipeline_id'";
		return self::queryTable($sql);
	}
    public function getMaxRev_id($process_gid) {
		$sql = "SELECT MAX(rev_id) rev_id FROM process WHERE process_gid = '$process_gid'";
		return self::queryTable($sql);
	}
    public function getMaxPipRev_id($pipeline_gid) {
		$sql = "SELECT MAX(rev_id) rev_id FROM biocorepipe_save WHERE pipeline_gid = '$pipeline_gid'";
		return self::queryTable($sql);
	}
	public function getOutputsPP($id) {
		$sql = "SELECT parameter_id, sname, id, operator, closure, reg_ex FROM process_parameter where process_id = '$id' and type = 'output'";
		return self::queryTable($sql);
	}
	//update if user owns the project
    public function updateProjectGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project p
        INNER JOIN project_pipeline pp ON p.id=pp.project_id
        SET p.group_id='$group_id', p.perms='$perms', p.date_modified=now(), p.last_modified_user ='$ownerID'  WHERE pp.id = '$id' AND p.perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updateProjectInputGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project_input pi
        INNER JOIN project_pipeline_input ppi ON pi.input_id=ppi.input_id
        SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE ppi.project_pipeline_id = '$id' and pi.perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updateProjectPipelineInputGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE project_pipeline_input SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE project_pipeline_id = '$id' AND perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updateInputGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE input i
        INNER JOIN project_pipeline_input ppi ON ppi.input_id=i.id
        SET i.group_id='$group_id', i.perms='$perms', i.date_modified=now(), i.last_modified_user ='$ownerID'  WHERE ppi.project_pipeline_id = '$id' and  i.perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updatePipelineGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE biocorepipe_save pi
        INNER JOIN project_pipeline_input ppi ON pi.id=ppi.pipeline_id
        SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE ppi.project_pipeline_id = '$id' AND pi.perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updatePipelineGroupPermByPipeId($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE biocorepipe_save pi
        SET pi.group_id='$group_id', pi.perms='$perms', pi.date_modified=now(), pi.last_modified_user ='$ownerID'  WHERE pi.id = '$id' AND pi.perms <= '$perms'";
        return self::runSQL($sql);
    }
     public function updatePipelineProcessGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "SELECT pip.nodes
        FROM biocorepipe_save pip
        INNER JOIN project_pipeline_input pi ON pip.id=pi.pipeline_id
        WHERE pi.project_pipeline_id = '$id' and pi.owner_id='$ownerID'";
        $nodesArr = json_decode(self::queryTable($sql));
        if (!empty($nodesArr[0])){
        $nodes = json_decode($nodesArr[0]->{"nodes"});
        foreach ($nodes as $item):
            if ($item[2] !== "inPro" && $item[2] !== "outPro"){
                $proId = $item[2];
                $this->updateParameterGroupPerm($proId, $group_id, $perms, $ownerID);
                $this->updateProcessGroupPerm($proId, $group_id, $perms, $ownerID);
                $this->updateProcessParameterGroupPerm($proId, $group_id, $perms, $ownerID);
                $this->updateProcessGroupGroupPerm($proId, $group_id, $perms, $ownerID);
            }
        endforeach;
        }
     }

    //update if user owns the process
    public function updateProcessGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE process SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id' and  perms <= '$perms'";
        return self::runSQL($sql);
    }
    
    public function updateProcessParameterGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE process_parameter SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE process_id = '$id' AND perms <= '$perms'";
        return self::runSQL($sql);
    }
    
    public function updateParameterGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE parameter p
                INNER JOIN process_parameter pp ON p.id=pp.parameter_id
                SET p.group_id='$group_id', p.perms='$perms', p.date_modified=now(), p.last_modified_user ='$ownerID'  WHERE pp.process_id = '$id' and  p.perms <= '$perms'";
        return self::runSQL($sql);
    }
    
    public function updateParameterGroupPermById($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE parameter
                SET group_id='$group_id', perms='$perms', date_modified=now(), last_modified_user ='$ownerID'  WHERE id = '$id' and perms <= '$perms'";
        return self::runSQL($sql);
    }
    
    public function updateProcessGroupGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE process_group pg
                INNER JOIN process p ON pg.id=p.process_group_id
                SET pg.group_id='$group_id', pg.perms='$perms', pg.date_modified=now(), pg.last_modified_user ='$ownerID'  WHERE p.id = '$id' AND pg.perms <= '$perms'";
        return self::runSQL($sql);
    }
    
	public function updatePipelineGroupGroupPerm($id, $group_id, $perms, $ownerID) {
        $sql = "UPDATE pipeline_group pg
                INNER JOIN biocorepipe_save p ON pg.id=p.pipeline_group_id
                SET pg.group_id='$group_id', pg.perms='$perms', pg.date_modified=now(), pg.last_modified_user ='$ownerID'  WHERE p.id = '$id' AND pg.perms <= '$perms'";
        return self::runSQL($sql);
    }
    public function updatePipelinePerms($nodesRaw, $group_id, $perms, $ownerID) {
        foreach ($nodesRaw as $item):
            if ($item[2] !== "inPro" && $item[2] !== "outPro" ){
                //pipeline modules
                if (preg_match("/p(.*)/", $item[2], $matches)){
                    $pipeModId = $matches[1];
                    if (!empty($pipeModId)){
                        settype($pipeModId, "integer");
                        $this->updatePipelineGroupPermByPipeId($pipeModId, $group_id, $perms, $ownerID);
                        $this->updatePipelineGroupGroupPerm($pipeModId, $group_id, $perms, $ownerID);
                    } 
                //processes    
                } else {
                    $proId = $item[2];
                    $this->updateParameterGroupPerm($proId, $group_id, $perms, $ownerID);
                    $this->updateProcessGroupPerm($proId, $group_id, $perms, $ownerID);
                    $this->updateProcessParameterGroupPerm($proId, $group_id, $perms, $ownerID);
                    $this->updateProcessGroupGroupPerm($proId, $group_id, $perms, $ownerID);
                }
            }  
        endforeach;
    }
    
	public function saveAllPipeline($dat,$ownerID) {
		$obj = json_decode($dat);
		$name =  $obj[0]->{"name"};
        $id = $obj[1]->{"id"};
		$nodes = json_encode($obj[2]->{"nodes"});
		$mainG = "{\'mainG\':".json_encode($obj[3]->{"mainG"})."}";
		$edges = "{\'edges\':".json_encode($obj[4]->{"edges"})."}";
        $summary = addslashes(htmlspecialchars(urldecode($obj[5]->{"summary"}), ENT_QUOTES));
        $group_id = $obj[6]->{"group_id"};
        $perms = $obj[7]->{"perms"};
        $pin = $obj[8]->{"pin"};
        $pin_order = $obj[9]->{"pin_order"};
        $publish = $obj[10]->{"publish"};
        $script_pipe_header = addslashes(htmlspecialchars(urldecode($obj[11]->{"script_pipe_header"}), ENT_QUOTES));
        $script_pipe_footer = addslashes(htmlspecialchars(urldecode($obj[12]->{"script_pipe_footer"}), ENT_QUOTES));
        $script_mode_header = $obj[13]->{"script_mode_header"};
        $script_mode_footer = $obj[14]->{"script_mode_footer"};
        $pipeline_group_id = $obj[15]->{"pipeline_group_id"};
        $process_list = $obj[16]->{"process_list"};
        $pipeline_list = $obj[17]->{"pipeline_list"};
        $pipeline_gid = $obj[18]->{"pipeline_gid"};
        $rev_comment = $obj[19]->{"rev_comment"};
        $rev_id = $obj[20]->{"rev_id"};
        settype($rev_id, "integer");
        settype($pipeline_gid, "integer");
        settype($group_id, "integer");
        settype($pin_order, "integer");
        $nodesRaw = $obj[2]->{"nodes"};
        if (!empty($nodesRaw)){
            $this->updatePipelinePerms($nodesRaw, $group_id, $perms, $ownerID);
        }
	    if ($id > 0){
			//update all pipeline group_id
//    		$pipeline_gid = json_decode($this->getPipelineGID($id))[0]->{'pipeline_gid'};
//			$this->updateAllPipelineGroupByGid($pipeline_gid,$pipeline_group_id,$ownerID);
			$sql = "UPDATE biocorepipe_save set name = '$name', edges = '$edges', summary = '$summary', mainG = '$mainG', nodes ='$nodes', date_modified = now(), group_id = '$group_id', perms = '$perms', pin = '$pin', publish = '$publish', script_pipe_header = '$script_pipe_header', script_pipe_footer = '$script_pipe_footer', script_mode_header = '$script_mode_header', script_mode_footer = '$script_mode_footer', pipeline_group_id='$pipeline_group_id', process_list='$process_list', pipeline_list='$pipeline_list', pin_order = '$pin_order', last_modified_user = '$ownerID' where id = '$id'";
		}else{
            $sql = "INSERT INTO biocorepipe_save(owner_id, summary, edges, mainG, nodes, name, pipeline_gid, rev_comment, rev_id, date_created, date_modified, last_modified_user, group_id, perms, pin, pin_order, publish, script_pipe_header, script_pipe_footer, script_mode_header, script_mode_footer,pipeline_group_id,process_list,pipeline_list) VALUES ('$ownerID', '$summary', '$edges', '$mainG', '$nodes', '$name', '$pipeline_gid', '$rev_comment', '$rev_id', now(), now(), '$ownerID', '$group_id', '$perms', '$pin', '$pin_order', $publish, '$script_pipe_header', '$script_pipe_footer', '$script_mode_header', '$script_mode_footer', '$pipeline_group_id', '$process_list', '$pipeline_list' )";
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
                    $sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id
                    FROM biocorepipe_save pip
                    INNER JOIN users u ON pip.owner_id = u.id";
                    return self::queryTable($sql);
                }
            }
        }
        $where = " where pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15)";
		$sql = "select DISTINCT pip.id, pip.rev_id, pip.name, pip.summary, pip.date_modified, u.username, pip.script_pipe_header, pip.script_pipe_footer, pip.script_mode_header, pip.script_mode_footer, pip.pipeline_group_id
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
                        $sql = "select pip.*, u.username, IF(pip.owner_id='$ownerID',1,0) as own
                        FROM biocorepipe_save pip
                        INNER JOIN users u ON pip.owner_id = u.id
                        where pip.id = '$id'";
                        return self::queryTable($sql);
                    }
                }
            }
		$sql = "select pip.*, u.username, IF(pip.owner_id='$ownerID',1,0) as own
                FROM biocorepipe_save pip
                INNER JOIN users u ON pip.owner_id = u.id
                LEFT JOIN user_group ug ON pip.group_id=ug.g_id
                where pip.id = '$id' AND (pip.owner_id = '$ownerID' OR pip.perms = 63 OR (ug.u_id ='$ownerID' and pip.perms = 15))";
	   return self::queryTable($sql);
	}
    public function removePipelineById($id) {
		$sql = "DELETE FROM biocorepipe_save WHERE id = '$id'";
	   return self::runSQL($sql);
	}
    public function updatePipelineName($id, $name) {
        $sql = "UPDATE biocorepipe_save SET name='$name'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function savePipelineDetails($id, $summary,$group_id, $perms, $pin, $pin_order, $publish, $pipeline_group_id, $ownerID) {
        $sql = "UPDATE biocorepipe_save SET summary='$summary', group_id='$group_id', publish='$publish', perms='$perms', pin='$pin', pin_order='$pin_order', last_modified_user = '$ownerID', pipeline_group_id='$pipeline_group_id'  WHERE id = '$id'";
        return self::runSQL($sql);
    }
    public function insertPipelineName($name,$ownerID) {
        $sql = "INSERT INTO biocorepipe_save(owner_id, name, rev_id, date_created, date_modified, last_modified_user) VALUES
			('$ownerID','$name', '0', now(), now(), '$ownerID')";
        return self::insTable($sql);
    }
}
