<?php
/**
 * @package    Joomla.Site
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C)
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Builds the route for the com_weblinksmanager component
 *
 * @param array &$query The query array to build the route from
 *
 * @return array  The segments of the URL to be used
 */
function weblinksmanagerBuildRoute(&$query)
{
    $segments = array();

    if (isset($query['view'])) {
        $segments[] = $query['view'];
        unset($query['view']);
    }

    if (isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }

    return $segments;
}

/**
 * Parses the segments of a URL into a query array
 *
 * @param array $segments The URL segments
 *
 * @return array  The query parameters to be used
 */
function weblinksmanagerParseRoute($segments)
{
    $vars = array();

    if (isset($segments[0])) {
        $vars['view'] = $segments[0];
    }

    if (isset($segments[1])) {
        $vars['id'] = $segments[1];
    }

    return $vars;
}
