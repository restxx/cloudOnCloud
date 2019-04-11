-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.17-log - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 towcloud.scloudm_netflow 结构
CREATE TABLE IF NOT EXISTS `scloudm_netflow` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project` varchar(10) NOT NULL DEFAULT '0',
  `idc` varchar(10) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `netflow` decimal(16,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_idc_time` (`project`,`idc`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='物理机带宽数据';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
