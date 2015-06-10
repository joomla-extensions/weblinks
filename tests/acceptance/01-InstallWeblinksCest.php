<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

class InstallWeblinksCest
{
	// tests
	public function installWeblinks(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->installJoomla();
		$I->doAdministratorLogin();
		$I->setErrorReportingToDevelopment();

		$I->comment('get Weblinks repository folder from acceptance.suite.yml (see _support/AcceptanceHelper.php)');
		$path = $I->getConfiguration('repo_folder');
		$I->installExtensionFromDirectory($path . 'src/com_weblinks/');
		$I->doAdministratorLogout();

	}
}