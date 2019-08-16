<?php

use Step\Acceptance\Category;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class AdministratorCategoriesCest
{

	public $categoryTitle;

	/**
	 * Creates random names for the objects that will be used by the tests
	 *
	 * @link https://github.com/fzaninotto/Faker#fakerproviderbase
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->categoryTitle = $this->faker->bothify('AdministratorCategoriesCest category ?##?');
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

	public function administratorCreateCategory(Category $I)
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
		$I->see('Category saved', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorCreateCategory
	 */
	public function administratorPublishCategory(Category $I)
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
		$I->see('0 categories published.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorPublishCategory
	 */
	public function administratorUnpublishCategory(Category $I)
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
		$I->see('1 category unpublished', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorUnpublishCategory
	 */
	public function administratorArchiveCategory(Category $I)
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
		$I->see('1 category archived.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorArchiveCategory
	 */
	public function administratorTrashCategory(Category $I)
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
		$I->see('1 category trashed.', ['id' => 'system-message-container']);
	}

	/**
	 * @depends administratorTrashCategory
	 */
	public function administratorDeleteCategory(Category $I)
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
		$I->see('1 category deleted.', ['id' => 'system-message-container']);
	}

	public function administratorVerifyAvailableTabs(Category $I)
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
}
