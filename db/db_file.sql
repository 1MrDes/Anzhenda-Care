-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost:8889
-- 生成日期： 2022-07-08 10:16:28
-- 服务器版本： 5.7.34
-- PHP 版本： 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `db_file`
--

-- --------------------------------------------------------

--
-- 表的结构 `cache`
--

CREATE TABLE `cache` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '缓存名',
  `val` text NOT NULL COMMENT '缓存数据',
  `expire` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '有效期'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `uploaded_file`
--

CREATE TABLE `uploaded_file` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL DEFAULT '',
  `size` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',
  `path` varchar(250) NOT NULL DEFAULT '',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT '255',
  `cloud_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云平台ID',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_key` (`name`);

--
-- 表的索引 `uploaded_file`
--
ALTER TABLE `uploaded_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_deleted` (`is_deleted`),
  ADD KEY `idx1` (`cloud_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cache`
--
ALTER TABLE `cache`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `uploaded_file`
--
ALTER TABLE `uploaded_file`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
