<?php
// +------------------------------------------------------------------------------------------+
// | PHP version 4																			  |
// +------------------------------------------------------------------------------------------+
// | Copyright (c) 2006 by Andrew Skoolkin                                                    |
// +------------------------------------------------------------------------------------------+
// | This source file is SiteDrive library ModuleSettings.                                    |
// | Library is designed for working with settings in SiteDrive modules.                      |
// +------------------------------------------------------------------------------------------+
// | Authors: Andrew Skoolkin <askulkin@nursat.kz>                                            |
// +------------------------------------------------------------------------------------------+

class ModuleSettings {
	const DB_TABLE = 'module_values';
	const DEFAULT_LABELS_PAGE = '@settings_labels';
	const SETTINGS_PAGE_DESC = '@settings';
	const LABELS_PAGE_DESC = '@labels';
	
	static $settings_ok = false;			// true - настройки существуют, false - не существуют
	static $labels_ok = false;				// true - строки существуют, false - не существуют
	
	static $module_name;					// Имя модуля
	
	static $MODULE;							// Массив всех параметров модуля
	static $DESCRIPTION;					// Массив описаний
	static $SETTINGS;						// Массив настроек
	static $LABELS;							// Массив строк
	
	static $SETTINGS_PARAMS;				// Параметры настроек
	static $LABELS_PARAMS;					// Параметры строк
	
	// Возвращает массив аттрибутов узла [имя аттрибута = значение]
	function getNodeAttributes($node) {
		$attributes = array();
		foreach ($node->attributes() as $attr_name => $attr_val) {
			$attributes[$attr_name] = strval($attr_val);
		}
		
		return $attributes;
	}
	
	// Загрузка настроек из XML файла
	function loadXMLConfig($filename) {
		if (!file_exists($filename)) return false;
		
		$xml = simplexml_load_file($filename);
		
		// Имя
		self::$module_name = strval($xml->name);
		self::$MODULE['name'] = self::$module_name;
		
		// Описание
		$nodes = $xml->description->lang;
		
		foreach ($nodes as $lang) {
			$lang_id = intval($lang['id']);
			self::$DESCRIPTION[$lang_id] = strval($lang);
		}
		
		self::$MODULE['description'] = self::$DESCRIPTION;
		
		// Настройки
		if (count($xml->settings->option) > 0) {
			$nodes = $xml->settings->option;
			self::$SETTINGS_PARAMS = self::getNodeAttributes($xml->settings);
			
			foreach ($nodes as $option) {
				$option_name = strval($option['name']);
				
				self::$SETTINGS[$option_name] = self::getNodeAttributes($option);
				self::$SETTINGS[$option_name]['value'] = strval($option);
			}
			
			if (count(self::$SETTINGS) > 0) {
				self::$settings_ok = true;
				self::$MODULE['settings'] = self::$SETTINGS;
			}
		}

		// Строки
		if (count($xml->labels->label) > 0) {
			$nodes = $xml->labels->label;
			self::$LABELS_PARAMS = self::getNodeAttributes($xml->labels);
			
			foreach ($nodes as $label) {
				$label_name = strval($label['name']);
				self::$LABELS[$label_name] = strval($label);
			}
			
			if (count(self::$LABELS) > 0) {
				self::$labels_ok = true;
				self::$MODULE['labels'] = self::$LABELS;
			}
		}
		
		return true;
	}
	
	// Загрузка настроек из БД
	function loadDBConfig($module_name) {
	
	}
	
	// Возвращает описание модуля
	function getModuleDescription($filename, $lang_id) {
		$description = '';
		if (self::loadXMLConfig($filename)) {
			if (isset(self::$DESCRIPTION[$lang_id])) $description = self::$DESCRIPTION[$lang_id];
		}
		
		return $description;
	}
	
	// Возвращаем массив настроек
	function getSettings() {
		$set = array();
		foreach (self::$SETTINGS as $name => $val) {
			$set[$name] = $val['value'];
		}
		
		return $set;
	}
	
	// Проверяем существует ли параметр в таблице настроек
	function isParamExists($param_name, $page_id) {
		$result = db_query("SELECT * FROM ".self::DB_TABLE." WHERE name = '".$param_name."' AND page_id = ".$page_id." LIMIT 1");
		if (db_num_rows($result) > 0) return true;
			else return false;
	}
	
	// Добавляем настройки в БД
	function addSettings($parent_id, $lang_id) {
		$page_id = 0;
		if ($parent_id == 0 || $lang_id == 0) return false;
		
		// Добавляем настройки (settings)
		if (self::$settings_ok && self::$SETTINGS_PARAMS['add'] == 'yes') {
			
			// Настройки внутри страницы модуля
			if (self::$SETTINGS_PARAMS['self'] == "yes") {
				// Получаем страницу для сохранения настроек
				$page_id = self::getSettingsPage($parent_id, $lang_id, self::$SETTINGS_PARAMS['title'], self::SETTINGS_PAGE_DESC);
			} else {
				// Настройки в общей группе
				if (isset(self::$DESCRIPTION[$lang_id])) $title = self::$DESCRIPTION[$lang_id]; else $title = self::$module_name;
				$title.= " - ".self::$SETTINGS_PARAMS['title'];
				
				$page_id = self::getSettingsPage(0, $lang_id, $title, "@".self::$module_name."_settings");
			}
			
			if ($page_id > 0) {
				foreach (self::$SETTINGS as $setting) {
					if (self::$SETTINGS_PARAMS['unique'] == "no" || (self::$SETTINGS_PARAMS['unique'] == "yes" && !self::isParamExists($setting['name'], $page_id))) {
						db_query("INSERT INTO ".self::DB_TABLE." SET page_id = ".$page_id.", lang_id = ".$lang_id.", name = '".mysql_escape_string($setting['name'])."', description = '".mysql_escape_string($setting['description'])."', value = '".mysql_escape_string($setting['value'])."'");
					}
				}
			}
		}
		
		// Добавляем строки (labels)
		if (self::$labels_ok && self::$LABELS_PARAMS['add'] == 'yes') {
			
			// Строки внутри страницы модуля
			if (self::$LABELS_PARAMS['self'] == "yes") {
				// Получаем страницу для сохранения строк
				$page_id = self::getSettingsPage($parent_id, $lang_id, self::$LABELS_PARAMS['title'], self::LABELS_PAGE_DESC);
			} else {
				// Строки в общей группе
				$page_id = db_get_data("SELECT id FROM pages WHERE type = 'values' AND lang_id = ".$lang_id." AND description = '".self::DEFAULT_LABELS_PAGE."'", "id");
			}
			
			if ($page_id > 0) {
				foreach (self::$LABELS as $name => $val) {
					if (self::$LABELS_PARAMS['unique'] == "no" || (self::$LABELS_PARAMS['unique'] == "yes" && !self::isParamExists($name, $page_id))) {
						db_query("INSERT INTO ".self::DB_TABLE." SET page_id = ".$page_id.", lang_id = ".$lang_id.", name = '".mysql_escape_string($name)."', value = '".mysql_escape_string($val)."'");
					}
				}
			}
		}
	}
	
	// Проверяем существует ли дочерняя страница настроек, указанного типа и возвращаем ее ID
	// Если страница не существует, создаем ее и возвращаем ее ID
	function getSettingsPage($parent_id, $lang_id, $page_title, $page_desc) {
		if ($parent_id > 0) {
			// Внутри модуля (@settings)
			$page_id = db_get_data("SELECT id FROM pages WHERE type = 'values' AND parent_id = ".$parent_id." AND description = '".$page_desc."' LIMIT 1", "id");
			if ($page_id > 0) {
				return $page_id;
			} else {
				$sortfield = db_get_data("SELECT MAX(sortfield) AS num FROM pages WHERE lang_id = ".$lang_id, "num");
				$sortfield++;
				db_query("INSERT INTO pages SET parent_id = ".$parent_id.", lang_id = ".$lang_id.", type = 'values', title = '".$page_title."', description = '".$page_desc."', sortfield = ".$sortfield);
				return db_insert_id();
			}
		} else {
			// Глобальная (@settings_folder)
			$page_id = db_get_data("SELECT id FROM pages WHERE lang_id = ".$lang_id." AND type = 'values' AND description = '".$page_desc."' LIMIT 1", "id");
			if ($page_id > 0) {
				return $page_id;
			} else {
				$settings_id = db_get_data("SELECT id FROM pages WHERE type = 'settings' AND lang_id = ".$lang_id." AND description = '@settings_folder' LIMIT 1", "id");
				if ($settings_id > 0) {
					$sortfield = db_get_data("SELECT MAX(sortfield) AS num FROM pages WHERE lang_id = ".$lang_id, "num");
					$sortfield++;
					db_query("INSERT INTO pages SET parent_id = ".$settings_id.", lang_id = ".$lang_id.", type = 'values', title = '".$page_title."', description = '".$page_desc."', sortfield = ".$sortfield);
					return db_insert_id();
				}
			}
		}
		
		return 0;
	}
}
?>