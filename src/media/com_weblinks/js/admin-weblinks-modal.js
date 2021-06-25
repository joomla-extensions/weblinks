/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";
	/**
	 * Javascript to insert the link
	 * View element calls jSelectWeblink when an weblink is clicked
	 * jSelectWeblink creates the link tag, sends it to the editor,
	 * and closes the select frame.
	 **/
	window.jSelectWeblink = function (id, title, catid, object, link, lang) {
		var hreflang = '', editor, tag;

		if (!Joomla.getOptions('xtd-weblinks')) {
			// Something went wrong
			window.parent.Joomla.Modal.getCurrent().close();
			return false;
		}

		editor = Joomla.getOptions('xtd-weblinks').editor;

		if (lang !== '')
		{
			hreflang = ' hreflang="' + lang + '"';
		}

		tag = '<a' + hreflang + ' href="' + link + '">' + title + '</a>';

		window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
		window.parent.Joomla.Modal.getCurrent().close();
	};

	document.addEventListener('DOMContentLoaded', function(){
		// Get the elements
		var elements = document.querySelectorAll('.select-link');

		for(var i = 0, l = elements.length; l>i; i++) {
			// Listen for click event
			elements[i].addEventListener('click', function (event) {
				event.preventDefault();
				var functionName = event.target.getAttribute('data-function');

				if (functionName === 'jSelectWeblink') {
					// Used in xtd_weblinks
					window[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), event.target.getAttribute('data-cat-id'), null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language', null));
				} else {
					// Used in com_menus
					window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), event.target.getAttribute('data-cat-id'), null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language', null));
				}
			})
		}
	});
})();
