CREATE DATABASE  IF NOT EXISTS `paas_rbac` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `paas_rbac`;
-- MySQL dump 10.13  Distrib 5.7.20, for Linux (x86_64)
--
-- Host: localhost    Database: paas_rbac
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.17.04.1

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
-- Table structure for table `ac_enterprise_admin_rule`
--

DROP TABLE IF EXISTS `ac_enterprise_admin_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_enterprise_admin_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) DEFAULT NULL,
  `id_rule` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index2` (`id_admin`,`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_enterprise_admin_rule`
--

LOCK TABLES `ac_enterprise_admin_rule` WRITE;
/*!40000 ALTER TABLE `ac_enterprise_admin_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_enterprise_admin_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_privilege`
--

DROP TABLE IF EXISTS `ac_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_privilege` (
  `id_privilege` int(11) NOT NULL AUTO_INCREMENT,
  `id_father` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) DEFAULT NULL,
  `show_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_privilege`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_privilege`
--

LOCK TABLES `ac_privilege` WRITE;
/*!40000 ALTER TABLE `ac_privilege` DISABLE KEYS */;
INSERT INTO `ac_privilege` VALUES (1,0,'privilege_manage','系统权限、角色管理'),(2,0,'sys_admin_manage','管理系统管理员'),(3,2,'sys_admin_add','添加系统管理员'),(4,2,'sys_admin_delete','删除系统管理员'),(5,2,'sys_admin_modify','修改系统管理员信息'),(6,2,'sys_admin_read','查看系统管理员信息'),(7,2,'sys_admin_rule','管理系统管理员角色'),(8,0,'sys_log_read','查看系统日志'),(9,0,'enterprise_manage','管理企业'),(10,9,'enterprise_add','添加企业'),(11,9,'enterprise_delete','删除企业'),(12,9,'enterprise_modify','修改企业信息'),(13,9,'enterprise_read','查看企业信息'),(14,0,'enterprise_admin_manage','管理企业管理员'),(15,14,'enterprise_admin_add','添加企业管理员'),(16,14,'enterprise_admin_delete','删除企业管理员'),(17,14,'enterprise_admin_modify','修改企业管理员信息'),(18,14,'enterprise_admin_read','查看企业管理员信息'),(19,14,'enterprise_admin_rule','管理企业管理员角色'),(20,0,'web_manage','管理站点'),(21,20,'web_add','添加站点'),(22,20,'web_delete','删除站点'),(23,20,'web_modify','修改站点信息'),(24,20,'web_read','查看站点信息'),(25,0,'user_manage','用户管理'),(26,25,'user_add','添加用户'),(27,25,'user_delete','删除用户'),(28,25,'user_modify','修改用户信息'),(29,25,'user_read','查看用户信息'),(30,25,'user_rule','管理用户角色'),(31,0,'channel_manage','渠道管理'),(32,31,'channel_add','添加渠道'),(33,31,'channel_delete','删除渠道'),(34,31,'channel_modify','修改渠道信息'),(35,31,'channel_read','查看渠道信息');
/*!40000 ALTER TABLE `ac_privilege` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_resource`
--

DROP TABLE IF EXISTS `ac_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_resource` (
  `id_resource` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL COMMENT '0:sys, 1:enteprise, 2:websit',
  `relation_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) DEFAULT NULL,
  `show_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_resource`
--

LOCK TABLES `ac_resource` WRITE;
/*!40000 ALTER TABLE `ac_resource` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_resource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_rule`
--

DROP TABLE IF EXISTS `ac_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_rule` (
  `id_rule` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL COMMENT '0:sys, 1:enteprise, 2:websit',
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
-- Table structure for table `ac_rule_resource_privilege`
--

DROP TABLE IF EXISTS `ac_rule_resource_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_rule_resource_privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_rule` int(11) DEFAULT NULL,
  `id_resource` int(11) DEFAULT NULL,
  `id_privilege` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`id_rule`,`id_resource`,`id_privilege`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_rule_resource_privilege`
--

LOCK TABLES `ac_rule_resource_privilege` WRITE;
/*!40000 ALTER TABLE `ac_rule_resource_privilege` DISABLE KEYS */;
INSERT INTO `ac_rule_resource_privilege` VALUES (1,1,0,1,'all 角色拥有 privilege_manage 权限'),(2,1,0,2,'all 角色拥有 sys_admin_manage 权限'),(3,1,0,8,'all 角色拥有 sys_log_read 权限'),(4,1,0,9,'all 角色拥有 enterprise_manage 权限'),(5,1,0,14,'all 角色拥有 enterprise_admin_manage 权限'),(6,1,0,20,'all 角色拥有 web_manage 权限'),(7,1,0,25,'all 角色拥有 user_manage 权限'),(8,1,0,31,'all 角色拥有 channel_manage 权限');
/*!40000 ALTER TABLE `ac_rule_resource_privilege` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_sys_admin_rule`
--

DROP TABLE IF EXISTS `ac_sys_admin_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_sys_admin_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) DEFAULT NULL,
  `id_rule` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index2` (`id_admin`,`id_rule`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_sys_admin_rule`
--

LOCK TABLES `ac_sys_admin_rule` WRITE;
/*!40000 ALTER TABLE `ac_sys_admin_rule` DISABLE KEYS */;
INSERT INTO `ac_sys_admin_rule` VALUES (1,1,1,'系统初始超级管理员拥有系统的所有初始权限');
/*!40000 ALTER TABLE `ac_sys_admin_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_user_rule_`
--

DROP TABLE IF EXISTS `ac_user_rule_`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_user_rule_` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_rule` int(11) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index2` (`id_user`,`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_user_rule_`
--

LOCK TABLES `ac_user_rule_` WRITE;
/*!40000 ALTER TABLE `ac_user_rule_` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_user_rule_` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_operation_log`
--

DROP TABLE IF EXISTS `admin_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `id_enterprise` int(11) DEFAULT '0' COMMENT '所属企业 id。0 表示是系统管理员。',
  `action` varchar(45) NOT NULL COMMENT '动作，例如：add, create, delete, disable, enable...',
  `target_id` int(11) DEFAULT NULL COMMENT '被操作者 id',
  `target_type` int(11) DEFAULT NULL COMMENT '被操作者类型',
  `description` varchar(256) DEFAULT NULL,
  `operation_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_operation_log`
--

LOCK TABLES `admin_operation_log` WRITE;
/*!40000 ALTER TABLE `admin_operation_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_operation_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise`
--

DROP TABLE IF EXISTS `enterprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise` (
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
-- Dumping data for table `enterprise`
--

LOCK TABLES `enterprise` WRITE;
/*!40000 ALTER TABLE `enterprise` DISABLE KEYS */;
/*!40000 ALTER TABLE `enterprise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise_admin`
--

DROP TABLE IF EXISTS `enterprise_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise_admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `id_enterprise` int(11) NOT NULL COMMENT '所属企业',
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `salt` varchar(128) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `real_name` varchar(128) DEFAULT NULL,
  `state` int(11) DEFAULT '0',
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enterprise_admin`
--

LOCK TABLES `enterprise_admin` WRITE;
/*!40000 ALTER TABLE `enterprise_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `enterprise_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_admin`
--

DROP TABLE IF EXISTS `sys_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `salt` varchar(128) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `real_name` varchar(128) DEFAULT NULL,
  `state` int(11) DEFAULT '0',
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_admin`
--

LOCK TABLES `sys_admin` WRITE;
/*!40000 ALTER TABLE `sys_admin` DISABLE KEYS */;
INSERT INTO `sys_admin` VALUES (1,'admin','admin@system','13240269288',NULL,NULL,NULL,0,NULL,NULL,'2017-10-15 16:03:58',NULL,NULL);
/*!40000 ALTER TABLE `sys_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `third_party_channel`
--

DROP TABLE IF EXISTS `third_party_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `third_party_channel` (
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
-- Dumping data for table `third_party_channel`
--

LOCK TABLES `third_party_channel` WRITE;
/*!40000 ALTER TABLE `third_party_channel` DISABLE KEYS */;
/*!40000 ALTER TABLE `third_party_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_`
--

DROP TABLE IF EXISTS `user_`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `salt` varchar(128) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `real_name` varchar(128) DEFAULT NULL,
  `state` int(11) DEFAULT '0',
  `id_channel` int(11) DEFAULT '0' COMMENT '是从哪个渠道加过来的。0 为非渠道用户。',
  `oauth_platform_type` varchar(128) DEFAULT NULL COMMENT '第三方登录平台类型。‘’ 和 ‘0’ 表示没有第三方登录平台关联帐号；‘1’ 是微信 unionid；‘2’是微信 openid；''3''是 QQ；‘4’是新浪；',
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `qq_openid` varchar(128) DEFAULT NULL,
  `sina_openid` varchar(128) DEFAULT NULL,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_`
--

LOCK TABLES `user_` WRITE;
/*!40000 ALTER TABLE `user_` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website`
--

DROP TABLE IF EXISTS `website`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website` (
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
  CONSTRAINT `fk_website_1` FOREIGN KEY (`id_enterprise`) REFERENCES `enterprise` (`id_enterprise`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website`
--

LOCK TABLES `website` WRITE;
/*!40000 ALTER TABLE `website` DISABLE KEYS */;
/*!40000 ALTER TABLE `website` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-10-27 23:50:09
