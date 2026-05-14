UPDATE `#__content_types` 
SET `table` = JSON_SET(`table`, 
    '$.special.type', 'WeblinkTable',
    '$.special.prefix', 'Joomla\\Component\\Weblinks\\Administrator\\Table\\',
    '$.dbtable.prefix', 'Joomla\\CMS\\Table\\'
)
WHERE `type_alias` = 'com_weblinks.weblink';

UPDATE `#__content_types` 
SET `table` = JSON_SET(`table`, 
    '$.special.type', 'CategoryTable',
    '$.special.prefix', 'Joomla\\Component\\Categories\\Administrator\\Table\\',
    '$.dbtable.prefix', 'Joomla\\CMS\\Table\\'
)
WHERE `type_alias` = 'com_weblinks.category';
