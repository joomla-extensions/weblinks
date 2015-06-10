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
		$I->waitForText('Menu Manager: Menu Items', '5', ['css' => 'h1']);
		$I->expectTo('see menu menager items');
		$I->checkForPhpNoticesOrWarnings();
		$I->amGoingTo('try to save a category with a filled title');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.add')\"]"]);
		$I->waitForText('Menu Manager: New Menu Item', '5', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], 'automated testing' . rand(1, 100));
		$I->click(['xpath' => "//a[@href=\"#menuTypeModal\"]"]);
		$I->waitForElement('.iframe', 15);

		// TODO: create a pull request in joomla-cms and add a name to the iframe there
		// attach a nanme so that we can switch to the iframe later
		$I->executeJS('jQuery(".iframe").attr("name", "blah")');
		$I->switchToIFrame("blah");
		$I->click(['link' => "Weblinks"]);
		$I->click(['xpath' => "//a[contains(@title, 'Show all the web link categories within a category')]"]);
		$I->switchToIFrame();
		$I->waitForText('Menu Manager: New Menu Item', '5', ['css' => 'h1']);
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('item.apply')\"]"]);
		$I->expectTo('see a success message after saving the category');
		$I->see('Menu item successfully saved', ['id' => 'system-message-container']);
	}
}
