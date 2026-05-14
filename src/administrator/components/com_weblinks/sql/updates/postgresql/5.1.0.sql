UPDATE "#__content_types" 
SET "table" = jsonb_set(
    jsonb_set("table"::jsonb, '{special,type}', '"WeblinkTable"'),
    '{special,prefix}', '"Joomla\\Component\\Weblinks\\Administrator\\Table\\"',
    '{dbtable,prefix}', '"Joomla\\CMS\\Table\\"'
)
WHERE "type_alias" = 'com_weblinks.weblink';

UPDATE "#__content_types" 
SET "table" = jsonb_set(
    jsonb_set("table"::jsonb, '{special,type}', '"CategoryTable"'),
    '{special,prefix}', '"Joomla\\Component\\Categories\\Administrator\\Table\\"',
    '{dbtable,prefix}', '"Joomla\\CMS\\Table\\"'
)
WHERE "type_alias" = 'com_weblinks.category';