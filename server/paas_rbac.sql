CREATE DATABASE  IF NOT EXISTS `paas_rbac` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `paas_rbac`;
-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: paas_rbac
-- ------------------------------------------------------
-- Server version	5.7.19-0ubuntu0.17.04.1

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
  PRIMARY KEY (`id`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_privilege`
--

LOCK TABLES `ac_privilege` WRITE;
/*!40000 ALTER TABLE `ac_privilege` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_rule`
--

LOCK TABLES `ac_rule` WRITE;
/*!40000 ALTER TABLE `ac_rule` DISABLE KEYS */;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_rule_resource_privilege`
--

LOCK TABLES `ac_rule_resource_privilege` WRITE;
/*!40000 ALTER TABLE `ac_rule_resource_privilege` DISABLE KEYS */;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_sys_admin_rule`
--

LOCK TABLES `ac_sys_admin_rule` WRITE;
/*!40000 ALTER TABLE `ac_sys_admin_rule` DISABLE KEYS */;
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
  PRIMARY KEY (`id`)
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
  PRIMARY KEY (`id_enterprise`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_admin`
--

LOCK TABLES `sys_admin` WRITE;
/*!40000 ALTER TABLE `sys_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_admin` ENABLE KEYS */;
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
  `wx_unionid` varchar(128) DEFAULT NULL,
  `wx_openid` varchar(128) DEFAULT NULL,
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(512) DEFAULT NULL COMMENT '用于跨站点统一登录，无此需求可以忽略。',
  `token_create_time` datetime DEFAULT NULL COMMENT 'token 创建时间',
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
  `symbol_name` varchar(128) DEFAULT NULL COMMENT 'url 中 子域名',
  `style` int(11) NOT NULL DEFAULT '0' COMMENT '网站风格（皮肤和式样）',
  `description` varchar(256) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `registe_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_website`),
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

-- Dump completed on 2017-10-14 23:29:55
