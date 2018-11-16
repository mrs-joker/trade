

CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类ID',
  `category_code` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一健值',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `additional_data` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '附加数据',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
  `created_by` int(10) NOT NULL DEFAULT '0' COMMENT '添加数据的人',
  `updated_by` int(10) NOT NULL DEFAULT '0' COMMENT '最后修改数据人',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index1` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;