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

-- 导出  表 towcloud.scloudm_product 结构
CREATE TABLE IF NOT EXISTS `scloudm_product` (
  `product_id` int(11) NOT NULL COMMENT '项目ID',
  `name` varchar(20) NOT NULL,
  `alias` varchar(10) NOT NULL,
  `type` varchar(20) NOT NULL,
  `desc` varchar(50) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='星云产品列表';

-- 正在导出表  towcloud.scloudm_product 的数据：~80 rows (大约)
/*!40000 ALTER TABLE `scloudm_product` DISABLE KEYS */;
INSERT INTO `scloudm_product` (`product_id`, `name`, `alias`, `type`, `desc`, `group_id`) VALUES
	(200001, '公司公共', '', '公司公共', '公司公共', 1),
	(200002, '征途免费版', 'ztmfb', '自研产品', 'RPG 网络角色扮演', 1),
	(200003, '巨人', 'jr', '自研产品', 'RPG 网络角色扮演', 1),
	(200005, '龙魂', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200006, '万王之王3', 'wwzw', '服务提供', 'RPG 网络角色扮演', 1),
	(200008, '游戏公共', '', '游戏公共', '游戏公共', 3),
	(200010, '《征途》怀旧版', 'zthjb', '自研产品', 'RPG 网络角色扮演', 1),
	(200019, '征途2', 'zt2', '自研产品', 'RPG 网络角色扮演', 1),
	(200021, '兵王', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200025, '仙途', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200028, '绿色征途', 'ztls', '自研产品', 'RPG 网络角色扮演', 1),
	(200031, '巨人嘟嘟', 'wwzw', '自研工具', 'wwzw', 2),
	(200033, '艾尔之光', '', '代理', 'RPG 动作角色扮演', 1),
	(200036, '择天记', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200037, '光荣使命', '', '自研产品', 'FPS 第一人称射击', 1),
	(200051, 'TPS[军事版]', '', '自研产品', 'TPS 第三人称射击', 1),
	(200106, '仙侠世界', 'xxsj', '自研产品', 'RPG 角色扮演', 1),
	(200111, '暗黑三国', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200113, '巨人江湖', 'jh', '自研产品', 'RPG 角色扮演', 1),
	(200114, '最无极', '', '自研产品', 'RPG 动作角色扮演', 1),
	(200115, '莽荒纪', '', '合作开发', 'RPG 角色扮演', 2),
	(200116, '千军', 'qj', '市场合作', 'RPG 网络角色扮演', 1),
	(200117, '绿色征途腾讯版', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200120, '新征途', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200125, '模拟仿真', 'wwzw', '服务提供', '22', 3),
	(200130, '仙途贰', '', '自研产品', 'RPG 角色扮演', 1),
	(200132, '征途', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200134, '恶魔法则', '', '自研产品', 'RPG 角色扮演', 2),
	(200135, 'MONI玩', '', '市场合作', '', 1),
	(200136, '页游公共-暗黑三国', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200137, '页游公共', '', '自研产品', '', 1),
	(200141, '炫斗封神', '', '自研产品', 'RPG 动作角色扮演', 1),
	(200142, '铁血龙魂', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200143, '巨人移动', '', '服务提供', '', 1),
	(200145, '狂野星球', '', '代理', 'RPG 网络角色扮演', 1),
	(200146, '征途2经典版', 'zt2jdb', '自研产品', 'RPG 网络角色扮演', 1),
	(200148, '三国笑传', '', '自研产品', 'SLG 策略战棋', 2),
	(200151, '武极天下', '', '自研产品', 'RPG 网络角色扮演', 2),
	(200152, '征程', 'xzt', '合作开发', 'RPG 网络角色扮演', 2),
	(200157, '星云', 'wwzw', '服务提供', 'yy', 1),
	(200158, '大主宰', '', '代理', 'ETC 其他', 2),
	(200160, '国民足球', '', '自研产品', 'ETC 其他', 2),
	(200161, '金秀贤明星学院', '', '自研产品', 'SIM 模拟经营', 2),
	(200164, '仙侠世界2', '', '自研产品', 'RPG 角色扮演', 1),
	(200166, '综合运营公共项目', '', '服务提供', '', 1),
	(200168, '全民星际大战', '', '合作开发', 'ACT 动作射击', 1),
	(200170, '末世战纪', '', '代理', 'RPG 动作角色扮演', 2),
	(200172, 'WiFi共享', 'wwzw', '自研工具', 'ii', 2),
	(200173, '征途穿越版', '', '自研产品', 'RPG 网络角色扮演', 1),
	(200175, '征途2动作版', 'zt2dzb', '自研产品', '', 1),
	(200176, '征程精英版', '', '合作开发', 'RPG 网络角色扮演', 2),
	(200178, '刀剑2-侠魔志', '', '代理', 'ACT 平台动作', 2),
	(200181, '新古龙群侠传', '', '自研产品', 'RPG 角色扮演', 2),
	(200182, '球球大作战', '', '自研产品', 'RTT 即时战术', 2),
	(200183, '街篮', '', '代理', 'ETC 其他', 2),
	(200184, '点数消耗客服统计项目', 'wwzw', '自研工具', 'p', 1),
	(200188, '巨人武侠', '', '代理', 'RPG 动作角色扮演', 2),
	(200189, '艾斯蒂敢达战争要塞', '', '代理', 'ETC 其他', 2),
	(200190, 'MUST', 'wwzw', '自研工具', 'o', 1),
	(200191, '五千年', '', '自研产品', 'SLG 策略战棋', 2),
	(200192, '研究院支撑业务', '', '服务提供', '', 1),
	(200193, '街机三国', '', '代理', 'RPG 动作角色扮演', 2),
	(200195, '虚荣', '', '代理', 'RPG 策略角色扮演', 2),
	(200196, '龙珠端游', '', '自研产品', '', 1),
	(200197, '龙珠手游', 'wwzw', '自研产品', '9', 2),
	(200200, 'Beside M', 'wwzw', '自研产品', 'i', 2),
	(200204, '犬夜叉', '', '自研产品', '犬夜叉', 2),
	(200205, '星际冲突', '', '自研产品', '2', 2),
	(200206, '天书奇谭', '', '自研产品', '', 1),
	(200207, '楚汉征战', '', '自研产品', '2', 2),
	(200208, '僵尸', '', '自研产品', '', 1),
	(200209, '糖果传奇类', '', '自研产品', '', 1),
	(200210, '卡通农场类', '', '自研产品', '', 1),
	(200211, '征途3', '', '自研产品', '', 1),
	(200212, '海岛奇兵', '', '自研产品', '', 1),
	(200214, '球球大爆破', '', '自研产品', 'ETC 其他', 2),
	(200215, '球球大灌篮', '', '自研产品', '', 1),
	(200217, '疯狂坦克', '', '自研产品', '', 1),
	(200218, '部落争霸', '', '自研产品', '2', 2),
	(200220, '球球战争', '', '自研产品', '', 1);
/*!40000 ALTER TABLE `scloudm_product` ENABLE KEYS */;


-- 导出  表 towcloud.scloudm_product_group 结构
CREATE TABLE IF NOT EXISTS `scloudm_product_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `desc` varchar(200) NOT NULL,
  `prices` text NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='星云产品组';

-- 正在导出表  towcloud.scloudm_product_group 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `scloudm_product_group` DISABLE KEYS */;
INSERT INTO `scloudm_product_group` (`group_id`, `name`, `desc`, `prices`) VALUES
	(1, '端页游、非游戏、新平台、公司公共', '端页游、非游戏、新平台、公司公共', '{"jgf":"22","zhfwcb":"44","syshbgp":"555","sybjbgp":"444","sysh":"22","sybj":"11","sytj":"55","syltsh":"33","syltbj":"44","sylttj":"66","cdn":"55"}'),
	(2, '手游、嘟嘟、wifi共享', '手游、嘟嘟、wifi共享', '{"jgf":"22","zhfwcb":"44","syshbgp":"555","sybjbgp":"444","sysh":"22","sybj":"11","sytj":"55","syltsh":"44","syltbj":"55","sylttj":"66","cdn":"55"}'),
	(3, '游戏公共、手游公共', '游戏公共、手游公共', '{"jgf":"22","zhfwcb":"44","syshbgp":"555","sybjbgp":"444","sysh":"22","sybj":"11","sytj":"55","cdn":"55"}');
/*!40000 ALTER TABLE `scloudm_product_group` ENABLE KEYS */;


-- 导出  表 towcloud.scloudm_public_group 结构
CREATE TABLE IF NOT EXISTS `scloudm_public_group` (
  `product_id` int(11) NOT NULL,
  `product_id2` int(11) NOT NULL,
  `percent` int(11) NOT NULL,
  UNIQUE KEY `product_id_product_id2` (`product_id`,`product_id2`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公共项目，手游平摊管理';

-- 正在导出表  towcloud.scloudm_public_group 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `scloudm_public_group` DISABLE KEYS */;
INSERT INTO `scloudm_public_group` (`product_id`, `product_id2`, `percent`) VALUES
	(200166, 200151, 40),
	(200166, 200152, 60);
/*!40000 ALTER TABLE `scloudm_public_group` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
