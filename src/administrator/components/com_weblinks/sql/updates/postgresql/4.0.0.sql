ALTER TABLE "#__weblinks" ALTER COLUMN "created" DROP DEFAULT;

ALTER TABLE "#__weblinks" ALTER COLUMN "modified" DROP DEFAULT;

ALTER TABLE "#__weblinks" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__weblinks" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__weblinks" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__weblinks" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__weblinks" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__weblinks" ALTER COLUMN "checked_out_time" DROP DEFAULT;

ALTER TABLE "#__weblinks" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__weblinks" ALTER COLUMN "checked_out" DROP NOT NULL;

UPDATE "#__weblinks" SET "modified" = "created" WHERE "modified" = '1970-01-01 00:00:00';

UPDATE "#__weblinks" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__weblinks" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__weblinks" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__weblinks" SET "checked_out" = null WHERE "checked_out" = 0;

UPDATE "#__categories" SET "modified_time" = "created_time" WHERE "modified_time" = '1970-01-01 00:00:00' AND "extension" = 'com_weblinks';

UPDATE "#__categories" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00' AND "extension" = 'com_weblinks';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" IN ('com_weblinks.weblink', 'com_weblinks.category')
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
 WHERE "core_type_alias" = 'com_weblinks.weblink'
   AND "core_publish_up" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_publish_down" = NULL
 WHERE "core_type_alias" = 'com_weblinks.weblink'
   AND "core_publish_down" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" IN ('com_weblinks.weblink', 'com_weblinks.category')
   AND "core_checked_out_time" = '1970-01-01 00:00:00';
