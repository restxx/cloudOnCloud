CREATE TABLE `operation_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`firm_id` INT(11) NOT NULL,
	`username` VARCHAR(50) NOT NULL,
	`host_id` VARCHAR(50) NOT NULL,
	`operation` VARCHAR(50) NOT NULL,
	`result` SMALLINT(6) NOT NULL COMMENT '1:success;0:failed',
	`create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `firm_id_host_id` (`firm_id`, `host_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
