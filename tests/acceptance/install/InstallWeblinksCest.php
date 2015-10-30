<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


class InstallWeblinksCest
{
	public function installJoomla(\AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->installJoomla();
		$I->doAdministratorLogin();
// Debug PHP in TravisCI
$I->amOnPage('/administrator/index.php?option=com_admin&view=sysinfo');
$I->waitForElement(['link' => 'PHP Information']);
$I->click('PHP Information');
$I->click('clicking unexisting element to make the test fail');
		$I->setErrorReportingToDevelopment();
	}

	/**
	 * @depends installJoomla
	 */
	public function installWeblinks(\AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
		$I->comment('get Weblinks repository folder from acceptance.suite.yml (see _support/AcceptanceHelper.php)');
		$path = $I->getConfiguration('repo_folder');
		$I->installExtensionFromFolder($path . 'src/com_weblinks/');
		$I->doAdministratorLogout();
	}
}