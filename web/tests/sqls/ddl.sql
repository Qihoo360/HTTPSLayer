/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table balance
# ------------------------------------------------------------

CREATE TABLE `balance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned DEFAULT NULL COMMENT '项目',
  `location` varchar(10) DEFAULT NULL COMMENT '机房',
  `vip` varchar(20) NOT NULL DEFAULT '' COMMENT 'VIP',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `qfe_idc` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table cert_host
# ------------------------------------------------------------

CREATE TABLE `cert_host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '域名',
  `certificate_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属证书',
  PRIMARY KEY (`id`),
  KEY `idx_certificate_id` (`certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table certificate
# ------------------------------------------------------------

CREATE TABLE `certificate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '证书名称',
  `priv_key` varchar(50) DEFAULT NULL COMMENT '私钥',
  `pub_key` varchar(50) DEFAULT NULL COMMENT '公钥',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `serial_no` varchar(300) DEFAULT '' COMMENT '序列号',
  `subject` varchar(300) DEFAULT '' COMMENT 'Subject名称',
  `priority` int(11) DEFAULT NULL COMMENT '优先级',
  `algorithm` varchar(50) DEFAULT '' COMMENT '签名算法',
  `issuer` varchar(200) DEFAULT '' COMMENT '颁发者',
  `valid_start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `valid_end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `contact_email` varchar(60) DEFAULT '' COMMENT '联系人邮箱',
  `priv_content` text COMMENT '私钥内容',
  `pub_content` text COMMENT '公钥内容',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table frequency
# ------------------------------------------------------------

CREATE TABLE `frequency` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `project_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '项目id',
  `description` varchar(255) DEFAULT NULL,
  `path` varchar(256) NOT NULL DEFAULT '' COMMENT '路径path',
  `according` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '限频依据 1 ip + cookie 2 只是ip  3 只是cookie ',
  `method` varchar(256) NOT NULL DEFAULT '' COMMENT 'POST GET 等',
  `cookie_name` varchar(256) NOT NULL DEFAULT '' COMMENT '依据cookie时，cookie的名字',
  `time_window` varchar(2048) NOT NULL DEFAULT '' COMMENT '时间窗口配置',
  `referer` varchar(2048) NOT NULL DEFAULT '' COMMENT '允许的referer',
  `arguments` varchar(2048) NOT NULL DEFAULT '' COMMENT '必须含有的参数',
  `white_ip` text NOT NULL COMMENT '白名单ip',
  `black_ip` text NOT NULL COMMENT '黑名单ip',
  `handle_way` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '处理方式 1 只记日志 2 验证码  3 业务处理',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '状态 0 删除  1 开启 2 未开启',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `create_user` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户',
  `update_user` int(11) NOT NULL DEFAULT '0' COMMENT '更新用户',
  `update_operation` int(11) NOT NULL DEFAULT '0' COMMENT '更新操作',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table frequency_version
# ------------------------------------------------------------

CREATE TABLE `frequency_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目id',
  `data` text NOT NULL COMMENT '上线数据',
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本',
  `online_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '上线日期',
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '回滚日期',
  `online_user` varchar(100) NOT NULL DEFAULT '' COMMENT '上线人员',
  `update_user` varchar(100) NOT NULL DEFAULT '' COMMENT '回滚人员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 2上线后被回滚 1 在线 0 下线',
  `project_label` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table global_config
# ------------------------------------------------------------

CREATE TABLE `global_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '当前配置内容',
  `status` tinyint(6) NOT NULL DEFAULT '0' COMMENT '状态 0.失效， 1.预发布，2.上线',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目id',
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL DEFAULT '0',
  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_time` double DEFAULT NULL,
  `prefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_log_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table proj_host
# ------------------------------------------------------------

CREATE TABLE `proj_host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '域名',
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属项目',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table project
# ------------------------------------------------------------

CREATE TABLE `project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '项目名',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
  `contact_email` varchar(64) NOT NULL DEFAULT '' COMMENT '业务联系人邮箱',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `label` varchar(64) NOT NULL DEFAULT '' COMMENT '标签',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table rel_proj_cert
# ------------------------------------------------------------

CREATE TABLE `rel_proj_cert` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '项目',
  `certificate_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '证书',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_proj_cert` (`project_id`,`certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '邮箱',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` varchar(20) DEFAULT '' COMMENT '联系电话',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `is_admin` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否是管理员',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `role_id` tinyint(6) unsigned NOT NULL DEFAULT '0' COMMENT '角色',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
