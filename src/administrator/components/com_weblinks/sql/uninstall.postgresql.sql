DELETE FROM "#__content_types" WHERE "type_alias" IN ('com_weblinks.weblink', 'com_weblinks.category');
DELETE FROM "#__action_logs_extensions" WHERE "extension" = 'com_weblinks';
DELETE FROM "#__action_log_config" WHERE "type_title" = 'weblinks';
