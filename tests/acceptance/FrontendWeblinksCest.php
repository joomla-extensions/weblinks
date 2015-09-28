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
	 * @param   AcceptanceTester       $I
	 * @param   \Codeception\Scenario  $scenario  DI - is needed for Weblink Step
	 */
	public function createWeblinkAndConfirmFrontend(AcceptanceTester $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Weblink creation in /administrator/');

		$I->doAdministratorLogin();

		// Get weblink StepObject
		$weblinkStep = new AcceptanceTester\WeblinkSteps($scenario);
		$weblinkStep->createWeblink($this->title);

		// Menu link
		$I->createMenuItem('automated testing' . rand(1, 100), 'Weblinks', 'List All Web Link Categories');

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
