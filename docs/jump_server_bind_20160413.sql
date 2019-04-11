CREATE TABLE `jump_server_bind` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(50) NOT NULL,
	`firm_id` VARCHAR(50) NOT NULL,
	`host_id` VARCHAR(255) NOT NULL,
	`host_ip` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `host_ip` (`host_ip`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=24;
