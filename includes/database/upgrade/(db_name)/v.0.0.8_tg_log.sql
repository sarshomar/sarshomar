CREATE TABLE `sarshomar_log`.`telegrams` (
`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`chatid` bigint(20) NULL DEFAULT NULL,
`user_id` int(10) UNSIGNED NULL DEFAULT NULL,
`step` text CHARACTER SET utf8mb4,
`hook` mediumtext CHARACTER SET utf8mb4,
`hooktext` text CHARACTER SET utf8mb4,
`hookdate` datetime NULL DEFAULT NULL,
`hookmessageid` text CHARACTER SET utf8mb4,
`send` mediumtext CHARACTER SET utf8mb4,
`senddate` datetime NULL DEFAULT NULL,
`sendtext` text CHARACTER SET utf8mb4,
`sendmesageid` text CHARACTER SET utf8mb4,
`sendmethod` text CHARACTER SET utf8mb4,
`sendkeyboard` text CHARACTER SET utf8mb4,
`response` mediumtext CHARACTER SET utf8mb4,
`responsedate` datetime NULL DEFAULT NULL,
`status` enum('enable','disable','ok','failed','other') DEFAULT NULL,
`url` text CHARACTER SET utf8mb4,
`meta` mediumtext CHARACTER SET utf8mb4,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;