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
	private $title;

	public function __construct()
	{
		// This way works just fine, but not 100% sure if that is the recommended way:
		$this->title = 'automated testing' . rand(1,100);
	}

	public function administratorCreateWeblink(AcceptanceTester $I)
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
		$I->fillField(['id' => 'jform_title'], $this->title);
		$I->fillField(['id' => 'jform_url'],'http://example.com/automated_testing' . $this->title);
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
		$I->waitForText('Web Links Manager: Web Link','5',['css' => 'h1']);
		$I->expectTo('see a success message and the weblink added after saving the weblink');
		$I->see('Web link successfully saved',['id' => 'system-message-container']);
		$I->see($this->title,['id' => 'weblinkList']);
	}

	/**
	 * @depends administratorCreateWeblink
	 *
	 * @param AcceptanceTester $I
	 */
	public function administratorCreateWeblinkTrash(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Weblink removal in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links Manager: Web Links','5',['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('Search the just saved weblink');
		$I->fillField(['id' => 'filter_search'],$this->title);
		$I->pressKey(['id' => 'filter_search'],WebDriverKeys::ENTER);
		$I->waitForText('Web Links Manager: Web Links','5',['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('Delete the just saved weblink');
		$I->click(['id' => 'cb0']);
		$I->click(['xpath'=> "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{ Joomla.submitbutton('weblinks.trash')}\"]"]);
		$I->waitForText('Web Links Manager: Web Link','5',['css' => 'h1']);
		$I->expectTo('see a success message and the weblink removed from the list');
		$I->see('Web link successfully trashed',['id' => 'system-message-container']);
		$I->cantSee($this->title,['id' => 'weblinkList']);
	}

	public function administratorCreateWeblinkWithoutTitleFails(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Weblink creation without title fails in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links Manager: Web Links','5',['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a weblink with empty title and it should fail');
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.add')\"]"]);
		$I->waitForText('Web Links Manager: Web Link','5',['css' => 'h1']);
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.apply')\"]"]);
		$I->expectTo('see an error when trying to save a weblink without title and without URL');
		$I->see('Invalid field:  Title',['id' => 'system-message-container']);
		$I->see('Invalid field:  URL',['id' => 'system-message-container']);
	}
}