-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: localhost    Database: biocorepipe
-- ------------------------------------------------------
-- Server version	5.7.21-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `amazon_credentials`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amazon_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `amz_acc_key` varchar(100) DEFAULT NULL,
  `amz_suc_key` varchar(100) DEFAULT NULL,
  `amz_def_reg` varchar(45) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amazon_credentials`
--

LOCK TABLES `amazon_credentials` WRITE;
/*!40000 ALTER TABLE `amazon_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `amazon_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `biocorepipe_save`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biocorepipe_save` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `edges` text,
  `mainG` text,
  `nodes` text,
  `pin_order` int(5) DEFAULT NULL,
  `pin` varchar(6) DEFAULT NULL,
  `publish` int(2) DEFAULT NULL,
  `name` text NOT NULL,
  `script_header` text,
  `script_mode` varchar(20) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  `rev_id` int(11) DEFAULT NULL,
  `rev_comment` varchar(20) DEFAULT NULL,
  `pipeline_gid` int(11) DEFAULT NULL,
  `summary` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biocorepipe_save`
--

LOCK TABLES `biocorepipe_save` WRITE;
/*!40000 ALTER TABLE `biocorepipe_save` DISABLE KEYS */;
/*!40000 ALTER TABLE `biocorepipe_save` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(256) DEFAULT NULL,
  `message` text,
  `url` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `input`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `input`
--

LOCK TABLES `input` WRITE;
/*!40000 ALTER TABLE `input` DISABLE KEYS */;
/*!40000 ALTER TABLE `input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parameter`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `file_type` varchar(256) DEFAULT NULL,
  `qualifier` varchar(256) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parameter`
--

LOCK TABLES `parameter` WRITE;
/*!40000 ALTER TABLE `parameter` DISABLE KEYS */;
/*!40000 ALTER TABLE `parameter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perms`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perms_name` varchar(45) DEFAULT NULL,
  `perms_var` varchar(45) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `val` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perms`
--

/*!40000 ALTER TABLE `perms` DISABLE KEYS */;
INSERT INTO `perms` VALUES (1,'Only me','only_me',3),(2,'Only my groups','only_my_groups',15),(3,'Everyone','everyone',63);
/*!40000 ALTER TABLE `perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `process`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_group_id` int(11) DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `summary` blob,
  `script` text,
  `script_mode` varchar(20) DEFAULT NULL,
  `script_header` text,
  `script_mode_header` varchar(20) DEFAULT NULL,
  `publish` int(2) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  `rev_id` int(11) DEFAULT NULL,
  `rev_comment` varchar(20) DEFAULT NULL,
  `process_gid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `process`
--

LOCK TABLES `process` WRITE;
/*!40000 ALTER TABLE `process` DISABLE KEYS */;
/*!40000 ALTER TABLE `process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `process_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `process_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `process_group`
--

LOCK TABLES `process_group` WRITE;
/*!40000 ALTER TABLE `process_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `process_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `process_parameter`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `process_parameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `sname` varchar(256) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `closure` varchar(256) DEFAULT NULL,
  `reg_ex` varchar(100) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id` (`process_id`),
  KEY `parameter_id` (`parameter_id`),
  CONSTRAINT `process_parameter_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `process` (`id`),
  CONSTRAINT `process_parameter_ibfk_2` FOREIGN KEY (`parameter_id`) REFERENCES `parameter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `process_parameter`
--

LOCK TABLES `process_parameter` WRITE;
/*!40000 ALTER TABLE `process_parameter` DISABLE KEYS */;
/*!40000 ALTER TABLE `process_parameter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_amazon`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_amazon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `status` varchar(15) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `ssh` varchar(256) DEFAULT NULL,
  `nodes` varchar(10) DEFAULT NULL,
  `autoscale_check` varchar(6) DEFAULT NULL,
  `autoscale_maxIns` varchar(10) DEFAULT NULL,
  `next_path` varchar(256) DEFAULT NULL,
  `amazon_cre_id` int(11) DEFAULT NULL,
  `ssh_id` int(11) DEFAULT NULL,
  `default_region` varchar(256) DEFAULT NULL,
  `access_key` varchar(256) DEFAULT NULL,
  `secret_key` varchar(256) DEFAULT NULL,
  `instance_type` varchar(256) DEFAULT NULL,
  `image_id` varchar(256) DEFAULT NULL,
  `shared_storage_mnt` varchar(256) DEFAULT NULL,
  `shared_storage_id` varchar(256) DEFAULT NULL,
  `subnet_id` varchar(256) DEFAULT NULL,
  `executor` varchar(25) DEFAULT NULL,
  `job_time` varchar(25) DEFAULT NULL,
  `job_queue` varchar(25) DEFAULT NULL,
  `job_cpu` varchar(25) DEFAULT NULL,
  `job_memory` varchar(25) DEFAULT NULL,
  `executor_job` varchar(25) DEFAULT NULL,
  `next_time` varchar(25) DEFAULT NULL,
  `next_queue` varchar(25) DEFAULT NULL,
  `next_cpu` varchar(25) DEFAULT NULL,
  `next_memory` varchar(25) DEFAULT NULL,
  `cmd` varchar(500) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_amazon`
--

LOCK TABLES `profile_amazon` WRITE;
/*!40000 ALTER TABLE `profile_amazon` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_amazon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_cluster`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_cluster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `username` varchar(256) DEFAULT NULL,
  `hostname` varchar(256) DEFAULT NULL,
  `ssh_id` int(11) DEFAULT NULL,
  `next_path` varchar(256) DEFAULT NULL,
  `executor` varchar(25) DEFAULT NULL,
  `job_time` varchar(25) DEFAULT NULL,
  `job_queue` varchar(25) NOT NULL,
  `job_cpu` varchar(25) DEFAULT NULL,
  `job_memory` varchar(25) DEFAULT NULL,
  `executor_job` varchar(25) DEFAULT NULL,
  `next_time` varchar(25) DEFAULT NULL,
  `next_queue` varchar(25) DEFAULT NULL,
  `next_cpu` varchar(25) DEFAULT NULL,
  `next_memory` varchar(25) DEFAULT NULL,
  `cmd` varchar(500) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_cluster`
--

LOCK TABLES `profile_cluster` WRITE;
/*!40000 ALTER TABLE `profile_cluster` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_cluster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_local`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `next_path` varchar(256) DEFAULT NULL,
  `executor` varchar(25) DEFAULT NULL,
  `job_time` varchar(25) DEFAULT NULL,
  `job_queue` varchar(25) NOT NULL,
  `job_cpu` varchar(25) DEFAULT NULL,
  `job_memory` varchar(25) DEFAULT NULL,
  `executor_job` varchar(25) DEFAULT NULL,
  `next_time` varchar(25) DEFAULT NULL,
  `next_queue` varchar(25) DEFAULT NULL,
  `next_cpu` varchar(25) DEFAULT NULL,
  `next_memory` varchar(25) DEFAULT NULL,
  `cmd` varchar(500) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_local`
--

LOCK TABLES `profile_local` WRITE;
/*!40000 ALTER TABLE `profile_local` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  `summary` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_input`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `input_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `input_id` (`input_id`),
  CONSTRAINT `project_input_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  CONSTRAINT `project_input_ibfk_2` FOREIGN KEY (`input_id`) REFERENCES `input` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_input`
--

LOCK TABLES `project_input` WRITE;
/*!40000 ALTER TABLE `project_input` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_pipeline`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_pipeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `summary` blob,
  `output_dir` varchar(256) DEFAULT NULL,
  `publish_dir_check` varchar(6) NOT NULL,
  `publish_dir` varchar(256) NOT NULL,
  `cmd` varchar(500) DEFAULT NULL,
  `amazon_cre_id` int(11) NOT NULL,
  `profile` varchar(30) DEFAULT NULL,
  `interdel` varchar(6) DEFAULT NULL,
  `exec_next_settings` varchar(300) DEFAULT NULL,
  `exec_each` varchar(6) DEFAULT NULL,
  `exec_all` varchar(6) DEFAULT NULL,
  `exec_all_settings` text,
  `exec_each_settings` text,
  `singu_img` varchar(256) DEFAULT NULL,
  `singu_opt` varchar(500) DEFAULT NULL,
  `docker_img` varchar(256) DEFAULT NULL,
  `docker_opt` varchar(500) DEFAULT NULL,
  `singu_check` varchar(6) DEFAULT NULL,
  `docker_check` varchar(6) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `pipeline_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `pipeline_id` (`pipeline_id`),
  CONSTRAINT `project_pipeline_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  CONSTRAINT `project_pipeline_ibfk_2` FOREIGN KEY (`pipeline_id`) REFERENCES `biocorepipe_save` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_pipeline`
--

LOCK TABLES `project_pipeline` WRITE;
/*!40000 ALTER TABLE `project_pipeline` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_pipeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_pipeline_input`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_pipeline_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `pipeline_id` int(11) NOT NULL,
  `input_id` int(11) NOT NULL,
  `project_pipeline_id` int(11) NOT NULL,
  `g_num` int(11) DEFAULT NULL,
  `given_name` varchar(256) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  `qualifier` varchar(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `pipeline_id` (`pipeline_id`),
  KEY `input_id` (`input_id`),
  CONSTRAINT `project_pipeline_input_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  CONSTRAINT `project_pipeline_input_ibfk_2` FOREIGN KEY (`input_id`) REFERENCES `input` (`id`),
  CONSTRAINT `project_pipeline_input_ibfk_3` FOREIGN KEY (`pipeline_id`) REFERENCES `biocorepipe_save` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_pipeline_input`
--

LOCK TABLES `project_pipeline_input` WRITE;
/*!40000 ALTER TABLE `project_pipeline_input` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_pipeline_input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `run`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `run` (
  `project_pipeline_id` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `run_status` varchar(11) DEFAULT NULL,
  `attempt` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`project_pipeline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `run`
--

LOCK TABLES `run` WRITE;
/*!40000 ALTER TABLE `run` DISABLE KEYS */;
/*!40000 ALTER TABLE `run` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `run_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `run_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_pipeline_id` int(11) NOT NULL,
  `run_status` varchar(20) DEFAULT NULL,
  `duration` varchar(30) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_ended` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `run_log`
--

LOCK TABLES `run_log` WRITE;
/*!40000 ALTER TABLE `run_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `run_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ssh`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ssh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `check_ourkey` varchar(6) DEFAULT NULL,
  `check_userkey` varchar(6) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ssh`
--

LOCK TABLES `ssh` WRITE;
/*!40000 ALTER TABLE `ssh` DISABLE KEYS */;
/*!40000 ALTER TABLE `ssh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) DEFAULT NULL,
  `g_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`u_id`) USING BTREE,
  KEY `group` (`g_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `clusteruser` varchar(45) DEFAULT NULL,
  `role` varchar(45) DEFAULT NULL,
  `name` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `institute` varchar(45) DEFAULT NULL,
  `lab` varchar(45) DEFAULT NULL,
  `photo_loc` varchar(255) NOT NULL DEFAULT '/img/avatar5.png',
  `memberdate` datetime DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `google_image` varchar(255) DEFAULT NULL,
  `google_id` varchar(100) NOT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userind` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-13 21:54:58
