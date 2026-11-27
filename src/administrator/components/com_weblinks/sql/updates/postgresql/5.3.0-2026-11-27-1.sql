UPDATE "#__content_types"
SET "content_history_options" = jsonb_set("content_history_options"::jsonb, '{formFile}', '"administrator/components/com_weblinks/forms/weblink.xml"')
WHERE "type_alias" = 'com_weblinks.weblink';

UPDATE "#__content_types"
SET "content_history_options" = jsonb_set("content_history_options"::jsonb, '{formFile}', '"administrator/components/com_categories/forms/category.xml"')
WHERE "type_alias" = 'com_weblinks.category';
