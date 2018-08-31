<script type="text/javascript">
	function getTemplate() {
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 8
		  	},
		  	onGetTemplate
		);
	}

	function onGetTemplate(data) {
		document.getElementById('tpl').innerHTML = data;
	}

	function ajax_start() {
		document.getElementById("result").innerHTML = '';
		document.getElementById("main_result").innerHTML = '';
		document.getElementById("files").style.display = 'none';

		ajax_struct();
	}

	function ajax_struct() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var vendor_str = '';
		var shops_str = '';
		var city_str = '';

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 8) == 'vendors_') {
				if (aCbx[i].checked == true) {
					vendor_str += aCbx[i].value + ';';
				}
			}
		}

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 6) == 'shops_') {
				if (aCbx[i].checked == true) {
					shops_str += aCbx[i].value + ';';
				}
			}
		}

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 5) == 'city_') {
				if (aCbx[i].checked == true) {
					city_str += aCbx[i].value + ';';
				}
			}
		}

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 1,
				vendors: vendor_str,
				shops: shops_str,
				cities: city_str
		  	},
		  	onAjaxStruct
		);
	}

	function onAjaxStruct(data) {
		document.getElementById("result").innerHTML = data;
		document.getElementById("main_result").innerHTML += '<div>Подготовка структуры ссылок - <b>готово</b></div>';

		ajax_generate();
	}

	function ajax_generate() {
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 2
		  	},
		  	onAjaxGenerate
		);
	}

	function onAjaxGenerate(data) {
		if (data != "stop") {
			ajax_generate();

			document.getElementById("result").innerHTML = '<div>'+ data +'</div>';
		} else {
			document.getElementById("main_result").innerHTML += '<div>Получение контента для разбора - <b>готово</b></div>';

			ajax_parse();
		}
	}

	function ajax_parse() {
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 3
		  	},
		  	onAjaxParse
		);
	}

	function onAjaxParse(data) {
		if (data != "stop") {
			ajax_parse();

			document.getElementById("result").innerHTML = '<div>'+ data +'</div>';
		} else {
			document.getElementById("main_result").innerHTML += '<div>Разбор контента - <b>готово</b></div>';

			ajax_result();
		}
	}

	function ajax_result() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var rrc_status = 0;
		var goods_str = '';

		if (jQuery('#use_goods').prop('checked')) {
		  	rrc_status = 1;
		} 

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 4) == 'rrc_') {
				if (aCbx[i].checked == true) {
					goods_str += aCbx[i].value + ';';
				}
			}
		}
		
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 4,
				rrc_status: rrc_status,
				goods: goods_str 
		  	},
		  	onAjaxResult
		);
	}

	function onAjaxResult(data) {
		document.getElementById("files").style.display = '';
		document.getElementById("result").innerHTML = '<div>'+ data +'</div>';
		document.getElementById("main_result").innerHTML += '<div>Генерация файлов с результатами - <b>готово</b></div>';
	}

	function getShop() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var str = '';

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 9) == 'chapters_') {
				if (aCbx[i].checked == true) {
					str += aCbx[i].value + ';';
				}
			}
		}

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 5,
				chapters: str
		  	},
		  	onGetShop
		);
	}

	function onGetShop(data) {
		document.getElementById("shops").innerHTML = data;
	}

	function getCities() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var str = '';

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 6) == 'shops_') {
				if (aCbx[i].checked == true) {
					str += aCbx[i].value + ';';
				}
			}
		}

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 6,
				shops: str
		  	},
		  	onGetCities
		);
	}

	function onGetCities(data) {
		document.getElementById("cities").innerHTML = data;
	}

	function selAll(obj, value) {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var str = 'city_rel_'+ value;

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].alt == str) {
				aCbx[i].checked = obj.checked;
			}
		}
	}

	function creatTpl() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var brend_val = '';
		var chapter_val = '';
		var shop_val = '';
		var city_val = '';
		var rcc_val = '';
		var name = '';
		var rrc_status = 0;

		if (jQuery('#use_goods').prop('checked')) {
		  	rrc_status = 1;
		} 

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 8) == 'vendors_') {
				if (aCbx[i].checked == true) {
					brend_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 9) == 'chapters_') {
				if (aCbx[i].checked == true) {
					chapter_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 6) == 'shops_') {
				if (aCbx[i].checked == true) {
					shop_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 5) == 'city_') {
				if (aCbx[i].checked == true) {
					city_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 4) == 'rrc_') {
				if (aCbx[i].checked == true) {
					rcc_val += aCbx[i].id + ';';
				}
			}
		}

		if (name = prompt('Введите название шаблона', '')) {
			jQuery.post(
				'./../file_generator.php', {
					type: "html-request",
					action: 7,
					name: name,
					brend_val: brend_val,
					chapter_val: chapter_val,
					shop_val: shop_val,
					city_val: city_val,
					rcc_val: rcc_val,
					rrc_status: rrc_status
			  	},
			  	onCreatTpl
			);
		}
	}

	function onCreatTpl(data) {
		document.getElementById("main_result").innerHTML += '<div>Шаблон создан</div>';
		getTemplate();
	}

	function updateTpl() {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var brend_val = '';
		var chapter_val = '';
		var shop_val = '';
		var city_val = '';
		var rcc_val = '';
		var rrc_status = 0;
		var id = jQuery('#tpl').val();

		if (jQuery('#use_goods').prop('checked')) {
		  	rrc_status = 1;
		} 

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 8) == 'vendors_') {
				if (aCbx[i].checked == true) {
					brend_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 9) == 'chapters_') {
				if (aCbx[i].checked == true) {
					chapter_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 6) == 'shops_') {
				if (aCbx[i].checked == true) {
					shop_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 5) == 'city_') {
				if (aCbx[i].checked == true) {
					city_val += aCbx[i].id + ';';
				}
			}
		}

		for (var i in aCbx){
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 4) == 'rrc_') {
				if (aCbx[i].checked == true) {
					rcc_val += aCbx[i].id + ';';
				}
			}
		}

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 9,
				id: id,
				brend_val: brend_val,
				chapter_val: chapter_val,
				shop_val: shop_val,
				city_val: city_val,
				rcc_val: rcc_val,
				rrc_status: rrc_status
		  	},
		  	onUpdateTpl
		);
	}

	function onUpdateTpl(data) {
		document.getElementById("main_result").innerHTML += '<div>Шаблон обновлен</div>';
		getTemplate();
	}

	function useTpl() {
		var id = jQuery('#tpl').val();

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 10,
				id: id
		  	},
		  	onUseTpl
		);
	}

	function onUseTpl(data) {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var info = data.split("#");

		// отмечаем бренды
		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 8) == 'vendors_') {
				aCbx[i].checked = false;
			}
		}

		var brend_arr = info[0].split(";");
		for (var key in brend_arr) {
			document.getElementById(brend_arr[key]).checked = true;			
		}
		
		// отмечаем разделы
		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 9) == 'chapters_') {
				aCbx[i].checked = false;
			}
		}

		var chapter_arr = info[1].split(";");
		for (var key in chapter_arr) {
			document.getElementById(chapter_arr[key]).checked = true;
		}

		// подгружаем магазины для выбранных разделов
		getShop();

		// отмечаем магазины
		setTimeout(markShops, 500, data);

		// подгружаем города для выбранных магазинов
		setTimeout(getCities, 1500);

		// отмечаем города
		setTimeout(markCities, 2500, data);

		// отмечаем РРЦ
		if (info[5] > 0) {
			jQuery("#use_goods").attr("checked", "checked");
		} else {
			jQuery('#use_goods').removeAttr("checked");
		}

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 4) == 'rrc_') {
				aCbx[i].checked = false;
			}
		}

		var rrc_arr = info[4].split(";");
		for (var key in rrc_arr) {
			document.getElementById(rrc_arr[key]).checked = true;
		}
	}

	function markShops(data) {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var info = data.split("#");

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 6) == 'shops_') {
				aCbx[i].checked = false;
			}
		}

		var shops_arr = info[2].split(";");
		for (var key in shops_arr) {
			document.getElementById(shops_arr[key]).checked = true;
		}
	}

	function markCities(data) {
		var aCbx = document.body.getElementsByTagName("INPUT");
		var info = data.split("#");

		for (var i in aCbx) {
			if (aCbx[i].id > "" && aCbx[i].id.substr(0, 5) == 'city_') {
				aCbx[i].checked = false;
			}
		}

		var cities_arr = info[3].split(";");
		for (var key in cities_arr) {
			document.getElementById(cities_arr[key]).checked = true;
		}
	}

	function delTpl() {
		if (confirm('Вы действительно хотите удалить шаблон?')) {
			var id = jQuery('#tpl').val();
			
			jQuery.post(
				'./../file_generator.php', {
					type: "html-request",
					action: 11,
					id: id
			  	},
			  	onDelTpl
			);
		}
	}

	function onDelTpl(data) {
		getTemplate();
	}

	function getRrc() {
		sel_val = jQuery("#rrc_country").val();

		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 13,
				id: sel_val
		  	},
		  	onGetRrc
		);
	}

	function onGetRrc(data) {
		document.getElementById('rrc_out').innerHTML = data;
	}

	getTemplate();
</script>

<div class="sd_pageTitle">Генерация отчета</div>

<form action="" name="userForm" method="post" enctype="multipart/form-data">

<div style="width: 650px; margin: 20px auto 10px auto"><b>Шаблон</b></div>
<div style="width: 600px; margin: 0px auto 10px auto">
	<select name="tpl" id="tpl"></select>
	<input type="button" name="send" class="sd_button" value="Применить" style="width: 150px" onclick="useTpl();">
	<input type="button" name="send" class="sd_button" value="Удалить шаблон" style="width: 200px" onclick="delTpl();">
</div>

<div style="width: 650px; margin: 20px auto 10px auto"><b>Бренды</b></div>
<div style="width: 600px; margin: 0px auto 10px auto">{VENDOR_LIST}</div>

<div style="width: 650px; margin: 20px auto 10px auto"><b>Разделы</b></div>
<div style="width: 600px; margin: 0px auto 10px auto">{CHAPTER_LIST}</div>

<div style="width: 650px; margin: 20px auto 10px auto"><b>Магазины</b></div>
<div style="width: 600px; margin: 0px auto 10px auto" id="shops"></div>

<div style="width: 650px; margin: 20px auto 10px auto"><b>Города</b></div>
<div style="width: 600px; margin: 0px auto 10px auto" id="cities"></div>

<div style="width: 650px; margin: 20px auto 10px auto"><hr><input type="checkbox" value="1" id="use_goods" name="use_goods">Использовать допонительную выборку по товарам</div>
<div style="width: 650px; margin: 0px auto 20px auto">
	<select name="rrc_country" id="rrc_country" onchange="getRrc();">
	<option value="24">По Казахстану</option>
	<option value="140">По России</option>
	</select>
</div>
<div style="width: 600px; margin: 0px auto 10px auto" id="rrc_out">{RRC_OUT}</div>

<div style="width: 650px; margin: auto; text-align: center">
	<input type="button" onclick="ajax_start();" name="send" class="sd_button" value="Сгенерировать" style="width: 200px">&nbsp;
	<input type="button" name="save" class="sd_button" value="Сохранить шаблон" style="width: 200px" onclick="updateTpl();">&nbsp;
	<input type="button" name="send" class="sd_button" value="Создать новый шаблон" style="width: 200px" onclick="creatTpl();">
</div> 

<div style="width: 600px; padding: 10px; margin: 40px auto 40px auto" id="main_result"></div>
<div style="width: 600px; height: 200px; padding: 10px; margin: 0px auto 40px auto; overflow: auto; border: 1px solid #000" id="result"></div>
<div style="width: 650px; margin: auto; text-align: center">
	<input type="button" onclick="ajax_parse();" name="send" class="sd_button" value="Повтовторный разбор" style="width: 200px">
</div>

<div id="files" style="display: none; text-align: center">
	<div style="margin: 10px auto 10px auto"><a href="http://parser.trainspotting.kz/file.xls" class="ln1">Скачать файл отчета</a></div>
	<div style="margin: 10px auto 10px auto"><a href="http://parser.trainspotting.kz/archive.zip" class="ln1">Скачать архив скриншотов</a></div>
</div>

</form>
