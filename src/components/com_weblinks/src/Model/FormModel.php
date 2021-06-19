<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Weblinks\Administrator\Model\WeblinkModel;

/**
 * Weblinks model.
 *
 * @since  1.6
 */
class FormModel extends WeblinkModel
{
	/**
	 * Model typeAlias string. Used for version history.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_weblinks.weblink';

	/**
	 * Get the return URL.
	 *
	 * @return  string  The return URL.
	 *
	 * @since   1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('w_id');
		$this->setState('weblink.id', $pk);

		// Add compatibility variable for default naming conventions.
		$this->setState('form.id', $pk);

		$categoryId = $app->input->getInt('catid');
		$this->setState('weblink.catid', $categoryId);

		$return = $app->input->get('return', null, 'base64');

		if (!Uri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->getString('layout'));
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_weblinks.form', 'weblink', array('control' => 'jform', 'load_data' => $loadData));

		// Disable the buttons and just allow editor none for not authenticated users
		if (Factory::getUser()->guest)
		{
			$form->setFieldAttribute('description', 'editor', 'none');
			$form->setFieldAttribute('description', 'buttons', 'no');
		}

		return $form;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Weblink', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}
}
