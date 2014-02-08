<?php

/**
 * Admin-Class for IFRAME
 *
 */
class admin_2000_Settings extends admin_MAIN_Settings {

	/**
	 * constructor
	 *
	 * @return admin_2000_Settings
	 */
	public function __construct() {
		parent::__construct();
		new IFRAME();
	}

	/**
	 * Call a function, based on parameter adm
	 * This is the main function, which will be called for getting some content
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		// Check for Access
		if ($userCheck->checkAccess($this->_adminAccess)) {
			// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'iframe_content', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$isContent = pURIParameters::get('iscontent', false, pURIParameters::$BOOL);

					$tpl = $this->getAdminContent($tpl, 1000);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);

					// If this is a Content-Plugin Request, replace some additional variables
					if ($isContent) {
						$text = $this->getTextEntryObject($entry);
						$tpl = str_replace('[ENTRY_TEMPLATE]', $text->layout, $tpl);
						$tpl = str_replace('[ENTRY_OPTIONS]', json_encode($text->optionsArray), $tpl);
						unset($text);
					}

					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;

				case 'content':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$pageContent = pURIParameters::get('page_content', false, pURIParameters::$BOOL);
					$settingsContent = pURIParameters::get('page_settings', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$options = pURIParameters::get('options', '', pURIParameters::$STRING);

					$text = $this->getTextEntryObject($entity);

					echo '{"section":'.$section.',"page_content":'.($pageContent||$settingsContent ? 'true' : 'false').',';
					if ($pageContent) {
						//

					} else if ($settingsContent > 0) {
						echo $this->_getJSONPageSettings($settingsContent);

					} else {
						//echo $this->_getJSONImageList($section);
					}
					echo '}';
					exit();
					break;

					case 'save_page':
					$entity = pURIParameters::get('entity', 0, pURIParameters::$INT);
					$template = pURIParameters::get('template', 'default', pURIParameters::$STRING);
					$options = pURIParameters::get('options', new stdClass(), pURIParameters::$OBJECT);
					$title = pURIParameters::get('title', '', pURIParameters::$STRING);
					$iframeUrl = pURIParameters::get('ifr_url', 'about:blank', pURIParameters::$STRING);
					$lang = new pLanguage(pURIParameters::get('lang', MASTER_LANGUAGE, pURIParameters::$STRING));

					$opt  = '#show_height='.((isset($options->show_height)) ? $options->show_height : '100').'#';
					$opt .= '#height_unit='.((isset($options->height_unit)) ? $options->height_unit : '%').'#';
					$opt .= '#show_width='.((isset($options->show_width)) ? $options->show_width : '300').'#';
					$opt .= '#width_unit='.((isset($options->width_unit)) ? $options->width_unit : 'px').'#';
					$opt .= '#show_title='.((isset($options->show_title) && ($options->show_title == 'true')) ? 'true' : 'false').'#';
					$opt .= '#title='.((isset($options->title)) ? $options->title : 'default').'#';

					$text = $this->getTextEntryObject($entity);
					$text->text = $iframeUrl;
					$text->layout = $template;
					$text->options = $opt;
					$text->title = $title;
					$text->lang = $lang->languageId;

					$success = $text->save();

					echo '{"success":'.($success ? 'true' : 'false').',"call":"contentSaved","list:'.json_encode($text->getPluginContent()).'}';
					exit();
					break;
			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get Settings and Layouts with preselected values from a given Textentry
	 *
	 * @param int $id TextID to get all Settings and Layouts from
	 * @return string
	 */
	private function _getJSONPageSettings($id) {
		$text = new pTextEntry($id);

		$options = $this->getPluginOptions($text->plugin);
		$layouts = $this->getLayoutList();

		foreach ($layouts as $layout) {
			foreach ($layout->options as $name => $option) {
				$options[$name] = $option;
			}
		}

		return '"settings":'.json_encode($options).',"layouts":'.json_encode($layouts).',"title":'.json_encode($text->title).',"frame_url":'.json_encode($text->text);
	}

}
?>