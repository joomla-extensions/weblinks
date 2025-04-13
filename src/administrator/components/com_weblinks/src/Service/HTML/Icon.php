<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Service\HTML;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

/**
 * Weblinks Component HTML Helper
 *
 * @since  4.0.0
 */
class Icon
{
    /**
     * The application
     *
     * @var    CMSApplication
     *
     * @since  4.0.0
     */
    private $application;

    /**
     * Service constructor
     *
     * @param   CMSApplication  $application  The application
     *
     * @since   4.0.0
     */
    public function __construct(CMSApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Method to generate a link to the create item page for the given category
     *
     * @param   object    $category  The category information
     * @param   Registry  $params    The item parameters
     * @param   array     $attribs   Optional attributes for the link
     *
     * @return  string  The HTML markup for the create item link
     *
     * @since  4.0.0
     */
    public function create($category, $params, $attribs = [])
    {
        $uri  = Uri::getInstance();
        $url  = 'index.php?option=com_weblinks&task=weblink.add&return=' . base64_encode($uri) . '&w_id=0&catid=' . $category->id;
        $text = LayoutHelper::render('joomla.content.icons.create', ['params' => $params, 'legacy' => false]);
        // Add the button classes to the attribs array
        if (isset($attribs['class'])) {
            $attribs['class'] .= ' btn btn-primary';
        } else {
            $attribs['class'] = 'btn btn-primary';
        }

        $button = HTMLHelper::_('link', Route::_($url), $text, $attribs);
        $output = '<span class="hasTooltip" title="' . HTMLHelper::_('tooltipText', 'COM_WEBLINKS_FORM_CREATE_WEBLINK') . '">' . $button . '</span>';
        return $output;
    }

    /**
     * Display an edit icon for the weblink.
     *
     * This icon will not display in a popup window, nor if the weblink is trashed.
     * Edit access checks must be performed in the calling code.
     *
     * @param   object    $weblink  The weblink information
     * @param   Registry  $params   The item parameters
     * @param   array     $attribs  Optional attributes for the link
     * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return  string   The HTML for the weblink edit icon.
     *
     * @since   4.0.0
     */
    public function edit($weblink, $params, $attribs = [], $legacy = false)
    {
        $user = $this->application->getIdentity();
        $uri  = Uri::getInstance();
        // Ignore if in a popup window.
        if ($params && $params->get('popup')) {
            return '';
        }

        // Ignore if the state is negative (trashed).
        if ($weblink->state < 0) {
            return '';
        }

        // Show checked_out icon if the contact is checked out by a different user
        if (
            property_exists($weblink, 'checked_out')
            && property_exists($weblink, 'checked_out_time')
            && $weblink->checked_out
            && $weblink->checked_out !== $user->get('id')
        ) {
            $checkoutUser = Factory::getApplication()->getIdentity($weblink->checked_out);
            $date         = HTMLHelper::_('date', $weblink->checked_out_time);
            $tooltip      = Text::sprintf('COM_WEBLINKS_CHECKED_OUT_BY', $checkoutUser->name)
                . ' <br> ' . $date;
            $text                        = LayoutHelper::render('joomla.content.icons.edit_lock', ['contact' => $weblink, 'tooltip' => $tooltip, 'legacy' => $legacy]);
            $attribs['aria-describedby'] = 'editweblink-' . (int) $weblink->id;
            $output                      = HTMLHelper::_('link', '#', $text, $attribs);
            return $output;
        }

        $weblinkUrl = RouteHelper::getWeblinkRoute($weblink->slug, $weblink->catid, $weblink->language);
        $url        = $weblinkUrl . '&task=weblink.edit&w_id=' . $weblink->id . '&return=' . base64_encode($uri);
        if ((int) $weblink->state === 0) {
            $tooltip = Text::_('COM_WEBLINKS_EDIT_UNPUBLISHED_WEBLINK');
        } else {
            $tooltip = Text::_('COM_WEBLINKS_EDIT_PUBLISHED_WEBLINK');
        }

        $nowDate = strtotime(Factory::getDate());
        $icon    = $weblink->state ? 'edit' : 'eye-slash';

        if (
            ($weblink->publish_up !== null && strtotime($weblink->publish_up) > $nowDate)
            || ($weblink->publish_down !== null && strtotime($weblink->publish_down) < $nowDate)
        ) {
            $icon = 'eye-slash';
        }

        $aria_described = 'editweblink-' . (int) $weblink->id;
        $text           = '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
        $text .= Text::_('JGLOBAL_EDIT');
        $text .= '<div role="tooltip" id="' . $aria_described . '">' . $tooltip . '</div>';
        $attribs['aria-describedby'] = $aria_described;
        $output                      = HTMLHelper::_('link', Route::_($url), $text, $attribs);
        return $output;
    }
}
