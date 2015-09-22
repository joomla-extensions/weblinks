<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

class FrontendWeblinksCest
{
	private $title;

	public function __construct()
	{
		// This way works just fine, but not 100% sure if that is the recommended way:
		$this->title = 'automated testing ' . uniqid();
	}

	/**
	 * Create a weblink in the backend and confirm it exists and is visible in the Frontend
	 *
	 * @  depends \AdministratorWeblinksCest::administratorCreateWeblink
	 *
	 * @param   \AcceptanceTester $I
	 *
	 * @return  void
	 */
	public function createWeblinkAndConfirmFrontend(AcceptanceTester $I)
	{
		// We should think on making administratorCreateWeblink an step object or an actor
		$I->am('Administrator');
		$I->wantToTest('Weblink creation in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links','30',['css' => 'h1']);
		$I->expectTo('see weblinks page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a weblink with a filled title and URL');
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.add')\"]"]);
		$I->waitForText('Web Link: New','30',['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], $this->title);
		$I->fillField(['id' => 'jform_url'],'http://example.com/automated_testing' . $this->title);
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
		$I->waitForText('Web Links','30',['css' => 'h1']);
		$I->expectTo('see a success message and the weblink added after saving the weblink');
		$I->see('Web link successfully saved',['id' => 'system-message-container']);
		$I->see($this->title,['id' => 'weblinkList']);

		// Menu link
		$I->amGoingTo('Navigate to Menu Manager page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$I->waitForText('Menus: Items','30', ['css' => 'h1']);
		$I->expectTo('see menu menager items');
		$I->checkForPhpNoticesOrWarnings();
		$I->amGoingTo('try to save a category with a filled title');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.add')\"]"]);
		$I->waitForText('Menus: New Item','30', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], 'automated testing' . rand(1, 100));
		$I->click(['xpath' => "//a[@href=\"#menuTypeModal\"]"]);
		$I->waitForElement('.iframe','30');
		$I->comment('I switch to Menu Type iframe');
		$I->switchToIFrame("Menu Item Type");
		$I->waitForElementVisible(['link' => "Weblinks"],'30');
		$I->click(['link' => "Weblinks"]);
		$I->wait(1);
		$I->waitForElementVisible(['xpath' => "//a[contains(@title, 'Show all the web link categories within a category')]"], 60);
		$I->click(['xpath' => "//a[contains(@title, 'Show all the web link categories within a category')]"]);
		$I->wait(1);
		$I->switchToIFrame();
		$I->waitForElement(['xpath' => "//input[@value='List All Web Link Categories']"],'30');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.apply')\"]"]);
		$I->expectTo('see a success message after saving the category');
		$I->see('Menu item successfully saved', ['id' => 'system-message-container']);

		// Go to the frontend
		$I->wantToTest('If the menu entry exists in the frontend');
		$I->amOnPage('index.php?option=com_weblinks');
		$I->waitForText('Uncategorised','30', ['css' => 'h3']);
		$I->expectTo('see weblink categories');
		$I->checkForPhpNoticesOrWarnings();
		$I->amGoingTo('try to open the uncategorised Item');
		$I->click(['xpath' => "//a[contains(text(), 'Uncategorised')]"]);

		// It's a h2 now..
		$I->waitForText('Uncategorised','30', ['css' => 'h2']);
		$I->expectTo('see the weblink we created');
		$I->see($this->title, ['class' => 'list-title']);
	}
}
