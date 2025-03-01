CREATE TABLE `physical_categories` (
  `id` int(11) NOT NULL,
  `physical_cateid` int(11) NOT NULL,
  `physical_catename` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `physical_categories`
--

INSERT INTO `physical_categories` (`id`, `physical_cateid`, `physical_catename`) VALUES
(1, 200, '歩行時間'),
(2, 201, '歩数'),
(3, 202, '歩行距離'),
(4, 203, '確定体重'),
(5, 204, '確定熱量');


CREATE TABLE IF NOT EXISTS `physical_datas` (
  `id` bigint(20) unsigned NOT NULL,
  `tgt_physical_date` datetime NOT NULL,
  `tgt_physical_category` int(11) NOT NULL COMMENT 'カテゴリ',
  `tgt_physical_item` text CHARACTER SET utf8mb4 NOT NULL,
  `tgt_physical_data` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
