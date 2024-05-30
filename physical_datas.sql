-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-05-30 14:08:41
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `caloriewars`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `physical_datas`
--

CREATE TABLE `physical_datas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tgt_physical_date` datetime NOT NULL,
  `tgt_physical_category` int(11) NOT NULL COMMENT 'カテゴリ',
  `tgt_physical_item` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tgt_physical_data` float NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `physical_datas`
--

INSERT INTO `physical_datas` (`id`, `tgt_physical_date`, `tgt_physical_category`, `tgt_physical_item`, `tgt_physical_data`, `created_at`, `updated_at`) VALUES
(1, '2024-05-15 00:00:00', 200, '尻池経由～兵庫駅', 129, '2024-05-23 15:12:46', '2024-05-23 15:12:46'),
(2, '2024-05-15 00:00:00', 201, 'お馴染みの尻池公園経由', 15000, '2024-05-23 15:14:56', '2024-05-23 15:14:56'),
(3, '2024-05-25 00:00:00', 200, 'ぶらぶら', 215, '2024-05-25 12:57:19', '2024-05-25 12:57:19'),
(4, '2024-05-25 00:00:00', 201, 'ぶらぶら', 23878, '2024-05-25 13:02:41', '2024-05-25 13:02:41'),
(5, '2024-05-25 00:00:00', 202, 'ウォーキング', 17, '2024-05-25 13:03:46', '2024-05-25 13:03:46'),
(6, '2024-05-25 00:00:00', 203, '念願の60kgきり！', 60, '2024-05-25 13:04:13', '2024-05-25 13:04:13'),
(7, '2024-05-25 00:00:00', 204, 'なかなか', 2401, '2024-05-25 13:05:22', '2024-05-25 13:05:22'),
(8, '2024-05-24 00:00:00', 200, 'テレワーク後', 125, '2024-05-25 13:06:01', '2024-05-25 13:06:01'),
(9, '2024-05-24 00:00:00', 201, 'テレワーク後', 13770, '2024-05-25 13:06:37', '2024-05-25 13:06:37'),
(10, '2024-05-24 00:00:00', 202, 'テレワーク後', 10, '2024-05-25 13:07:03', '2024-05-25 13:07:03'),
(11, '2024-05-25 00:00:00', 204, 'テレワーク後', 1986, '2024-05-25 13:08:02', '2024-05-25 13:08:02'),
(12, '2024-05-24 00:00:00', 204, 'テレワーク後', 1986, '2024-05-25 13:08:43', '2024-05-25 13:08:43'),
(13, '2024-05-25 00:00:00', 204, 'ぶらぶら', 2401, '2024-05-25 13:09:17', '2024-05-25 13:09:17'),
(14, '2024-05-23 00:00:00', 200, '物理出社', 161, '2024-05-25 13:10:05', '2024-05-25 13:10:05'),
(15, '2024-05-23 00:00:00', 201, '物理出社', 17286, '2024-05-25 13:10:32', '2024-05-25 13:10:32'),
(16, '2024-05-23 00:00:00', 204, '物理出社', 2061, '2024-05-25 13:11:16', '2024-05-25 13:11:16'),
(17, '2024-05-23 00:00:00', 202, '物理出社', 13, '2024-05-25 13:11:47', '2024-05-25 13:11:47'),
(18, '2024-05-23 00:00:00', 203, '物理出社', 61, '2024-05-25 13:12:13', '2024-05-25 13:12:13'),
(19, '2024-05-22 00:00:00', 200, '物理出社', 150, '2024-05-25 13:12:57', '2024-05-25 13:12:57'),
(20, '2024-05-22 00:00:00', 202, '物理出社', 12, '2024-05-25 13:14:04', '2024-05-25 13:14:04'),
(21, '2024-05-22 00:00:00', 201, '物理出社', 16261, '2024-05-25 13:14:40', '2024-05-25 13:14:40'),
(22, '2024-05-22 00:00:00', 203, '物理出社', 62, '2024-05-25 13:15:07', '2024-05-25 13:15:07'),
(23, '2024-05-22 00:00:00', 204, '物理出社', 2010, '2024-05-25 13:15:37', '2024-05-25 13:15:37'),
(24, '2024-05-21 00:00:00', 200, '記載なし', 122, '2024-05-25 13:27:49', '2024-05-25 13:27:49'),
(25, '2024-05-21 00:00:00', 201, '記載なし', 13600, '2024-05-25 13:28:15', '2024-05-25 13:28:15'),
(26, '2024-05-21 00:00:00', 202, '記載なし', 10, '2024-05-25 13:28:45', '2024-05-25 13:28:45'),
(27, '2024-05-21 00:00:00', 204, '記載なし', 1999, '2024-05-25 13:29:15', '2024-05-25 13:29:15'),
(28, '2024-05-25 00:00:00', 203, '記載なし', 59.9, '2024-05-25 13:34:49', '2024-05-25 13:34:49'),
(29, '2024-05-23 00:00:00', 203, '記載なし', 61.4, '2024-05-25 13:35:24', '2024-05-25 13:35:24'),
(30, '2024-05-22 00:00:00', 203, '記載なし', 61.55, '2024-05-25 13:35:57', '2024-05-25 13:35:57'),
(31, '2024-05-21 00:00:00', 203, '記載なし', 61.7, '2024-05-25 13:36:32', '2024-05-25 13:36:32'),
(32, '2024-05-20 00:00:00', 200, '記載なし', 101, '2024-05-25 13:37:18', '2024-05-25 13:37:18'),
(33, '2024-05-20 00:00:00', 201, '記載なし', 11282, '2024-05-25 13:37:48', '2024-05-25 13:37:48'),
(34, '2024-05-20 00:00:00', 202, '記載なし', 8.34, '2024-05-25 13:38:18', '2024-05-25 13:38:18'),
(35, '2024-05-20 00:00:00', 204, '記載なし', 555, '2024-05-25 13:38:32', '2024-05-25 13:38:32'),
(36, '2024-05-18 00:00:00', 203, '記載なし', 61.65, '2024-05-25 13:39:11', '2024-05-25 13:39:11'),
(37, '2024-05-19 00:00:00', 200, 'テレワークで終日自宅', 35, '2024-05-25 13:39:53', '2024-05-25 13:39:53'),
(38, '2024-05-19 00:00:00', 201, '記載なし', 3563, '2024-05-25 13:40:12', '2024-05-25 13:40:12'),
(39, '2024-05-19 00:00:00', 202, '記載なし', 2.69, '2024-05-25 13:40:31', '2024-05-25 13:40:31'),
(40, '2024-05-19 00:00:00', 204, '記載なし', 124, '2024-05-25 13:40:58', '2024-05-25 13:40:58'),
(41, '2024-05-18 00:00:00', 200, '記載なし', 160, '2024-05-25 13:41:25', '2024-05-25 13:41:25'),
(42, '2024-05-18 00:00:00', 201, '記載なし', 17561, '2024-05-25 13:42:22', '2024-05-25 13:42:22'),
(43, '2024-05-18 00:00:00', 204, '記載なし', 989, '2024-05-25 13:42:57', '2024-05-25 13:42:57'),
(44, '2024-05-18 00:00:00', 202, '記載なし', 13.01, '2024-05-25 13:43:26', '2024-05-25 13:43:26');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `physical_datas`
--
ALTER TABLE `physical_datas`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `physical_datas`
--
ALTER TABLE `physical_datas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;