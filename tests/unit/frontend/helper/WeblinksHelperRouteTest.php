<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class WeblinksHelperRouteTest extends \Codeception\TestCase\Test
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		require_once 'src/components/com_weblinks/helpers/route.php';
	}

	protected function _after()
	{
	}

	// tests
	public function testGetFormRouteNewWeblink()
	{
		$this->tester->assertContains('weblink.add&w_id=0', WeblinksHelperRoute::getFormRoute(null));
	}
}
