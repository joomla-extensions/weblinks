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