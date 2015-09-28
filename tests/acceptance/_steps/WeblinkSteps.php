<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace AcceptanceTester;

class WeblinkSteps extends \AcceptanceTester
{
	/**
	 * Creates a weblink
	 *
	 * @param   string   $title  The title for the weblink
	 */
	public function createWeblink($title)
	{
		$I = $this;

		$I->amGoingTo('Navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links', '30', ['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a weblink with a filled title and URL');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('weblink.add')\"]"]);
		$I->waitForText('Web Link: New', '30', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], $title);
		$I->fillField(['id' => 'jform_url'], 'http://example.com/automated_testing' . $title);
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
		$I->waitForText('Web Links', '30', ['css' => 'h1']);
		$I->expectTo('see a success message and the weblink added after saving the weblink');
		$I->see('Web link successfully saved', ['id' => 'system-message-container']);
		$I->see($title, ['id' => 'weblinkList']);
	}
}