<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$_SESSION['ownerID'] = '1';
$_SESSION['username'] = 'admin';
$_SESSION['google_id'] = '111';
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$google_id = isset($_SESSION['google_id']) ? $_SESSION['google_id'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

chdir('ajax/');
use PHPUnit\Framework\TestCase;


class ajaxQueryTest extends TestCase
{
    public function testgetMaxProcess_gid() {
		ob_start();
		$_REQUEST['p'] = 'getMaxProcess_gid';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->process_gid,null);
		ob_end_clean();
	}
    public function testInsertParameter() {
		ob_start();
		$_REQUEST['p'] = 'saveParameter';
		$_REQUEST['name'] = "inputDir";
		$_REQUEST['qualifier'] = "val";
		$_REQUEST['file_type'] = "inputDir";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertParameter
     */
    public function testupdateParameter() {
	ob_start();
		$_REQUEST['p'] = 'saveParameter';
		$_REQUEST['id'] = "1";
		$_REQUEST['name'] = "inputDir";
		$_REQUEST['qualifier'] = "val";
		$_REQUEST['file_type'] = "inputDir_update";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getAllParameters';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->name,'inputDir');
		$this->assertEquals(json_decode($data)[0]->file_type,'inputDir_update');
		ob_end_clean();
	}
        /**
     * @depends testupdateParameter
     */
    public function testgetAllParameters() {
		ob_start();
		$_REQUEST['p'] = 'getAllParameters';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'inputDir');
		ob_end_clean();
	}
    /**
     * @depends testupdateParameter
     */
    public function testgetEditDelParameters() {
		ob_start();
		$_REQUEST['p'] = 'getEditDelParameters';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'inputDir');
		ob_end_clean();
	}


    public function testInsertUser() {
		ob_start();
		$_REQUEST['p'] = 'saveUser';
		$_REQUEST['id'] = '';
		$_REQUEST['google_id'] = '111';
		$_REQUEST['name'] = "onur yukselen";
		$_REQUEST['google_image'] = "https://lh4.googleusercontent.com/-h7_FO3k9sB4/AAAAAAAAAAI/AAAAAAAAAAA/AGi4gfw9MqsLVfHz5xXsoOzA1KIZ1yLwXw/s96-c/photo.jpg";
		$_REQUEST['username'] = "admin";
		$_REQUEST['email'] = "admin@gmail.com";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertUser
     */
    public function testInsertUser2() {
		ob_start();
		$_REQUEST['p'] = 'saveUser';
		$_REQUEST['id'] = '';
		$_REQUEST['google_id'] = '222';
		$_REQUEST['name'] = "member name";
		$_REQUEST['google_image'] = "https://lh4.googleusercontent.com/-h7_FO3k9sB4/AAAAAAAAAAI/AAAAAAAAAAA/AGi4gfw9MqsLVfHz5xXsoOzA1KIZ1yLwXw/s96-c/photo.jpg";
		$_REQUEST['username'] = "member";
		$_REQUEST['email'] = "member@gmail.com";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
     /**
     * @depends testInsertUser
     */
    public function testUpdateUser() {
		ob_start();
		$_REQUEST['p'] = 'saveUser';
		$_REQUEST['id'] = '1';
		$_REQUEST['google_id'] = '111';
		$_REQUEST['name'] = "onur yukselen";
		$_REQUEST['google_image'] = "https://lh6.googleusercontent.com/-j-GMmh9Xzd0/AAAAAAAAAAI/AAAAAAAAByM/HnRa5tGHpLU/s96-c/photo.jpg";
		$_REQUEST['username'] = "admin";
		$_REQUEST['email'] = "admin@gmail.com";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($db->getUser($google_id))[0]->id,'1');
		ob_end_clean();
		$this->assertEquals(json_decode($db->getUser($google_id))[0]->google_image,'https://lh6.googleusercontent.com/-j-GMmh9Xzd0/AAAAAAAAAAI/AAAAAAAAByM/HnRa5tGHpLU/s96-c/photo.jpg');
	}
    /**
     * @depends testUpdateUser
     */
    public function testInsertGroup() {
		ob_start();
		$_REQUEST['p'] = 'saveGroup';
		$_REQUEST['name'] = 'test_group';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
     /**
     * @depends testInsertGroup
     */
    public function testInsertUserGroup() {
		ob_start();
		$_REQUEST['p'] = 'saveUserGroup';
		$_REQUEST['u_id'] = '1';
		$_REQUEST['g_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    public function testInsertProject() {
		ob_start();
		$_REQUEST['p'] = 'saveProject';
		$_REQUEST['name'] = 'test_project';
		$_REQUEST['summary'] = "testSummary";
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertProject
     */
	public function testGetProjects() {
		ob_start();
		$_REQUEST['p'] = 'getProjects';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_project');
		$this->assertEquals(json_decode($data)[0]->summary,'testSummary');
		ob_end_clean();
	}
    /**
     * @depends testGetProjects
     */
    public function testUpdateProject() {
		ob_start();
		$_REQUEST['p'] = 'saveProject';
		$_REQUEST['id'] = '1';
		$_REQUEST['name'] = 'test_project';
		$_REQUEST['summary'] = "testSummaryUpdate";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProjects';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_project');
		$this->assertEquals(json_decode($data)[0]->summary,'testSummaryUpdate');
		ob_end_clean();
	}
    public function testInsertProcessGroup() {
		ob_start();
		$_REQUEST['p'] = 'saveProcessGroup';
		$_REQUEST['group_name'] = 'test_menu_before';
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertProcessGroup
     */
    public function testUpdateProcessGroup() {
		ob_start();
		$_REQUEST['p'] = 'saveProcessGroup';
		$_REQUEST['id'] = '1';
		$_REQUEST['group_name'] = 'test_menu';
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getAllProcessGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->group_name, 'test_menu');
		ob_end_clean();
	}
	public function testInsertPipelineGroup() {
		ob_start();
		$_REQUEST['p'] = 'savePipelineGroup';
		$_REQUEST['group_name'] = 'test_menu_pipe_before';
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    /**
     * @depends testInsertPipelineGroup
     */
    public function testUpdatePipelineGroup() {
		ob_start();
		$_REQUEST['p'] = 'savePipelineGroup';
		$_REQUEST['id'] = '2';
		$_REQUEST['group_name'] = 'test_menu_pipe';
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getPipelineGroup';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[1]->id, '2');
		$this->assertEquals(json_decode($data)[1]->group_name, 'test_menu_pipe');
		ob_end_clean();
	}
	/**
     * @depends testUpdatePipelineGroup
     */
    public function testgetPipelineGroup() {
		ob_start();
		$_REQUEST['p'] = 'getPipelineGroup';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->group_name, 'Public Pipelines');
		ob_end_clean();
	}
    /**
     * @depends testgetMaxProcess_gid
     */
    public function testInsertProcess() {
		ob_start();
		$_REQUEST['p'] = 'saveProcess';
		$_REQUEST['id'] = '';
		$_REQUEST['name'] = 'test_process';
		$_REQUEST['process_gid'] = 'NaN';
		$_REQUEST['summary'] = 'test_summary';
		$_REQUEST['process_group_id'] = '1';
		$_REQUEST['script'] = 'test_script';
		$_REQUEST['script_header'] = 'test_script_header';
		$_REQUEST['script_footer'] = 'test_script_footer';
		$_REQUEST['script_mode'] = 'perl';
		$_REQUEST['script_mode_header'] = 'python';
		$_REQUEST['rev_id'] = '';
		$_REQUEST['rev_comment'] = '';
		$_REQUEST['group'] = '';
		$_REQUEST['perms'] = '15';
		$_REQUEST['publish'] = '0';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertProcess
     */
	public function testgetProcessData() {
		ob_start();
		$_REQUEST['p'] = 'getProcessData';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_process');
		$this->assertEquals(json_decode($data)[0]->summary,'test_summary');
		ob_end_clean();
	}
    /**
     * @depends testgetProcessData
     */
	public function testgetProcessDataById() {
		ob_start();
		$_REQUEST['p'] = 'getProcessData';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_process');
		$this->assertEquals(json_decode($data)[0]->summary,'test_summary');
		ob_end_clean();
	}
    /**
     * @depends testInsertProcess
     */
     public function testUpdateProcess() {
		ob_start();
		$_REQUEST['p'] = 'saveProcess';
		$_REQUEST['id'] = '1';
		$_REQUEST['name'] = 'test_process';
		$_REQUEST['process_gid'] = '0';
		$_REQUEST['summary'] = 'test_summary';
		$_REQUEST['process_group_id'] = '1';
		$_REQUEST['script'] = 'test_script';
		$_REQUEST['script_header'] = 'update_script_header';
		$_REQUEST['script_footer'] = 'update_script_footer';
		$_REQUEST['script_mode'] = 'perl';
		$_REQUEST['script_mode_header'] = 'python';
		$_REQUEST['rev_id'] = '';
		$_REQUEST['rev_comment'] = '';
		$_REQUEST['group'] = '';
		$_REQUEST['perms'] = '63';
		$_REQUEST['publish'] = '0';
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProcessData';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->perms,'63');
		$this->assertEquals(json_decode($data)[0]->script_header,'update_script_header');
		ob_end_clean();
	 }
	/**
     * @depends testUpdateProcess
     */
	public function testcheckMenuGr() {
		ob_start();
		$_REQUEST['p'] = 'checkMenuGr';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_process');
		ob_end_clean();
	}
	/**
     * @depends testUpdateProcess
     */
	public function testgetProcessRevision() {
		ob_start();
		$_REQUEST['p'] = 'getProcessRevision';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->rev_id,'0');
		ob_end_clean();
	}
	/**
     * @depends testgetProcessRevision
     */
	public function testgetProcess_gid() {
		ob_start();
		$_REQUEST['p'] = 'getProcess_gid';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->process_gid,'0');
		ob_end_clean();
	}
	/**
     * @depends testgetProcess_gid
     */
	public function testgetMaxRev_id() {
		ob_start();
		$_REQUEST['p'] = 'getMaxRev_id';
		$_REQUEST['process_gid'] = '0';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->rev_id,'0');
		ob_end_clean();
	}
	
	
	
	
	
	
     /**
     * @depends testUpdateProcessGroup
     * @depends testUpdateProcess
     */
    public function testgetAllProcessGroups() {
		ob_start();
		$_REQUEST['p'] = 'getAllProcessGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->group_name, 'test_menu');
		ob_end_clean();
	}
    public function testInsertInput() {
		ob_start();
		$_REQUEST['p'] = 'saveInput';
		$_REQUEST['name'] = "testinput_before_update";
		$_REQUEST['type'] = "val";
		$_REQUEST['id'] = "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertInput
     */
    public function testgetInputsById() {
		ob_start();
		$_REQUEST['p'] = 'getInputs';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput_before_update');
		ob_end_clean();
	}
    /**
     * @depends testgetInputsById
     */
    public function testUpdateInput() {
		ob_start();
		$_REQUEST['p'] = 'saveInput';
		$_REQUEST['name'] = "testinput";
        $_REQUEST['type'] = "val";
		$_REQUEST['id'] = "1";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getInputs';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput');
		ob_end_clean();
	}
	/**
     * @depends testUpdateInput
     */
	public function testinsertPublicInput() {
		ob_start();
		$_REQUEST['p'] = 'savePublicInput';
		$_REQUEST['name'] = "testpublicinputval";
		$_REQUEST['type'] = "val";
		$_REQUEST['host'] = "testhost";
		$_REQUEST['id'] = "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		
		ob_end_clean();
	}
	/**
     * @depends testinsertPublicInput
     */
	public function testinsertPublicInput2() {
		ob_start();
		$_REQUEST['p'] = 'savePublicInput';
		$_REQUEST['name'] = "testpublicinputfile";
		$_REQUEST['type'] = "file";
		$_REQUEST['host'] = "testhost";
		$_REQUEST['id'] = "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'3');
		
		ob_end_clean();
	}
	/**
     * @depends testinsertPublicInput2
     */
	public function testupdatePublicInput() {
		ob_start();
		$_REQUEST['p'] = 'savePublicInput';
		$_REQUEST['name'] = "testpublicinput_after_update";
		$_REQUEST['type'] = "val";
		$_REQUEST['host'] = "testhost";
		$_REQUEST['id'] = "2";
		include('ajaxquery.php');
		$_REQUEST['p'] = 'getInputs';
		$_REQUEST['id'] = '2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'2');
		$this->assertEquals(json_decode($data)[0]->name, 'testpublicinput_after_update');
		ob_end_clean();
	}
	 /**
     * @depends testinsertPublicInput2
     */
    public function testgetPublicFiles() {
		ob_start();
		$_REQUEST['p'] = 'getPublicFiles';
		$_REQUEST['host'] = 'testhost';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->input_id, '3');
		$this->assertEquals(json_decode($data)[0]->name, 'testpublicinputfile');
		ob_end_clean();
	}
	 /**
     * @depends testgetPublicFiles
     */
    public function testgetPublicValues() {
		ob_start();
		$_REQUEST['p'] = 'getPublicValues';
		$_REQUEST['host'] = 'testhost';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->input_id, '2');
		$this->assertEquals(json_decode($data)[0]->name, 'testpublicinput_after_update');
		ob_end_clean();
	}
	
	
	
	
        //***discuss
        //also update version is missing
//    public function testInsertSSH() {
//		ob_start();
//		$_REQUEST['p'] = 'saveSSHKeys';
//		$_REQUEST['name'] = "dockerKey";
//		$_REQUEST['check_userkey'] = "on";
//		$_REQUEST['check_ourkey'] = "";
////		$_REQUEST['prikey'] = "";
////		$_REQUEST['pubkey'] = "";
//		include('ajaxquery.php');
//		$this->assertEquals(json_decode($data)->id,'1');
//		ob_end_clean();
//	}
    public function testInsertAmz() {
		ob_start();
		$_REQUEST['p'] = 'saveAmzKeys';
		$_REQUEST['name'] = "amzKey";
		$_REQUEST['id'] = "";
		$_REQUEST['amz_def_reg'] = "test1";
		$_REQUEST['amz_acc_key'] = "test2";
		$_REQUEST['amz_suc_key'] = "test3";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertAmz
     */
    public function testgetAmz() {
		ob_start();
		$_REQUEST['p'] = 'getAmz';
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'amzKey');
		ob_end_clean();
	}
    /**
     * @depends testgetAmz
     */
    public function testgetAmzbyID() {
		ob_start();
		$_REQUEST['p'] = 'getAmz';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->amz_def_reg, 'test1');
		$this->assertEquals(json_decode($data)[0]->amz_acc_key, 'test2');
		$this->assertEquals(json_decode($data)[0]->amz_suc_key, 'test3');
		ob_end_clean();
	}

    /**
     * @depends testgetAmzbyID
     */
    public function testupdateAmz() {
		ob_start();
		$_REQUEST['p'] = 'saveAmzKeys';
		$_REQUEST['id'] = "1";
		$_REQUEST['name'] = "amzKey";
		$_REQUEST['amz_def_reg'] = "test1_up";
		$_REQUEST['amz_acc_key'] = "test2_up";
		$_REQUEST['amz_suc_key'] = "test3_up";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getAmz';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->amz_def_reg, 'test1_up');
		$this->assertEquals(json_decode($data)[0]->amz_acc_key, 'test2_up');
		$this->assertEquals(json_decode($data)[0]->amz_suc_key, 'test3_up');
		ob_end_clean();
	}

    public function testInsertProfileCluster() {
		ob_start();
		$_REQUEST['p'] = 'saveProfileCluster';
		$_REQUEST['id'] = '';
		$_REQUEST['name'] = "localtest";
		$_REQUEST['cmd'] = "source /etc/profile";
		$_REQUEST['executor'] = "local";
		$_REQUEST['next_memory'] = "";
		$_REQUEST['next_queue'] = "";
		$_REQUEST['next_time'] = "";
		$_REQUEST['next_cpu'] = "";
		$_REQUEST['next_clu_opt'] = "";
		$_REQUEST['executor_job'] = "local";
		$_REQUEST['job_memory'] = "";
		$_REQUEST['job_queue'] = "";
		$_REQUEST['job_time'] = "";
		$_REQUEST['job_cpu'] = "";
		$_REQUEST['job_clu_opt'] = "";
		$_REQUEST['username'] = "docker";
		$_REQUEST['hostname'] = "localhost";
		$_REQUEST['next_path'] = "";
		$_REQUEST['ssh_id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    /**
     * @depends testInsertProfileCluster
     */
    public function testgetProfileClusterbyId() {
		ob_start();
		$_REQUEST['p'] = 'getProfileCluster';
		$_REQUEST['id'] = '2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'2');
		$this->assertEquals(json_decode($data)[0]->name,'localtest');
		$this->assertEquals(json_decode($data)[0]->next_queue,'');
		ob_end_clean();
	}
    /**
     * @depends testgetProfileClusterbyId
     */
    public function testUpdateProfileCluster() {
		ob_start();
		$_REQUEST['p'] = 'saveProfileCluster';
		$_REQUEST['id'] = '2';
		$_REQUEST['name'] = "localtest";
		$_REQUEST['cmd'] = "source /etc/profile";
		$_REQUEST['executor'] = "local";
		$_REQUEST['next_memory'] = "";
		$_REQUEST['next_queue'] = "short";
		$_REQUEST['next_time'] = "";
		$_REQUEST['next_cpu'] = "";
        $_REQUEST['next_clu_opt'] = "";
		$_REQUEST['executor_job'] = "local";
		$_REQUEST['job_memory'] = "";
		$_REQUEST['job_queue'] = "short";
		$_REQUEST['job_time'] = "";
		$_REQUEST['job_cpu'] = "";
        $_REQUEST['job_clu_opt'] = "";
		$_REQUEST['username'] = "docker";
		$_REQUEST['hostname'] = "localhost";
		$_REQUEST['next_path'] = "";
		$_REQUEST['ssh_id'] = "1";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProfileCluster';
		$_REQUEST['id'] = '2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'2');
		$this->assertEquals(json_decode($data)[0]->name,'localtest');
		$this->assertEquals(json_decode($data)[0]->next_queue,'short');
		$this->assertEquals(json_decode($data)[0]->job_queue,'short');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProfileCluster
     */
    public function testgetProfileCluster() {
		ob_start();
		$_REQUEST['p'] = 'getProfileCluster';
        $_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'2');
		$this->assertEquals(json_decode($data)[0]->name,'localtest');
		ob_end_clean();
	}
    public function testInsertProfileAmazon() {
		ob_start();
		$_REQUEST['p'] = 'saveProfileAmazon';
		$_REQUEST['id'] = '';
		$_REQUEST['name'] = "amazontest";
		$_REQUEST['cmd'] = "";
		$_REQUEST['executor'] = "local";
		$_REQUEST['next_memory'] = "";
		$_REQUEST['next_queue'] = "";
		$_REQUEST['next_time'] = "";
		$_REQUEST['next_cpu'] = "";
        $_REQUEST['next_clu_opt'] = "";
		$_REQUEST['executor_job'] = "local";
		$_REQUEST['job_memory'] = "";
		$_REQUEST['job_queue'] = "";
		$_REQUEST['job_time'] = "";
		$_REQUEST['job_cpu'] = "";
        $_REQUEST['job_clu_opt'] = "";
		$_REQUEST['ins_type'] = "";
		$_REQUEST['image_id'] = "";
		$_REQUEST['subnet_id'] = "";
		$_REQUEST['shared_storage_id'] = "";
		$_REQUEST['shared_storage_mnt'] = "";
		$_REQUEST['next_path'] = "";
		$_REQUEST['ssh_id'] = "1";
		$_REQUEST['amazon_cre_id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
     /**
     * @depends testInsertProfileAmazon
     */
    public function testgetProfileAmazonById() {
		ob_start();
		$_REQUEST['p'] = 'getProfileAmazon';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'amazontest');
		ob_end_clean();
	}
    /**
     * @depends testgetProfileAmazonById
     */
    public function testUpdateProfileAmazon() {
		ob_start();
		$_REQUEST['p'] = 'saveProfileAmazon';
		$_REQUEST['id'] = "1";
		$_REQUEST['name'] = "amazontest";
		$_REQUEST['cmd'] = "";
		$_REQUEST['executor'] = "local";
		$_REQUEST['next_memory'] = "";
		$_REQUEST['next_queue'] = "";
		$_REQUEST['next_time'] = "";
		$_REQUEST['next_cpu'] = "";
        $_REQUEST['next_clu_opt'] = "";
		$_REQUEST['executor_job'] = "local";
		$_REQUEST['job_memory'] = "";
		$_REQUEST['job_queue'] = "long";
		$_REQUEST['job_time'] = "";
		$_REQUEST['job_cpu'] = "";
        $_REQUEST['job_clu_opt'] = "";
		$_REQUEST['ins_type'] = "";
		$_REQUEST['image_id'] = "";
		$_REQUEST['subnet_id'] = "";
		$_REQUEST['shared_storage_id'] = "";
		$_REQUEST['shared_storage_mnt'] = "";
		$_REQUEST['next_path'] = "";
		$_REQUEST['ssh_id'] = "1";
		$_REQUEST['amazon_cre_id'] = "1";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProfileAmazon';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->job_queue,'long');
		ob_end_clean();
	}
    /**
     * @depends testInsertProfileAmazon
     */
    public function testgetProfileAmazon() {
		ob_start();
		$_REQUEST['p'] = 'getProfileAmazon';
        $_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'amazontest');
		ob_end_clean();
	}
    /**
     * @depends testgetProfileAmazon
     */
    public function testgetAmazonStatus() {
		ob_start();
        require_once("../ajax/dbfuncs.php");
        $db = new dbfuncs();
        $id = '1';
        $ownerID = '1';
		$this->assertEquals(json_decode($db->getAmazonStatus($id,$ownerID))[0]->status,null);
		ob_end_clean();
	}
    /**
     * @depends testgetAmazonStatus
     */
    public function testupdateAmazonProStatus() {
		ob_start();
		$_REQUEST['p'] = 'updateAmazonProStatus';
		$_REQUEST['id'] = '1';
		$_REQUEST['status'] = 'update';
		include('ajaxquery.php');
        $this->assertEquals(json_decode($db->getAmazonStatus($id,$ownerID))[0]->status,'update');
		ob_end_clean();
	}
    public function testInsertProjectInput() {
		ob_start();
		$_REQUEST['p'] = 'saveProjectInput';
		$_REQUEST['input_id'] = "1";
		$_REQUEST['project_id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    public function testsaveAllPipeline() {
		ob_start();
		$_REQUEST['p'] = 'saveAllPipeline';
		$_REQUEST['dat'] = '[{"name":"test_pipeline"},{"id":""},{"nodes":{"g-0":[318.6666564941406,106.66666412353516,"1","test_process"]}},{"mainG":[0,0,1]},{"edges":[]},{"summary":""},{"group_id":""},{"perms":"3"},{"pin":"false"},{"pin_order":""},{"publish":"0"},{"script_pipe_header":""},{"script_pipe_footer":"0"},{"script_mode_header":"0"},{"script_mode_footer":"0"},{"pipeline_group_id":"1"},{"process_list":""},{"pipeline_list":""},{"pipeline_gid":null},{"rev_comment":""},{"rev_id":0}]';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
	
    /**
     * @depends testsaveAllPipeline
     */
    public function testgetPipeline_gid() {
		ob_start();
		$_REQUEST['p'] = 'getPipeline_gid';
        $_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->pipeline_gid,'0');
		ob_end_clean();
	}
    /**
     * @depends testgetPipeline_gid
     */
    public function testgetMaxPipeline_gid() {
		ob_start();
		$_REQUEST['p'] = 'getMaxPipeline_gid';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->pipeline_gid,'0');
		ob_end_clean();
	}
    /**
     * @depends testgetMaxPipeline_gid
     */
    public function testgetSavedPipelinesLogin() {
		ob_start();
		$_REQUEST['p'] = 'getSavedPipelines';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_pipeline');
		ob_end_clean();
	}
    /**
     * @depends testgetSavedPipelinesLogin
     */
    public function testloadPipelineLogin() {
		ob_start();
		$_REQUEST['p'] = 'loadPipeline';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_pipeline');
		ob_end_clean();
	}
    /**
     * @depends testloadPipelineLogin
     */
    public function testsaveAllPipelineUpdate() {
		ob_start();
		$_REQUEST['p'] = 'saveAllPipeline';
		$_REQUEST['dat'] = '[{"name":"test_pipeline"},{"id":"1"},{"nodes":{"g-0":[318.6666564941406,106.66666412353516,"1","test_process"]}},{"mainG":[0,0,1]},{"edges":[]},{"summary":"pipeline_summary_updated"},{"group_id":""},{"perms":"63"},{"pin":"true"},{"pin_order":""},{"publish":"0"},{"script_pipe_header":""},{"script_pipe_footer":"0"},{"script_mode_header":"0"},{"script_mode_footer":"0"},{"pipeline_group_id":"1"},{"process_list":"1"},{"pipeline_list":"2,3"},{"pipeline_gid":null},{"rev_comment":""},{"rev_id":0}]';
		include('ajaxquery.php');
        $_REQUEST['p'] = 'loadPipeline';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->perms,'63');
		ob_end_clean();
	}
    /**
     * @depends testsaveAllPipelineUpdate
     */
    public function testloadPipelinePublic() {
		ob_start();
		$_REQUEST['p'] = 'loadPipeline';
        $_REQUEST['ownerID'] = '';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->perms,'63');
		ob_end_clean();
	}
    /**
     * @depends testloadPipelinePublic
     */
    public function testinsertPipelineName() {
		ob_start();
		$_REQUEST['p'] = 'savePipelineName';
		$_REQUEST['id'] = '';
		$_REQUEST['name'] = 'test_pipeline_before_update';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    /**
     * @depends testinsertPipelineName
     */
    public function testupdatePipelineName() {
		ob_start();
		$_REQUEST['p'] = 'savePipelineName';
		$_REQUEST['id'] = '2';
		$_REQUEST['name'] = 'test_pipeline';
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getSavedPipelines';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[1]->id,'2');
		$this->assertEquals(json_decode($data)[1]->name,'test_pipeline');
		ob_end_clean();
	}
    /**
     * @depends testupdatePipelineName
     */
    public function testgetSavedPipelinesPublic() {
		ob_start();
		$_REQUEST['p'] = 'getSavedPipelines';
        $_REQUEST['ownerID'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->summary,'pipeline_summary_updated');
		ob_end_clean();
	}
	/**
     * @depends testgetSavedPipelinesPublic
     */
	public function testgetPipelineRevision() {
		ob_start();
		$_REQUEST['p'] = 'getPipelineRevision';
		$_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->rev_id,'0');
		ob_end_clean();
	}
	
	/**
     * @depends testgetPipelineRevision
     */
	public function testsavePipelineDetails() {
		ob_start();
		$_REQUEST['p'] = 'savePipelineDetails';
		$_REQUEST['id'] = '1';
		$_REQUEST['summary'] = 'pipeline_summary_updated2';
		$_REQUEST['nodes'] = array("g-0"=>[318.6666564941406,106.66666412353516,"1","test_process","{}"]);
		$_REQUEST['group_id'] = '0';
		$_REQUEST['perms'] = '63';
		$_REQUEST['pin'] = 'true';
		$_REQUEST['pin_order'] = '0';
		$_REQUEST['publish'] = '0';
		$_REQUEST['pipeline_group_id'] = '1';
		include('ajaxquery.php');
		$_REQUEST['p'] = 'loadPipeline';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->pin,'true');
		$this->assertEquals(json_decode($data)[0]->summary,'pipeline_summary_updated2');
		ob_end_clean();
	}
	/**
     * @depends testsavePipelineDetails
     */
	public function testgetMaxPipRev_id() {
		ob_start();
		$_REQUEST['p'] = 'getMaxPipRev_id';
		$_REQUEST['pipeline_gid'] = '0';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->rev_id,'0');
		ob_end_clean();
	}
	/**
     * @depends testgetMaxPipRev_id
     */
	 public function testsaveAllPipeline2() {
		ob_start();
		$_SESSION['ownerID'] = '2';
		$_REQUEST['p'] = 'saveAllPipeline';
		$_REQUEST['dat'] = '[{"name":"test_pipeline2"},{"id":""},{"nodes":{"g-0":[318.6666564941406,106.66666412353516,"1","test_process"]}},{"mainG":[0,0,1]},{"edges":[]},{"summary":""},{"group_id":""},{"perms":"3"},{"pin":"false"},{"pin_order":""},{"publish":"0"},{"script_pipe_header":""},{"script_pipe_footer":"0"},{"script_mode_header":"0"},{"script_mode_footer":"0"},{"pipeline_group_id":"1"},{"process_list":"1,4"},{"pipeline_list":"2,3"},{"pipeline_gid":null},{"rev_comment":""},{"rev_id":0}]';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'3');
		ob_end_clean();
	}
	

	
	

    public function testsavefeedback() {
		ob_start();
		$_REQUEST['p'] = 'savefeedback';
		$_REQUEST['email'] = 'test@gmail.com';
		$_REQUEST['message'] = 'test_message';
		$_REQUEST['url'] = 'https://dolphinnext.umassmed.edu/index.php?np=2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testsaveAllPipelineUpdate
     * @depends testUpdateProject
     */
    public function testInsertProjectPipeline() {
		ob_start();
		$_SESSION['ownerID'] = '1';
		$_REQUEST['p'] = 'saveProjectPipeline';
		$_REQUEST['id'] = '';
		$_REQUEST['pipeline_id'] = '1';
		$_REQUEST['project_id'] = '1';
		$_REQUEST['name'] = 'test_run';
		$_REQUEST['summary'] = 'test_sum';
		$_REQUEST['output_dir'] = '';
		$_REQUEST['publish_dir'] = '';
		$_REQUEST['publish_dir_check'] = '';
		$_REQUEST['perms'] = '3';
		$_REQUEST['profile'] = '1';
		$_REQUEST['interdel'] = '';
		$_REQUEST['group_id'] = '';
		$_REQUEST['cmd'] = '';
        $_REQUEST['exec_each'] = "";
        $_REQUEST['exec_all'] = "";
        $_REQUEST['exec_all_settings'] = "";
        $_REQUEST['exec_each_settings'] = "";
        $_REQUEST['exec_next_settings'] = "";
        $_REQUEST['docker_check'] = "";
        $_REQUEST['docker_img'] = "";
        $_REQUEST['docker_opt'] = "";
        $_REQUEST['singu_check'] = "";
        $_REQUEST['singu_save'] = "";
        $_REQUEST['singu_img'] = "";
        $_REQUEST['singu_opt'] = "";
        $_REQUEST['amazon_cre_id'] = "";
        $_REQUEST['withReport']= "";
        $_REQUEST['withTrace']= "";
        $_REQUEST['withTimeline']= "";
        $_REQUEST['withDag']= "";
        $_REQUEST['process_opt']= "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
	/**
     * @depends testInsertProjectPipeline
	 * @depends testsaveAllPipeline2
     */
    public function testInsertProjectPipeline2() {
		ob_start();
		$_SESSION['ownerID'] = '2';
		$_REQUEST['p'] = 'saveProjectPipeline';
		$_REQUEST['id'] = '';
		$_REQUEST['pipeline_id'] = '3';
		$_REQUEST['project_id'] = '1';
		$_REQUEST['name'] = 'test_run2';
		$_REQUEST['summary'] = 'test_sum2';
		$_REQUEST['output_dir'] = '';
		$_REQUEST['publish_dir'] = '';
		$_REQUEST['publish_dir_check'] = '';
		$_REQUEST['perms'] = '15';
		$_REQUEST['profile'] = '1';
		$_REQUEST['interdel'] = '';
		$_REQUEST['group_id'] = '';
		$_REQUEST['cmd'] = '';
        $_REQUEST['exec_each'] = "";
        $_REQUEST['exec_all'] = "";
        $_REQUEST['exec_all_settings'] = "";
        $_REQUEST['exec_each_settings'] = "";
        $_REQUEST['exec_next_settings'] = "";
        $_REQUEST['docker_check'] = "";
        $_REQUEST['docker_img'] = "";
        $_REQUEST['docker_opt'] = "";
        $_REQUEST['singu_check'] = "";
        $_REQUEST['singu_save'] = "";
        $_REQUEST['singu_img'] = "";
        $_REQUEST['singu_opt'] = "";
        $_REQUEST['amazon_cre_id'] = "";
        $_REQUEST['withReport']= "";
        $_REQUEST['withTrace']= "";
        $_REQUEST['withTimeline']= "";
        $_REQUEST['withDag']= "";
        $_REQUEST['process_opt']= "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    /**
     * @depends testInsertProjectPipeline
     * @depends testUpdateProject
     * @depends testInsertProjectPipeline2
     */
    public function testgetProjectPipelines() {
		ob_start();
		$_SESSION['ownerID'] = '1';
		$_REQUEST['p'] = 'getProjectPipelines';
		$_REQUEST['id'] = '';
		$_REQUEST['project_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->pp_name, 'test_run');
		$this->assertEquals(json_decode($data)[0]->summary, 'test_sum');
		ob_end_clean();
	}
    /**
     * @depends testgetProjectPipelines
     */
    public function testUpdateProjectPipeline() {
		ob_start();
		$_REQUEST['p'] = 'saveProjectPipeline';
		$_REQUEST['id'] = '1';
		$_REQUEST['pipeline_id'] = '1';
		$_REQUEST['project_id'] = '1';
		$_REQUEST['name'] = 'test_run';
		$_REQUEST['summary'] = 'test_sum';
		$_REQUEST['output_dir'] = '';
		$_REQUEST['publish_dir'] = '';
		$_REQUEST['publish_dir_check'] = '';
		$_REQUEST['perms'] = '63';
		$_REQUEST['profile'] = '1';
		$_REQUEST['interdel'] = '';
		$_REQUEST['group_id'] = '';
		$_REQUEST['cmd'] = '';
        $_REQUEST['exec_each'] = "";
        $_REQUEST['exec_all'] = "";
        $_REQUEST['exec_all_settings'] = "";
        $_REQUEST['exec_each_settings'] = "";
        $_REQUEST['exec_next_settings'] = "";
        $_REQUEST['docker_check'] = "";
        $_REQUEST['docker_img'] = "";
        $_REQUEST['docker_opt'] = "";
        $_REQUEST['singu_check'] = "";
        $_REQUEST['singu_save'] = "";
        $_REQUEST['singu_img'] = "";
        $_REQUEST['singu_opt'] = "";
        $_REQUEST['amazon_cre_id'] = "";
        $_REQUEST['withReport']= "";
        $_REQUEST['withTrace']= "";
        $_REQUEST['withTimeline']= "";
        $_REQUEST['withDag']= "";
        $_REQUEST['process_opt']= "";
		include('ajaxquery.php');
		$_REQUEST['p'] = 'getProjectPipelines';
		$_REQUEST['project_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->perms, '63');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProcess
     * @depends testupdateParameter
     */
    public function testInsertProcessParameter() {
		ob_start();
		$_REQUEST['p'] = 'saveProcessParameter';
		$_REQUEST['id'] = '';
		$_REQUEST['sname'] = 'test_input';
		$_REQUEST['closure'] = '';
		$_REQUEST['reg_ex'] = '';
		$_REQUEST['operator'] = '';
		$_REQUEST['process_id'] = '1';
		$_REQUEST['parameter_id'] = '1';
		$_REQUEST['type'] = 'input';
        $_REQUEST['perms'] = "3";
        $_REQUEST['group'] = "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testInsertProcessParameter
     */
    public function testInsertProcessParameterOutput() {
		ob_start();
		$_REQUEST['p'] = 'saveProcessParameter';
		$_REQUEST['sname'] = 'before_update.txt';
		$_REQUEST['closure'] = '';
		$_REQUEST['reg_ex'] = '';
		$_REQUEST['operator'] = '';
		$_REQUEST['process_id'] = '1';
		$_REQUEST['parameter_id'] = '1';
		$_REQUEST['type'] = 'output';
        $_REQUEST['perms'] = "3";
        $_REQUEST['group'] = "";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    /**
     * @depends testInsertProcessParameterOutput
     */
    public function testgetInputsPP() {
		ob_start();
		$_REQUEST['p'] = 'getInputsPP';
		$_REQUEST['project_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->sname, 'test_input');
		ob_end_clean();
	}
    /**
     * @depends testgetInputsPP
     */
    public function testUpdateProcessParameter() {
		ob_start();
		$_REQUEST['p'] = 'saveProcessParameter';
		$_REQUEST['id'] = '2';
		$_REQUEST['sname'] = 'filelist.txt';
		$_REQUEST['closure'] = '';
		$_REQUEST['reg_ex'] = '';
		$_REQUEST['operator'] = '';
		$_REQUEST['process_id'] = '1';
		$_REQUEST['parameter_id'] = '1';
		$_REQUEST['type'] = 'output';
        $_REQUEST['perms'] = "63";
        $_REQUEST['group'] = "";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getOutputsPP';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '2');
		$this->assertEquals(json_decode($data)[0]->sname, 'filelist.txt');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProcessParameter
     */
    public function testgetOutputsPP() {
		ob_start();
		$_REQUEST['p'] = 'getOutputsPP';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '2');
		$this->assertEquals(json_decode($data)[0]->sname, 'filelist.txt');
		ob_end_clean();
	}
	/**
     * @depends testgetOutputsPP
     */
    public function testcheckParameter() {
		ob_start();
		$_REQUEST['p'] = 'checkParameter';
		$_REQUEST['parameter_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'test_process');
		ob_end_clean();
	}
	
	
    public function testInsertProPipeInput() {
		ob_start();
		$_SESSION['ownerID'] = '1';
		$_REQUEST['p'] = 'saveProPipeInput';
		$_REQUEST['id'] = "";
		$_REQUEST['input_id'] = "1";
		$_REQUEST['project_id'] = "1";
		$_REQUEST['pipeline_id'] = "1";
		$_REQUEST['project_pipeline_id'] = "1";
		$_REQUEST['g_num'] = "0";
		$_REQUEST['given_name'] = "test_inputparam";
		$_REQUEST['qualifier'] = "val";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'1');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProject
     * @depends testUpdateInput
     * @depends testInsertProjectPipeline
     */
    public function testgetProjectPipelineInputsById() {
		ob_start();
		$_REQUEST['p'] = 'getProjectPipelineInputs';
		$_REQUEST['project_pipeline_id'] = '1';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->input_id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput');
		ob_end_clean();
	}
    /**
     * @depends testgetProjectPipelineInputsById
     */
    public function testUpdateProPipeInput() {
		ob_start();
		$_REQUEST['p'] = 'saveProPipeInput';
		$_REQUEST['id'] = "1";
		$_REQUEST['input_id'] = "1";
		$_REQUEST['project_id'] = "1";
		$_REQUEST['pipeline_id'] = "1";
		$_REQUEST['project_pipeline_id'] = "1";
		$_REQUEST['g_num'] = "0";
		$_REQUEST['given_name'] = "test_inputparam";
		$_REQUEST['qualifier'] = "file";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProjectPipelineInputs';
		$_REQUEST['project_pipeline_id'] = '1';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->qualifier, 'file');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProcess
     */
    public function testduplicateProcess() {
		ob_start();
		$_REQUEST['p'] = 'duplicateProcess';
		$_REQUEST['name'] = "duplicate_process";
		$_REQUEST['id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
        //check duplicated process parameters
        $_REQUEST['p'] = 'getInputsPP';
		$_REQUEST['process_id'] = '2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '3');
		$this->assertEquals(json_decode($data)[0]->sname, 'test_input');
        $_REQUEST['p'] = 'getOutputsPP';
		$_REQUEST['process_id'] = '2';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '4');
		$this->assertEquals(json_decode($data)[0]->sname, 'filelist.txt');
		ob_end_clean();
	}
    /**
     * @depends testduplicateProcess
     */
    public function testcreateProcessRev() {
		ob_start();
		$_REQUEST['p'] = 'createProcessRev';
		$_REQUEST['rev_comment'] = "test_comment";
		$_REQUEST['rev_id'] = "1";
		$_REQUEST['process_gid'] = "1";
		$_REQUEST['id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'3');
        //check duplicated process parameters
        $_REQUEST['p'] = 'getInputsPP';
		$_REQUEST['process_id'] = '3';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '6');
		$this->assertEquals(json_decode($data)[0]->sname, 'test_input');
        $_REQUEST['p'] = 'getOutputsPP';
		$_REQUEST['process_id'] = '3';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '7');
		$this->assertEquals(json_decode($data)[0]->sname, 'filelist.txt');
		ob_end_clean();
	}
    /**
     * @depends testInsertProPipeInput
     */
    public function testduplicateProjectPipelineInput() {
		ob_start();
		$_REQUEST['p'] = 'duplicateProjectPipelineInput';
		$_REQUEST['new_id'] = "2";
		$_REQUEST['old_id'] = "1";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->id,'2');
		ob_end_clean();
	}
    public function testCheckLoginDecline() {
		ob_start();
		$_REQUEST['p'] = 'checkLogin';
        $_SESSION['google_id']="";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)->error,'1');
        ob_end_clean();
        ob_start();
        $_SESSION['ownerID'] = '1';
        $_SESSION['username'] = 'admin';
        $_SESSION['google_id'] = '111';
        $ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
        $google_id = isset($_SESSION['google_id']) ? $_SESSION['google_id'] : "";
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
		ob_end_clean();
	}
    /**
     * @depends testInsertUser
     * @depends testInsertUser2
     * @depends testCheckLoginDecline
     */
    public function testCheckLogin() {
		ob_start();
		$_REQUEST['p'] = 'checkLogin';
        $_SESSION['google_id']="111";
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->username,'admin');
		ob_end_clean();
	}
    /**
     * @depends testInsertGroup
     * @depends testInsertUser
     */
    public function testgetMemberAdd() {
		ob_start();
		$_REQUEST['p'] = 'getMemberAdd';
		$_REQUEST['g_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'2');
		$this->assertEquals(json_decode($data)[0]->username,'member');
		ob_end_clean();
	}
    /**
     * @depends testInsertGroup
     * @depends testInsertUser
     */
    public function testviewGroupMembers() {
		ob_start();
		$_REQUEST['p'] = 'viewGroupMembers';
		$_REQUEST['g_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->username,'admin');
		ob_end_clean();
	}
    /**
     * @depends testInsertGroup
     */
    public function testgetGroups() {
		ob_start();
		$_REQUEST['p'] = 'getGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_group');
		ob_end_clean();
	}
    /**
     * @depends testInsertGroup
     */
    public function testgetAllGroups() {
		ob_start();
		$_REQUEST['p'] = 'getAllGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_group');
		ob_end_clean();
	}
    /**
     * @depends testInsertGroup
     */
    public function testgetUserGroups() {
		ob_start();
		$_REQUEST['p'] = 'getUserGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_group');
		ob_end_clean();
	}
    /**
     * @depends testUpdateUser
     */
    public function testgetUserRole() {
		ob_start();
		$_REQUEST['p'] = 'getUserRole';
        $_SESSION['ownerID'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->role, null);
		ob_end_clean();
	}

	//   should be defined for admin user
//	/**
//     * @depends testgetUserRole
//     */
//    public function testgetAllUsers() {
//		ob_start();
//		$_REQUEST['p'] = 'getAllUsers';
//		include('ajaxquery.php');
//		$this->assertEquals(json_decode($data)[0]->id, '1');
//		$this->assertEquals(json_decode($data)[0]->username, 'admin');
//		ob_end_clean();
//	}
	
	
    /**
     * @depends testInsertProjectPipeline
     */
    public function testgetExistProjectPipelines() {
		ob_start();
		$_REQUEST['p'] = 'getExistProjectPipelines';
		$_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->pp_name, 'test_run');
		ob_end_clean();
	}
	
	
	

	
	
    /**
     * @depends testUpdateProject
     * @depends testUpdateInput
     */
    public function testgetProjectInputs() {
		ob_start();
		$_REQUEST['p'] = 'getProjectInputs';
		$_REQUEST['project_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->input_id, '1');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProject
     * @depends testUpdateInput
     */
    public function testgetProjectInput() {
		ob_start();
		$_REQUEST['p'] = 'getProjectInput';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->input_id, '1');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProject
     * @depends testUpdateInput
     * @depends testInsertProjectPipeline
     * @depends testInsertProPipeInput
     */
    public function testgetProjectPipelineInputsByGnum() {
		ob_start();
		$_REQUEST['p'] = 'getProjectPipelineInputsByGnum';
		$_REQUEST['g_num'] = '0';
		$_REQUEST['project_pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->input_id, '1');
		$this->assertEquals(json_decode($data)[0]->given_name, 'test_inputparam');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProject
     * @depends testUpdateInput
     * @depends testInsertProjectPipeline
     */
    public function testgetProjectPipelineInputs() {
		ob_start();
		$_REQUEST['p'] = 'getProjectPipelineInputs';
		$_REQUEST['project_pipeline_id'] = '1';
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->input_id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput');
		ob_end_clean();
	}
	/**
     * @depends testgetProjectPipelineInputs
     */
    public function testcheckPipeline() {
		ob_start();
		$_REQUEST['p'] = 'checkPipeline';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'test_pipeline');
		ob_end_clean();
	}
	/**
     * @depends testcheckPipeline
     */
    public function testcheckPipelinePerm() {
		ob_start();
		$_REQUEST['p'] = 'checkPipelinePerm';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'test_pipeline');
		ob_end_clean();
	}
	
	/**
     * @depends testsaveAllPipeline2
     */
    public function testcheckPipelinePublic() {
		ob_start();
		$_SESSION['ownerID'] = '1';
		$_REQUEST['p'] = 'checkPipelinePublic';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '3');
		$this->assertEquals(json_decode($data)[0]->name, 'test_pipeline2');
		ob_end_clean();
	}
	/**
     * @depends testcheckPipelinePublic
	 * @depends testInsertProjectPipeline2
     */
    public function testcheckProjectPipelinePublic() {
		ob_start();
		$_REQUEST['p'] = 'checkProjectPipelinePublic';
		$_REQUEST['process_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '3');
		$this->assertEquals(json_decode($data)[0]->name, 'test_pipeline2');
		ob_end_clean();
	}
	/**
     * @depends testcheckProjectPipelinePublic
     */
    public function testcheckProject() {
		ob_start();
		$_REQUEST['p'] = 'checkProject';
		$_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'test_project');
		ob_end_clean();
	}
	/**
     * @depends testcheckProject
     */
    public function testcheckProjectPublic() {
		ob_start();
		$_REQUEST['p'] = 'checkProjectPublic';
		$_REQUEST['pipeline_id'] = '3';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '2');
		$this->assertEquals(json_decode($data)[0]->name, 'test_project');
		ob_end_clean();
	}
	/**
     * @depends testcheckProjectPublic
     */
    public function testcheckInput() {
		ob_start();
		$_REQUEST['p'] = 'checkInput';
		$_REQUEST['name'] = 'testinput';
		$_REQUEST['type'] = 'val';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput');
		ob_end_clean();
	}
	/**
     * @depends testcheckInput
     */
    public function testcheckProjectInput() {
		ob_start();
		$_REQUEST['p'] = 'checkProjectInput';
		$_REQUEST['input_id'] = '1';
		$_REQUEST['project_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		ob_end_clean();
	}
	/**
     * @depends testcheckProjectInput
     */
    public function testcheckProPipeInput() {
		ob_start();
		$_REQUEST['p'] = 'checkProPipeInput';
		$_REQUEST['input_id'] = '1';
		$_REQUEST['project_id'] = '1';
		$_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		ob_end_clean();
	}
	/**
     * @depends testcheckProPipeInput
     */
    public function testcheckProjectPipePerm() {
		ob_start();
		$_REQUEST['p'] = 'checkProjectPipePerm';
		$_REQUEST['pipeline_id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'test_run');
		ob_end_clean();
	}
	

	
	

	/**
     * @depends testsaveAllPipeline2
     */
	public function testgetPublicPipelines() {
		ob_start();
		$_REQUEST['p'] = 'getPublicPipelines';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_pipeline');
		$this->assertEquals(json_decode($data)[0]->summary,'pipeline_summary_updated2');
		ob_end_clean();
	}
	
	/**
     * @depends testgetPublicPipelines
     * @depends testgetProjectPipelineInputsByGnum
     */
    public function testrenameProjectPipelineInputByGnum() {
		ob_start();
		$_REQUEST['p'] = 'renameProjectPipelineInputByGnum';
		$_REQUEST['id'] = "1";
		$_REQUEST['g_num'] = "0";
		$_REQUEST['given_name'] = "test_inputparam2";
		include('ajaxquery.php');
        $_REQUEST['p'] = 'getProjectPipelineInputs';
		$_REQUEST['project_pipeline_id'] = '1';
		$_REQUEST['id'] = '';
		$_REQUEST['g_num'] = '0';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->given_name, 'test_inputparam2');
		ob_end_clean();
	}


	
	
	
	
	
	
    /**
     * @depends testUpdateInput
     */
    public function testgetInputs() {
		ob_start();
		$_REQUEST['p'] = 'getInputs';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->name, 'testinput');
		ob_end_clean();
	}
    /**
     * @depends testUpdateProcessGroup
     */
    public function testgetEditDelProcessGroups() {
		ob_start();
		$_REQUEST['p'] = 'getEditDelProcessGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id, '1');
		$this->assertEquals(json_decode($data)[0]->group_name, 'test_menu');
		ob_end_clean();
	}
	/**
     * @depends testUpdatePipelineGroup
     */
    public function testgetEditDelPipelineGroups() {
		ob_start();
		$_REQUEST['p'] = 'getEditDelPipelineGroups';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[1]->id, '2');
		$this->assertEquals(json_decode($data)[1]->group_name, 'test_menu_pipe');
		ob_end_clean();
	}
	/**
     * @depends testUpdatePipelineGroup
     * @depends testsaveAllPipelineUpdate
     */
	public function testcheckPipeMenuGr() {
		ob_start();
		$_REQUEST['p'] = 'checkPipeMenuGr';
		$_REQUEST['id'] = '1';
		include('ajaxquery.php');
		$this->assertEquals(json_decode($data)[0]->id,'1');
		$this->assertEquals(json_decode($data)[0]->name,'test_pipeline');
		ob_end_clean();
	}
	
	
//#############################	
//#########   remove  #########
//#############################	
	
	
	/**
     * @depends testrenameProjectPipelineInputByGnum
     */
    public function testremoveProjectPipelineInput() {
		ob_start();
		$_REQUEST['p'] = 'removeProjectPipelineInput';
		$_REQUEST['id'] = "1";
		include('ajaxquery.php');
		$_REQUEST['p'] = 'getProjectPipelineInputs';
		$_REQUEST['project_pipeline_id'] = '1';
		$_REQUEST['id'] = '';
		include('ajaxquery.php');
		$this->assertEquals($data,'[]');
		ob_end_clean();
	}
	
	
}
?>
