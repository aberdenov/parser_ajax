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

<div class="box-modal" id="contentEditModal">
	<div class="box-modal_close arcticmodal-close">закрыть</div>

        <div class="sd_pageTitle">Свойства страницы</div>
        <form name="frmPageEdit" id="frmPageEdit" method="post" action="">
        <input type="hidden" name="page_id" value="{PAGE_ID}">
        <input type="hidden" name="parent_id" value="{PAGE_PARENT_ID}">
        <input type="hidden" name="old_title" value="{OLD_TITLE}">
        <input type="hidden" name="deleted" value="{DELETED}">
        <input type="hidden" name="visible" value="1">
        <input type="hidden" name="group" value="0">
        <table width="90%" border="0" cellspacing="0" cellpadding="5" align="center">
          <!-- <tr>
            <td class="sd_formLabel4">id:</td>
            <td class="sd_formInput2"><input type="text" class="sd_textbox" name="id" readonly value="{VALUE_ID}"></td>
          </tr> -->
          <tr>
            <td class="sd_formLabel4">Тип страницы:</td>
            <td class="sd_formInput2">
                <select class="sd_textbox" name="type" {EDITABLE_TYPE} disabled="disabled">
                {VALUE_TYPE}
                </select>
            </td>
          </tr>
          <!-- <tr>
            <td class="sd_formLabel4">Группа:</td>
            <td class="sd_formInput2">
            	<select class="sd_textbox" name="group">
                {VALUE_GROUP}
            	</select>
            </td>
          </tr> -->
          <!-- <tr>
            <td class="sd_formLabel4">Название для ЧПУ:</td>
            <td class="sd_formInput2"><input type="text" class="sd_textbox" value="{VALUE_MOD_REWRITE}" name="mod_rewrite"></td>
          </tr> -->
          <tr>
            <td class="sd_formLabel4">Название:</td>
            <td class="sd_formInput2"><input type="text" class="sd_textbox" value="{VALUE_TITLE}" name="title">
            </td>
          </tr>
          <!-- <tr>
            <td class="sd_formLabel4">Описание:</td>
            <td class="sd_formInput2"><textarea class="sd_txtarea" name="description" style="height: 300px">{VALUE_DESCRIPTION}</textarea></td>
          </tr>
          <tr>
            <td class="sd_formLabel4">Курс доллара:</td>
            <td class="sd_formInput2"><input type="text" class="sd_textbox" value="{VALUE_KURS}" name="kurs"></td>
          </tr> -->
          <!-- <tr>
            <td class="sd_formLabel4">Иконка:</td>
            <td class="sd_formInput2">
                <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="85"><input type="text" class="sd_textbox" style="width: 80px;" name="icon" id="pages_icon" value="{VALUE_ICON}" onkeydown="return onlyDigits(window);" maxlength="5"></td>
                    <td><input type="button" value="..." title="Выбрать изображение" class="sd_button" style="width:30px;" onClick="selectImage('pages_icon');"></td>
                    <td style="padding-left: 4px;"><img src="images/preview.gif" style="cursor:pointer;" alt="Предпросмотр" onClick="showPopupImage('{SESSION_ID}', dlg.icon.value)"></td>
                </tr>
                </table>
            </td>
          </tr> -->
          <!-- <tr>
            <td class="sd_formLabel4">Ссылка:</td>
            <td class="sd_formInput2"><input type="text" name="external_link" class="sd_textbox" maxlength="100" value="{VALUE_LINK}"></td>
          </tr>
          <tr>
            <td class="sd_formLabel4">Стартовая страница:</td>
            <td class="sd_formInput2"><input type="checkbox" name="start_page" {VALUE_START_PAGE}></td>
          </tr>
          <tr>
            <td class="sd_formLabel4">Видимая:</td>
            <td class="sd_formInput2"><input type="checkbox" name="visible" {VALUE_VISIBLE}></td>
          </tr>
           <tr>
            <td class="sd_formLabel4">Доступно после<br />авторизации:</td>
            <td class="sd_formInput2"><input type="checkbox" name="auth" {VALUE_AUTH}></td>
          </tr>
          <tr>
            <td class="sd_formLabel4">Сортировать:</td>
            <td class="sd_formInput2">
                <select class="sd_textbox" name="sort_by">
                    {SORTBY_LIST}
                </select>
                
                <select class="sd_textbox" name="sort_order" style="margin-top: 5px">
                    {SORTORDER_LIST}
                </select>
            </td>
          </tr>
          <tr>
            <td class="sd_formLabel4">Шаблон:</td>
            <td class="sd_formInput2">
                <select class="sd_textbox" name="template">
                    {TEMPLATE_LIST}
                </select>
            </td>
          </tr>
          <tr>
            <td class="sd_formLabel4" valign="top">Отображение:</td>
            <td class="sd_formInput2">
                <select class="sd_textbox" name="content" onchange="if (this.selectedIndex == 0) document.all['user_def'].style.display = ''; else document.all['user_def'].style.display = 'none';">
                    {CONTENT_LIST}
                </select>
                <div style="display: '{USER_DEF_DISPLAY}'; margin-top: 5px" id="user_def"><input type="text" name="user_defined" value="{USER_DEF_VALUE}" class="sd_textbox"></div>
            </td>
          </tr> -->
          <tr>
            <td colspan="2" class="sd_formButton"><input type="button" class="sd_button" value="Сохранить" onclick="pageSave();"></td>
          </tr>
        </table>
        </form>

	</div>
</div>