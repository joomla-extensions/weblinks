<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

class AdministratorCategoriesCest
{
    public function administratorCreateCategory(AcceptanceTester $I)
    {
        $I->am('Administrator');
        $I->wantToTest('Category creation in /administrator/');

        $I->doAdministratorLogin();

        $I->amGoingTo('Navigate to Categories page in /administrator/');
        $I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
        $I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
        $I->expectTo('see categories page');
        $I->checkForPhpNoticesOrWarnings();

        $I->amGoingTo('try to save a category with a filled title');
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
        $I->waitForText('Category Manager: Add A New Weblinks Category','30',['css' => 'h1']);
        $I->fillField(['id' => 'jform_title'],'automated testing' . rand(1,100));
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
        $I->expectTo('see a success message after saving the category');
        $I->see('Category successfully saved',['id' => 'system-message-container']);
    }

    public function administratorCreateCategoryWithoutTitleFails(AcceptanceTester $I)
    {
        $I->am('Administrator');
        $I->wantToTest('Category creation in /administrator/ without title');

        $I->doAdministratorLogin();

        $I->amGoingTo('Navigate to Categories page in /administrator/');
        $I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
        $I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
        $I->expectTo('see categories page');

        $I->amGoingTo('try to save a category with empty title and it should fail');
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
        $I->waitForText('Category Manager: Add A New Weblinks Category','30',['css' => 'h1']);
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
        $I->expectTo('see an error when trying to save a category without title');
        $I->see('Invalid field:  Title',['id' => 'system-message-container']);
    }

	public function administratorPublishWeblink(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category publishing in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->expectTo('see categories page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a category with a filled title');
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
		$I->waitForText('Category Manager: Add A New Weblinks Category','30',['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'],'automated testing pub' . rand(1,100));
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.save')\"]"]);

		$I->expectTo('see a success message after saving the category');
		$I->see('Category successfully saved',['id' => 'system-message-container']);

		$I->amGoingTo('Search for automated testing');
		$I->fillField(['xpath'=> "//input[@id=\"filter_search\"]"], "automated testing pub" . "\n");

		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->amGoingTo('Select the first weblink');
		$I->click(['xpath'=> "//input[@id=\"cb0\"]"]);

		$I->amGoingTo('try to publish a weblink category');
		$I->click(['xpath'=> "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list.');}else{ Joomla.submitbutton('categories.publish')}\"]"]);
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->expectTo('see a success message after publishing the category');
		$I->see('1 category successfully published.',['id' => 'system-message-container']);
	}

	public function administratorUnpublishWeblink(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Category unpublishing in /administrator/');

		$I->doAdministratorLogin();

		$I->amGoingTo('Navigate to Categories page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->expectTo('see categories page');
		$I->checkForPhpNoticesOrWarnings();

		$I->amGoingTo('try to save a category with a filled title');
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
		$I->waitForText('Category Manager: Add A New Weblinks Category','30',['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'],'automated testing unpub' . rand(1,100));
		$I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.save')\"]"]);

		$I->expectTo('see a success message after saving the category');
		$I->see('Category successfully saved',['id' => 'system-message-container']);

		$I->amGoingTo('Search for automated testing');
		$I->fillField(['xpath'=> "//input[@id=\"filter_search\"]"], "automated testing unpub" . "\n");

		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->amGoingTo('Select the first weblink');
		$I->click(['xpath'=> "//input[@id=\"cb0\"]"]);

		$I->amGoingTo('Try to publish a weblink category');
		$I->click(['xpath'=> "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list.');}else{ Joomla.submitbutton('categories.publish')}\"]"]);
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->expectTo('See a success message after publishing the category');
		$I->see('1 category successfully published.',['id' => 'system-message-container']);

		// Unpublish it again
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->amGoingTo('Select the first weblink');
		$I->click(['xpath'=> "//input[@id=\"cb0\"]"]);

		$I->amGoingTo('Try to unpublish a weblink category');
		$I->click(['xpath'=> "//button[@onclick=\"if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list.');}else{ Joomla.submitbutton('categories.unpublish')}\"]"]);
		$I->waitForText('Weblinks: Categories','30',['css' => 'h1']);
		$I->expectTo('See a success message after unpublishing the category');
		$I->see('1 category successfully unpublished',['id' => 'system-message-container']);
	}
}