CREATE TABLE `projects` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`mname` VARCHAR(50) NULL DEFAULT NULL,
	`mid` VARCHAR(255) NULL DEFAULT NULL COMMENT 'mapping id to project of firm',
	`mfid` INT(11) NULL DEFAULT NULL COMMENT 'firm id',
	`username` VARCHAR(50) NOT NULL,
	`balance` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `name` (`name`, `username`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;

CREATE TABLE `users_projects` (
	`uid` INT(11) NOT NULL,
	`pid` INT(11) NOT NULL,
	UNIQUE INDEX `uid_pid` (`uid`, `pid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

alter table users add `puid` BIGINT(20) NOT NULL DEFAULT '0';
