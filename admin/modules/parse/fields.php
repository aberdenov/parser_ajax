<?php
		$fields = array (
			"id" => array(
					"Display in grid" => true,
					"Display in form" => false,
					"Read only" => true,
					"Field type" => "textbox",
					"Title" => array (1=> "Номер", "ID", "Номер"),
				),

			"name" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Название", "Название", "Название"),
				),

			"domen" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Доменое имя", "Доменое имя", "Доменое имя"),
				),

			"link_parse" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Разбор ссылки (0 - отключено, 1, 2, 3 - варианты значений)", "Разбор ссылки (0 - отключено, 1, 2, 3 - варианты значений)", "Разбор ссылки (0 - отключено, 1, 2, 3 - варианты значений)"),
				),

			"param_parse" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Разбор по параметрам", "Разбор по параметрам", "Разбор по параметрам"),
				),

			"price_cut_type1" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Обрезка цены (способ 1)", "Обрезка цены (способ 1)", "Обрезка цены (способ 1)"),
				),

			"price_cut_type2" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Обрезка цены (способ 2)", "Обрезка цены (способ 2)", "Обрезка цены (способ 2)"),
				),

			"price_cut_type3" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Обрезка цены (способ 3)", "Обрезка цены (способ 3)", "Обрезка цены (способ 3)"),
				),

			"price_cut_type4" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Обрезка цены (способ 4)", "Обрезка цены (способ 4)", "Обрезка цены (способ 4)"),
				),

			"title_clean" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Обрезка заголовка", "Обрезка заголовка", "Обрезка заголовка"),
				),

			"url_type" => array(
					"Display in grid" => true,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Относительные пути", "Относительные пути", "Относительные пути"),
				),

			"city" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "external_table",
					"External table" => array("pages", 'id', 'title', 'parent_id = 26', 'title'),
					"Title" => array (1=> "Справочник городов", "Справочник городов", "Справочник городов"),
				),

			"cookie_val" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Название переменной cookie города", "Название переменной cookie города", "Название переменной cookie города"),
				),

			"sleep" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Задержка запросов", "Задержка запросов", "Задержка запросов"),
				),

			"use_proxy" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Использовать прокси", "Использовать прокси", "Использовать прокси"),
				),

			"big_cookie" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Сложная cookie", "Сложная cookie", "Сложная cookie"),
				),

			"big_cookie_start" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Начало сложной cookie", "Начало сложной cookie", "Начало сложной cookie"),
				),

			"big_cookie_end" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Окончание сложной cookie", "Окончание сложной cookie", "Окончание сложной cookie"),
				),

			"user_agent" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Изменять user agent", "Изменять user agent", "Изменять user agent"),
				),

			"snoopy" => array(
					"Display in grid" => false,
					"Display in form" => true,
					"Read only" => false,
					"Field type" => "checkbox",
					"Title" => array (1=> "Подключение по сокету", "Подключение по сокету", "Подключение по сокету"),
				),

			"sortfield" => array(
					"Display in grid" => false,
					"Display in form" => false,
					"Read only" => false,
					"Field type" => "textbox",
					"Title" => array (1=> "Индекс сортировки", "Sortfield", "Индекс сортировки"),
				),
		);
?>