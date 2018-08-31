<?php
	# DEFINES ############################################################################
	
	set_time_limit(0);

	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	require_once("../class.Pages.php");
	require_once(FASTTEMPLATES_PATH . "template.php");
	
	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"page" => "page.tpl",
			"dialog_generate" => "dialog_generate.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
			"generate_result"   => "generate_result.tpl",
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");

	# MAIN ##################################################################################
	
	$out = '';
	$result = db_query("SELECT * FROM module_vendors");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {
			$out .= '<div><input type="checkbox" value="'.$row['id'].'" id="vendors_'.$row['id'].'" name="vendors[]"">'.$row['name'].'</div>';
		}
	}
	$tpl->assign("VENDOR_LIST", $out);

	$out = '';
	$result = db_query("SELECT * FROM pages WHERE parent_id = 4 AND deleted = 0 AND visible = 1 ORDER BY sortfield");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {
			$out .= '<div><input type="checkbox" value="'.$row['id'].'" id="chapters_'.$row['id'].'" name="chapter[]" onchange="getShop();">'.$row['title'].'</div>';
		}
	}
	$tpl->assign("CHAPTER_LIST", $out);

	$out = '';
	$result = db_query("SELECT * FROM pages WHERE parent_id = 24 AND deleted = 0 AND visible = 1 ORDER BY sortfield");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {
			$out .= '<div style="margin: 20px auto 10px auto"><b>'.$row['title'].'</b></div>';

			$result2 = db_query("SELECT * FROM pages WHERE parent_id = ".$row['id']." AND deleted = 0 AND visible = 1 ORDER BY sortfield");
			if (db_num_rows($result2) > 0) {
				while ($row2 = db_fetch_array($result2)) {
					$out .= '<div style="margin: 20px auto 10px auto">'.$row2['title'].'&nbsp;<a href="javascript:void(0);" style="color: #000" onclick="openDiv(\'block_'.$row2['id'].'\')">[развернуть]</a></div>';
					$out .= '<div id="block_'.$row2['id'].'" style="display: none">';

					$result3 = db_query("SELECT * FROM module_elements WHERE page_id = ".$row2['id']." ORDER BY sortfield");
					if (db_num_rows($result3) > 0) {
						while ($row3 = db_fetch_array($result3)) {
							$out .= '<div style="display: inline-block; width: 200px">
                                            <div style="display: inline-block; width: 20px; vertical-align: top"><input type="checkbox" value="'.$row3['id'].'" id="rrc_'.$row3['id'].'" name="rrc[]"></div>
                                            <div style="display: inline-block; width: 160px; vertical-align: top">'.$row3['name'].'</div>
                                        </div>';
						}
					}

					$out .= '</div>';
				}
			}
		}
	}
	$tpl->assign("RRC_OUT", $out);

	$tpl->parse("PAGE_CONTENT", "dialog_generate");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>