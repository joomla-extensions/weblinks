UPDATE `#__content_types`
SET `table` = JSON_SET(`table`, '$.common.prefix', 'Joomla\\CMS\\Table\\')
WHERE `type_alias` IN ('com_weblinks.weblink', 'com_weblinks.category');
