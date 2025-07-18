-- ai添加的注释
CREATE TABLE `phpyun_company_job` (
  `id` INT UNSIGNED AUTO_INCREMENT COMMENT '职位ID（主键，自增）',
  `uid` INT UNSIGNED NOT NULL COMMENT '企业用户ID',
  `name` VARCHAR(255) NOT NULL COMMENT '职位名称',
  `com_name` VARCHAR(255) NOT NULL COMMENT '公司名称',
  `hy` INT DEFAULT NULL COMMENT '行业ID',
  `job1` INT DEFAULT NULL COMMENT '一级职位分类ID',
  `job1_son` INT DEFAULT NULL COMMENT '二级职位分类ID',
  `job_post` INT DEFAULT NULL COMMENT '三级职位分类ID（最细分类）',
  `provinceid` INT DEFAULT NULL COMMENT '工作省份ID',
  `cityid` INT DEFAULT NULL COMMENT '工作城市ID',
  `three_cityid` INT DEFAULT NULL COMMENT '工作区县ID',
  `cert` TINYINT DEFAULT 0 COMMENT '企业是否已认证（1是，0否）',
  `type` TINYINT DEFAULT 0 COMMENT '职位性质（如全职、兼职）',
  `number` VARCHAR(50) DEFAULT NULL COMMENT '招聘人数',
  `exp` INT DEFAULT NULL COMMENT '工作经验要求ID',
  `report` INT DEFAULT NULL COMMENT '到岗时间要求',
  `sex` TINYINT DEFAULT 0 COMMENT '性别要求（0不限，1男，2女）',
  `edu` INT DEFAULT NULL COMMENT '学历要求',
  `marriage` INT DEFAULT NULL COMMENT '婚姻状况要求',
  `description` TEXT COMMENT '职位描述（支持HTML）',
  `xuanshang` TINYINT DEFAULT 0 COMMENT '是否悬赏职位（1是，0否）',
  `xsdate` INT DEFAULT NULL COMMENT '悬赏到期时间（时间戳）',
  `sdate` INT DEFAULT NULL COMMENT '发布时间（时间戳）',
  `edate` INT DEFAULT NULL COMMENT '职位截止时间（时间戳）',
  `jobhits` INT DEFAULT 0 COMMENT '职位浏览次数',
  `lastupdate` INT DEFAULT NULL COMMENT '职位最后更新时间',
  `rec` TINYINT DEFAULT 0 COMMENT '是否推荐职位（1推荐，0不推荐）',
  `cloudtype` VARCHAR(50) DEFAULT NULL COMMENT '云同步类型（预留）',
  `state` TINYINT DEFAULT 1 COMMENT '审核状态（1通过，0未审核，2未通过）',
  `statusbody` VARCHAR(255) DEFAULT NULL COMMENT '审核不通过原因',
  `age` VARCHAR(50) DEFAULT NULL COMMENT '年龄要求范围描述',
  `lang` VARCHAR(255) DEFAULT NULL COMMENT '语言要求（可多项）',
  `welfare` VARCHAR(255) DEFAULT NULL COMMENT '职位福利（逗号分隔）',
  `pr` TINYINT DEFAULT NULL COMMENT '企业性质ID',
  `mun` TINYINT DEFAULT NULL COMMENT '企业规模ID',
  `com_provinceid` INT DEFAULT NULL COMMENT '公司所在省份ID',
  `rating` INT DEFAULT NULL COMMENT '企业会员等级ID',
  `status` TINYINT DEFAULT 1 COMMENT '职位状态（1显示，0隐藏）',
  `urgent` TINYINT DEFAULT 0 COMMENT '是否紧急招聘',
  `r_status` TINYINT DEFAULT 0 COMMENT '是否首页推荐',
  `end_email` VARCHAR(100) DEFAULT NULL COMMENT '接收简历邮箱',
  `urgent_time` INT DEFAULT NULL COMMENT '紧急招聘截止时间',
  `com_logo` VARCHAR(255) DEFAULT NULL COMMENT '公司Logo地址',
  `autotype` TINYINT DEFAULT 0 COMMENT '是否自动刷新',
  `autotime` INT DEFAULT NULL COMMENT '自动刷新时间戳',
  `is_link` TINYINT DEFAULT 1 COMMENT '是否使用公司联系方式（1是，0否）',
  `link_type` TINYINT DEFAULT 1 COMMENT '联系方式类型（1默认，2自定义）',
  `source` TINYINT DEFAULT 0 COMMENT '职位来源（0平台，1API，2采集）',
  `rec_time` INT DEFAULT NULL COMMENT '推荐截止时间',
  `snum` INT DEFAULT 0 COMMENT '已投递人数',
  `operatime` INT DEFAULT NULL COMMENT '后台操作时间',
  `did` INT DEFAULT 0 COMMENT '分站ID（多城市支持）',
  `is_email` TINYINT DEFAULT 0 COMMENT '是否开启简历投递提醒邮件',
  `minsalary` INT DEFAULT NULL COMMENT '最低薪资（元）',
  `maxsalary` INT DEFAULT NULL COMMENT '最高薪资（元）',
  `sharepack` TINYINT DEFAULT 0 COMMENT '是否开启分享红包',
  `rewardpack` TINYINT DEFAULT 0 COMMENT '是否开启推荐赏金',
  `is_graduate` TINYINT DEFAULT 0 COMMENT '是否接受应届生',
  `x` VARCHAR(50) DEFAULT NULL COMMENT '地图经度',
  `y` VARCHAR(50) DEFAULT NULL COMMENT '地图纬度',
  `zuid` INT DEFAULT 0 COMMENT '后台创建者ID',
  `exp_req` VARCHAR(100) DEFAULT NULL COMMENT '经验要求文本',
  `edu_req` VARCHAR(100) DEFAULT NULL COMMENT '学历要求文本',
  `sex_req` VARCHAR(20) DEFAULT NULL COMMENT '性别要求文本',
  `minage_req` TINYINT DEFAULT NULL COMMENT '最小年龄要求',
  `maxage_req` TINYINT DEFAULT NULL COMMENT '最大年龄要求',

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='企业招聘职位表';

-- 原表结构
CREATE TABLE `phpyun_company_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `com_name` varchar(50) NOT NULL DEFAULT '',
  `hy` int(5) DEFAULT NULL,
  `job1` int(5) DEFAULT NULL,
  `job1_son` int(5) DEFAULT NULL,
  `job_post` int(5) DEFAULT NULL,
  `provinceid` int(5) DEFAULT NULL,
  `cityid` int(5) DEFAULT NULL,
  `three_cityid` int(5) DEFAULT NULL,
  `cert` varchar(50) NOT NULL DEFAULT '',
  `type` int(5) NOT NULL,
  `number` int(2) NOT NULL,
  `exp` int(5) NOT NULL,
  `report` int(5) NOT NULL,
  `sex` int(5) NOT NULL,
  `edu` int(5) NOT NULL,
  `marriage` int(5) NOT NULL,
  `description` text NOT NULL,
  `xuanshang` int(11) NOT NULL DEFAULT 0,
  `xsdate` int(11) DEFAULT NULL,
  `sdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  `jobhits` int(10) NOT NULL DEFAULT 0,
  `lastupdate` varchar(10) NOT NULL DEFAULT '',
  `rec` int(2) DEFAULT 0,
  `cloudtype` int(2) DEFAULT NULL,
  `state` int(2) DEFAULT 0,
  `statusbody` varchar(200) NOT NULL DEFAULT '',
  `age` int(11) DEFAULT NULL,
  `lang` text NOT NULL,
  `welfare` text NOT NULL,
  `pr` int(5) DEFAULT NULL,
  `mun` int(5) DEFAULT NULL,
  `com_provinceid` int(5) DEFAULT NULL,
  `rating` int(5) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  `urgent` int(1) DEFAULT NULL,
  `r_status` int(1) DEFAULT 1,
  `end_email` int(1) DEFAULT 0,
  `urgent_time` int(11) DEFAULT NULL,
  `com_logo` varchar(100) NOT NULL DEFAULT '',
  `autotype` int(11) DEFAULT 0,
  `autotime` int(11) DEFAULT 0,
  `is_link` int(1) DEFAULT 1,
  `link_type` int(1) DEFAULT 1,
  `source` int(1) DEFAULT 1,
  `rec_time` int(11) DEFAULT 0,
  `snum` int(11) DEFAULT 0,
  `operatime` int(11) DEFAULT NULL,
  `did` int(11) DEFAULT NULL,
  `is_email` int(1) DEFAULT 1,
  `minsalary` int(11) DEFAULT NULL,
  `maxsalary` int(11) DEFAULT NULL,
  `sharepack` int(11) NOT NULL DEFAULT 0,
  `rewardpack` int(11) NOT NULL DEFAULT 0,
  `is_graduate` int(11) DEFAULT 0,
  `x` varchar(50) DEFAULT NULL,
  `y` varchar(50) DEFAULT NULL,
  `zuid` int(11) DEFAULT NULL,
  `exp_req` varchar(255) DEFAULT NULL,
  `edu_req` varchar(255) DEFAULT NULL,
  `sex_req` varchar(255) DEFAULT NULL,
  `minage_req` int(5) DEFAULT NULL,
  `maxage_req` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `lastupdate` (`lastupdate`),
  KEY `cityid` (`provinceid`,`cityid`,`three_cityid`),
  KEY `jobid` (`job1`,`job1_son`,`job_post`),
  KEY `urgent` (`urgent_time`),
  KEY `rectime` (`rec_time`),
  KEY `sharepcak` (`sharepack`),
  KEY `rewardpack` (`rewardpack`),
  KEY `xsdate` (`xsdate`),
  KEY `isgraduate` (`is_graduate`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
