UPDATE "#__content_types"
SET "table" = jsonb_set("table"::jsonb, '{common,prefix}', '"Joomla\\CMS\\Table\\"')
WHERE "type_alias" IN ('com_weblinks.weblink', 'com_weblinks.category');
