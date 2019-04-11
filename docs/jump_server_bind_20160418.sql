CREATE TABLE `jump_server_bind` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(50) NOT NULL,
	`firm_id` VARCHAR(50) NOT NULL,
	`host_id` VARCHAR(255) NOT NULL,
	`host_name` VARCHAR(100) NOT NULL,
	`host_ip` VARCHAR(50) NOT NULL,
	`host_username` VARCHAR(50) NOT NULL,
	`host_port` INT(6) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `unionKey` (`username`, `host_ip`, `host_username`, `host_port`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
