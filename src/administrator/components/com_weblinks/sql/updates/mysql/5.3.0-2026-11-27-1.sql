UPDATE `#__content_types`
SET `content_history_options` = JSON_SET(`content_history_options`, '$.formFile', 'administrator/components/com_weblinks/forms/weblink.xml')
WHERE `type_alias` = 'com_weblinks.weblink';

UPDATE `#__content_types`
SET `content_history_options` = JSON_SET(`content_history_options`, '$.formFile', 'administrator/components/com_categories/forms/category.xml')
WHERE `type_alias` = 'com_weblinks.category';
