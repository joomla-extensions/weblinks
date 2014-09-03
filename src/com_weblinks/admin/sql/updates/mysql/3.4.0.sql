# This is a rollup of all database schema changes applied from 3.0.0 to 3.3.x

drop procedure if exists weblinks_schema_change;

delimiter ';;'
create procedure weblinks_schema_change() begin

	/* delete columns if they exist */
	if exists(SELECT * FROM information_schema.columns WHERE table_name = '#__weblinks' AND column_name = 'sid') THEN
		ALTER TABLE `#__weblinks` DROP COLUMN `sid`;
	end if;
	if exists(SELECT * FROM information_schema.columns WHERE table_name = '#__weblinks' AND column_name = 'date') THEN
		ALTER TABLE `#__weblinks` DROP COLUMN `date`;
	end if;
	if exists(SELECT * FROM information_schema.columns WHERE table_name = '#__weblinks' AND column_name = 'archived') THEN
		ALTER TABLE `#__weblinks` DROP COLUMN `archived`;
	end if;
	if exists(SELECT * FROM information_schema.columns WHERE table_name = '#__weblinks' AND column_name = 'approved') THEN
		ALTER TABLE `#__weblinks` DROP COLUMN `approved`;
	end if;

end;;

delimiter ';'
call weblinks_schema_change();

drop procedure if exists weblinks_schema_change;

ALTER TABLE `#__weblinks` ENGINE=InnoDB;
ALTER TABLE `#__weblinks` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__weblinks` ADD COLUMN `images` text NOT NULL;
