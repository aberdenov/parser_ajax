<script src="js/tinymce/tinymce.min.js"></script>
<script>
	tinymce.init({ 
		selector:'textarea', 
		language: 'ru',
		plugins: [
		  'advlist autolink lists link image imagetools charmap print preview anchor',
		  'searchreplace visualblocks code fullscreen',
		  'insertdatetime media table contextmenu paste code'
		],
		toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
		image_list: "images_list.php",
		setup: function (editor) {
			editor.on('change', function () {
				editor.save();
			});
		} 
	});
</script>

<div class="box-modal" id="contentEditModal_{ROW_TABLE_NAME}_{ROW_ID}">
	<div class="box-modal_close arcticmodal-close">закрыть</div>
	    
    <form method="post" name="content_edit_form" id="content_edit_form">
    <input type="hidden" name="id" value="{ROW_ID}">
    <input type="hidden" name="page_id" value="{ROW_PAGE_ID}">
    <input type="hidden" name="tablename" value="{ROW_TABLE_NAME}">
    <input type="hidden" name="type" value="{ROW_TYPE}">
	<table width="100%" cellpadding="0" cellspacing="0">{FIELDS}</table>
    
    <div style="margin-top: 20px"><input type="button" style="height: 30px" class="sd_button" value="Сохранить" name="submit" onclick="contentEdit();"></div>
    </form>
</div>