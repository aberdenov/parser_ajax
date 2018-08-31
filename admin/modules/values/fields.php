<?php
# Массив 
# 1 - Заголовок 
# 2 - Название связанной таблицы
# 3 - Название поля из которого будут подставляться значения
# 4 - true - использовать подстановку
# 5 - id справочника 
# 6 - true - отбражать поле в редакторе (административном модуле)

$fields = array (
			"id" => array(
					"Display in grid" => true,
					"Display in form" => false,
					"Read only" => true,
					"Field type" => "textbox",
					"Title" => array (1=> "РќРѕРјРµСЂ", "ID", "РќРѕРјРµСЂ"),
				),

			"name" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "РРјСЏ РїРµСЂРµРјРµРЅРЅРѕР№", "Value name", "РРјСЏ РїРµСЂРµРјРµРЅРЅРѕР№"),
				),

			"value" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Р—РЅР°С‡РµРЅРёРµ", "Value", "Р—РЅР°С‡РµРЅРёРµ"),
				),

			"description" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "РћРїРёСЃР°РЅРёРµ", "Description", "РћРїРёСЃР°РЅРёРµ"),
				),

			"sortfield" => array(
					"Display in grid" => false,
					"Display in form" => false,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "РРЅРґРµРєСЃ СЃРѕСЂС‚РёСЂРѕРІРєРё", "Sortfield", "РРЅРґРµРєСЃ СЃРѕСЂС‚РёСЂРѕРІРєРё"),
				),
		);
?>