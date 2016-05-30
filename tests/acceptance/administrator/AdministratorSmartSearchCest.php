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
		$this->title  = 'SmartSearch' . $this->faker->randomNumber();
		$this->articletext = 'This is a test';
	}

	/*
	 * Before the tests proper, switch the WYSIWYG editor off.
	 * This is to make it easier to create test content.
	 */
	public function administratorDisableEditor(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Disable the editor before the tests proper');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Global Configuration page in /administrator/ and disable the plugin');
		$I->amOnPage('administrator/index.php?option=com_config');
		$I->waitForText('Global Configuration', '30', ['css' => 'h1']);
		$I->selectOptionInChosen('Default Editor', 'Editor - None');
		$I->clickToolbarButton('Save & Close');
		$I->waitForText('Control Panel', '30', ['css' => 'h1']);
		$I->expectTo('see a success message after saving the configuration');
		$I->see('Configuration successfully saved', ['id' => 'system-message-container']);
	}

	/*
	 * Before the tests proper, the Smart Search content plugin must be enabled.
	 */
	public function administratorEnableContentPlugin(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Enabling the Smart Search content plugin before the tests proper');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Smart Search page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->expectTo('see a message saying that the content plugin should be enabled');
		$I->waitForElement(['link' => 'enable this plugin']);
		$I->click(['link' => 'enable this plugin']);
		$I->waitForText('Plugins', '30', ['css' => 'h1']);
		$I->waitForElement(['link' => 'Content - Smart Search']);
		$I->checkOption(['id' => 'cb0']);
		$I->clickToolbarButton('Publish');		// Note: The button is called "Enable", but we need to call it "Publish" here.
		$I->waitForText('Plugin successfully enabled', '30', ['class' => 'alert-message']);
	}

	/*
	 * Purge the index.
	 */
	public function administratorPurgeIndex(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Purging the index');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Smart Search page in /administrator/ and purge the index');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->waitForText('Smart Search', '30', ['css' => 'h1']);
		$I->clickToolbarButton('Trash');		// Note: The button is called "Clear Index", but we need to call it "Trash" here.
		$I->acceptPopup();
		$I->waitForText('All items have been successfully deleted', '30', ['class' => 'alert-message']);
	}

	/*
	 * Index the current content.
	 */
	public function administratorRunTheIndexer(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Smart Search Indexer');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Smart Search page in /administrator/ and index the content');
		$I->amOnPage('administrator/index.php?option=com_finder');
		$I->waitForText('Smart Search: Indexed Content', '30', ['css' => 'h1']);
		$I->click(['css' => 'button[data-target="#modal-archive"]']);
		$I->wait(5);
		$I->switchToIframe('Smart Search Indexer');

		// Put something here to check that it worked.
	}

	/*
	 * Add a new article.
	 * Since the content plugin is enabled, this will add the article to the search index.
	 */
	public function administratorAddNewArticle(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Adding a new article before the tests proper');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to the Article Manager Edit page in /administrator/ and create a new article');
		$I->amOnPage('administrator/index.php?option=com_content&view=article&layout=edit');
		$I->waitForText('Articles: New', '30', ['css' => 'h1']);

		$I->fillField(['id' => 'jform_title'], $this->title);
		$I->fillField(['id' => 'jform_articletext'], $this->articletext);
		$I->clickToolbarButton('Save & Close');
		$I->waitForText('Articles', '30', ['css' => 'h1']);
		$I->expectTo('see a success message and the article added after saving it');
		$I->see('Article successfully saved', ['id' => 'system-message-container']);
		$I->see($this->title, ['id' => 'articleList']);
	}

	/*
	 * After the tests, the Smart Search content plugin must be disabled, ready for the next test.
	 */
	public function administratorDisableContentPlugin(\Step\Acceptance\weblink $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Disabling the Smart Search content plugin, ready for the next test run');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Plugins page in /administrator/ and disable the Smart Search Content plugin');
		$I->amOnPage('administrator/index.php?option=com_plugins&view=plugins&filter[search]=Content - Smart Search');
		$I->waitForText('Plugins', '30', ['css' => 'h1']);
		$I->waitForElement(['link' => 'Content - Smart Search']);
		$I->checkOption(['id' => 'cb0']);
		$I->clickToolbarButton('Unpublish');		// Note: The button is called "Disable", but we need to call it "Unpublish" here.
		$I->waitForText('Plugin successfully disabled', '30', ['class' => 'alert-message']);
	}
}

