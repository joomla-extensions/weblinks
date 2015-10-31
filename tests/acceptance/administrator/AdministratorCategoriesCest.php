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
	public function administratorVerifyAvailableTabs(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category Edit View Tabs');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/ and verify the Tabs');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->clickToolbarButton('New');
		$I->waitForText('Weblinks: New Category', '30', ['css' => 'h1']);
		$I->verifyAvailableTabs(['Category', 'Publishing', 'Permissions', 'Options']);
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
		$I->deleteCategory($categoryName);
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
		$I->deleteCategory($categoryName);
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
		$I->deleteCategory($categoryName);
	}
	public function administratorArchiveCategory(\Step\Acceptance\category $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Archiving Category in /administrator/');
		$I->doAdministratorLogin();
		$salt = rand(1,100);
		$I->createCategory('automated testing arch'.$salt);
		$I->amGoingTo('Search for automated testing');
		$I->fillField(['xpath' => "//input[@id=\"filter_search\"]"], "automated testing arch".$salt. "\n");
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->amGoingTo('Select the first weblink');
		$I->click(['xpath' => "//input[@id=\"cb0\"]"]);
		$I->amGoingTo('try to archive a weblink category');
		$I->click(['xpath' => "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list.');}else{ Joomla.submitbutton('categories.archive')}\"]"]);
		$I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
		$I->expectTo('see a success message after Archiving the category');$I->see('1 category successfully archived.', ['id' => 'system-message-container']);
		$I->setFilter('select status','Archived');
		$I->trashCategory('automated testing arch'.$salt);
	}
}
