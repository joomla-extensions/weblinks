<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Class AdministratorLanguageCest
{
  /**
  * Create multilingial (english and french), validate its creation on the frontend
  *
  * return void
  */
  public function __construct()
  {
    $this->faker = Faker\Factory::create();
    $this->webTitle = 'Weblink' . $this->faker->randomNumber();
    $this->catTitle =  'Category' . $this->faker->randomNumber();
    $this->url  = $this->faker->url();
    $this->menuItem = 'Menu Item' . $this->faker->randomNumber();
  }

  /**
  * Test to create and then delete the menu item
  *
  * return void
  */
  public function createDeleteMenu(AcceptanceTester $I)
  {
    $I->am('Administrator');
    $I->wantToTest('menu creation in /administrator/');
    $I->doAdministratorLogin();
    $I->amGoingTo('Navigate to MenuItems page in /administrator/');
    $I->amOnPage('administrator/index.php?option=com_menus&view=items');
    $I->waitForText('Menus: Items', '30', ['css' => 'h1']);
    $I->expectTo('see MenuItem page');
    $I->checkForPhpNoticesOrWarnings();
    $title = $this->menuItem;
    $I->createMenuItem($title, "Weblinks", "List All Web Link Categories", "Main Menu","All");
    $I->deleteMenuItem($title);
  }

  /**
  * Create multilingial (english and french) menuitem , valiate its creation on the frontend
  *
  * return void
  */
  public function administratorCreateMenuItem(AcceptanceTester $I)
  {
   $I->am('Administrator');
    $I->wantToTest('menu creation in /administrator/');
    $I->doAdministratorLogin();
    $I->amGoingTo('Navigate to MenuItems page in /administrator/');
    $I->amOnPage('administrator/index.php?option=com_menus&view=items');
    $I->waitForText('Menus: Items', '30', ['css' => 'h1']);
    $I->expectTo('see MenuItem page');
    $I->checkForPhpNoticesOrWarnings();
    $I->createMenuItem("Test Lang en-GB", "Weblinks", "List All Web Link Categories", "Main Menu (en-GB)","All");
    $I->createMenuItem("Test Lang fr-FR", "Weblinks", "List All Web Link Categories", "Main Menu (fr-FR)","All");
    $I->amGoingTo("Home page");
    $I->amOnPage("index.php/");
    $I->waitForText("Test Lang en-GB", '30', ['css' => 'a']);
    $I->click(['xpath' => "//li[2]/a/img"]);
    $I->see("Page d'accueil");
    $I->click(['xpath' => "//li[1]/a/img"]);
    $I->see("Home");
    $I->deleteMenuItem("Test Lang en-GB");
    $I->deleteMenuItem("Test Lang fr-FR");
}

  /**
  * Create multilingial weblinks with different categories, validate its creation on the frontend
  *
  * return void
  */
  public function administratorCreateMenuCheckLanguageTranslationFrontEnd(\Step\Acceptance\category $I)
  {
    $I->am('Administrator');
    $I->wantToTest('Category creation in /administrator/');
    $I->doAdministratorLogin();
    $I->amGoingTo('Navigate to Categories page in /administrator/');
    $I->amOnPage('administrator/index.php?option=com_categories&extension=com_weblinks');
    $I->waitForText('Weblinks: Categories', '30', ['css' => 'h1']);
    $I->expectTo('see categories page');
    $I->checkForPhpNoticesOrWarnings();
    $I->amGoingTo('try to save a category with a filled title english');
    $I->clickToolbarButton('new');
    $I->waitForText('Weblinks: New Category', '30', ['css' => 'h1']);
    $title1 = $this->catTitle.'-en';
    $title2 = $this->catTitle.'-fr';
    $I->createCategory($title1);
    $I->createCategory($title2);
    $I->am('Administrator');
    $I->wantToTest('menu creation in /administrator/');
    $I->amGoingTo('Navigate to MenuItems page in /administrator/');
    $I->amOnPage('administrator/index.php?option=com_menus&view=items');
    $I->waitForText('Menus: Items', '30', ['css' => 'h1']);
    $I->expectTo('see MenuItem page');
    $I->checkForPhpNoticesOrWarnings();
    $menuItemtitle1 = $this->menuItem;
    $menuItemtitle2 = $this->menuItem;
    $I->createMenuItem($menuItemtitle1, "Weblinks", "List All Web Link Categories", "Main Menu (en-GB)","English (UK)");
    $I->createMenuItem($menuItemtitle2, "Weblinks", "List All Web Link Categories", "Main Menu (fr-FR)","French (FR)");
    $I->amGoingTo("Home page");
    $I->amOnPage("index.php/");
    $I->click(['xpath' => "//li[2]/a/img"]);
    $I->see("Home");
    $I->click(['xpath' => "//li[1]/a/img"]);
    $I->see("Page d'accueil");

    $I->am('Administrator');
    $I->wantToTest('delete category created in /administrator/');
    $I->trashCategory($title1);
    $I->trashCategory($title2);
    $I->wantToTest('delete category created in /administrator/');
    $I->deleteCategory($title1);
    $I->deleteCategory($title2);
    $I->wantToTest('delete menu created in /administrator/');
    $I->deleteMenuItem($menuItemtitle1, "Main Menu (en-GB)");
    $I->deleteMenuItem($menuItemtitle2, "Main Menu (fr-FR)");
  }
}
