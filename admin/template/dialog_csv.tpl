<script type="text/javascript">
	
	function startParse() {
		jQuery.post(
			'./../file_generator.php', {
				type: "html-request",
				action: 12
		  	},
		  	onStartParse
		);
	}

	function onStartParse(data) {
		if (data != "stop") {
			startParse();

			document.getElementById("result").innerHTML += '<div>'+ data +'</div>';
		} else {
			document.getElementById("main_result").innerHTML = '<div>Разбор прокси завершен</div>';
		}
	}

	{FUNCTION}
</script>
<div class="sd_pageTitle">Загрузка proxy серверов из csv файла</div>

{RESULT_MESSAGE}

<form action="" name="userForm" method="post" enctype="multipart/form-data">

<div style="width: 650px; margin: 20px auto 10px auto"><b>Подгрузка proxy</b></div>
<div style="width: 650px; margin: 0px auto 10px auto"><input type="file" id="file" name="file"></div>

<div style="width: 650px; margin: auto; text-align: center">
	<input type="submit" name="send" class="sd_button" value="Загрузить" style="width: 200px">
</div> 

<div style="width: 600px; padding: 10px; margin: 40px auto 40px auto" id="main_result"></div>
<div style="width: 600px; height: 200px; padding: 10px; margin: 20px auto 40px auto; overflow: auto; border: 1px solid #000" id="result"></div>
</form>
