-- --------------------------------------------------------
-- 主机:                           localhost
-- 服务器版本:                        5.7.3-m13 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 twocloud 的数据库结构
CREATE DATABASE IF NOT EXISTS `twocloud` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `twocloud`;


-- 导出  表 twocloud.auditlog 结构
CREATE TABLE IF NOT EXISTS `auditlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `host_ip` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1:login,2:command',
  `log` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `host_ip_username` (`host_ip`,`username`),
  KEY `create_at` (`create_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 twocloud.js_public_key 结构
CREATE TABLE IF NOT EXISTS `js_public_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `public_key` text,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 twocloud.jump_server_bind 结构
CREATE TABLE IF NOT EXISTS `jump_server_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `firm_id` varchar(50) NOT NULL,
  `host_id` varchar(255) NOT NULL,
  `host_name` varchar(100) NOT NULL,
  `host_ip` varchar(50) NOT NULL,
  `host_username` varchar(50) NOT NULL,
  `host_port` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unionKey` (`username`,`host_ip`,`host_username`,`host_port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 twocloud.operation_log 结构
CREATE TABLE IF NOT EXISTS `operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firm_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `host_id` varchar(50) NOT NULL,
  `operation` varchar(50) NOT NULL,
  `result` smallint(6) NOT NULL COMMENT '1:success;0:failed',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `firm_id_host_id` (`firm_id`,`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 twocloud.users 结构
CREATE TABLE IF NOT EXISTS `users` (
  `uid` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `mobile` bigint(20) DEFAULT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `nickname` varchar(40) DEFAULT NULL,
  `sex` tinyint(4) DEFAULT NULL,
  `money` bigint(20) NOT NULL DEFAULT '0',
  `updetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group` tinyint(4) DEFAULT '0' COMMENT '0:普通用户,1:管理员',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 twocloud.user_firm_passport 结构
CREATE TABLE IF NOT EXISTS `user_firm_passport` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `passport_info` text NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:正常，1:未定义',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
