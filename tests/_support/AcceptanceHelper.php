<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{
	/**
	 * Function to getConfiguration from the YML and return in the test
	 *
	 * @param null $element
	 *
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function getConfiguration($element = null)
	{
		if (is_null($element)) {
			throw new InvalidArgumentException('empty value or non existing element was requested from configuration');
		}

		return $this->config[$element];
	}

	/**
	 * Function to Verify the Tabs on a Joomla! screen
	 *
	 * @param  Array   $actualTabs    Actual Tabs on the Page
	 * @param  Array   $expectedTabs  Expected Tabs on the Page
	 * @param  String  $pageName      Name of the View
	 *
	 * @return void
	 */
	public function verifyTabs($actualTabs, $expectedTabs, $pageName)
	{
		$this->assertEquals($expectedTabs, $actualTabs, "Tab Labels should match on edit view of" . $pageName);
	}
}
