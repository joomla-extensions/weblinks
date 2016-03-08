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
    $this->title  = 'Weblink' . $this->faker->randomNumber();
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
    $title = "kshitij en-GB3".rand(1, 100);
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
  * Create multilingial weblinks with different categories, valiate its creation on the frontend
  *
  * return void
  */
  public function administratorCreateMenuCheckLanguageTranslationFrontEnd(\Step\Acceptance\weblink $I, AcceptanceTester $P)
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
    $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('category.add')\"]"]);
    $I->waitForText('Weblinks: New Category', '30', ['css' => 'h1']);
    $salt = rand(1, 100);
    $I->fillField(['id' => 'jform_title'], 'webcat-en ' . $salt);
    $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('category.apply')\"]"]);
    $I->expectTo('see a success message after saving the category');
    $I->see('Category successfully saved', ['id' => 'system-message-container']);

    $I->amGoingTo('try to save a category with a filled title french');
    $I->clickToolbarButton("save & new");
    //$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('category.save2new')\"]"]);
    $I->waitForText('Weblinks: New Category', '30', ['css' => 'h1']);
    $I->fillField(['id' => 'jform_title'], 'webcat-fr ' . $salt);
    $I->clickToolbarButton("save & close");
    $I->expectTo('see a success message after saving the category');
    $I->see('Category successfully saved', ['id' => 'system-message-container']);
    $I->createWeblink($this->title, $this->url, "No");

    $I = $P;

    $I->am('Administrator');
    $I->wantToTest('menu creation in /administrator/');
    //$I->doAdministratorLogin();
    $I->amGoingTo('Navigate to MenuItems page in /administrator/');
    $I->amOnPage('administrator/index.php?option=com_menus&view=items');
    $I->waitForText('Menus: Items', '30', ['css' => 'h1']);
    $I->expectTo('see MenuItem page');
    $I->checkForPhpNoticesOrWarnings();
    $I->createMenuItem("Test Lang en-GB".$salt, "Weblinks", "List All Web Link Categories", "Main Menu (en-GB)","All");
    $I->createMenuItem("Test Lang fr-FR".$salt, "Weblinks", "List All Web Link Categories", "Main Menu (fr-FR)","All");
    $I->amGoingTo("Home page");
    $I->amOnPage("index.php/");
    //$I->waitForText("Test Lang en-GB", '30', ['css' => 'a']);
    $I->click(['xpath' => "//li[2]/a/img"]);
    $I->see("Home");

    $I->click(['xpath' => "//li[1]/a/img"]);
    $I->see("Page d'accueil");

    $I->am('Administrator');
    $I->wantToTest('delete menu created in /administrator/');
    //$I->doAdministratorLogin();
    $I->amOnPage('administrator/index.php?option=com_menus&view=items');
    $I->deleteMenuItem("Test Lang en-GB".$salt, "Main Menu (en-GB)");
    $I->deleteMenuItem("Test Lang fr-FR".$salt, "Main Menu (fr-FR)");

  }
}
