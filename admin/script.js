var selected_menu = 0;
var selectedElementId = '';
var selectedGridElementId = '';
var selectedGridElementTable = '';

function getContentList(page_id) {
	$.post(
		'ajax_feedback.php', {
			type: "html-request",
			action: 1,
			page_id: page_id
		},
		onGetContentList
	);
}

function onGetContentList(data) {
	document.getElementById('grid').innerHTML = data;
}

function editContent(type, table, id) {
	$.arcticmodal({
		type: 'ajax', 
		url: 'dialog_content_edit.php?page_id='+ selected_menu +'&type='+ type +'&tablename='+ table +'&id='+ id +'&btn_type=0',
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function editExternalContent(type, table, id) {
	$.arcticmodal({
		type: 'ajax',
		url: 'dialog_content_edit.php?page_id='+ selected_menu +'&type='+ type +'&tablename='+ table +'&id='+ id +'&btn_type=1',
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function createExternalContent(type, table, id) {
	$.arcticmodal({
		type: 'ajax',
		url: 'dialog_content_edit.php?page_id='+ id +'&type='+ type +'&tablename='+ table +'&id='+ 0 +'&btn_type=1',
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function createPage() {
	$.arcticmodal({
		type: 'ajax',
		url: 'dialog_pages_create.php?page_id='+ selected_menu, 
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function editPage() {
	$.arcticmodal({
		type: 'ajax',
		url: 'dialog_pages_properties.php?page_id='+ selected_menu, 
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function pageCreate() {
	$.post(
		'ajax_feedback.php', {
			type: "html-request",
			action: 2,
			form: $('#frmPageCreate').serialize()
		},
		onFrmPageCreate
	);
}

function onFrmPageCreate(data) {
	$.arcticmodal('close');
	createPage();
}

function pageSave() {
	$.post(
		'ajax_feedback.php', {
			type: "html-request",
			action: 3,
			form: $('#frmPageEdit').serialize()
		},
		onFrmPageSave
	);
}

function onFrmPageSave(data) {
	$.arcticmodal('close');
	editPage();
}

function deletePage() {
	if (confirm('Вы действительно хотите удалить раздел?')) {
		$.post(
				'ajax_feedback.php', {
					type: "html-request",
					action: 17, 
					id: selected_menu
				},
				onPageDelete
			);
	}
}

function onPageDelete(data) {
	window.location = 'control.php';	
}

function contentEdit() {
	$.post(
		'ajax_feedback.php', {
			type: "html-request",
			action: 4,
			form: $('#content_edit_form').serialize()
		},
		onContentEdit
	);
}

function onContentEdit(data) {
	$.arcticmodal('close');
	eval(data);
}

function getPageInfo(id) {
	$.post(
		'ajax_feedback.php', {
			type: "html-request",
			action: 5,
			id: id
		},
		onGetPageInfo
	);
}

function onGetPageInfo(data) {
	var info = data.split("#");
	
	document.getElementById('page_type').value = info[0];
	document.getElementById('page_table').value = info[1];
	document.getElementById('page_id').value = info[2];
}

function addGroup(menu_id) {
	var var_value;
	
	if (var_value = prompt('Введите название группы', '')) {
		$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 6,
				var_value: var_value,
				menu_id: menu_id
			},
			onGroupResult
		);
	}			
}

function deleteGroup(menu_id) {
	var objSel = document.getElementById('group_id');
	var obj_value = objSel.options[objSel.selectedIndex].value;

	if (confirm('Вы действительно хотите удалить группу?')) {
		$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 7, 
				var_value: obj_value,
				menu_id: menu_id
			},
			onGroupResult 
		); 
	}
}

function deleteImage(image_id, menu_id) {
	if (confirm('Вы действительно хотите удалить изображение?')) {
		$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 8, 
				image_id: image_id,
				menu_id: menu_id
			},
			onGroupResult 
		); 
	}
}

function renameGroup(menu_id) {
	var var_value;
	var objSel = document.getElementById('group_id');
	var obj_value = objSel.options[objSel.selectedIndex].value;
	
	if (var_value = prompt('Введите новое название группы', '')) {
		$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 9,
				var_value: var_value,
				group_id: obj_value,
				menu_id: menu_id
			},
			onGroupResult
		);
	}			
}

function onGroupResult(data) {
	location.reload();
}

function selectImage(sel_item_id) {
	selectedElementId = sel_item_id;

	$.arcticmodal({
		type: 'ajax',
		url: 'dialog_select_images.php', 
		afterLoading: function(data, el) {
			//alert('afterLoading');
		},
		afterLoadingOnShow: function(data, el) {
			//alert('afterLoadingOnShow');
		}
	});
}

function selectImageId(imageId) {
	if (selectedElementId != "") {
		document.getElementById(selectedElementId).value = imageId;
	}

	$('#imagesModal').arcticmodal('close');
}

function deleteLinkElement(table, id, dialog_type, dialog_table, dialog_id) {
	if (confirm('Вы действительно хотите удалить элемент?')) {
		$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 10, 
				table: table,
				id: id,
				dialog_type: dialog_type,
				dialog_table: dialog_table,
				dialog_id: dialog_id
			},
			onDeleteLinkElement
		); 
	}
}

function onDeleteLinkElement(data) {
	var info = data.split("#");
	
	editContent(info[0], info[1], info[2]);
}

function changeGroup() {	
	var objSel = document.getElementById('group_id');
	var obj_value = objSel.options[objSel.selectedIndex].value;
	
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 11, 
				group_id: obj_value
			},
			onChangeGroup
		); 
}

function onChangeGroup(data) {
	document.getElementById('imagesCont').innerHTML = data;
}

function copyLink(lang_id) {
	var url = 'page.php?page_id='+ selected_menu +'&lang='+ lang_id;
	
	var client = new ZeroClipboard(document.getElementById("click-to-copy"));
	
	client.on("copy", function (event) {
		var clipboard = event.clipboardData;
	  	clipboard.setData("text/plain", url);
	});
}

function pageMoveUp() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 12, 
				id: selected_menu
			},
			onPageMove
		); 
}

function pageMoveDown() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 13, 
				id: selected_menu
			},
			onPageMove
		); 
}

function onPageMove() {
	window.location = 'control.php';	
}

function deleteContent() {
	if (confirm('Вы действительно хотите удалить элемент?')) {
		$.post(
				'ajax_feedback.php', {
					type: "html-request",
					action: 14, 
					id: selectedGridElementId,
					tablename: selectedGridElementTable
				},
				onContentDelete
			);
	}
}

function onContentDelete(data) {
	window.location = 'control.php';	
}

function selectGridId(id, table) {
	selectedGridElementId = id;
	selectedGridElementTable = table;
}

function deleteUser(id) {
	if (confirm('Вы действительно хотите удалить пользователя?')) {
		$.post(
				'ajax_feedback.php', {
					type: "html-request",
					action: 15, 
					id: id
				},
				onUserDelete
			);
	}
}

function onUserDelete(data) {
	window.location = 'dialog_users.php';	
}

function changePassword(id) {
	var password = prompt('Новый пароль');

	if (password != "") {
		$.post(
				'ajax_feedback.php', {
					type: "html-request",
					action: 16, 
					id: id,
					password: password
				},
				onPasswordChange
			);
	}
}

function onPasswordChange(data) {
	window.location = 'dialog_users.php';	
}

function copyContent() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 18, 
				id: selectedGridElementId,
				page_id: selected_menu
			},
			onCopyContent
		); 
}

function onCopyContent(data) {
	
}

function cutContent() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 19, 
				id: selectedGridElementId,
				page_id: selected_menu
			},
			onCutContent
		); 
}

function onCutContent(data) {
	
}

function pasteContent() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 20, 
				page_id: selected_menu
			},
			onPasteContent
		); 
}

function onPasteContent(data) {
	getContentList(data);
}

function openDiv(layerID) {
	if (document.getElementById(layerID).style.display == 'none') {
		document.getElementById(layerID).style.display = '';
	} else {
		document.getElementById(layerID).style.display = 'none';
	}
	
	return false;
}

function copyPage() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 21, 
				id: selected_menu
			},
			onPageCopy
		); 
}

function onPageCopy(data) {
	
}

function cutPage() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 22, 
				id: selected_menu
			},
			onPageCut
		); 
}

function onPageCut(data) {
	
}

function pastePage() {
	$.post(
			'ajax_feedback.php', {
				type: "html-request",
				action: 23, 
				id: selected_menu
			},
			onPagePaste
		); 
}

function onPagePaste(data) {
	window.location = 'control.php';	
}
