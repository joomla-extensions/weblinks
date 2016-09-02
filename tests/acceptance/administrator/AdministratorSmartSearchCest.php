<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AdministratorSmartSearchCest
{
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->title  = $this->faker->bothify('SmartSearch ?##?');
		$this->url  = $this->faker->url;
		$this->articletext = 'This is a test';
	}

	/**
	 * Before the tests proper, switch the WYSIWYG editor off. This is to make it easier to create test content.
	 */
	public function administratorDisableEditor(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Disable the editor before the tests proper');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Global Configuration page in /administrator/ and disable the WYSIWYG Editor');
		$I->amOnPage('administrator/index.php?option=com_config');
		$I->waitForText('Global Configuration', 30, ['class' => 'page-title']);
		$I->selectOptionInChosen('Default Editor', 'Editor - None');
		$I->clickToolbarButton('Save & Close');
		$I->waitForText('Control Panel', 30, ['class'=> 'page-title']);
		$I->expectTo('see a success message after saving the configuration');
		$I->see('Configuration successfully saved', ['id' => 'system-message-container']);
	}

	public function administratorEnableContentPlugin(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Enabling the Smart Search content plugin. Note that this is not a requirement for Smart Search to index Weblinks');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Smart Search page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->expectTo('see a message saying that the content plugin should be enabled');
		$I->waitForElement(['link' => 'Smart Search Content Plugin']);
		$I->click(['link' => 'Smart Search Content Plugin']);
		$I->waitForText('Plugins: Content - Smart Search', 30, ['class'=> 'page-title']);
		$I->selectOptionInChosen('Status', 'Enabled');
		$I->clickToolbarButton('save & close');
		$I->waitForText('Plugin successfully saved.', 30, ['id' => 'system-message-container']);
		$I->see('Plugin successfully saved.', ['id' => 'system-message-container']);
	}

	/**
	 * Before the tests proper, the Weblinks Smart Search plugin must be enabled.
	 */
	public function administratorEnableSmartsearchWeblinksPlugin(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Enabling the Smart Search Weblinks plugin');

		$I->doAdministratorLogin();

		$I->amOnPage('administrator/index.php?option=com_plugins');
		$I->searchForItem('Smart Search - Web Links');
		$I->click(['link' => 'Smart Search - Web Links']);
		$I->waitForText('Plugins: Smart Search - Web Links', 30, ['class'=> 'page-title']);
		$I->selectOptionInChosen('Status', 'Enabled');
		$I->clickToolbarButton('save & close');
		$I->waitForText('Plugin successfully saved.', 30, ['id' => 'system-message-container']);
		$I->see('Plugin successfully saved.', ['id' => 'system-message-container']);
	}

	/**
	 * Purge the index.
	 */
	public function administratorPurgeIndex(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Purging the index');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Smart Search page in /administrator/ and purge the index');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->waitForText('Smart Search', 30, ['class'=> 'page-title']);

		$I->click('Clear Index');
		$I->acceptPopup();
		$I->waitForText('All items have been successfully deleted', 30, ['class' => 'alert-message']);
		$I->see('All items have been successfully deleted', ['class' => 'alert-message']);
	}

	public function administratorCreateWeblink(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->doAdministratorLogin();

		$I->createWeblink($this->title, $this->url);
	}

	/**
	 * Index the current content.
	 */
	public function administratorRunTheIndexer(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Smart Search Indexer');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Smart Search page in /administrator/ and index the content');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->waitForText('Smart Search: Indexed Content', 30, ['class'=> 'page-title']);
		$I->click(['xpath' => "//div[@id='toolbar']//button[contains(text()[normalize-space()], 'Index')]"]);
		$I->comment('I wait while smart search indexes the links');
		$I->wait(2);
		$I->waitForText($this->title, 30, '#j-main-container');
	}

	/**
	 * After the tests, the Smart Search content plugin must be disabled, ready for the next test.
	 */
	public function administratorDisableContentPlugin(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Disabling the Smart Search content plugin, ready for the next test run');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Plugins page in /administrator/ and disable the Smart Search Content plugin');
		$I->amOnPage('administrator/index.php?option=com_plugins&view=plugins');
		$I->searchForItem('Content - Smart Search');
		$I->waitForText('Plugins', 30, ['class'=> 'page-title']);
		$I->waitForElement(['link' => 'Content - Smart Search']);
		$I->checkOption(['id' => 'cb0']);
		$I->clickToolbarButton('Unpublish');		// Note: The button is called "Disable", but we need to call it "Unpublish" here.
		$I->waitForText('Plugin successfully disabled', 30, ['class' => 'alert-message']);
	}

	/**
	 * After the tests, the Smart Search content plugin must be disabled, ready for the next test.
	 */
	public function administratorDisableSmartsearchWeblinksPlugin(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->am('Administrator');
		$I->wantToTest('Disabling the Smart Search content plugin, ready for the next test run');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Plugins page in /administrator/ and disable the Smart Search Content plugin');
		$I->amOnPage('administrator/index.php?option=com_plugins&view=plugins');
		$I->searchForItem('Smart Search - Web Links');
		$I->waitForText('Plugins', 30, ['class'=> 'page-title']);
		$I->waitForElement(['link' => 'Smart Search - Web Links']);
		$I->checkOption(['id' => 'cb0']);
		$I->clickToolbarButton('Unpublish');		// Note: The button is called "Disable", but we need to call it "Unpublish" here.
		$I->waitForText('Plugin successfully disabled', 30, ['class' => 'alert-message']);
	}

	public function cleanUp(\Step\Acceptance\weblink $I, $scenario)
	{
		$scenario->skip('Temporarilly skipped, see: https://github.com/joomla-extensions/weblinks/issues/239');
		$I->doAdministratorLogin();

		$I->administratorDeleteWeblink($this->title);
	}
}
