<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="js/bootstrap.min.js"></script>

<!-- Required Stylesheets -->
<link href="css/bootstrap-treeview.css" rel="stylesheet">

<!-- Required Javascript -->
<script src="js/bootstrap-treeview.js"></script>

<table width="100%" height="100%" cellpadding="0" cellspacing="0" id="table_cont">
<tr>
	<td width="350" valign="top" style="padding: 10px">
    	<div id="tree" style="overflow: auto"></div>
    </td>
    <td valign="top">
    	<div id="grid" style="overflow: auto; border-left: 1px solid #dddddd"></div>        
    </td>
</tr>
</table>

<input type="hidden" value="" id="page_type" />
<input type="hidden" value="" id="page_table" />
<input type="hidden" value="" id="page_id" />

<ul id="treeMenu" class="contextMenu">
    <li><a href="javascript:void(0);" onclick="createPage();">Создать новую</a></li>
    <li class="separator"></li>
    <li><a href="">На уровень выше</a></li>
    <li><a href="">На уровень ниже</a></li>
    <li><a href="javascript:void(0);" onclick="pageMoveUp();">Переместить выше</a></li>
    <li><a href="javascript:void(0);" onclick="pageMoveDown();">Переместить ниже</a></li>
    <li class="separator"></li>
    <li><a href="javascript:void(0);" onclick="cutPage();">Вырезать</a></li>
    <li><a href="javascript:void(0);" onclick="copyPage();">Копировать</a></li>
    <li><a href="javascript:void(0);" onclick="pastePage();">Вставить</a></li>
    <li class="separator"></li>
    <li><a href="javascript:void(0);" onclick="copyLink('{LANG_ID}');" id="click-to-copy">Копировать ссылку</a></li>
    <li><a href="javascript:void(0);" onclick="deletePage();">Удалить</a></li>
    <li class="separator"></li>
    <li><a href="javascript:void(0);" onclick="editPage();">Свойства</a></li>
</ul>

<ul id="contentMenu" class="contextMenu">
    <li><a href="">Редактировать</a></li>
    <li><a href="javascript:void(0);" onclick="editContent(document.getElementById('page_type').value, document.getElementById('page_table').value, 0);">Создать новую</a></li>
    <li class="separator"></li>
    <li><a href="javascript:void(0);" onclick="copyContent();">Копировать</a></li>
    <li><a href="javascript:void(0);" onclick="cutContent();">Вырезать</a></li>
    <li><a href="javascript:void(0);" onclick="pasteContent();">Вставить</a></li>
    <li class="separator"></li>
    <li><a href="">Переместить выше</a></li>
    <li><a href="">Переместить ниже</a></li>  
    <li class="separator"></li>
    <li><a href="javascript:void(0);" onclick="deleteContent();">Удалить</a></li>
</ul>

<script language="javascript">
	function getTree() { 
		var tree = [
			{MENU_LIST}
		];
		
		// Some logic to retrieve, or generate tree structure
	  	return tree;
	}
	
	$('#tree').treeview({data: getTree()})
		.on('nodeSelected', function(event, data) {
			getContentList(data.id);
			selected_menu = data.id;
			getPageInfo(data.id);	
		})
		.on('nodeChecked', function(event, data) {
			alert(2);	
		});
	
	$('#tree').height($('#table_cont').height() - 40);
	$('#grid').height($('#table_cont').height() - 18);
	
	$(document).ready( function() {
		
		// Show menu when #myDiv is clicked
		$("#tree").contextMenu({
			menu: 'treeMenu'
		},
			function(action, el, pos) {
			/*alert(
				'Action: ' + action + '\n\n' +
				'Element ID: ' + $(el).attr('id') + '\n\n' + 
				'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\n\n' + 
				'X: ' + pos.docX + '  Y: ' + pos.docY+ ' (relative to document)'
				);*/
				
		});
		
		$("#grid").contextMenu({
			menu: 'contentMenu'
		},
			function(action, el, pos) {
				getPageInfo(selected_menu);
		});
		
		/*// Show menu when a list item is clicked
		$("#myList UL LI").contextMenu({
			menu: 'myMenu'
		}, function(action, el, pos) {
			alert(
				'Action: ' + action + '\n\n' +
				'Element text: ' + $(el).text() + '\n\n' + 
				'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\n\n' + 
				'X: ' + pos.docX + '  Y: ' + pos.docY+ ' (relative to document)'
				);
		});		*/		
		
	});
	
	// Grid
//	$("#grid-basic").bootgrid().on("click.rs.jquery.bootgrid", function (event, col, row){
//			alert(1);
//	});
</script>