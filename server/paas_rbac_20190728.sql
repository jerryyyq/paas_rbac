-- MySQL dump 10.13  Distrib 8.0.16, for Linux (x86_64)
--
-- Host: localhost    Database: paas_rbac
-- ------------------------------------------------------
-- Server version	8.0.16

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8mb4 ;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ac_enterprise`
--

DROP TABLE IF EXISTS `ac_enterprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_enterprise` (
  `id_enterprise` int(11) NOT NULL AUTO_INCREMENT,
  `symbol_name` varchar(45) NOT NULL COMMENT '在整个系统中的唯一符号名，用于企业管理员登录时标明自己隶属于哪个企业',
  `real_name` varchar(256) NOT NULL,
  `country` varchar(256) DEFAULT NULL,
  `province` varchar(256) DEFAULT NULL,
  `address` varchar(512) DEFAULT NULL,
  `zipcode` varchar(45) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `state` int(11) DEFAULT '0',
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_enterprise`),
  UNIQUE KEY `symbol_name_UNIQUE` (`symbol_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_enterprise`
--

LOCK TABLES `ac_enterprise` WRITE;
/*!40000 ALTER TABLE `ac_enterprise` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_enterprise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_enterprise_operation_log`
--

DROP TABLE IF EXISTS `ac_enterprise_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_enterprise_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_enterprise` int(11) DEFAULT '0' COMMENT '所属企业 id。0 表示是系统管理员。',
  `id_user` int(11) NOT NULL,
  `action` varchar(45) NOT NULL COMMENT '动作，例如：add, create, delete, disable, enable...',
  `target_id` int(11) DEFAULT NULL COMMENT '被操作者 id',
  `target_type` int(11) DEFAULT NULL COMMENT '被操作者类型',
  `description` varchar(256) DEFAULT NULL,
  `operation_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_enterprise_operation_log`
--

LOCK TABLES `ac_enterprise_operation_log` WRITE;
/*!40000 ALTER TABLE `ac_enterprise_operation_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_enterprise_operation_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_privilege`
--

DROP TABLE IF EXISTS `ac_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_privilege` (
  `id_privilege` int(11) NOT NULL AUTO_INCREMENT,
  `id_father` int(11) NOT NULL DEFAULT '0' COMMENT '父节点 id',
  `have_child` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有子权限。用于支持多层级权限系统。',
  `name` varchar(128) NOT NULL,
  `show_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_privilege`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_privilege`
--

LOCK TABLES `ac_privilege` WRITE;
/*!40000 ALTER TABLE `ac_privilege` DISABLE KEYS */;
INSERT INTO `ac_privilege` VALUES (1,0,0,'privilege_manage','系统权限、角色管理'),(2,0,1,'sys_admin_manage','管理系统管理员'),(3,2,0,'sys_admin_add','添加系统管理员'),(4,2,0,'sys_admin_delete','删除系统管理员'),(5,2,0,'sys_admin_modify','修改系统管理员信息'),(6,2,0,'sys_admin_read','查看系统管理员信息'),(7,2,0,'sys_admin_rule','管理系统管理员角色'),(8,0,0,'sys_log_read','查看系统日志'),(9,0,1,'enterprise_manage','管理企业'),(10,9,0,'enterprise_add','添加企业'),(11,9,0,'enterprise_delete','删除企业'),(12,9,0,'enterprise_modify','修改企业信息'),(13,9,0,'enterprise_read','查看企业信息'),(14,0,1,'enterprise_admin_manage','管理企业管理员'),(15,14,0,'enterprise_admin_add','添加企业管理员'),(16,14,0,'enterprise_admin_delete','删除企业管理员'),(17,14,0,'enterprise_admin_modify','修改企业管理员信息'),(18,14,0,'enterprise_admin_read','查看企业管理员信息'),(19,14,0,'enterprise_admin_rule','管理企业管理员角色'),(20,0,1,'web_manage','管理站点'),(21,20,0,'web_add','添加站点'),(22,20,0,'web_delete','删除站点'),(23,20,0,'web_modify','修改站点信息'),(24,20,0,'web_read','查看站点信息'),(25,0,1,'user_manage','用户管理'),(26,25,0,'user_add','添加用户'),(27,25,0,'user_delete','删除用户'),(28,25,0,'user_modify','修改用户信息'),(29,25,0,'user_read','查看用户信息'),(30,25,0,'user_rule','管理用户角色'),(31,0,1,'channel_manage','渠道管理'),(32,31,0,'channel_add','添加渠道'),(33,31,0,'channel_delete','删除渠道'),(34,31,0,'channel_modify','修改渠道信息'),(35,31,0,'channel_read','查看渠道信息');
/*!40000 ALTER TABLE `ac_privilege` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_rule`
--

DROP TABLE IF EXISTS `ac_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_rule` (
  `id_rule` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` int(11) DEFAULT NULL COMMENT '1:sys, 2:enteprise, 3:websit',
  `name` varchar(128) DEFAULT NULL,
  `show_name` varchar(128) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id_rule`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_rule`
--

LOCK TABLES `ac_rule` WRITE;
/*!40000 ALTER TABLE `ac_rule` DISABLE KEYS */;
INSERT INTO `ac_rule` VALUES (1,0,'all','超级管理员','拥有系统所有管理权限');
/*!40000 ALTER TABLE `ac_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_rule_privilege`
--

DROP TABLE IF EXISTS `ac_rule_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_rule_privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_rule` int(11) DEFAULT NULL,
  `id_privilege` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`id_rule`,`id_privilege`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_rule_privilege`
--

LOCK TABLES `ac_rule_privilege` WRITE;
/*!40000 ALTER TABLE `ac_rule_privilege` DISABLE KEYS */;
INSERT INTO `ac_rule_privilege` VALUES (1,1,1,'all 角色拥有 privilege_manage 权限'),(2,1,2,'all 角色拥有 sys_admin_manage 权限'),(3,1,8,'all 角色拥有 sys_log_read 权限'),(4,1,9,'all 角色拥有 enterprise_manage 权限'),(5,1,14,'all 角色拥有 enterprise_admin_manage 权限'),(6,1,20,'all 角色拥有 web_manage 权限'),(7,1,25,'all 角色拥有 user_manage 权限'),(8,1,31,'all 角色拥有 channel_manage 权限');
/*!40000 ALTER TABLE `ac_rule_privilege` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_sys_operation_log`
--

DROP TABLE IF EXISTS `ac_sys_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_sys_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `action` varchar(45) NOT NULL COMMENT '动作，例如：add, create, delete, disable, enable...',
  `target_id` int(11) DEFAULT NULL COMMENT '被操作者 id',
  `target_type` int(11) DEFAULT NULL COMMENT '被操作者类型',
  `description` varchar(256) DEFAULT NULL,
  `operation_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_sys_operation_log`
--

LOCK TABLES `ac_sys_operation_log` WRITE;
/*!40000 ALTER TABLE `ac_sys_operation_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_sys_operation_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_third_party_channel`
--

DROP TABLE IF EXISTS `ac_third_party_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_third_party_channel` (
  `id_channel` int(11) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `Identification_code` varchar(256) DEFAULT NULL COMMENT '唯一识别码',
  `channel_url` varchar(512) DEFAULT NULL COMMENT '第三方渠道的网站 URL',
  `sharing_ratio` float DEFAULT NULL COMMENT '收入分成比例',
  `state` int(11) DEFAULT '0',
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_channel`),
  UNIQUE KEY `Identification_code_UNIQUE` (`Identification_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_third_party_channel`
--

LOCK TABLES `ac_third_party_channel` WRITE;
/*!40000 ALTER TABLE `ac_third_party_channel` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_third_party_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_user`
--

DROP TABLE IF EXISTS `ac_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL COMMENT '用户名',
  `email` varchar(45) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `salt` varchar(128) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `real_name` varchar(128) DEFAULT NULL COMMENT '真实姓名',
  `state` int(11) DEFAULT '0' COMMENT '用户的状态：例如是否激活、注销等等',
  `id_channel` int(11) DEFAULT '0' COMMENT '是从哪个渠道加过来的。0 为非渠道用户。',
  `oauth_platform_type` varchar(128) DEFAULT NULL COMMENT '第三方登录平台类型。‘’ 和 ‘0’ 表示没有第三方登录平台关联帐号；‘1’ 是微信 unionid；‘2’是微信 openid；''3''是 QQ；‘4’是新浪；',
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `qq_openid` varchar(128) DEFAULT NULL,
  `sina_openid` varchar(128) DEFAULT NULL,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `email_verify_state` int(11) DEFAULT '0' COMMENT '邮件地址校验状态。0=未校验；1=校验成功；2=已发校验码。',
  `email_verify_code` varchar(128) DEFAULT NULL COMMENT '邮件地址校验码。',
  `email_verify_code_send_time` datetime DEFAULT NULL COMMENT '邮件地址校验码发送时间。',
  `mobile_verify_state` int(11) DEFAULT '0' COMMENT '手机校验状态。0=未校验；1=校验成功；2=已发校验码。',
  `mobile_verify_code` varchar(128) DEFAULT NULL COMMENT '手机校验码。',
  `mobile_verify_code_send_time` datetime DEFAULT NULL COMMENT '手机校验码发送时间。',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `mobile_UNIQUE` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_user`
--

LOCK TABLES `ac_user` WRITE;
/*!40000 ALTER TABLE `ac_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_user_resource_rule`
--

DROP TABLE IF EXISTS `ac_user_resource_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_user_resource_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_rule` int(11) DEFAULT NULL,
  `resource_type` int(11) NOT NULL COMMENT '1:sys, 2:enteprise, 3:websit',
  `id_resource` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index2` (`id_user`,`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_user_resource_rule`
--

LOCK TABLES `ac_user_resource_rule` WRITE;
/*!40000 ALTER TABLE `ac_user_resource_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_user_resource_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_website`
--

DROP TABLE IF EXISTS `ac_website`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `ac_website` (
  `id_website` int(11) NOT NULL AUTO_INCREMENT,
  `id_enterprise` int(11) NOT NULL COMMENT '属于哪个企业的',
  `name` varchar(512) DEFAULT NULL,
  `url` varchar(128) NOT NULL,
  `symbol_name` varchar(128) NOT NULL COMMENT 'url 中 子域名',
  `style` int(11) NOT NULL DEFAULT '0' COMMENT '网站风格（皮肤和式样）',
  `description` varchar(256) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_website`),
  UNIQUE KEY `symbol_name_UNIQUE` (`symbol_name`),
  KEY `fk_website_1_idx` (`id_enterprise`),
  CONSTRAINT `fk_website_1` FOREIGN KEY (`id_enterprise`) REFERENCES `ac_enterprise` (`id_enterprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_website`
--

LOCK TABLES `ac_website` WRITE;
/*!40000 ALTER TABLE `ac_website` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_website` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-28 21:49:13
