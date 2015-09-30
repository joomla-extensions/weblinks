<?php
namespace Step\Acceptance;

/**
 * Class Weblink
 *
 * Step Object to interact with a weblink
 *
 * @todo: this class should grow until being able to execute generic operations over a Weblink: change status, add to category...
 *
 * @package Step\Acceptance
 * @see http://codeception.com/docs/06-ReusingTestCode#StepObjects
 */
class Weblink extends \AcceptanceTester
{
	/**
	 * Creates a weblink
	 *
	 * @param   string  $title The title for the weblink
	 * @param   string  $url   The url for the
	 *
	 */
	public function createWeblink($title, $url)
	{
		$I = $this;

		$I->comment('I navigate to Weblinks page in /administrator/');
		$I->amOnPage('administrator/index.php?option=com_weblinks');
		$I->waitForText('Web Links', '30', ['css' => 'h1']);
		$I->comment('I see weblinks page');

		$I->comment('I try to save a weblink with a filled title and URL');
		$I->click('New');
		$I->waitForText('Web Link: New', '30', ['css' => 'h1']);
		$I->fillField(['id' => 'jform_title'], $title);
		$I->fillField(['id' => 'jform_url'], $url);
		$I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('weblink.save')\"]"]);
		$I->waitForText('Web link successfully saved', '30', ['id' => 'system-message-container']);
	}
}