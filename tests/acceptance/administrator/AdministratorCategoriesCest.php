<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class AdministratorCategoriesCest
{

	/**
	* Function to delete the menuItem
	*
	* @param  string $menuItem Title of the menuItem which is to be deleted
	* @return void
	*/
	private function deleteMenuItem(AcceptanceTester $I, $menuItem)
	{
	    $I->amGoingTo('Delete the just saved MenuItem');
	    $I->amOnPage('/administrator/index.php?option=com_menus&view=items');
	    $I->searchForItem($menuItem);
	    $I->checkAllResults();
	    $I->clickToolbarButton('Trash');
	    $I->expectTo('see a success message and the menuItem removed from the list');
	    $I->see('1 menu item successfully trashed.', ['id' => 'system-message-container']);
	    $I->searchForItem($menuItem);
	    $I->setFilter('select status', 'Trashed');
	    $I->checkAllResults();
	    $I->clickToolbarButton('empty trash');
	    $I->see("1 menu item successfully deleted.", ['id' => 'system-message-container']);
	}
	private function deleteWeblink(AcceptanceTester $I, $weblinkTitle)
  	{
	    $I->amGoingTo('Delete the just saved Weblink');
	    $I->amOnPage('/administrator/index.php?option=com_weblinks');
	    $I->searchForItem($weblinkTitle);
	    $I->checkAllResults();
	    $I->clickToolbarButton('Trash');
	    $I->expectTo('see a success message and the weblink removed from the list');
	    $I->see('1 web link successfully trashed.', ['id' => 'system-message-container']);
	    $I->selectOptionInChosen('- Select Status -', 'Trashed');
	    $I->searchForItem($weblinkTitle);
	    $I->checkAllResults();
	    $I->clickToolbarButton('empty trash');
	    $I->see("1 web link successfully deleted.", ['id' => 'system-message-container']);
	}
	public $categoryTitle;

	/**
	 * Creates random names for the objects that will be used by the tests
	 *
	 * @see https://github.com/fzaninotto/Faker#fakerproviderbase
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->categoryTitle = $this->faker->bothify('AdministratorCategoriesCest category ?##?');
	}
	/**
	 * Creates a weblink with category
	 *
	 * @param   string $title The title for the weblink
	 * @param   string $url The url for the
	 * @param   string $cat The category of the weblink
	 *
	 */
	  private function createWeblinkWithCategory(AcceptanceTester $I, $title, $url, $cat)
	  {
	    $I->comment('I navigate to Weblinks page in /administrator/');
	    $I->amOnPage('administrator/index.php?option=com_weblinks');
	    $I->waitForText('Web Links', '30', ['css' => 'h1']);
	    $I->comment('I see weblinks page');
	    $I->comment('I try to save a weblink with a filled title and URL');
	    $I->click('New');
	    $I->waitForText('Web Link: New', '30', ['css' => 'h1']);
	    $I->fillField(['id' => 'jform_title'], $title);
	    $I->fillField(['id' => 'jform_url'], $url);
	    $I->selectOptionInChosen('Category', "- " . $cat);
	    $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
	    $I->waitForText('Web link successfully saved', '30', ['id' => 'system-message-container']);
	}
	public function administratorCreateCategoryWithoutTitleFails(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category creation in /administrator/ without title fails');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->expectTo('see categories page');

		$I->amGoingTo('try to save a category with empty title and it should fail');
		$I->clickToolbarButton('new');
		$I->waitForText('Web Links: New Category', '60', ['css' => 'h1']);
		$I->clickToolbarButton('save');
		$I->expectTo('see an error when trying to save a category without title');
		$I->see('Invalid field:  Title', ['id' => 'system-message-container']);
	}

	public function administratorCreateCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('create a Category in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/ and create a Category');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->expectTo('see categories page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a category with a filled title');
		$I->clickToolbarButton('New');
		$I->waitForText('Web Links: New Category', '60', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], $this->categoryTitle);
		$I->clickToolbarButton('Save & Close');
		$I->expectTo('see a success message after saving the category');
		$I->see('Category successfully saved', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorCreateCategory
	 */
	public function administratorPublishCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');

		$I->wantToTest('Publishing a Category in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->searchForItem($this->categoryTitle);
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->checkAllResults();
		$I->amGoingTo('try to publish a Web Links Category');
		$I->clickToolbarButton('publish');
		$I->waitForElement(['id' => 'system-message-container'], '60');
		$I->expectTo('see a success message after publishing the category');
		$I->see('1 category successfully published.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorPublishCategory
	 */
	public function administratorUnpublishCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Unpublish a Category in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->searchForItem($this->categoryTitle);
		$I->checkAllResults();
		$I->amGoingTo('try to unpublish a Web Links Category');
		$I->clickToolbarButton('unpublish');
		$I->waitForElement(['id' => 'system-message-container'], '60');
		$I->expectTo('See a success message after unpublishing the category');
		$I->see('1 category successfully unpublished', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorUnpublishCategory
	 */
	public function administratorArchiveCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Archiving a Category in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->searchForItem($this->categoryTitle);
		$I->checkAllResults();
		$I->amGoingTo('try to archive a Web Links category');
		$I->clickToolbarButton('archive');
		$I->waitForElement(['id' => 'system-message-container'], '60');
		$I->expectTo('see a success message after Archiving the category');
		$I->see('1 category successfully archived.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorArchiveCategory
	 */
	public function administratorTrashCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Trashing a Category in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->setFilter('Select Status', 'Archived');
		$I->searchForItem($this->categoryTitle);
		$I->checkAllResults();
		$I->amGoingTo('try to delete a Web Links Category');
		$I->clickToolbarButton('Trash');
		$I->waitForElement(['id' => 'system-message-container'], '60');
		$I->expectTo('see a success message after Trashing the category');
		$I->see('1 category successfully trashed.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorTrashCategory
	 */
	public function administratorDeleteCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Deleting a Category in /administrator/');

		$I->doAdministratorLogin();
		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Web Links: Categories', '60', ['css' => 'h1']);
		$I->setFilter('Select Status', 'Trashed');
		$I->searchForItem($this->categoryTitle);
		$I->checkAllResults();
		$I->amGoingTo('try to delete a Web Links Category');
		$I->clickToolbarButton('Empty trash');
		$I->acceptPopup();
		$I->waitForElement(['id' => 'system-message-container'], '60');
		$I->expectTo('see a success message after Deleting the category');
		$I->see('1 category successfully deleted.', ['id' => 'system-message-container']);
	}

	public function administratorVerifyAvailableTabs(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category Edit View Tabs');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/ and verify the Tabs');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->clickToolbarButton('New');
		$I->waitForText('Web Links: New Category', '30', ['css' => 'h1']);
		$I->verifyAvailableTabs(['Category', 'Options', 'Publishing', 'Permissions']);
	}
	public function administratorMenuWeblinkCategory(\Step\Acceptance\category $I)
  	{
	    $I->am('Administrator');
	    $salt = rand(1, 100);
	    $categoryName = 'automated testing' . $salt;
	
	    $I->doAdministratorLogin();
	    $I->amGoingTo('Navigate to Categories page in /administrator/ and create a Category');
	    $I->createCategory($categoryName);
	    $title = 'weblink' . $salt;
	    $url = 'www.google.com';
	    $this->createWeblinkWithCategory($I, $title, $url, $categoryName);
	    $menuTitle = 'menuItem' . $salt;
	    $I->createMenuItem($menuTitle, $menuCategory = 'Weblinks', $menuItem = 'List Web Links in a Category', $menu = 'Main Menu', $language = 'All');
	    $I->selectOptionInChosen('Select a Category', $categoryName);
	    $I->click('Save & Close');
	
	    // Go to the frontend
	    $I->comment('I want to check if the menu entry exists in the frontend');
	    $I->amOnPage('index.php/');
	    $I->click(['link' => $menuTitle]);
	    $I->waitForText($categoryName, 60, ['css' => 'h2']);
	    $I->seeElement(['xpath' => "//a[contains(text(),'" . $title . "')]"]);
	
	    //Go to backend
	    $I->amOnPage('/administrator/');
	    $this->deleteWeblink($I, $title);
	    $I->trashCategory($categoryName);
	    $this->deleteMenuItem($I, $menuTitle);
	}
	public function administratorWeblinkSubmit(\Step\Acceptance\category $I)
  	{
	    $I->am('Administrator');
	    $I->wantToTest('Weblink creation in /administrator/');
	
	    $I->doAdministratorLogin();
	
	    // Get the weblink StepObject
	    $I->amGoingTo('Navigate to Weblinks page in /administrator/');
	    $I->amOnPage('administrator/index.php?option=com_weblinks');
	    $I->clickToolbarButton('options');
	    $I->waitForText("Web Links Manager Options",30,['css' => 'h1']);
	    $I->click(['xpath' => "//a[contains(text(),'Permissions')]"]);
	    $I->selectOption('Create','Allowed');
	    $I->clickToolbarButton('Save & Close');
	    $I->waitForText("Web Links",30,['css' => 'h1']);
	    $I->amGoingTo('Navigate to Categories page in /administrator/ and create a Category');
	    $salt = rand(1,100);
	    $categoryName = 'automated testing' . $salt;
	    $I->createCategory($categoryName);
	    $title = 'weblink' . $salt;
	    $url = 'www.google.com';
	    $menuTitle = 'menuItem' . $salt;
	    $I->createMenuItem($menuTitle, $menuCategory = 'Weblinks', $menuItem = 'Submit a Web Link', $menu = 'Main Menu', $language = 'All');
	    $I->click('Save & Close');
	
	    // Go to the frontend
	    $I->comment('I want to check if the menu entry exists in the frontend');
	    $I->amOnPage('index.php/');
	    $I->click(['link' => $menuTitle]);
	    $I->fillField(['id' => 'jform_title'], $title);
	    $I->selectOptionInChosen('Category', "- " . $categoryName);
	    $I->fillField(['id' => 'jform_url'], 'www.google.com');
	    $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
	    $I->see('Web Link successfully submitted.', ['id' => 'system-message-container']);
	
	    $I->trashCategory($categoryName);
	    $this->deleteMenuItem($I, $menuTitle);
  	}
}
