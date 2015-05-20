<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \AcceptanceTester;

class WeblinksCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->am('Administrator');
        $I->wantTo('Install Joomla CMS 3.x');
        $I->installJoomla();
        $I->doAdministratorLogin();
        $I->setErrorReportingToDevelopment();

        $I->comment('get Weblinks repository folder from acceptance.suite.yml (see _support/AcceptanceHelper.php)');
        $path = $I->getConfiguration('repo_folder');
        $I->installExtensionFromDirectory($path . 'src/com_weblinks/');
        $I->doAdministratorLogout();
    }

    public function _after(AcceptanceTester $I)
    {
        // @todo: uninstall weblinks
    }

    // tests
    public function administratorCreateCategory(AcceptanceTester $I)
    {
        $I->am('Administrator');
        $I->wantToTest('Category creation in /administrator/');
        $I->doAdministratorLogin();
        $I->amGoingTo('Create a category in administrator');
        $I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
        $I->waitForText('Category Manager: Weblinks','5',['css' => 'h1']);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
        $I->waitForText('Category Manager: Add A New Weblinks Category','5',['css' => 'h1']);
        $I->amGoingTo('try to save a category with empty title and it should fail');
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
        $I->see('Invalid field:  Title',['id' => 'system-message-container']);
        $I->amGoingTo('try to save a category with a filled title');
        $I->fillField(['id' => 'jform_title'],'automated testing');
        $I->click(['xpath'=> "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
        $I->see('Category successfully saved',['id' => 'system-message-container']);
    }
}