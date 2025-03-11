<?php

/**
 * @package         Joomla.Site
 * @subpackage      mod_weblinks
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Weblinks\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_weblinks
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['list'] = $this->getHelperFactory()->getHelper('WeblinksHelper')->getWeblinks(
            $data['params'],
            $this->getApplication()
        );

        $data['moduleclass_sfx'] = htmlspecialchars($data['params']->get('moduleclass_sfx', ''));

        return $data;
    }
}
