<script src="js/jquery.uploadify.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="css/uploadify.css">

<div class="sd_pageTitle">Изображения</div>

<table cellpadding="5" cellspacing="0" border="0" width="600" align="center"> 
<tr>
    <td class="sd_formLabel4">Группы:</td>
    <td class="sd_formInput2">
        
        <form name="frmGroups" action="" method="get">
		<input type="hidden" name="selimg" value="{SEL_IMG}">
        <table width="100%"  border="0" cellspacing="0" cellpadding="0">
         <tr>
            <td style="padding-right: 5px;">
                <select class="sd_textbox" name="group_id" id="group_id">
                {GROUPS}
                </select>
            </td>
            <td width="80" align="right">
                <input type="submit" class="sd_button" value="Перейти">		
            </td>
         </tr>
        </table>
        </form>
        
        <form action="" name="imgForm" method="post" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="{MAX_FILE_SIZE}">
        <div class="tx1">
            <a href="javascript:void(0);" onClick="addGroup('{MENU_ID}'); return false;" class="ln1"><img src="images/gallery/add_group.gif" class="im2">Добавить</a>
            <a href="javascript:void(0);" onClick="renameGroup('{MENU_ID}'); return false;" class="ln1"><img src="images/gallery/rename_group.gif" class="im2">Переименовать</a>
            <a href="javascript:void(0);" onClick="deleteGroup('{MENU_ID}'); return false;" class="ln1"><img src="images/gallery/delete_group.gif" class="im2">Удалить</a>
        </div>
    </td>
</tr>
<tr>
    <td class="sd_formLabel4">Файлы:</td>
    <td class="sd_formInput2">
        <input id="file_upload" name="file_upload" type="file" multiple="true" class="sd_textbox">
        <div id="queue"></div>
    </td>
</tr>
</table> 

<table cellpadding="5" cellspacing="0" border=0 width="600" align="center">
<tr>
    <td colspan="2" class="sd_formButton">
        <div style="font-size: 11px; color: #505050; font-weight: bold; margin: 10px; text-align: center">{TOTAL_IMAGES}</div>
    </td>
</tr>
</table>

<div>&nbsp;</div>

<table cellpadding="5" cellspacing="0" border="0" width="600" align="center">
<tr>
    <td class="sd_outset" style="border-left: 1px solid #808080; border-top: 1px solid #A6A6A6;"><b>ID</b></td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;"><b>Название</b></td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;"><b>Файл</b></td>
    <td class="sd_outset" style="border-top: 1px solid #A6A6A6;" width="30">&nbsp;</td>
</tr>
{UPLOADED_IMAGES_LIST}
</table>

<div style="margin: 10px; font: normal 12px Arial; text-align: center">{GALLERY_PAGES}</div>
</form>

<script type="text/javascript">
	$(function() {
		$('#file_upload').uploadify({
			'formData'     : {
				'timestamp' : '{TIMESTAMP}',
				'token'     : '{SALT}',
				'group_id'	: $('#group_id').val(),
			},
			'swf'      : 'uploadify.swf',
			'uploader' : 'uploadify.php', 
			'onUploadSuccess' : function(file, data, response)  {
				//alert(data);
			},
			'onQueueComplete' : function(queueData) {
				location.reload();
			}
		});
	});
</script>