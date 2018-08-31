<script type="text/javascript">
	
	function getSubPages(obj, result_id) {
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 14,
				id: jQuery("#"+ obj).val(),
				result_id: result_id,
		  	},
		  	onGetSubPages
		);
	}

	function onGetSubPages(data) {
		var info = data.split("#");

		document.getElementById(info[1]).innerHTML = info[0];
	}
</script>
<div class="sd_pageTitle">Загрузка справочников РРЦ из csv файла</div>

{RESULT_MESSAGE}

<form action="" name="userForm" method="post" enctype="multipart/form-data">

<div style="width: 650px; margin: 20px auto 10px auto"><b>Подгрузка РРЦ</b></div>

<div style="width: 650px; margin: 20px auto 10px auto">Регион</div>
<div style="width: 650px; margin: 0px auto 10px auto"><select id="region" onchange="getSubPages('region', 'chapter');">{REGION_LIST}</select></div>

<div style="width: 650px; margin: 20px auto 10px auto">Раздел</div>
<div style="width: 650px; margin: 0px auto 10px auto"><select id="chapter" onchange="getSubPages('chapter', 'brend');">{CHAPTER_LIST}</select></div>

<div style="width: 650px; margin: 20px auto 10px auto">Бренд</div>
<div style="width: 650px; margin: 0px auto 10px auto"><select id="brend" name="brend">{BREND_LIST}</select></div>

<div style="width: 650px; margin: 0px auto 10px auto"><input type="file" id="file" name="file"></div>

<div style="width: 650px; margin: auto; text-align: center">
	<input type="submit" name="send" class="sd_button" value="Загрузить" style="width: 200px">
</div> 
</form>

