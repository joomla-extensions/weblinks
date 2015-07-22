<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

/**
 * Class MenuCest
 *
 * @since  3.4.1
 */
class MenuCest
{
	/**
	 * Create a menu Item
	 *
	 * @param   \AcceptanceTester $I
	 *
	 * @return  void
	 */
	public function createMenuItem(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Frontend menu creation in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Menu Manager page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$I->waitForText('Menus: Items', '5', ['css' => 'h1']);
		$I->expectTo('see menu menager items');
		$I->checkForPhpNoticesOrWarnings();
		$I->amGoingTo('try to save a category with a filled title');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.add')\"]"]);
		$I->waitForText('Menus: New Item', '5', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], 'automated testing' . rand(1, 100));
		$I->click(['xpath' => "//a[@href=\"#menuTypeModal\"]"]);
		$I->waitForElement('.iframe', 60);
		$I->comment('I switch to Menu Type iframe');
		$I->switchToIFrame("Menu Item Type");
		$I->waitForElementVisible(['link' => "Weblinks"],60);
		$I->click(['link' => "Weblinks"]);
		$I->wait(1);
		$I->waitForElementVisible(['xpath' => "//a[contains(@title, 'Show all the web link categories within a category')]"], 60);
		$I->click(['xpath' => "//a[contains(@title, 'Show all the web link categories within a category')]"]);
		$I->wait(1);
		$I->switchToIFrame();
		$I->waitForElement(['xpath' => "//input[@value='List All Web Link Categories']"],60);
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.apply')\"]"]);
		$I->expectTo('see a success message after saving the category');
		$I->see('Menu item successfully saved', ['id' => 'system-message-container']);
	}
}
