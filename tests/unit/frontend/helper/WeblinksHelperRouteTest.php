<?php

use Joomla\Component\Weblinks\Site\Helper\RouteHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class WeblinksHelperRouteTest extends UnitTestCase
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
		$this->assertStringContainsString('weblink.add&w_id=0', RouteHelper::getFormRoute(null));
	}
}
