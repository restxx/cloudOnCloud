CREATE TABLE `audit_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`host_ip` VARCHAR(50) NOT NULL,
	`username` VARCHAR(50) NOT NULL,
	`type` TINYINT(4) NOT NULL COMMENT '1:login,2:command',
	`log` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `host_ip_username` (`host_ip`, `username`),
	INDEX `create_at` (`create_at`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;