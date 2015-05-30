<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

class AdministratorWeblinksCest
{
	public function administratorCreateWebLink(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Weblink creation in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links Manager: Web Links','5',['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a weblink with a filled title and URL');
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.add')\"]"]);
		$I->waitForText('Web Links Manager: Web Link','5',['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'],'automated testing' . rand(1,100));
		$I->fillField(['id' => 'jform_url'],'http://example.com/automated_testing' . rand(1,100));
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.apply')\"]"]);
		$I->expectTo('see a success message after saving the weblink');
		$I->see('Web link successfully saved',['id' => 'system-message-container']);
	}
}