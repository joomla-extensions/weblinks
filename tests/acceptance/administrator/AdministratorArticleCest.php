<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class AdministratorArticleCest
{
	public function administratorCreateArticle(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$tags = 'Internet';
		$tagsCreation = array("Internet", "Something", "Everything", "Porn");
		$titleName = 'Sameple Tile' .rand(10, 1000);

		$I->doAdministratorLogin();
		$I->amOnPage('administrator/index.php?option=com_tags');
		$I->clickToolbarButton('New');
		foreach($tagsCreation as $x)
		{
			$I->fillField(['id' => "jform_title"], $x);
			$I->clickToolbarButton('Save & New');
		}

		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->waitForText('Articles', '30', ['css' => 'h1']);

		$I->clickToolbarButton('New');

		$I->click(['xpath' => "//div[@id='jform_tags_chzn']/ul/li/input"]);
		$tagName = str_split($tagsCreation[0]);
		foreach ($tagName as $char)
		{
			$I->pressKey(['xpath' => "//div[@id='jform_tags_chzn']/ul/li/input"], $char);
		}
		$I->click(['xpath' => "//div[@id='jform_tags_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $tags . "')]"]);
		$I->fillField(['id' => "jform_title"], $titleName);
		$I->clickToolbarButton('save & close');
		$I->see('successfully saved.', ['id' => 'system-message-container']);
	}
}