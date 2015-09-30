<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class FrontendWeblinksCest
{
	private $title;

	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->title  = 'Weblink' . $this->faker->randomNumber();
		$this->url  = $this->faker->url();
		$this->menuItem = 'Menu Item' . $this->faker->randomNumber();
	}

	/**
	 * Create a weblink in the backend and confirm it exists and is visible in the Frontend
	 *
	 * @param   \Step\Acceptance\Weblink  $I
	 */
	public function createWeblinkAndConfirmFrontend(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Listing a category of Weblinks in frontend');

		$I->doAdministratorLogin();

		$I->createWeblink($this->title, $this->url);

		// Menu link
		$I->createMenuItem($this->menuItem, 'Weblinks', 'List All Web Link Categories', 'Main Menu');

		// Go to the frontend
		$I->comment('I want to check if the menu entry exists in the frontend');
		$I->amOnPage('index.php?option=com_weblinks');
		$I->expectTo('see weblink categories');
		$I->waitForText('Uncategorised','30', ['css' => 'h3']);
		$I->checkForPhpNoticesOrWarnings();
		$I->comment('I open the uncategorised Weblink Category');
		$I->click(['link' => 'Uncategorised']);

		$I->waitForText('Uncategorised','30', ['css' => 'h2']);
		$I->expectTo('see the weblink we created');
		$I->seeElement(['link' => $this->title]);
		$I->seeElement(['xpath' => "//a[@href='$this->url']"]);
	}
}
