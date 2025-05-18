<?php

set_time_limit(0);
include("../global.php");

if($_GET[step]!="6"){
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>正在升级中.... - - Powered by PHPYun.</title>

<link href="'.$config[sy_weburl].'/template/member/style/msg.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="content" style="background:f5f5f5;">
  <div class="content-title" style="width:100%"><span style="width:100%">正在升级中,已进行'.$_GET[step].'/5</span></div>
  <div style="border:1px solid #ccc; float:left;width:100%;">
    <dl style="width:100%">
       <p style="text-align:center;"><img src="loading.gif"></p><br>
	   <p style="text-align:center;"><font color="red">本次更新时间较长,请不要中途中断,以免升级失败,请耐心等待！</font></p>
    </dl>
</div>
</div>
</body>
</html>';
};
if($_GET[step]==""){

	echo "<script>location.href='$config[sy_weburl]/update/index.php?step=1';</script>";

}

/****************************第一步：新增数据功能表************************************/
if($_GET[step]=="1"){



	$db->query("CREATE TABLE `$db_config[def]zhaopinhui_job` (
	`id`  int(11) NOT NULL AUTO_INCREMENT ,
	`uid`  int(11) NULL DEFAULT 0 ,
	`job_name`  varchar(64)  NOT NULL DEFAULT '' ,
	`zid`  int(11) NULL DEFAULT 0 ,
	`jobid`  varchar(255)  NOT NULL DEFAULT '' ,
	`ctime`  int(11) NULL DEFAULT 0 ,
	PRIMARY KEY (`id`),
	INDEX `zid` (`zid`) USING BTREE 
	)ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	


	$db->query("CREATE TABLE `$db_config[def]admin_wmtype` (
	`id`  int(11) NOT NULL AUTO_INCREMENT ,
	`type`  int(11) NOT NULL ,
	`uid`  text  NOT NULL ,
	`name`  varchar(255) NOT NULL ,
	PRIMARY KEY (`id`)
	)ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE `$db_config[def]error_log` (
	`id`  int(11) NOT NULL AUTO_INCREMENT ,
	`uid`  int(11) NULL DEFAULT NULL ,
	`type`  int(11) NULL DEFAULT NULL ,
	`content`  varchar(255)  NULL DEFAULT NULL ,
	`ctime`  int(10) NULL DEFAULT NULL ,
	PRIMARY KEY (`id`)
	)ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE `$db_config[def]wx_msg` (
	`id`  int(11) NOT NULL AUTO_INCREMENT ,
	`uid`  int(11) NOT NULL ,
	`utype`  int(1) NOT NULL ,
	`mbconfig`  varchar(200)  NOT NULL ,
	`wxid`  varchar(100)  NOT NULL ,
	`ctime`  int(11) NOT NULL ,
	`status`  int(10) NOT NULL ,
	`msg`  varchar(255)  NOT NULL ,
	`body`  text  NULL DEFAULT NULL ,
	PRIMARY KEY (`id`)
	)ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE `$db_config[def]yqmb` (
	`id`  int(11) NOT NULL AUTO_INCREMENT ,
	`uid`  int(11) NULL DEFAULT NULL ,
	`name`  varchar(100)  NOT NULL DEFAULT '' ,
	`linkman`  varchar(255)  NOT NULL DEFAULT '' ,
	`linktel`  varchar(255)  NOT NULL DEFAULT '' ,
	`address`  varchar(255)  NOT NULL ,
	`intertime`  int(10) NOT NULL ,
	`content`  text  NOT NULL ,
	`addtime`  int(11) NULL DEFAULT NULL ,
	`did`  int(11) NULL DEFAULT NULL ,
	PRIMARY KEY (`id`)
	)ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	echo "<script>location.href='$config[sy_weburl]/update/index.php?step=2';</script>";

}
/****************************第二步：新增数据字段************************************/


if($_GET[step]=="2"){
	

	$db->query("ALTER TABLE `$db_config[def]ad` ADD COLUMN `appurl`  varchar(100)  NULL DEFAULT NULL AFTER `lianmeng_url`");

	$db->query("ALTER TABLE `$db_config[def]adclick` MODIFY COLUMN `ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]admin_announcement` ADD COLUMN `startime`  int(11) NOT NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]admin_announcement` MODIFY COLUMN `endtime`  int(11) NOT NULL ");

	$db->query("ALTER TABLE `$db_config[def]admin_log` MODIFY COLUMN `ip`  varchar(100)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]admin_user` ADD COLUMN `org`  int(11) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]admin_user` ADD COLUMN `power`  int(1) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]admin_user` ADD COLUMN `spower`  int(1) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company` ADD COLUMN `f_time`  int(11) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company` ADD COLUMN `pcrmuid`  int(11) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company` ADD COLUMN `release_time`  int(11) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `exp_req`  varchar(255)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `edu_req`  varchar(255)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `sex_req`  varchar(255)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `minage_req`  int(5) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `maxage_req`  int(5) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_order` ADD COLUMN `port`  int(1) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_rating` ADD COLUMN `spview_num`  int(11) NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]company_service_detail` ADD COLUMN `spview_num`  int(11) NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]company_statis` MODIFY COLUMN `oldrating_name`  varchar(200)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]company_statis` ADD COLUMN `spview_num`  int(11) NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]login_log` MODIFY COLUMN `ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]look_resume` ADD COLUMN `isshow`  int(5) NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]member` MODIFY COLUMN `reg_ip`  varchar(40)  NOT NULL DEFAULT '' ");

	$db->query("ALTER TABLE `$db_config[def]member` MODIFY COLUMN `login_ip`  varchar(40)  NOT NULL DEFAULT '' ");

	$db->query("ALTER TABLE `$db_config[def]member_log` MODIFY COLUMN `ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]member_logout` ADD COLUMN `username`  varchar(30)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]member_logout` ADD COLUMN `tel`  varchar(15)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]member_reg` MODIFY COLUMN `ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]moblie_msg` MODIFY COLUMN `ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]moblie_msg` ADD COLUMN `port`  int(1) NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]moblie_msg` ADD COLUMN `location`  varchar(255)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]news_base` ADD COLUMN `starttime`  int(11) NOT NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]news_base` ADD COLUMN `endtime`  int(11) NOT NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]once_job` MODIFY COLUMN `login_ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]recycle` ADD COLUMN `ident`  varchar(255)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]resume_tiny` MODIFY COLUMN `login_ip`  varchar(40)  NULL DEFAULT NULL ");

	$db->query("ALTER TABLE `$db_config[def]special_com` ADD COLUMN `sort`  int(11) NOT NULL DEFAULT 0 ");


	$db->query("ALTER TABLE `$db_config[def]zhaopinhui` ADD COLUMN `sort`  int(11) NULL DEFAULT 0 ");

	$db->query("ALTER TABLE `$db_config[def]zhaopinhui_com` ADD COLUMN `sort`  int(11) NOT NULL DEFAULT 0 ");


	$db->query("TRUNCATE TABLE  `$db_config[def]admin_navigation`");

	$db->query("INSERT INTO `$db_config[def]admin_navigation` (`id`, `name`, `keyid`, `url`, `menu`, `classname`, `sort`, `display`, `dids`, `level`) VALUES
(1, '会员', 0, '', 1, '0', 1, 0, 1, 1),
(2, '内容', 0, '', 1, '0', 2, 0, 1, 1),
(3, '运营', 0, '', 1, '0', 3, 0, 1, 1),
(4, '工具', 0, '', 1, '0', 4, 0, 0, 1),
(5, '系统', 0, '', 1, '0', 5, 0, 1, 1),
(6, '企业', 1, '', 1, 'nav_company', 1, 0, 1, 2),
(7, '个人', 1, '', 1, 'nav_user', 2, 0, 1, 2),
(10, '新闻', 2, '', 0, 'nav_news', 1, 0, 1, 2),
(11, '测评', 2, '', 0, 'nav_cp', 2, 0, 0, 2),
(12, '招聘会', 2, '', 0, 'nav_zph', 3, 0, 1, 2),
(13, '工具箱', 2, '', 0, 'nav_gjx', 4, 0, 0, 2),
(14, '公告', 2, '', 0, 'nav_gg', 5, 0, 1, 2),
(17, '个人用户列表', 7, 'index.php?m=user_member', 1, '0', 1, 0, 1, 3),
(15, '问答', 2, '', 0, 'nav_ask', 6, 0, 1, 2),
(16, '企业管理', 6, 'index.php?m=admin_company', 2, '0', 1, 0, 1, 3),
(359, '问答设置', 15, 'index.php?m=admin_question&c=config', 1, '0', 3, 0, 0, 3),
(31, '咨询留言', 9, 'index.php?m=trainmessage', 1, '0', 7, 0, 1, 3),
(33, '简历管理', 7, 'index.php?m=admin_resume', 2, '0', 2, 0, 1, 3),
(34, '个人认证审核', 7, 'index.php?m=usercert', 1, '0', 3, 0, 1, 3),
(36, '普工简历', 156, 'index.php?m=admin_tiny', 1, '0', 2, 0, 1, 3),
(37, '求职咨询', 7, 'index.php?m=admin_msg', 1, '0', 6, 0, 1, 3),
(38, '简历记录管理', 7, 'index.php?m=admin_userlog', 1, '0', 7, 0, 1, 3),
(39, '个人设置', 7, 'index.php?m=admin_userset', 1, '0', 8, 0, 0, 3),
(40, '职位管理', 6, 'index.php?m=admin_company_job', 2, '0', 2, 0, 1, 3),
(41, '名企管理', 6, 'index.php?m=admin_hotjob', 2, '0', 3, 0, 1, 3),
(42, '兼职管理', 6, 'index.php?m=admin_partjob', 2, '0', 4, 0, 1, 3),
(44, '产品新闻管理', 6, 'index.php?m=productnews', 1, '0', 6, 0, 1, 3),
(47, '企业认证审核', 6, 'index.php?m=comcert', 1, '0', 9, 0, 1, 3),
(48, '店铺招聘', 156, 'index.php?m=admin_once', 1, '0', 1, 0, 1, 3),
(49, '专题招聘', 343, 'index.php?m=special', 1, '0', 1, 0, 0, 3),
(50, '职位记录管理', 6, 'index.php?m=admin_comlog', 1, '0', 12, 0, 1, 3),
(52, '增值套餐服务', 6, 'index.php?m=admin_comrating', 1, '0', 14, 0, 0, 3),
(53, '企业设置', 6, 'index.php?m=admin_comset', 2, '0', 15, 0, 0, 3),
(54, '新闻管理', 10, 'index.php?m=admin_news', 2, '0', 1, 0, 1, 3),
(55, '添加新闻', 10, 'index.php?m=admin_news&c=news', 1, '0', 2, 0, 1, 3),
(56, '新闻类别', 10, 'index.php?m=admin_news&c=group', 1, '0', 3, 0, 0, 3),
(57, '工具箱', 13, 'index.php?m=hr', 1, '0', 1, 0, 0, 3),
(58, '工具箱类别', 13, 'index.php?m=hrclass', 1, '0', 2, 0, 0, 3),
(59, '公告管理', 14, 'index.php?m=admin_announcement', 2, '0', 1, 0, 1, 3),
(61, '招聘会列表', 12, 'index.php?m=zhaopinhui', 1, '0', 1, 0, 1, 3),
(62, '添加招聘会', 12, 'index.php?m=zhaopinhui&c=add', 1, '0', 2, 0, 1, 3),
(63, '参会企业', 12, 'index.php?m=zhaopinhui&c=com', 1, '0', 3, 0, 1, 3),
(64, '招聘会场地', 12, 'index.php?m=zph_space', 1, '0', 4, 0, 0, 3),
(65, '意见反馈', 198, 'index.php?m=admin_message', 1, '0', 1, 0, 0, 3),
(66, '问答管理', 15, 'index.php?m=admin_question', 1, '0', 1, 0, 1, 3),
(382, 'OSS设置', 123, 'index.php?m=admin_oss_config', 1, '0', 9, 0, 0, 3),
(68, '系统消息', 198, 'index.php?m=sysnews', 1, '0', 4, 0, 0, 3),
(69, '测评试卷列表', 11, 'index.php?m=admin_evaluate', 1, '0', 1, 0, 0, 3),
(70, '添加测评试卷', 11, 'index.php?m=admin_evaluate&c=examup', 1, '0', 2, 0, 0, 3),
(71, '测评类别管理', 11, 'index.php?m=admin_evaluate&c=group', 1, '0', 3, 0, 0, 3),
(72, '测评留言管理', 11, 'index.php?m=admin_evaluate&c=message', 1, '0', 5, 0, 0, 3),
(73, '类别', 5, '', 0, 'nav_lb', 4, 0, 0, 2),
(74, '商城', 3, '', 0, 'nav_shop', 0, 0, 0, 2),
(75, '广告', 3, '', 0, 'nav_guanggao', 0, 0, 1, 2),
(76, '兑换商品管理', 74, 'index.php?m=reward', 1, '0', 1, 0, 0, 3),
(77, '兑换商品记录', 74, 'index.php?m=reward_list', 1, '0', 2, 0, 0, 3),
(82, '商品分类', 74, 'index.php?m=redeem_class', 1, '0', 7, 0, 0, 3),
(83, '广告管理', 75, 'index.php?m=advertise', 2, '0', 1, 0, 1, 3),
(84, '添加广告', 75, 'index.php?m=advertise&c=ad_add', 1, '0', 2, 0, 1, 3),
(86, '广告类别', 75, 'index.php?m=advertise&c=class', 1, '0', 4, 0, 0, 3),
(87, '个人会员分类', 73, 'index.php?m=userclass', 1, '0', 1, 0, 0, 3),
(88, '城市管理', 73, 'index.php?m=admin_city', 1, '0', 2, 0, 0, 3),
(89, '行业类别', 73, 'index.php?m=admin_industry', 1, '0', 3, 0, 0, 3),
(91, '企业会员分类', 73, 'index.php?m=comclass', 1, '0', 5, 0, 0, 3),
(92, '职位类别管理', 73, 'index.php?m=admin_job', 1, '0', 4, 0, 0, 3),
(93, '兼职分类', 73, 'index.php?m=partclass', 1, '0', 6, 0, 0, 3),
(97, '问答类别', 15, 'index.php?m=question_class', 1, '0', 2, 0, 0, 3),
(98, '举报原因管理', 73, 'index.php?m=admin_reason', 1, '0', 11, 0, 0, 3),
(101, '单页面类别', 195, 'index.php?m=desc_class', 1, '0', 14, 0, 0, 3),
(102, '管理员', 5, '', 0, 'nav_gly', 5, 0, 1, 2),
(103, '生成', 4, '', 0, 'nav_sc', 0, 0, 0, 2),
(104, '我的帐号', 102, 'index.php?m=admin_user&c=myuser', 2, '0', 1, 0, 1, 3),
(105, '管理员列表', 102, 'index.php?m=admin_user', 1, '0', 2, 0, 0, 3),
(106, '添加管理员', 102, 'index.php?m=admin_user&c=add', 1, '0', 3, 0, 0, 3),
(107, '添加管理员类型', 102, 'index.php?m=admin_user&c=addgroup', 1, '0', 5, 0, 0, 3),
(110, '管理员日志', 102, 'index.php?m=admin_log', 1, '0', 6, 0, 0, 3),
(109, '会员日志', 102, 'index.php?m=admin_memberlog', 1, '0', 7, 0, 0, 3),
(112, '管理员类型', 102, 'index.php?m=admin_user&c=group', 1, '0', 4, 0, 0, 3),
(113, '首页生成', 103, 'index.php?m=cache&c=index', 1, '0', 1, 0, 0, 3),
(114, '新闻首页', 103, 'index.php?m=cache&c=news', 1, '0', 2, 0, 0, 3),
(115, '新闻类别', 103, 'index.php?m=cache&c=newsclass', 1, '0', 3, 0, 0, 3),
(116, '新闻详细页', 103, 'index.php?m=cache&c=archive', 1, '0', 4, 0, 0, 3),
(117, '生成缓存', 103, 'index.php?m=cache&c=cache', 1, '0', 5, 0, 0, 3),
(118, '单页面生成', 103, 'index.php?m=cache&c=once', 1, '0', 6, 0, 0, 3),
(119, '一键更新', 103, 'index.php?m=cache&c=all', 1, '0', 7, 0, 0, 3),
(120, '生成XML', 103, 'index.php?m=admin_xml', 1, '0', 8, 0, 0, 3),
(121, '设置', 5, '', 0, 'nav_set', 1, 0, 0, 2),
(122, '微信', 4, '', 1, 'nav_weixin', 0, 0, 0, 2),
(123, '数据', 4, '', 1, 'nav_data', 0, 0, 0, 2),
(124, '模板', 5, '', 1, 'nav_tpl', 5, 0, 0, 2),
(125, '微信绑定', 122, 'index.php?m=wx', 1, '0', 1, 0, 0, 3),
(126, '微信菜单', 122, 'index.php?m=wx&c=wxnav', 1, '0', 2, 0, 0, 3),
(128, '登录日志', 122, 'index.php?m=wx&c=wxqrcodelog', 1, '0', 4, 0, 0, 3),
(129, '用户绑定', 122, 'index.php?m=wx&c=binduser', 1, '0', 5, 0, 0, 3),
(130, '搜索关键字', 122, 'index.php?m=wx&c=keyword', 1, '0', 6, 0, 0, 3),
(131, '自动回复', 122, 'index.php?m=wx&c=zdkeyword', 1, '0', 7, 0, 0, 3),
(132, '数据库管理', 123, 'index.php?m=database', 1, '0', 1, 0, 0, 3),
(133, '数据采集', 123, 'index.php?m=collection', 1, '0', 2, 0, 0, 3),
(135, '数据调用', 123, 'index.php?m=datacall', 1, '0', 4, 0, 0, 3),
(136, '切换模板', 124, 'index.php?m=admin_tpl', 1, '0', 1, 0, 0, 3),
(137, '企业模板', 124, 'index.php?m=admin_tpl&c=comtpl', 1, '0', 2, 0, 0, 3),
(138, '简历模板', 124, 'index.php?m=admin_tpl&c=resumetpl', 1, '0', 4, 0, 0, 3),
(139, '修改模板', 124, 'index.php?m=admin_tpl&c=template', 1, '0', 4, 0, 0, 3),
(140, '添加文档', 13, 'index.php?m=hr&c=add', 1, '0', 2, 0, 0, 3),
(142, '网站设置', 121, 'index.php?m=config', 1, '0', 1, 0, 0, 3),
(143, '模块设置', 121, 'index.php?m=model_config', 1, '0', 2, 0, 0, 1),
(144, '页面设置', 121, 'index.php?m=web_config', 1, '0', 3, 0, 0, 3),
(145, '导航设置', 121, 'index.php?m=navigation', 1, '0', 4, 0, 0, 3),
(146, '邮件服务器设置', 185, 'index.php?m=emailconfig', 1, '0', 1, 0, 0, 3),
(147, '短信设置', 186, 'index.php?m=msgconfig', 1, '0', 1, 0, 0, 3),
(148, '支付设置', 121, 'index.php?m=payconfig', 1, '0', 7, 0, 0, 3),
(149, 'SEO设置', 121, 'index.php?m=seo', 1, '0', 8, 0, 0, 3),
(151, '积分设置', 121, 'index.php?m=integral', 1, '0', 9, 0, 0, 3),
(152, '注册设置', 121, 'index.php?m=regset', 1, '0', 10, 0, 0, 3),
(153, '网站地图', 121, 'index.php?m=navmap', 1, '0', 11, 0, 0, 3),
(154, '添加公告', 14, 'index.php?m=admin_announcement&c=add', 1, '0', 2, 0, 1, 3),
(156, '微聘', 1, '', 1, 'nav_once', 6, 0, 1, 2),
(158, '登录', 4, '', 1, 'nav_login', 4, 0, 0, 2),
(159, '快捷登录', 158, 'index.php?m=qqconfig', 1, '0', 1, 0, 0, 3),
(160, '整合论坛', 158, 'index.php?m=admin_uc', 1, '0', 2, 0, 0, 3),
(161, '财务', 3, '', 0, 'nav_cw', 0, 0, 1, 2),
(162, '充值订单', 161, 'index.php?m=company_order', 1, '0', 3, 0, 1, 3),
(163, '消费记录', 161, 'index.php?m=company_pay', 2, '0', 2, 0, 1, 3),
(164, '后台充值', 161, 'index.php?m=recharge', 1, '0', 5, 0, 1, 3),
(169, '回收站', 123, 'index.php?m=recycle', 1, '0', 7, 0, 0, 3),
(170, '分站', 5, '', 1, 'nav_fz', 8, 0, 0, 2),
(171, '分站设置', 170, 'index.php?m=admin_domain', 1, '0', 1, 0, 0, 3),
(172, '分站管理', 170, 'index.php?m=admin_domain&c=alllist', 1, '0', 2, 0, 0, 3),
(173, '添加分站', 170, 'index.php?m=admin_domain&c=AddDomain', 1, '0', 3, 0, 0, 3),
(174, '计划任务', 121, 'index.php?m=cron', 1, '0', 11, 0, 0, 3),
(175, '预警设置', 121, 'index.php?m=warning', 1, '0', 12, 0, 0, 3),
(176, '举报', 3, '', 1, 'nav_jb', 6, 0, 1, 2),
(177, '营销', 3, '', 1, 'nav_yx', 7, 0, 0, 2),
(178, '被举报职位', 176, 'index.php?m=report', 1, '0', 1, 0, 1, 3),
(179, '被举报简历', 176, 'index.php?m=report&ut=2', 1, '0', 2, 0, 1, 3),
(180, '被举报问答', 176, 'index.php?m=report&type=1', 1, '0', 3, 0, 1, 3),
(181, '被投诉顾问', 176, 'index.php?m=report&type=2', 1, '0', 5, 0, 1, 3),
(182, '推广营稍', 177, 'index.php?m=email', 1, '0', 1, 0, 0, 3),
(184, '账号申诉', 401, 'index.php?m=admin_appeal', 1, '0', 6, 0, 1, 3),
(185, '邮件', 4, '', 1, 'nav_email', 6, 0, 1, 2),
(186, '短信', 4, '', 1, 'nav_msg', 7, 0, 0, 2),
(187, '短信模板设置', 186, 'index.php?m=msgconfig&c=tpl', 1, '0', 2, 0, 0, 3),
(188, '短信发送记录', 186, 'index.php?m=msgconfiglist', 1, '0', 3, 0, 0, 3),
(189, '邮件模板设置', 185, 'index.php?m=emailconfig&c=tpl', 1, '0', 2, 0, 0, 3),
(190, '邮件发送记录', 185, 'index.php?m=emailconfiglist', 1, '0', 3, 0, 0, 3),
(193, '关键字管理', 121, 'index.php?m=admin_keyword', 1, '', 11, 0, 0, 3),
(194, '友情链接', 121, 'index.php?m=link', 1, '', 12, 0, 0, 3),
(195, '单页面', 5, '', 1, 'nav_desc', 7, 0, 0, 2),
(196, '单页面管理', 195, 'index.php?m=description', 1, '0', 0, 0, 0, 3),
(198, '信息', 5, '', 1, 'nav_xx', 9, 0, 0, 2),
(200, '添加单页面', 195, 'index.php?m=description&c=add', 1, '0', 3, 0, 0, 3),
(201, '首页模板主题', 124, 'index.php?m=admin_tpl_index', 1, '0', 6, 0, 0, 3),
(204, '新闻属性', 10, 'index.php?m=admin_news&c=type', 1, '0', 5, 0, 0, 3),
(206, '添加商品', 74, 'index.php?m=reward&c=add', 1, '0', 3, 0, 0, 3),
(343, '专题', 3, '', 1, 'nav_sp', 4, 0, 0, 2),
(344, '添加专题', 343, 'index.php?m=special&c=add', 1, '0', 2, 0, 0, 3),
(345, '图片管理', 6, 'index.php?m=admin_company_pic', 1, '', 10, 0, 1, 3),
(346, '图片管理', 7, 'index.php?m=admin_user_pic', 1, '', 7, 0, 1, 3),
(349, '简历库', 8, 'index.php?m=lt_talent', 1, '', 9, 0, 1, 3),
(361, '测评用户记录', 11, 'index.php?m=admin_evaluate&c=record', 1, '', 6, 0, 0, 3),
(362, '添加广告类别', 75, 'index.php?m=advertise&c=addclass', 1, '', 5, 0, 0, 3),
(401, '会员', 1, '', 0, 'nav_member', 0, 0, 1, 2),
(402, '会员列表', 401, 'index.php?m=admin_member', 1, '', 1, 0, 1, 3),
(403, '登录日志', 401, 'index.php?m=admin_loginlog', 1, '', 4, 0, 1, 3),
(405, '删除会员', 402, 'index.php?m=admin_member&c=del', 1, '', 0, 0, 1, 4),
(406, '修改会员', 402, 'index.php?m=admin_member&c=editSave', 1, '', 0, 0, 1, 4),
(407, '重置密码', 402, 'index.php?m=admin_member&c=reset_pw', 1, '', 0, 0, 1, 4),
(408, '删除日志', 403, 'index.php?m=admin_loginlog&c=dellog', 1, '', 0, 0, 1, 4),
(409, '删除', 16, 'index.php?m=admin_company&c=del', 1, '', 0, 0, 1, 4),
(410, '修改', 16, 'index.php?m=admin_company&c=comeditsave', 1, '', 0, 0, 1, 4),
(411, '会员等级', 16, 'index.php?m=admin_company&c=rating', 1, '', 0, 0, 1, 4),
(412, '修改套餐', 16, 'index.php?m=admin_company&c=uprating', 1, '', 0, 0, 1, 4),
(413, '批量导出', 16, 'index.php?m=admin_company&c=xls', 1, '', 0, 0, 1, 4),
(414, '批量认证', 16, 'index.php?m=admin_company&c=batchfirm', 1, '', 0, 0, 1, 4),
(415, '删除', 40, 'index.php?m=admin_company_job&c=del', 1, '', 0, 0, 1, 4),
(416, '修改', 40, 'index.php?m=admin_company_job&c=show', 1, '', 0, 0, 1, 4),
(417, '职位置顶', 40, 'index.php?m=admin_company_job&c=xuanshang', 1, '', 0, 0, 1, 4),
(418, '职位推荐', 40, 'index.php?m=admin_company_job&c=recommend', 1, '', 0, 0, 1, 4),
(419, '职位紧急', 40, 'index.php?m=admin_company_job&c=urgent', 1, '', 0, 0, 1, 4),
(420, '批量导出', 40, 'index.php?m=admin_company_job&c=xls', 1, '', 0, 0, 1, 4),
(421, '修改', 41, 'index.php?m=admin_hotjob&c=save', 1, '', 0, 0, 1, 4),
(422, '删除', 41, 'index.php?m=admin_hotjob&c=del', 1, '', 0, 0, 1, 4),
(423, '删除', 42, 'index.php?m=admin_partjob&c=del', 1, '', 0, 0, 1, 4),
(424, '修改', 42, 'index.php?m=admin_partjob&c=show', 1, '', 0, 0, 1, 4),
(427, '删除', 44, 'index.php?m=comproduct&c=del', 1, '', 0, 0, 1, 4),
(428, '删除', 45, 'index.php??m=comnews&c=del&id=313', 1, '', 0, 0, 1, 4),
(430, '删除', 47, 'index.php?m=comcert&c=del&id=4431', 1, '', 0, 0, 1, 4),
(431, '删除', 345, 'index.php?m=admin_company_pic&c=del', 1, '', 0, 0, 1, 4),
(432, '修改', 345, 'index.php?m=admin_company_pic&c=uploadsave', 1, '', 0, 0, 1, 4),
(433, '删除职位申请', 50, 'index.php?m=admin_comlog&c=deluseridjob', 1, '', 0, 0, 1, 4),
(434, '删除面试邀请', 50, 'index.php?m=admin_comlog&c=deluseridmsg', 1, '', 0, 0, 1, 4),
(435, '删除职位浏览', 50, 'index.php?m=admin_comlog&c=dellookjob', 1, '', 0, 0, 1, 4),
(436, '删除兼职报名', 50, 'index.php?m=admin_comlog&c=delpartapply', 1, '', 0, 0, 1, 4),
(437, '删除收藏人才', 50, 'index.php?m=admin_comlog&c=deltalentpool', 1, '', 0, 0, 1, 4),
(438, '套餐设置', 52, 'index.php?m=admin_comrating&c=saveclass', 1, '', 0, 0, 1, 4),
(439, '增值包设置', 52, 'index.php?m=admin_comrating&c=saves', 1, '', 0, 0, 1, 4),
(440, '删除', 54, 'index.php?m=admin_news&c=delnews', 1, '', 0, 0, 1, 4),
(441, '删除', 56, 'index.php?m=admin_news&c=delgroup', 1, '', 0, 0, 1, 4),
(442, '转移类别', 56, 'index.php?m=admin_news&c=changeSon', 1, '', 0, 0, 1, 4),
(443, '删除', 69, 'index.php?m=admin_evaluate&c=delevaluate', 1, '', 0, 0, 1, 4),
(444, '删除', 71, 'index.php?m=admin_evaluate&c=delgroup', 1, '', 0, 0, 1, 4),
(445, '删除', 72, 'index.php?m=admin_evaluate&c=delmsg', 1, '', 0, 0, 1, 4),
(446, '删除记录', 361, 'index.php?m=admin_evaluate&c=delevaluatelog', 1, '', 0, 0, 1, 4),
(447, '删除招聘会', 61, 'index.php?m=zhaopinhui&c=del', 1, '', 0, 0, 1, 4),
(448, '删除', 63, 'index.php?m=zhaopinhui&c=delcom', 1, '', 0, 0, 1, 4),
(449, '删除场地', 64, 'index.php?m=zph_space&c=del', 1, '', 0, 0, 1, 4),
(450, '展位新增', 64, 'index.php?m=zph_space&c=save', 1, '', 0, 0, 1, 4),
(451, '展位修改', 64, 'index.php?m=zph_space&c=ajax', 1, '', 0, 0, 1, 4),
(452, '删除', 57, 'index.php?m=hr&c=del', 1, '', 0, 0, 1, 4),
(453, '编辑', 58, 'index.php?m=hrclass&c=add', 1, '', 0, 0, 1, 4),
(454, '删除', 58, 'index.php?m=hrclass&c=del', 1, '', 0, 0, 1, 4),
(455, '删除', 59, 'index.php?m=admin_announcement&c=del', 1, '', 0, 0, 1, 4),
(456, '修改', 66, 'index.php?m=admin_question&c=save', 1, '', 0, 0, 1, 4),
(457, '删除', 66, 'index.php?m=admin_question&c=del', 1, '', 0, 0, 1, 4),
(458, '推荐', 66, 'index.php?m=admin_question&c=recommend', 1, '', 0, 0, 1, 4),
(459, '修改', 97, 'index.php?m=question_class&c=save', 1, '', 0, 0, 1, 4),
(460, '删除', 97, 'index.php?m=question_class&c=del', 1, '', 0, 0, 1, 4),
(465, '修改', 81, 'index.php?m=admin_prepaid&c=upcard', 1, '', 0, 0, 1, 4),
(466, '删除', 81, 'index.php?m=admin_prepaid&c=del', 1, '', 0, 0, 1, 4),
(468, '删除', 163, 'index.php?m=company_pay&c=del', 1, '', 0, 0, 1, 4),
(469, '删除', 162, 'index.php?m=company_order&c=del', 1, '', 0, 0, 1, 4),
(470, '确认付款', 162, 'index.php?m=company_order&c=setpay', 1, '', 0, 0, 1, 4),
(471, '修改金额', 162, 'index.php?m=company_order&c=save', 1, '', 0, 0, 1, 4),
(472, '导出', 162, 'index.php?m=company_order&c=xls', 1, '', 0, 0, 1, 4),
(473, '删除', 83, 'index.php?m=advertise&c=del', 1, '', 0, 0, 1, 4),
(474, '删除', 86, 'index.php?m=advertise&c=delclass', 1, '', 0, 0, 1, 4),
(475, '删除', 76, 'index.php?m=reward&c=del', 1, '', 0, 0, 1, 4),
(476, '删除', 77, 'index.php?m=reward_list&c=del', 1, '', 0, 0, 1, 4),
(477, '修改', 82, 'index.php?m=redeem_class&c=ajax', 1, '', 0, 0, 1, 4),
(478, '删除', 82, 'index.php?m=redeem_class&c=del', 1, '', 0, 0, 1, 4),
(479, '删除', 49, 'index.php?m=special&c=del', 1, '', 0, 0, 1, 4),
(480, '删除简历', 179, 'index.php?m=report&c=delresume', 1, '', 0, 0, 1, 4),
(481, '恢复数据', 169, 'index.php?m=recycle&c=recover', 1, '', 0, 0, 1, 4),
(482, '清空/删除', 169, 'index.php?m=recycle&c=del', 1, '', 0, 0, 1, 4),
(483, '清除日志', 128, 'index.php?m=wx&c=clearwx', 1, '', 0, 0, 1, 4),
(484, '取消绑定', 129, 'index.php?m=wx&c=deluser', 1, '', 0, 0, 1, 4),
(507, '修改用户名', 25, 'index.php?m=admin_company&c=saveusername', 1, '', 0, 0, 1, 4),
(526, '修改', 48, 'index.php?m=admin_once&c=save', 1, '', 0, 0, 1, 4),
(527, '删除', 48, 'index.php?m=admin_once&c=del', 1, '', 0, 0, 1, 4),
(528, '批量延期', 48, 'index.php?m=admin_once&c=ctime', 1, '', 0, 0, 1, 4),
(529, '店铺设置', 48, 'index.php?m=admin_once&c=onceset', 1, '', 0, 0, 1, 4),
(530, '修改', 36, 'index.php?m=admin_tiny&c=save', 1, '', 0, 0, 1, 4),
(531, '删除', 36, 'index.php?m=admin_tiny&c=del', 1, '', 0, 0, 1, 4),
(532, '普工设置', 36, 'index.php?m=admin_tiny&c=tinyset', 1, '', 0, 0, 1, 4),
(549, '解绑删除', 394, 'index.php?m=user_member&c=delwflog', 1, '', 0, 0, 1, 4),
(560, '修改', 106, 'index.php?m=admin_user&c=save', 1, '', 0, 0, 1, 4),
(575, '修改', 17, 'index.php?m=user_member&c=editSave', 1, '', 0, 0, 1, 4),
(576, '删除', 17, 'index.php?m=user_member&c=del', 1, '', 0, 0, 1, 4),
(577, '日志删除', 17, 'index.php?m=user_member&c=memberlogdel', 1, '', 0, 0, 1, 4),
(578, '简历刷新', 33, 'index.php?m=admin_resume&c=refresh', 1, '', 0, 0, 1, 4),
(579, '简历推荐', 33, 'index.php?m=admin_resume&c=rec', 1, '', 0, 0, 1, 4),
(580, '简历置顶', 33, 'index.php?m=admin_resume&c=top', 1, '', 0, 0, 1, 4),
(581, '简历备注', 33, 'index.php?m=admin_resume&c=label', 1, '', 0, 0, 1, 4),
(582, '简历删除', 33, 'index.php?m=admin_resume&c=delResume', 1, '', 0, 0, 1, 4),
(583, '简历导出', 33, 'index.php?m=admin_resume&c=xls', 1, '', 0, 0, 1, 4),
(584, '删除', 34, 'index.php?m=admin_trust&c=del', 1, '', 0, 0, 1, 4),
(585, '删除', 37, 'index.php?m=admin_msg&c=del', 1, '', 0, 0, 1, 4),
(586, '删除下载记录', 38, 'index.php?m=admin_userlog&c=deldown', 1, '', 0, 0, 1, 4),
(587, '删除推送记录', 38, 'index.php?m=admin_userlog&c=deltrust', 1, '', 0, 0, 1, 4),
(588, '删除浏览记录', 38, 'index.php?m=admin_userlog&c=dellook', 1, '', 0, 0, 1, 4),
(589, '头像修改', 346, 'index.php?m=admin_user_pic&c=savePhoto', 1, '', 0, 0, 1, 4),
(590, '头像删除', 346, 'index.php?m=admin_user_pic&c=delPhoto', 1, '', 0, 0, 1, 4),
(591, '修改', 346, 'index.php?m=admin_user_pic&c=saveShow', 1, '', 0, 0, 1, 4),
(592, '删除', 346, 'index.php?m=admin_user_pic&c=delShow', 1, '', 0, 0, 1, 4),
(601, '申请转换身份', 401, 'index.php?m=admin_userchange', 1, '', 2, 0, 1, 3),
(602, '自我介绍', 73, 'index.php?m=admin_introduce', 1, '0', 14, 0, 1, 3),
(809, '修改', 104, 'index.php?m=admin_user&c=savePass', 1, '', 0, 0, 1, 4),
(821, '面试模板', 6, 'index.php?m=admin_yqmb', 1, '', 7, 0, 0, 1),
(822, '发布工具', 122, 'index.php?m=wx&c=pubtool', 1, '', 9, 0, 0, 1),
(830, '分配顾问', 16, 'index.php?m=admin_company&c=checkguwen', 1, '', 0, 0, 0, 4)");
	

	$group = $db->DB_select_once("admin_user_group","1 order by id asc");

	$db->query('UPDATE `' . $db_config[def] . 'admin_user_group` SET `group_power`=\'a:469:{i:0;s:3:"999";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";i:4;s:1:"4";i:5;s:1:"5";i:6;s:3:"212";i:7;s:3:"401";i:8;s:1:"6";i:9;s:1:"7";i:10;s:1:"8";i:11;s:1:"9";i:12;s:3:"156";i:13;s:3:"393";i:14;s:3:"339";i:15;s:2:"10";i:16;s:2:"11";i:17;s:2:"12";i:18;s:2:"13";i:19;s:2:"14";i:20;s:2:"15";i:21;s:3:"815";i:22;s:3:"823";i:23;s:3:"161";i:24;s:3:"166";i:25;s:2:"74";i:26;s:2:"75";i:27;s:3:"343";i:28;s:3:"176";i:29;s:3:"177";i:30;s:3:"122";i:31;s:3:"123";i:32;s:3:"103";i:33;s:3:"350";i:34;s:3:"158";i:35;s:3:"185";i:36;s:3:"186";i:37;s:3:"121";i:38;s:2:"73";i:39;s:3:"102";i:40;s:3:"124";i:41;s:3:"195";i:42;s:3:"170";i:43;s:3:"198";i:44;s:3:"213";i:45;s:3:"226";i:46;s:3:"402";i:47;s:3:"406";i:48;s:3:"405";i:49;s:3:"407";i:50;s:3:"596";i:51;s:3:"601";i:52;s:3:"403";i:53;s:3:"408";i:54;s:3:"806";i:55;s:2:"16";i:56;s:3:"414";i:57;s:3:"413";i:58;s:3:"412";i:59;s:3:"411";i:60;s:3:"410";i:61;s:3:"409";i:62;s:3:"830";i:63;s:2:"40";i:64;s:3:"418";i:65;s:3:"417";i:66;s:3:"416";i:67;s:3:"415";i:68;s:3:"419";i:69;s:3:"420";i:70;s:2:"41";i:71;s:3:"422";i:72;s:3:"421";i:73;s:2:"42";i:74;s:3:"424";i:75;s:3:"423";i:76;s:2:"43";i:77;s:3:"426";i:78;s:3:"425";i:79;s:2:"44";i:80;s:3:"427";i:81;s:3:"821";i:82;s:2:"46";i:83;s:3:"429";i:84;s:2:"47";i:85;s:3:"430";i:86;s:3:"345";i:87;s:3:"432";i:88;s:3:"431";i:89;s:2:"50";i:90;s:3:"437";i:91;s:3:"436";i:92;s:3:"435";i:93;s:3:"434";i:94;s:3:"433";i:95;s:2:"52";i:96;s:3:"439";i:97;s:3:"438";i:98;s:2:"53";i:99;s:2:"17";i:100;s:3:"575";i:101;s:3:"576";i:102;s:3:"577";i:103;s:2:"33";i:104;s:3:"578";i:105;s:3:"579";i:106;s:3:"580";i:107;s:3:"581";i:108;s:3:"582";i:109;s:3:"583";i:110;s:2:"34";i:111;s:3:"584";i:112;s:2:"35";i:113;s:2:"37";i:114;s:3:"585";i:115;s:3:"346";i:116;s:3:"589";i:117;s:3:"590";i:118;s:3:"591";i:119;s:3:"592";i:120;s:2:"38";i:121;s:3:"587";i:122;s:3:"588";i:123;s:3:"586";i:124;s:2:"39";i:125;s:2:"18";i:126;s:3:"487";i:127;s:3:"491";i:128;s:3:"490";i:129;s:3:"489";i:130;s:3:"488";i:131;s:2:"19";i:132;s:3:"493";i:133;s:3:"492";i:134;s:2:"20";i:135;s:3:"494";i:136;s:3:"495";i:137;s:2:"21";i:138;s:3:"496";i:139;s:2:"22";i:140;s:3:"497";i:141;s:3:"347";i:142;s:3:"503";i:143;s:3:"504";i:144;s:2:"23";i:145;s:3:"501";i:146;s:3:"502";i:147;s:3:"498";i:148;s:3:"499";i:149;s:3:"500";i:150;s:2:"24";i:151;s:3:"349";i:152;s:3:"505";i:153;s:2:"25";i:154;s:3:"507";i:155;s:3:"506";i:156;s:3:"508";i:157;s:3:"509";i:158;s:2:"26";i:159;s:3:"510";i:160;s:3:"511";i:161;s:3:"512";i:162;s:2:"27";i:163;s:3:"513";i:164;s:3:"514";i:165;s:3:"515";i:166;s:2:"28";i:167;s:3:"516";i:168;s:2:"29";i:169;s:3:"518";i:170;s:3:"517";i:171;s:3:"348";i:172;s:3:"520";i:173;s:3:"521";i:174;s:3:"522";i:175;s:3:"523";i:176;s:3:"524";i:177;s:2:"30";i:178;s:3:"519";i:179;s:2:"31";i:180;s:3:"525";i:181;s:2:"32";i:182;s:2:"48";i:183;s:3:"526";i:184;s:3:"527";i:185;s:3:"528";i:186;s:3:"529";i:187;s:2:"36";i:188;s:3:"530";i:189;s:3:"531";i:190;s:3:"532";i:191;s:3:"394";i:192;s:3:"543";i:193;s:3:"544";i:194;s:3:"545";i:195;s:3:"546";i:196;s:3:"547";i:197;s:3:"548";i:198;s:3:"549";i:199;s:3:"398";i:200;s:3:"550";i:201;s:3:"551";i:202;s:3:"399";i:203;s:3:"552";i:204;s:3:"400";i:205;s:3:"553";i:206;s:3:"554";i:207;s:3:"555";i:208;s:3:"397";i:209;s:3:"341";i:210;s:3:"542";i:211;s:3:"541";i:212;s:3:"540";i:213;s:3:"537";i:214;s:3:"538";i:215;s:3:"539";i:216;s:3:"340";i:217;s:3:"535";i:218;s:3:"536";i:219;s:3:"342";i:220;s:3:"533";i:221;s:3:"534";i:222;s:2:"54";i:223;s:3:"440";i:224;s:2:"55";i:225;s:2:"56";i:226;s:3:"441";i:227;s:3:"442";i:228;s:3:"204";i:229;s:2:"69";i:230;s:3:"443";i:231;s:2:"70";i:232;s:2:"71";i:233;s:3:"444";i:234;s:2:"72";i:235;s:3:"445";i:236;s:3:"361";i:237;s:3:"446";i:238;s:2:"61";i:239;s:3:"447";i:240;s:2:"62";i:241;s:2:"63";i:242;s:3:"448";i:243;s:2:"64";i:244;s:3:"451";i:245;s:3:"450";i:246;s:3:"449";i:247;s:2:"57";i:248;s:3:"452";i:249;s:2:"58";i:250;s:3:"453";i:251;s:3:"454";i:252;s:3:"140";i:253;s:2:"59";i:254;s:3:"455";i:255;s:3:"154";i:256;s:2:"66";i:257;s:3:"456";i:258;s:3:"458";i:259;s:3:"457";i:260;s:2:"97";i:261;s:3:"460";i:262;s:3:"459";i:263;s:3:"359";i:264;s:3:"816";i:265;s:3:"817";i:266;s:3:"818";i:267;s:3:"819";i:268;s:3:"824";i:269;s:3:"829";i:270;s:3:"802";i:271;s:3:"840";i:272;s:3:"165";i:273;s:3:"467";i:274;s:3:"163";i:275;s:3:"468";i:276;s:3:"162";i:277;s:3:"472";i:278;s:3:"471";i:279;s:3:"470";i:280;s:3:"469";i:281;s:3:"167";i:282;s:3:"164";i:283;s:3:"205";i:284;s:3:"208";i:285;s:3:"209";i:286;s:3:"210";i:287;s:3:"211";i:288;s:2:"79";i:289;s:3:"461";i:290;s:2:"78";i:291;s:3:"462";i:292;s:3:"463";i:293;s:2:"80";i:294;s:2:"81";i:295;s:3:"466";i:296;s:3:"465";i:297;s:3:"464";i:298;s:2:"76";i:299;s:3:"475";i:300;s:2:"77";i:301;s:3:"476";i:302;s:3:"206";i:303;s:2:"82";i:304;s:3:"477";i:305;s:3:"478";i:306;s:2:"83";i:307;s:3:"473";i:308;s:2:"84";i:309;s:2:"85";i:310;s:2:"86";i:311;s:3:"474";i:312;s:3:"362";i:313;s:2:"49";i:314;s:3:"479";i:315;s:3:"344";i:316;s:3:"178";i:317;s:3:"179";i:318;s:3:"480";i:319;s:3:"180";i:320;s:3:"181";i:321;s:3:"353";i:322;s:3:"184";i:323;s:3:"182";i:324;s:3:"367";i:325;s:3:"378";i:326;s:3:"379";i:327;s:3:"594";i:328;s:3:"125";i:329;s:3:"126";i:330;s:3:"127";i:331;s:3:"128";i:332;s:3:"483";i:333;s:3:"199";i:334;s:3:"129";i:335;s:3:"484";i:336;s:3:"130";i:337;s:3:"131";i:338;s:3:"828";i:339;s:3:"197";i:340;s:3:"822";i:341;s:3:"132";i:342;s:3:"133";i:343;s:3:"134";i:344;s:3:"135";i:345;s:3:"168";i:346;s:3:"169";i:347;s:3:"482";i:348;s:3:"481";i:349;s:3:"382";i:350;s:3:"113";i:351;s:3:"114";i:352;s:3:"115";i:353;s:3:"116";i:354;s:3:"117";i:355;s:3:"118";i:356;s:3:"119";i:357;s:3:"120";i:358;s:3:"404";i:359;s:3:"351";i:360;s:3:"352";i:361;s:3:"485";i:362;s:3:"486";i:363;s:3:"808";i:364;s:3:"159";i:365;s:3:"160";i:366;s:3:"376";i:367;s:3:"377";i:368;s:3:"146";i:369;s:3:"189";i:370;s:3:"190";i:371;s:3:"147";i:372;s:3:"187";i:373;s:3:"188";i:374;s:3:"142";i:375;s:3:"143";i:376;s:3:"144";i:377;s:3:"145";i:378;s:3:"148";i:379;s:3:"149";i:380;s:3:"151";i:381;s:3:"152";i:382;s:3:"174";i:383;s:3:"193";i:384;s:3:"153";i:385;s:3:"194";i:386;s:3:"175";i:387;s:2:"87";i:388;s:2:"88";i:389;s:2:"89";i:390;s:2:"92";i:391;s:3:"337";i:392;s:2:"91";i:393;s:2:"93";i:394;s:2:"94";i:395;s:2:"95";i:396;s:2:"96";i:397;s:2:"98";i:398;s:2:"99";i:399;s:3:"100";i:400;s:3:"602";i:401;s:3:"104";i:402;s:3:"809";i:403;s:3:"105";i:404;s:3:"106";i:405;s:3:"560";i:406;s:3:"112";i:407;s:3:"107";i:408;s:3:"110";i:409;s:3:"109";i:410;s:3:"827";i:411;s:3:"136";i:412;s:3:"137";i:413;s:3:"138";i:414;s:3:"139";i:415;s:3:"201";i:416;s:3:"202";i:417;s:3:"196";i:418;s:3:"200";i:419;s:3:"101";i:420;s:3:"171";i:421;s:3:"172";i:422;s:3:"173";i:423;s:3:"354";i:424;s:3:"111";i:425;s:3:"203";i:426;s:2:"65";i:427;s:2:"68";i:428;s:3:"814";i:429;s:3:"216";i:430;s:3:"220";i:431;s:3:"839";i:432;s:3:"217";i:433;s:3:"569";i:434;s:3:"570";i:435;s:3:"218";i:436;s:3:"571";i:437;s:3:"572";i:438;s:3:"219";i:439;s:3:"387";i:440;s:3:"225";i:441;s:3:"573";i:442;s:3:"804";i:443;s:3:"805";i:444;s:3:"390";i:445;s:3:"574";i:446;s:3:"838";i:447;s:3:"227";i:448;s:3:"556";i:449;s:3:"557";i:450;s:3:"228";i:451;s:3:"801";i:452;s:3:"229";i:453;s:3:"231";i:454;s:3:"558";i:455;s:3:"559";i:456;s:3:"561";i:457;s:3:"562";i:458;s:3:"392";i:459;s:3:"563";i:460;s:3:"389";i:461;s:3:"568";i:462;s:3:"395";i:463;s:3:"564";i:464;s:3:"396";i:465;s:3:"567";i:466;s:3:"391";i:467;s:3:"565";i:468;s:3:"566";}\'',"`id`='".$group['id']."'");

	
	if(!$config['resume_salarytype']){
		
		
		$db->query("INSERT INTO `$db_config[def]admin_config` SET `name`='resume_salarytype',`config`='1'");
		
		$config['resume_salarytype'] = '1';

	}
	if(!$config['sy_logintime']){
		
		
		$db->query("INSERT INTO `$db_config[def]admin_config` SET `name`='sy_logintime',`config`='1'");
		
		$config['sy_logintime'] = '1';

	}
	if(!$config['sy_reg_nameminlen']){
		
		
		$db->query("INSERT INTO `$db_config[def]admin_config` SET `name`='sy_reg_nameminlen',`config`='6'");
		
		$config['sy_reg_nameminlen'] = '6';

		$db->query("INSERT INTO `$db_config[def]admin_config` SET `name`='sy_reg_namemaxlen',`config`='20'");
		
		$config['sy_reg_namemaxlen'] = '20';


	}
	if(!$config['com_yqmb_num']){
		
		
		$db->query("INSERT INTO `$db_config[def]admin_config` SET `name`='com_yqmb_num',`config`='5'");
		
		$config['com_yqmb_num'] = '5';

		


	}
	
	made_web(PLUS_PATH.'config.php',ArrayToString($config),'config');

	echo "<script>location.href='$config[sy_weburl]/update/index.php?step=3';</script>";
}


/****************************第三步：补充SEO************************************/

if($_GET[step]=='3'){
	

	

	
	echo "<script>location.href='$config[sy_weburl]/update/index.php?step=4';</script>";
	

}
/****************************第四步：调整聊天数据************************************/

if($_GET['step']=="4"){

    
    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=5';</script>";
    
}
/****************************第五步：清空缓存************************************/
if($_GET[step]=="5"){
	$delfiles="data/templates_c";
	$dh=@opendir($delfiles);
	while($file=@readdir($dh)){
		if($file!="."&&$file!=".."){
			$fullpath=$delfiles."/".$file;
			@unlink($fullpath);
		}
	}
	@closedir($dh);
	echo "<script>location.href='$config[sy_weburl]/update/index.php?step=6';</script>";
}
/****************************第六步：升级完成************************************/
if($_GET[step]=="6"){
	echo "数据库升级成功！请删除/update/ 目录 根据以下提示继续操作！";
	echo "<pre>";
	echo "1：进入后台 系统-设置-网站LOGO 设置修改相关新增默认图选项";
	echo "<pre>";
	echo "2：进入后台 工具-生成-生成缓存";
	echo "<pre>";
	echo "3：进入后台 会员-企业/个人设置-设置相关新增选项";
	echo "<pre>";
	echo "4：进入后台 清除缓存";
	echo "<pre>";
	echo "5：其他各项配置按需修改";
	echo "<pre>";
}

?>