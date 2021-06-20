<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

/**
 * Weblinks HTML helper class.
 *
 * @since  1.6
 */
class AdministratorService
{
	/**
	 * Get the associated language flags
	 *
	 * @param   integer  $weblinkid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  \Exception
	 */
	public function association($weblinkid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_weblinks', '#__weblinks', 'com_weblinks.item', $weblinkid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated contact items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(
					[
						$db->quoteName('c.id'),
						$db->quoteName('c.title', 'title'),
						$db->quoteName('l.sef', 'lang_sef'),
						$db->quoteName('lang_code'),
						$db->quoteName('cat.title', 'category_title'),
						$db->quoteName('l.image'),
						$db->quoteName('l.title', 'language_title'),
					]
				)
				->from($db->quoteName('#__weblinks', 'c'))
				->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
				->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('c.language') . ' = ' . $db->quoteName('l.lang_code'))
				->whereIn($db->quoteName('c.id'), array_values($associations))
				->where($db->quoteName('c.id') . ' != :id')
				->bind(':id', $weblinkid, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				$languages = LanguageHelper::getContentLanguages(array(0, 1));
				$content_languages = array_column($languages, 'lang_code');

				foreach ($items as &$item)
				{
					if (in_array($item->lang_code, $content_languages))
					{
						$text = $item->lang_code;
						$url = Route::_('index.php?option=com_weblinks&task=weblink.edit&id=' . (int) $item->id);
						$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
							. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
						$classes = 'badge bg-secondary';

						$item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
							. '<div role="tooltip" id="tip-' . (int) $weblinkid . '-' . (int) $item->id . '">' . $tooltip . '</div>';
					}
					else
					{
						// Display warning if Content Language is trashed or deleted
						Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
					}
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
