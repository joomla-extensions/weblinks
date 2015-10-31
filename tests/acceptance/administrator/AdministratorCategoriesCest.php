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


	public function administratorCreateCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$categoryName = 'automated testing' . rand(1, 100);
		$I->wantToTest('Category creation in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/ and create a Category');
		$I->createCategory($categoryName);
		$I->amGoingTo('Delete the Category which was created');
		$I->trashCategory($categoryName);
	}

	public function administratorCreateCategoryWithoutTitleFails(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category creation in /administrator/ without title');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('see categories page');

		$I->amGoingTo('try to save a category with empty title and it should fail');
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
		$I->waitForText('Weblinks: New Category', '30', ['css' => 'h1']);
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
		$I->expectTo('see an error when trying to save a category without title');
		$I->see('Invalid field:  Title', ['id' => 'system-message-container']);
	}

	public function administratorPublishCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');

		$categoryName = 'automated testing pub' . rand(1, 100);
		$I->wantToTest('Category creation in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/ and create a new Category');
		$I->createCategory($categoryName);

		$I->searchForItem($categoryName);

		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->checkAllResults();

		$I->amGoingTo('try to publish a weblink category');
		$I->clickToolbarButton('publish');
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('see a success message after publishing the category');
		$I->see('1 category successfully published.', ['id' => 'system-message-container']);

		$I->amGoingTo('Delete the Category which was created');
		$I->trashCategory($categoryName);
	}

	public function administratorUnpublishCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');

		$categoryName = 'automated testing unpub' . rand(1, 100);
		$I->wantToTest('Category creation in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->createCategory($categoryName);

		$I->searchForItem($categoryName);

		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->checkAllResults();

		//publish the category
		$I->amGoingTo('try to publish a weblink category');
		$I->clickToolbarButton('publish');
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('see a success message after publishing the category');
		$I->see('1 category successfully published.', ['id' => 'system-message-container']);

		// Unpublish it again
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->checkAllResults();

		$I->amGoingTo('Try to unpublish a weblink category');
		$I->clickToolbarButton('unpublish');
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('See a success message after unpublishing the category');
		$I->see('1 category successfully unpublished', ['id' => 'system-message-container']);

		//delete the category
		$I->amGoingTo('Delete the Category which was created');
		$I->trashCategory($categoryName);
	}

	public function administratorArchiveCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Archiving Category in /administrator/');
		$I->doAdministratorLogin();
		$salt = rand(1, 100);
		$I->createCategory('automated testing arch' . $salt);
		$I->amGoingTo('Search for automated testing');
		$I->fillField(['xpath' => "//input[@id=\"filter_search\"]"], "automated testing arch" . $salt . "\n");
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->amGoingTo('Select the first weblink');
		$I->click(['xpath' => "//input[@id=\"cb0\"]"]);
		$I->amGoingTo('try to archive a weblink category');
		$I->click(['xpath' => "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list.');}else{ Joomla.submitbutton('categories.archive')}\"]"]);
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('see a success message after Archiving the category');
		$I->see('1 category successfully archived.', ['id' => 'system-message-container']);
		$I->setFilter('select status', 'Archived');
		//$I->searchForItem('automated testing arch'.$salt);
		$I->trashCategory('automated testing arch' . $salt);
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
}