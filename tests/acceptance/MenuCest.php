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
		$title = 'automated testing ' . uniqid();
		$I->createMenuItem($title, 'Weblinks', 'List All Web Link Categories');
		$I->expectTo('see a success message after saving the menu item');
		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Create a menu to category Item
	 *
	 * @param   \AcceptanceTester $I
	 *
	 * @return  void
	 */
	public function createCategoryMenuItem(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Frontend category menu creation in /administrator/');

		$I->doAdministratorLogin();
		$title = 'automated testing ' . uniqid();
		$I->createMenuItem($title, 'Weblinks', 'List Web Links in a Category');
		$I->expectTo('see a success message after saving the menu item');
		$I->checkForPhpNoticesOrWarnings();
	}
}
