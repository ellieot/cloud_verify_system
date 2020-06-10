-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2020-05-22 07:20:48
-- 服务器版本： 8.0.19
-- PHP 版本： 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `cloud`
--

-- --------------------------------------------------------

--
-- 表的结构 `cloud_advise`
--

CREATE TABLE `cloud_advise` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `submit_time` datetime DEFAULT NULL,
  `submit_ip` varchar(20) NOT NULL DEFAULT '000.000.000.000',
  `content` varchar(200) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='建议';

-- --------------------------------------------------------

--
-- 表的结构 `cloud_notice`
--

CREATE TABLE `cloud_notice` (
  `id` int UNSIGNED NOT NULL,
  `creat_time` varchar(20) NOT NULL DEFAULT '' COMMENT '创建时间',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(200) NOT NULL DEFAULT '' COMMENT '内容'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公告表';

--
-- 转存表中的数据 `cloud_notice`
--

INSERT INTO `cloud_notice` (`id`, `creat_time`, `title`, `content`) VALUES
(7, '2020-05-22 05:51:21', 'xss测试2', '&quot;;&lt;script&gt;alert(&quot;Hack on!&quot;);&lt;/script&gt;&quot;'),
(6, '2020-05-22 05:49:27', 'xss测试', '&lt;script&gt;alert(&quot;Hack on!&quot;);&lt;/script&gt;'),
(5, '2020-05-22 05:48:31', '测试公告', '公告内容在这里\r\n测试xss攻击:\r\n&lt;script&gt;\r\n  alert(&quot;Hack on!&quot;);\r\n&lt;/script&gt;');

-- --------------------------------------------------------

--
-- 表的结构 `cloud_plugin`
--

CREATE TABLE `cloud_plugin` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `author` varchar(20) NOT NULL,
  `ver` int NOT NULL,
  `address` varchar(100) NOT NULL,
  `vip` tinyint(1) NOT NULL,
  `create_Time` date NOT NULL,
  `md5` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cloud_plugin`
--

INSERT INTO `cloud_plugin` (`id`, `name`, `description`, `author`, `ver`, `address`, `vip`, `create_Time`, `md5`) VALUES
(0, '启动图插件', '给App注入一个启动图', 'PanGolin云科技', 1, 'https://pangolin-auth.xyz/Plugin/%E5%90%AF%E5%8A%A8%E5%9B%BE%E6%8F%92%E4%BB%B6.apk', 1, '2019-05-16', '7057b7d2d4e1c0e26a72329610bda8b2');

-- --------------------------------------------------------

--
-- 表的结构 `cloud_recommendedrule`
--

CREATE TABLE `cloud_recommendedrule` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `origin_day` int NOT NULL DEFAULT '0' COMMENT '原本时长',
  `give_day` int NOT NULL DEFAULT '0' COMMENT '赠送时长'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='被推荐人规则';

-- --------------------------------------------------------

--
-- 表的结构 `cloud_recommenderrule`
--

CREATE TABLE `cloud_recommenderrule` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `origin_day` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '原本时长',
  `give_day` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '赠送时长'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推荐人规则';

-- --------------------------------------------------------

--
-- 表的结构 `cloud_regcode`
--

CREATE TABLE `cloud_regcode` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL COMMENT '用户id',
  `software_id` int NOT NULL COMMENT '软件id',
  `time_str` varchar(10) NOT NULL DEFAULT '' COMMENT '中文时间',
  `software_name` varchar(20) NOT NULL DEFAULT '' COMMENT '软件名',
  `code` char(32) NOT NULL DEFAULT '' COMMENT '注册码',
  `produce_time` datetime DEFAULT NULL COMMENT '生成时间',
  `all_minutes` int UNSIGNED DEFAULT '0' COMMENT '可用分钟',
  `isonline` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不在线，1在线',
  `overdue` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未到期，1到期',
  `computer_uid` char(50) NOT NULL DEFAULT '' COMMENT '机器码',
  `beginuse_time` datetime DEFAULT NULL COMMENT '开始使用时间',
  `expire_time` datetime DEFAULT NULL COMMENT '到期时间',
  `last_time` datetime DEFAULT NULL COMMENT '上次登录时间',
  `last_ip` varchar(20) NOT NULL DEFAULT '000.000.000.000' COMMENT '最后一次登录ip',
  `use_count` int UNSIGNED DEFAULT '0' COMMENT '使用次数',
  `frozen` mediumint NOT NULL DEFAULT '0' COMMENT '0 正常1冻结',
  `token` char(5) DEFAULT NULL COMMENT '限制多开',
  `mark` char(30) NOT NULL DEFAULT '' COMMENT '备注',
  `recommend_code` int DEFAULT NULL COMMENT '推荐码'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='注册码表';

-- --------------------------------------------------------

--
-- 表的结构 `cloud_software`
--

CREATE TABLE `cloud_software` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL COMMENT '软件名',
  `version` varchar(10) DEFAULT '' COMMENT '软件版本',
  `update_url` varchar(45) DEFAULT '' COMMENT '更新地址',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户id',
  `try_minutes` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '试用分钟',
  `try_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '试用次数',
  `updatemode` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0强制更新1不强制',
  `bindmode` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0绑机单开1绑机多开2不绑多开',
  `unbindmode` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0允许解绑1不允许',
  `frozen` mediumint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 正常1冻结',
  `updatamsg` varchar(100) NOT NULL COMMENT '更新提示信息',
  `sy` mediumint NOT NULL COMMENT '是否开启试用',
  `model` mediumint NOT NULL COMMENT '模式',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `gg` varchar(500) NOT NULL COMMENT '弹窗公告',
  `web` int NOT NULL COMMENT '10代表发卡网的开启和关闭',
  `weburl` varchar(100) NOT NULL,
  `hint` varchar(500) NOT NULL,
  `edit` varchar(50) NOT NULL,
  `authmode` int NOT NULL COMMENT '注入模式',
  `msg` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `count` int DEFAULT NULL,
  `color` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `starttime` int DEFAULT NULL,
  `groupkey` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `qq` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `groupon` int DEFAULT NULL,
  `qqon` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cloud_software`
--

INSERT INTO `cloud_software` (`id`, `name`, `version`, `update_url`, `create_time`, `user_id`, `try_minutes`, `try_count`, `updatemode`, `bindmode`, `unbindmode`, `frozen`, `updatamsg`, `sy`, `model`, `title`, `gg`, `web`, `weburl`, `hint`, `edit`, `authmode`, `msg`, `count`, `color`, `starttime`, `groupkey`, `qq`, `groupon`, `qqon`) VALUES
(1, 'PanGolin', '1', 'www.genxin.com', '2019-05-07 07:34:03', 1, 30, 20, 0, 0, 0, 0, '', 1, 0, 'PanGolin云科技', '吃晚餐GDV发的\r\n\r\n发现是是是', 1, 'www.faka.com', '欢迎使用 就你那vv的说说', 'PanGolin提示信息', 0, '', 0, '', 0, '', '', 0, 0),
(39, '宝荣3.0引流', '1', '', '2020-05-21 17:05:27', 1, 30, 2, 0, 0, 0, 0, '', 0, 2, '', '', 1, '', '', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, '234', '1', '', '2020-05-21 17:16:59', 2, 30, 2, 0, 0, 0, 0, '', 0, 2, '', '', 1, '', '', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, '1234', '1', '', '2020-05-21 17:17:50', 1, 30, 2, 0, 0, 0, 0, '', 0, 2, '', '', 1, '', '', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, '1234567', '1', '', '2020-05-21 22:30:34', 1, 30, 2, 0, 0, 0, 0, '321', 0, 2, '', '', 1, '', '233', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `cloud_trial`
--

CREATE TABLE `cloud_trial` (
  `id` int UNSIGNED NOT NULL,
  `computer_uid` char(16) DEFAULT '0' COMMENT '机器码',
  `software_id` int DEFAULT '0' COMMENT '软件id',
  `has_try_count` int DEFAULT '1' COMMENT '已用次数',
  `last_ip` varchar(20) DEFAULT '000.000.000.000' COMMENT 'ip',
  `last_time` datetime DEFAULT NULL COMMENT '最后试用时间',
  `token` char(5) DEFAULT NULL COMMENT '限制多开'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `cloud_user`
--

CREATE TABLE `cloud_user` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL DEFAULT '',
  `password` char(40) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL DEFAULT '',
  `reg_time` datetime DEFAULT NULL,
  `lastlogin_time` datetime DEFAULT NULL COMMENT '上一次登录时间',
  `lastlogin_ip` varchar(20) NOT NULL DEFAULT '' COMMENT '上一次登录ip',
  `currentlogin_time` datetime DEFAULT NULL COMMENT '本次登录时间',
  `currentlogin_ip` varchar(20) NOT NULL DEFAULT '' COMMENT '本次登录ip',
  `login_count` int UNSIGNED DEFAULT NULL,
  `forget_time` datetime DEFAULT NULL COMMENT '上次找回密码时间',
  `vip` mediumint NOT NULL COMMENT '授权用户'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cloud_user`
--

INSERT INTO `cloud_user` (`id`, `username`, `password`, `email`, `reg_time`, `lastlogin_time`, `lastlogin_ip`, `currentlogin_time`, `currentlogin_ip`, `login_count`, `forget_time`, `vip`) VALUES
(1, 'admin123', '906e75654412ae98d8413e714444d892f3aaa801', '2497732985@qq.com', '2020-05-19 21:47:37', '2020-05-21 22:10:21', '::1', '2020-05-22 05:46:15', '::1', 15, '2020-05-20 15:36:13', 1);

--
-- 转储表的索引
--

--
-- 表的索引 `cloud_advise`
--
ALTER TABLE `cloud_advise`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_notice`
--
ALTER TABLE `cloud_notice`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_plugin`
--
ALTER TABLE `cloud_plugin`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_recommendedrule`
--
ALTER TABLE `cloud_recommendedrule`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_recommenderrule`
--
ALTER TABLE `cloud_recommenderrule`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_regcode`
--
ALTER TABLE `cloud_regcode`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_software`
--
ALTER TABLE `cloud_software`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_trial`
--
ALTER TABLE `cloud_trial`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cloud_user`
--
ALTER TABLE `cloud_user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cloud_advise`
--
ALTER TABLE `cloud_advise`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `cloud_notice`
--
ALTER TABLE `cloud_notice`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `cloud_recommendedrule`
--
ALTER TABLE `cloud_recommendedrule`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `cloud_recommenderrule`
--
ALTER TABLE `cloud_recommenderrule`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `cloud_regcode`
--
ALTER TABLE `cloud_regcode`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- 使用表AUTO_INCREMENT `cloud_software`
--
ALTER TABLE `cloud_software`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- 使用表AUTO_INCREMENT `cloud_trial`
--
ALTER TABLE `cloud_trial`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `cloud_user`
--
ALTER TABLE `cloud_user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
