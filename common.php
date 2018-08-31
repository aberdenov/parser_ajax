<?php
	error_reporting(0);
	//set_magic_quotes_runtime(0);

	if (!defined('IN_SITEDRIVE')) { die("Hacking attempt"); }
	# DEFINES ###############################################################################

	define("PATH_PREFIX", './admin/');
	define("DATABASES_PATH", 'databases/');
	define("PAGETEMPLATES_PATH", 'page_templates/');
	define("INCLUDES_PATH", './admin/includes/');
	define("ROWS_PER_PAGE", 50);
	define("SITE_HOST", getenv("HTTP_HOST"));
	define("SITE_URL", "http://".getenv("HTTP_HOST").$_SERVER['PHP_SELF']);
	define("PHP_VER", substr(PHP_VERSION, 0, 1));
	$_MONTHS = "";

	# INCLUDES ##############################################################################

	require_once (PATH_PREFIX . "includes/db_config.php");
	require_once (PATH_PREFIX . DATABASES_PATH . DB_TYPE);

	# VARS ##################################################################################

		$_MONTHS_RU = array ( 1=>
		'января',
		'февраля',
		'марта',
		'апреля',
		'мая',
		'июня',
		'июля',
		'августа',
		'сентября',
		'октября',
		'ноября',
		'декабря'
	);

	$_MONTHS_EN = array ( 1=>
		'january',
		'february',
		'march',
		'april',
		'may',
		'june',
		'july',
		'august',
		'september',
		'october',
		'november',
		'december'
	);

	$_MONTHS_KZ = array ( 1=>
		'қаңтар',
		'ақпан',
		'наурыз',
		'сәуір',
		'мамыр',
		'маусым',
		'шілде',
		'тамыз',
		'қыркүйек',
		'қазан',
		'қараша',
		'желтоқсан'
	);
	
	$_MONTHS_CH = array ( 1=>
		'january',
		'february',
		'march',
		'april',
		'may',
		'june',
		'july',
		'august',
		'september',
		'october',
		'november',
		'december'
	);

	# FUNCTIONS #############################################################################

	//      
 	function Initialize(&$param_array) {
		//$old_error_handler = set_error_handler("debug_error_handler");
		$array = array('page_id', 'lang', 'page', 'article_id', 'news_id', 'poll_id', 'gallery_id', 'image', 'parent_id');
		if (is_array($param_array)) {
			foreach ($param_array as $key => $val) {	
				$varname = preg_match("/^page_[0-9]+$/", $key);
				if ($varname > 0) $array[] = $key;				
			}					
		}
		initVars($array, 'int', $param_array);
	}

	//      ( )
	// param:  [string], type:  [string] int  ; string - ,    
	function validateParam($param, $type) {
		switch ($type) {
			case 'int': {
				if (preg_match("/^[0-9]+$/", $param)) return true;
				break;
			}
			case 'string': {
				if (preg_match("/^[a-zA-Z0-9_@.]+$/", $param)) return true;
				break;
			}
		}
		return false;
	}

	//           
	function initVars($param_array, $type, &$arr) {
		if (is_array($param_array)) {
			foreach ($param_array as $varname) {
				if (isset($arr[$varname])) {
					if (!validateParam($arr[$varname], $type)) {
						selfRedirect();
					} else {
						if ($type == 'int') $arr[$varname] = abs(intval($arr[$varname]));
						if ($type == 'string') $GLOBALS[$array_name][$val] = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $GLOBALS[$array_name][$val]);
					}
				}
			}
		}
	}

	//       
	function initVar($value_name, $type) {
		if ($type == 'int') $result = 0;
		if ($type == 'string') $result = '';
		if ($type == 'bool') $result = false;

		if (isset($_REQUEST[$value_name])) {
			switch ($type) {
				case 'int': {
					$result = $_REQUEST[$value_name];
					if (is_numeric($result)) $result = abs(intval($result)); else $result = 0;
					break;
				}
				case 'string': {
					$result = $_REQUEST[$value_name];
					$result = str_replace(array("\n", "\r"), "", $result);
					$result = preg_replace("/[^\w\x7F-\xFF\s_@.]/", " ", $result);
					break;
				}
				case 'bool': {
					$result = true;
					break;
				}
				default: {
					$result = false;
					break;
				}
			}
		}
		return $result;
	}

	//    ,        
	function redirectIsBad($param, $type) {
		if (is_array($param)) {
			foreach ($param as $val) {
				if (!validateParam($val, $type)) selfRedirect();
			}
		} else {
			if (!validateParam($param, $type)) selfRedirect();
		}
	}

	//       
	function adds(&$el,$level=0) {
	  if (is_array($el)) {
	    if (get_magic_quotes_gpc()) return;
	    foreach($el as $k=>$v) adds($el[$k],$level+1);
	  } else { 
	    if (!get_magic_quotes_gpc()) $el = addslashes($el);
	    if (!$level) return $el;
	  }
	}

	//  
	function selfRedirect($params='') {
		if (!isset($GLOBALS['_SERVER']["HTTP_REFERER"])) {
			header("Location: page.php");
		}
		else
			header("Location: " . $GLOBALS['_SERVER']["HTTP_REFERER"] . $params);
		exit;

	}

	//      url
	function pageReload($params = '') {
		if (empty($params)) {
			header("Location: ".$GLOBALS['_SERVER']["REQUEST_URI"]);
			exit;
		}

		$pos      = strpos($params, "#");
		$fragment = substr($params, $pos, strlen($params)-$pos);
		$params   = str_replace($fragment, "", $params);

		parse_str($GLOBALS['_SERVER']["QUERY_STRING"], $url_params);
		parse_str($params, $str_params);

		foreach ($url_params as $key => $val) {
			if (isset($str_params[$key])) {
				$url_params[$key] = $str_params[$key];
				unset($str_params[$key]);
			}
		}

		$param_array = array_merge($url_params, $str_params);
		$url = "";
		$i = 0;
		$count = count($param_array);

		foreach ($param_array as $key => $val) {
			$i++;
			$url.= $key."=".$val;
			if ($i < $count) $url.= "&";
		}
		$url = SITE_URL."?".$url.$fragment;
		header("Location: ".$url);
		exit;
	}

	//       ID
	function isImageExists($id) {
		$result = db_query("SELECT * FROM images WHERE id = ".$id." LIMIT 1");
		if (db_num_rows($result) > 0) return true;
		return false;
	}

	//  URL 
	function getImageUrl($id, $is_image = 'images') {
		$file = '';
		$result = db_query("SELECT url, type FROM images WHERE id = ".$id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_object($result);
			if ($is_image) {
				$file = "./admin/upload/images/".$row->url;
			} else {
				$file = "./admin/upload/previews/".$row->url;
				if (!file_exists($file)) $file = '';
			}
		}

		return $file;
	}

	//  
	function showMessage($messageType, $location='') {
		if (!$location) $location = $GLOBALS['_SERVER']["HTTP_REFERER"];
		$location = urlencode($location);
		header("Location: page.php?page_id=29&sender=$location&message=$messageType");
		exit;
	}

	//     
	function showResult($text, $css_class) {
		$result = '<div class="'.$css_class.'" align="center">'.$text.'</div>';
		return $result;
	}

	//      
	function assignList($assignTo, $valuesArray, $selectedValue = '', $fieldName = '') {
		$option = '';
		if (is_array($valuesArray)) {
			if (!is_array($selectedValue)) {
				foreach ($valuesArray as $value => $label) {
					if ($value == $selectedValue) {
						$selected = ' selected';
					} 
					else {
						$selected = '';
					}
					$option .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
				}
			} else {
				foreach ($valuesArray as $value => $label) {
					if (isset($selectedValue[$value]))
						$checked = 'checked';
					else
						$checked = '';
					$option .= '<input type="checkbox" name="'.$fieldName.'['.$value.']" value="1" '.$checked.'>'.$label.'</option><br>';
				}
			}
		}
		$GLOBALS['tpl']->assign($assignTo, $option);
	}

	//    page_id  lang
	function setValidPage() {
		if (!isset($_GET['lang']) && !isset($_GET['page_id'])) {
			$lang_id = db_get_data("SELECT id FROM languages WHERE main = 1 AND blocked = 0", "id");
			$_GET['lang'] = abs($lang_id);
		}

		if (!isset($_GET['page_id'])) {
			$start_page = getStartPage();
			if ($start_page == 0) {
				$first_page = db_get_data("SELECT id FROM pages WHERE lang_id = '".$_GET['lang']."' ORDER BY id LIMIT 1", "id");
				$_GET['page_id'] = abs($first_page);
			} else {
				$_GET['page_id'] = $start_page;
			}
		} else {
			$lang_id = db_get_data("SELECT lang_id FROM pages WHERE id = '".$_GET['page_id']."' LIMIT 1", "lang_id");
			$_GET['lang'] = abs($lang_id);
		}		
	}

	//     
  	function showDate($format, $date) {
		if (strpos($date, ":")) {
			ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $date, $regs);
			$time = mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		} else {
			ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $date, $regs);
			$time = mktime(0, 0, 0, $regs[2], $regs[3], $regs[1]);
		}
		return date($format, $time);
  	}

  	//   datetime   datetime
  	function getFromDate($date) {
		$result = array();
		ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $date, $regs);
		$result['year'] = $regs['1'];
		$result['month'] = abs($regs['2']);
		$result['day'] = abs($regs['3']);
		$result['hour'] = $regs['4'];
		$result['minute'] = $regs['5'];
		$result['second'] = $regs['6'];
		return $result;
	}

  	//     "j F Y"
	function showNormalDate($date) {
		$result = showDate("j F Y", $date);
		$engMonths = array(1=>
				'January',
				'February',
				'March',
				'April',
				'May',
				'June',
				'July',
				'August',
				'September',
				'October',
				'November',
				'December',
			);
		$result = str_replace($engMonths, $GLOBALS['_MONTHS'], $result);
		return $result;
	}

  	//    "['\"\\]"      
	function makeSafeString($string, $length = 0) {
		$string = ereg_replace("['\"\\]", '', $string);
		if (isset($length))
			$string = substr($string, 0, $length);
		return $string;
	}

	//    
	function getImage($id) {
		$result = db_query("SELECT * FROM images WHERE id = ".$id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row;
		}
	}

	//       
	function parseParams($str, $var) {
		ereg(" ".$var."=([a-z0-9]+) ", $str, $regs);
	  if (isset($regs[1])) return $regs[1];
	}

	//  HTML  
	function getImageTag($id, $align, $size = 1) {
		$tag = '';
		$result = db_query("SELECT * FROM images WHERE id = ".$id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);

			if (strtolower($row['type']) == 'swf') {
				$flashFile = "../upload/images/".$row['url'];
				$str = str_replace(" ", "&", $align);
				parse_str($str, $out);
				if (@$out['width'] > 0 && @$out['height'] > 0) {
					$width = $out['width'];
					$height = $out['height'];
				} else {
					$size = getimagesize($flashFile);
					$width = $size[0];
					$height = $size[1];
				}

				$tag = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$width.'" height="'.$height.'">';
			   	$tag.= '<param name="bgcolor" VALUE="#FFFFFF">';
			   	$tag.= '<param name="movie" value="'.$flashFile.'">';
			   	$tag.= '<param name="quality" value="high">';
				$tag.= '<param name="menu" value="false">';
			   	$tag.= '<embed src="'.$flashFile.'" menu="false" quality="high" BGCOLOR="#FFFFFF" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'"></embed>';
			   	$tag.= '</object>';
			} else {
				if ($row['width'] && $row['height'] && $size == 1) $params = ' width="'.$row['width'].'" height="'.$row['height'].'"';
					else $params = '';
				if (!empty($align)) $params .= ' '.$align;
				if (empty($params)) $border = ' border="0"'; else $border = '';
				if ($size == 2) {
					$tag = '<img src="../upload/previews/'.$row['name'].'.'.$row['type'].'"'.$border.$params.' onclick="showPhoto(\'../upload/images/'.$row['url'].'\', '.$row['width'].', '.$row['height'].', \''.$row['title'].'\')" style="cursor: hand;">';
				} else {
					$tag = '<img src="../upload/images/'.$row['name'].'.'.$row['type'].'"'.$border.$params.'>';
				}
			}
		}
		return $tag;
	}

  //    <IMAGE>   <IMG SRC>
  function addImages($str) {
		$str = preg_replace("/(?:(?:\<IMAGE=)|(?:\<image=))[\\\]{0,1}[\'\"]{0,1}(?:&quot;){0,1}([0-9]+)[\\\]{0,1}[\'\"]{0,1}(?:&quot;){0,1}[\s](?:sizeimg=)[\\\]{0,1}[\'\"]{0,1}(?:&quot;){0,1}([0-9])[\\\]{0,1}[\'\"]{0,1}(?:&quot;){0,1} ([a-zA-Z0-9 =\'\"(?:&quot;);:#]*)\>/e", "getImageTag('\\1', '\\3', '\\2')", $str);
		return $str;
	}

	# EXTENDED API FUNCTIONS #################################################################

	//        
	function choose() {
		if (func_num_args() < 2) return 'Nothing to choose';
		$index = func_get_arg(0);

		if (func_num_args() == 2 && is_array(func_get_arg(1))) {
			$arr = func_get_arg(1);
			if (isset($arr[$index]))
				$result = $arr[$index];
			 else
			    $result = 'Key ['.$index.'] does not exist in array.';
		} else {
			if (func_num_args() > $index)
				$result = func_get_arg($index);
			 else
				$result = 'Wrong parameter count. Argument '.$index.' not passed.';
		}
		return $result;
	}

	//   
	function getPageTitle($page_id, $get_parent = false, $type = '') {
		if ($get_parent) $from_id = 'parent_id'; else $from_id = 'id';
		if ($type != 'all') $where_type = " AND type != 'menu'"; else $where_type = '';

		$result = db_query("SELECT title FROM pages WHERE ".$from_id." = ".$page_id.$where_type);
		if (db_num_rows($result) > 0) {
			$row = db_fetch_object($result);
			if ($row->title != '') return $row->title;
				else return 'Empty title';
		}
		return false;
	}

	//    
	function getPagesCount($parent_id) {
		return db_get_data("SELECT COUNT(*) AS num FROM pages WHERE parent_id = ".$parent_id." AND lang_id = ".LANG_ID." AND visible = 1", "num");
	}

	//  ID   
	function getStartPage() {
		$result = db_query("SELECT id FROM pages WHERE lang_id = ".$_GET['lang']." AND startpage = 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_object($result);
			return $row->id;
		}
		return 0;
	}

	//    
	function isPageExists($page_id = 0, $lang_id = 0) {
		/*
		$result = db_query("SELECT * FROM pages WHERE id = ".$page_id." AND lang_id = ".$lang_id." LIMIT 1");
		if (db_num_rows($result) > 0) return true;
			else return false;
		*/

		$result = db_query("SELECT pages.id FROM pages, languages WHERE pages.id = ".$page_id." AND pages.lang_id = ".$lang_id." AND (pages.lang_id = languages.id AND languages.blocked = 0) LIMIT 1");
		if (db_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}

	//  ID  
	function getNextArticleID($start_id, $order_field = 'id') {
		$result = db_query("SELECT id FROM module_article WHERE page_id = ".$GLOBALS['HTTP_GET_VARS']['page_id']." AND id > ".$start_id." ORDER BY ".$order_field." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row['id'];
		}
		return 0;
	}

	//  ID  
	function getPrevArticleID($start_id, $order_field = 'id') {
		$result = db_query("SELECT id FROM module_article WHERE page_id = ".$GLOBALS['HTTP_GET_VARS']['page_id']." AND id < ".$start_id." ORDER BY ".$order_field." DESC LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row['id'];
		}
		return 0;
	}

	//         
	function isArticleExists($page_id) {
		$result = db_query("SELECT id FROM module_article WHERE page_id = ".$page_id." LIMIT 1");
		if (db_num_rows($result) > 0) return true;
		return false;
	}

	//  pade_id   
	function getFirstChildID($page_id) {
		$result = db_query("SELECT id FROM pages WHERE parent_id = ".$page_id." AND deleted = 0 ORDER BY sortfield LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row[0];
		}
		return 0;
	}

	//  ID     
	function getModulePageID($type, $lang = 0) {
		if ($lang == 0) $lang = LANG_ID;
		$result = db_query("SELECT id FROM pages WHERE lang_id = ".$lang." AND type ='".$type."' LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row[0];
		}
		return 0;
	}

	//  ID  
	function getPageParentID($page_id) {
		$result = db_query("SELECT parent_id FROM pages WHERE id = ".$page_id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row[0];
		}
		return 0;
	}

	//  ID    content, lang  parent_id
	function getPageID($content, $lang, $parent_id = 0) {
		if ($parent_id > 0) $condition = ' AND id = '.$parent_id; else $condition = '';
		$result = db_query("SELECT id FROM pages WHERE content = '".$content."' AND lang_id = ".$lang.$condition." ORDER BY sortfield LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row[0];
		}
		return 0;
	}

	//  ID   
	function getPageIcon($page_id) {
		$result = db_query("SELECT icon FROM pages WHERE id = ".$page_id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row[0];
		}
		return 0;
	}

	//     ID
	function getPage($page_id) {
  	$result = db_query("SELECT * FROM pages WHERE id = ".$page_id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
			return $row;
		}
	}

	//    
	function getPageUrl($page, $args = '', $id = 0) {
		if (empty($page) || $id > 0) {
			$page = db_get_data("SELECT id, lang_id, external_link FROM pages WHERE id = ".$id." LIMIT 1");
		}

		if (empty($page['external_link'])) $item_url = SITE_URL."?page_id=".$page['id']."&lang=".$page['lang_id'].$args;
			else $item_url = $page['external_link'];
			
		return $item_url;
	}

	//    : true - , false - 
	function isPageVisible($page_id) {
		$result = db_query("SELECT visible FROM pages WHERE id = ".$page_id." AND visible = 1 LIMIT 1");
		if (db_num_rows($result) > 0) return true;
		return false;
	}
	
	//      PARENT / CHILD / .. / SUBCHILD
	function getPagesChains($page_id, $delimiter, $css_class, $uri_params) {
		$items = array(1=> array("title" => "", "url" => ""));
		$items[1]['title'] = ''.getPageTitle($page_id).'';
		$items[1]['url'] = '';

    	$parent_id = getPageParentID($page_id);
		$i = 1;

		while ($parent_id > 0) {
			$result = db_query("SELECT * FROM pages WHERE id = ".$parent_id." LIMIT 1");
			if (db_num_rows($result) > 0) {
				$row = db_fetch_array($result);
				$result2 = db_query("SELECT * FROM pages WHERE id = ".$row['id']." LIMIT 1");
				if (db_num_rows($result2) > 0) {
					$i++;
					$row2 = db_fetch_array($result2);
					$parent_id = $row2['parent_id'];
					$items[$i]['title'] = $row['title'];
					$url = 'page.php?page_id='.$row2['id'].'&lang='.$row['lang_id'];
					if (!empty($uri_params)) $url.= '&'.$uri_params;
					$items[$i]['url'] = $url;
				} else { $parent_id = 0; }
			} else { $parent_id = 0; }
		}

		$str = '';
		$items_count = count($items);
		if ($items_count > 1) $items_count = $items_count - 2;

		for ($i = $items_count + 1; $i > 0; $i--) {
			$title = $items[$i]['title'];

			if ($i != 1) $str.= '<a href="'.$items[$i]['url'].'" class="'.$css_class.'">'.$title.'</a>';
				else $str.= $title;

			if ($i != 1) $str.= '<span class="ptit">'.$delimiter.'</span>';
		}
		return $str;
	}

	//    
	function getPageSort($page) {
		$sort = array("sortField" => "id", "sortOrder" => "ASC");

		if (is_array($page)) {
			$sort['sortField'] = $page['sort_by'];
			$sort['sortOrder'] = $page['sort_order'];
		} else {
			$result = db_query("SELECT sort_by, sort_order FROM pages WHERE id = ".$page." LIMIT 1");
			if (db_num_rows($result) > 0) {
				$row = db_fetch_array($result);

				$sort['sortField'] = $row['sort_by'];
				$sort['sortOrder'] = $row['sort_order'];
			}
		}

		if (empty($sort['sortField'])) $sort['sortField'] = 'id';
		if ($sort['sortOrder'] == 0) $sort['sortOrder'] = "ASC"; else $sort['sortOrder'] = "DESC";
		$order = " ORDER BY ".$sort['sortField']." ".$sort['sortOrder'];

		return $order;
	}

	//    ///////////////////////////////////////////////////////////////////

	//     
	function generatePassword($length) {
		$min = $length;   //   
		$max = $length;   //   
		$pwd = ''; 		  // 

		for ($i = 0; $i < rand($min, $max); $i++) {
			 $num = rand(48, 122);
			 if (($num > 97 && $num < 122)) {
				 $pwd.= chr($num);
			 } else if (($num > 65 && $num < 90)) {
				 $pwd.= chr($num);
			 } else if (($num > 48 && $num < 57)) {
				 $pwd.= chr($num);
			 } else if ($num == 95) {
				 $pwd.= chr($num);
			 } else {
			 	$i--;
			 }
		}
		return $pwd;
	}

	//     
	function strUpper($str) {
		if (function_exists('mb_strtoupper')) {
			$str = mb_strtoupper($str, "UTF-8");
		} else {
		   	$str = strtoupper($str);
	    }
		return $str;
 	}

	//     
	function strLower($str) {
		if (function_exists('mb_strtolower')) {
			$str = mb_strtolower($str, "UTF-8");
		} else {
		   	$str = strtolower($str);
	    }
		return $str;
    }

	//    HTML ,    ,      HTML ,  
	// $tags -  html 
	// $special -    html
	// $slashes -  \  
	// $crop -      
	function safy_text(&$val, $tags, $crop, $special, $slashes) {
		if ($tags) $val = strip_tags($val);
		if ($crop > 0) $val = substr($val, 0, $crop);
		if ($special) $val = htmlspecialchars($val);
		if ($slashes) $val = addslashes($val);
		return $val;
	}

	//        safy_text

	function makeSafeText($object, $tags = true, $special = true, $slashes = false, $crop = 0) {
		if (is_array($object)) {
			foreach ($object as $key => $value) {
    			if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
							safy_text($value[$key2], $tags, $crop, $special, $slashes);
					}
				} else {
					safy_text($object[$key], $tags, $crop, $special, $slashes);
				}
			}
		} else {
			safy_text($object, $tags, $crop, $special, $slashes);
		}
		return $object;
	}

	

	//     
	function safe_string($text, $from_charset = 'UTF-8', $to_charset = 'CP1251') {
		$text = iconv($from_charset, $to_charset, $text);
		ereg("([0-9a-zA-Z�-��-� ]*)", $text, $regs);
		$text = iconv($to_charset, $from_charset, $regs[0]);
		return $text;
	}

	

	//     , 
	function strLeft($str, $length) {
		$str = mb_substr($str, 0, $length, "UTF-8");
		return $str;
	}

	

	//     , 
	function strRight($str, $length) {
		$str = mb_substr($str, strlen($str) - $length, $length, "UTF-8");
		return $str;
	}

	

	//  URL       
	function getUrl($queryString, $args = '') {
		$urlArray = parse_url($queryString);
		$url = $urlArray['query'];
		if (!empty($args)) $url.= "&".$args;
		return $url;
	}

	

	// UTF-8 substr()
	function usubstr($str, $start_pos, $lenght, $ver = PHP_VER) {
		if ($ver == 4) {
			///$str = iconv("UTF-8", "UTF-8//IGNORE", $str);
			//$str = iconv("UTF-8", "WINDOWS-1251", $str);
			//$str = substr($str, $start_pos, $lenght);
			//$str = iconv("WINDOWS-1251", "UTF-8", $str);

			$str = mb_substr($str, $start_pos, $lenght, "UTF-8");
		} else {
			$str = @iconv_substr($str, $start_pos, $lenght, "UTF-8");
		}
		return $str;
	}


	//   //////////////////////////////////////////////////////////////////////////////
	//     
	function trace() {
		global $_SERVER;

		$split = '<hr size="1" noshade color="#0071BC">';
		$template = '<table width="99%" cellpadding="0" cellspacing="1" border="0" bgcolor="#0071BC" align="center" style="margin-top: 4px; margin-bottom: 8px;">
					 <tr><td height="20" bgcolor="#C5E0EB" style="color: #0067AC; padding: 2px 2px 2px 2px;"><b>{CAPTION}</b></td></tr>
					 <tr><td bgcolor="#F4F4F4" style="padding: 2px 2px 2px 2px;">{REPORT}</td></tr>
					 </table>';

		$ip = $_SERVER['REMOTE_ADDR'];
		$report = '';
		$i = 0;

		if (CONFIG_DEVELOPER_IP == $ip || CONFIG_DEVELOPER_IP == 0) {
			if (func_num_args() > 0) {
				$arg_array = func_get_args();
				foreach($arg_array as $value) {
					$i++;
					$type = gettype($value);
					switch ($type) {
						case 'array': $type = 'array'; break;
						case 'string':
							if (is_numeric($value)) $type = 'integer'; else $type = 'string';
							break;
						case 'boolean':	
							$value = $value ? "true" : "false";
							$type = 'boolean';	break;
						case 'object'; $type = 'object'; break;
					}

					if (is_array($value)) {
						$out = print_r($value, true);
						$from = array("Array", "[", "]", "=>", "(", ")");
						$to = array("<font color=\"#177B2F\"><b>Array</b></font>", "<br><font color=\"#464646\"><b>[</b></font>", "<font color=\"#464646\"><b>]</b></font>", "<font color=\"#464646\"><b>=></b></font>", "<font color=\"#464646\"><b>(</b></font>", "<font color=\"#464646\"><b>)</b></font>");
						$out = str_replace($from, $to, $out);
						$report.= $out;
					} else {
			 			$report.= $value.' <small style="color: #0071BC">('.$type.')</small>';
					}

					if ($i < func_num_args()) $report.= $split;
				}

				$caption = 'Trace report ['.date("h:i d.m.y").']';
				$report = str_replace(array("{CAPTION}", "{REPORT}"), array($caption, $report), $template);
				echo $report;
			} else {
				echo 'This is trace message<br>';
			}
		}

	}



	//  
	function debug_error_handler($errno, $errmsg, $filename, $linenum, $vars) {
	    // timestamp for the error entry
	    $dt = date("Y-m-d H:i:s (T)");

	    // define an assoc array of error string
	    // in reality the only entries we should
	    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
	    // E_USER_WARNING and E_USER_NOTICE

	    $errortype = array (
	                E_ERROR           => "Error",
	                E_WARNING         => "Warning",
	                E_PARSE           => "Parsing Error",
	                E_NOTICE          => "Notice",
	                E_CORE_ERROR      => "Core Error",
	                E_CORE_WARNING    => "Core Warning",
	                E_COMPILE_ERROR   => "Compile Error",
	                E_COMPILE_WARNING => "Compile Warning",
	                E_USER_ERROR      => "User Error",
	                E_USER_WARNING    => "User Warning",
	                E_USER_NOTICE     => "User Notice");
	                //E_STRICT          => "Runtime Notice"

	    // set of errors for which a var trace will be saved
	  	$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

	    $err = "<errorentry>\n";
	    $err .= "\t<datetime>" . $dt . "</datetime>\n";
	    $err .= "\t<errornum>" . $errno . "</errornum>\n";
	    $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
	    $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
	    $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
	    $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

	    if (in_array($errno, $user_errors))
	        $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
	    $err .= "</errorentry>\n\n";

	    // for testing
	    // echo $err;
		trace($err);

	    // save to the error log, and e-mail me if there is a critical user error
	    //error_log($err, 3, "./error.log");
		//trace($err);

	    if ($errno == E_USER_ERROR) {
	        //mail("phpdev@example.com", "Critical User Error", $err);
	    }
	}



	//      
	function documentExists($query, $timeout = 30) {
		$url = parse_url($query);
		$host = $url["host"];
		$path = $url["path"];
		if (isset($url["query"])) $path.= "?".$url["query"];

		$fp = @fsockopen ($host, 80, $errno, $errstr, $timeout);

		if ($fp) {
			$out = "GET {$path} HTTP/1.1\r\n";
		    $out.= "Host: {$host}\r\n";
		    $out.= "Connection: Close\r\n\r\n";

		    fwrite($fp, $out);
			$ret = fgets($fp, 128);
		    fclose($fp);
			if (trim($ret) == "HTTP/1.1 200 OK") return true;
		}
		return false;
	}



	//  
	function getDocument($query, $use_socket = true) {
		$content = '';

		if ($use_socket) {
			$url = parse_url($query);
			$host = $url["host"];
			$path = $url["path"];
			if (isset($url["query"])) $path.= "?".$url["query"];
			$fp = fsockopen($url["host"], 80, $errno, $errstr, 30);
			if ($fp) {
				$is_data = false;
				$out = "GET {$path} HTTP/1.1\r\n";
			    $out.= "Host: {$host}\r\n";
			    $out.= "Connection: Close\r\n\r\n";

			    fwrite($fp, $out);
			   	while (!feof($fp)) {
		   	    	$line = fgets($fp, 1024);
					if ($is_data) $content.= $line;
					if (ord($line) == 13) $is_data = true;
			    }
		    	fclose($fp);
			}
		} else {
			$handle = @fopen($query, "rb");
			if ($handle) {
				while (!feof($handle)) {
				  $content.= @fread($handle, 1024);
				}
				fclose($handle);
			}
		}
		return $content;
	}


	// function sendMail($mail, $subject, $body, $sender_name = "", $sender_mail = "") {
	// 	$smtp_server = "88.204.132.130";
	// 	$port = 8825;
	// 	$mydomain = "importoils.kz";
	// 	$username = "shop@importoils.kz";
	// 	//$password = 'serv2003@))#';

	// 	$header = "Date: ".date("D, j M Y G:i:s")." +0700\r\n"; 
	// 	$header .="X-Mailer: The Bat! (v3.99.3) Professional\r\n"; 
	// 	$header .="X-Priority: 3 (Normal)\r\n";
	// 	$header .= "MIME-Version: 1.0\r\n";
	// 	$header .= "Content-Type: text/html; charset=utf8\r\n";
	// 	$header .= "Content-Transfer-Encoding: 8bit\r\n";

	// 	$handle = fsockopen($smtp_server, $port);
		
	// 	sleep(2);

	// 	fputs($handle, "EHLO $mydomain\r\n");

	// 	// fputs($handle, "AUTH LOGIN\r\n");
	// 	// fputs($handle, base64_encode($username)."\r\n");
	// 	// fputs($handle, base64_encode($password)."\r\n");

	// 	fputs($handle, "MAIL FROM: <".$username.">\r\n");
	// 	fputs($handle, "RCPT TO: <".$mail.">\r\n");
	// 	fputs($handle, "DATA\r\n");
	// 	fputs($handle, "Content-Type: text/plain; charset=UTF-8\r\n");
	// 	fputs($handle, "To: ".$mail."\r\n");
	// 	fputs($handle, "Subject: ".$subject."\r\n");
	// 	fputs($handle, $header."\r\n".$body."\r\n");
	// 	fputs($handle, ".\r\n");

	// 	fputs($handle, "QUIT\r\n");
	// }
	 
	function sendMail($mail, $subject, $body, $sender_name = "", $sender_mail = "", $mail_replay = "") {
		$headers = "MIME-Version: 1.0\r\n";
		$headers.= "Content-type: text/html; charset=windows-1251\n";
		//$headers.= "To: ".$address." <".$address.">\r\n";
		if ($sender_mail && $sender_name) $headers.= "From: ".$sender_name." <".$sender_mail.">\r\n";
		$headers .= "Reply-To: ".$mail_replay;

		$subject = mb_convert_encoding($subject, "WINDOWS-1251", "UTF-8");
		$body = mb_convert_encoding($body, "WINDOWS-1251", "UTF-8");
		$headers = mb_convert_encoding($headers, "WINDOWS-1251", "UTF-8");
		
		$result = mail($mail, $subject, stripslashes($body), $headers);
		
		return $result;
	}
	
	function isParentPageVisible($page_id) {
		$sql = "SELECT * FROM pages WHERE id = (SELECT parent_id FROM pages where id=".$page_id.") AND visible = 1 LIMIT 1";
		//$result = db_query("SELECT visible FROM pages WHERE parent_id = ".$page_id." AND visible = 1 LIMIT 1");
		$result = db_query($sql);
		
		if (db_num_rows($result) > 0) return true;
		
		return false;
	}
	
	function ClosePage($page_id) {
			$result = db_query("SELECT auth FROM pages WHERE id = ".$page_id." LIMIT 1");
			$row = db_fetch_array($result);
			if ($row['auth']=='1'){
					return true;
			}
			else{
					return false;
			}
	}
	
	       function AutorizedPageUser($page_id, $usrname, $usrpass) {
                if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrname)) $usrname = "";
                if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrpass)) $usrpass = "";
                $result = db_query("SELECT id, login, password, group_id FROM users WHERE login = '".$usrname."' AND password = '".md5($usrpass)."' LIMIT 1");
                if (db_num_rows($result) > 0){
                        $row = db_fetch_array($result);
                        if ($row['group_id']=='1'){
                                $priv['read']=true;
                                $priv['write']=true;
                                $priv['create']=true;
                        }
                        else{
                                $result2 = db_query("SELECT allow_read, allow_write, allow_create FROM users_pages_priv WHERE user_id = '".$row['id']."' AND page_id = '".$page_id."' LIMIT 1");
                                if(db_num_rows($result2) > 0){
                                        $row2 = db_fetch_array($result2);
                                        if($row2['allow_read']==1){
                                                $priv['read']=true;
                                        }
                                        else{
                                                $priv['read']=false;
                                        }
                                        if($row2['allow_write']==1){
                                                $priv['write']=true;
                                        }
                                        else{
                                                $priv['write']=false;
                                        }
                                        if($row2['allow_create']==1){
                                                $priv['create']=true;
                                        }
                                        else{
                                                $priv['create']=false;
                                        }
                                }
                                else{
                                        $priv['read']=false;
                                        $priv['write']=false;
                                        $priv['create']=false;
                                }
                        }
                }
                else{
                $priv['read']=false;
                        $priv['write']=false;
                        $priv['create']=false;
                }

                return $priv;
        }


		function AutorizedUser($usrname, $usrpass) {
                if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrname)) $usrname = "";
                if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrpass)) $usrpass = "";
                $result = db_query("SELECT id, login, password, group_id FROM users WHERE login = '".$usrname."' AND password = '".$usrpass."' LIMIT 1");
                if (db_num_rows($result) > 0){
                        return true;
                }
                else{
                        return false;
                }
        }

	   function fClean($inner)
	   {
		  $inner = htmlspecialchars(stripslashes(trim($inner)));
		  $inner=str_replace(array("%09","%20","%22","%2E","%3E","%3C","%25",":","/","@","'","-","*","..",'"',".",";","\\","https","http","ftp"),"",$inner);
		  return $inner;
	   }
	
	// ���������� ��������
	function resize_image($width, $height, $quality, $source, $destination, $ext, $type) { 
		$img_size = getimagesize($source);
	  	
		if ($type == true) $d = $img_size[0] / $width;
			else $d = max($img_size[0] / $width, $img_size[1] / $height);
		
		$result[] = round($img_size[0] / $d);
	  	$result[] = round($img_size[1] / $d);

		if ($ext == "jpg") $src = imagecreatefromjpeg($source); 
		if ($ext == "gif") $src = imagecreatefromgif($source); 
		if ($ext == "png") $src = imagecreatefrompng($source); 

      	$img = imagecreatetruecolor($result[0], $result[1]);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		
      	imagecopyresampled($img, $src, 0, 0, 0, 0, $result[0], $result[1], imagesx($src), imagesy($src));  
		
		if ($ext == "jpg") imagejpeg($img, $destination, $quality); 
		if ($ext == "gif") imagegif($img, $destination);
		if ($ext == "png") imagepng($img, $destination);
	}
	
	function resize_image2($width, $height, $quality, $source, $destination, $ext, $type) { 
		$img_size = getimagesize($source);
	  	
		if ($img_size[1] > $img_size[0]) $d = $img_size[1] / $height;
			else $d = $img_size[0] / $width;
		//if ($type == true) $d = $img_size[1] / $height;
		//	else $d = max($img_size[0] / $width, $img_size[1] / $height); 
		
		$result[] = round($img_size[0] / $d);
	  	$result[] = round($img_size[1] / $d);

		if ($ext == "jpg") $src = imagecreatefromjpeg($source); 
		if ($ext == "gif") $src = imagecreatefromgif($source); 
		if ($ext == "png") $src = imagecreatefrompng($source); 

      	$img = imagecreatetruecolor($result[0], $result[1]);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		
      	imagecopyresampled($img, $src, 0, 0, 0, 0, $result[0], $result[1], imagesx($src), imagesy($src));  
		
		if ($ext == "jpg") imagejpeg($img, $destination, $quality); 
		if ($ext == "gif") imagegif($img, $destination);
		if ($ext == "png") imagepng($img, $destination);
	}
	
	function showRatingBtn($user_id) {
		$str = '<span id="rating_'.$user_id.'"><img src="images/rating_minus.png" class="im8" onclick="rating_minus('.$user_id.')" />&nbsp;<img src="images/rating.png" class="im6" />&nbsp;<img src="images/rating_plus.png" class="im8" onclick="rating_plus('.$user_id.')" /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		return $str;
	}
	
	function showRatingOk() {
		$str = '<span><img src="images/rating.png" class="im6" />&nbsp;<img src="images/rating_ok.png" class="im6" /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		return $str;
	}	
	
	function showGroupBtn($user_id, $group_id) {
		$str = '';
		$result = db_query("SELECT id FROM group_users WHERE group_id = ".$group_id." AND user_id = ".$user_id);
		$count = db_num_rows($result);
		
		if ($count > 0) {
			$str = getval("STR_USERS_COUNT_TITLE").':&nbsp;<span id="count_con_'.$group_id.'">'.$count.'</span>';
		} else {
			$author_id = db_get_data("SELECT author_id FROM module_groups WHERE id = ".$group_id." LIMIT 1", "author_id");
			
			if ($author_id == $user_id) {
				$str = getval("STR_USERS_COUNT_TITLE").':&nbsp;<span id="count_con_'.$group_id.'">'.$count.'</span>';
			} else {
				$str = getval("STR_USERS_COUNT_TITLE").':&nbsp;<span id="count_con_'.$group_id.'">'.$count.'</span><span id="btn_con_'.$group_id.'">&nbsp;|&nbsp;<a href="javascript: void(0);" class="ln9" onclick="groupJoin('.$user_id.', '.$group_id.');">'.getval("STR_USERS_CONNECT_BTN_TITLE").'</a></span>';
			}
		}
		
		return $str;
	}
	
	function sendAttachMail($mail, $subject, $body, $sender_name = "", $sender_mail = "", $filename = "", $filename_str = "") {
		$subject = mb_convert_encoding($subject, "WINDOWS-1251", "UTF-8");
		$body = mb_convert_encoding($body, "WINDOWS-1251", "UTF-8");
		
		$f         = fopen($filename, "rb"); 
		$un        = strtoupper(uniqid(time())); 
		$head      = "From: ".$subject." <".$sender_mail.">\r\n"; 
		$head     .= "To: ".$mail." <".$mail.">\r\n"; 
		$head     .= "Subject: ".$subject."\r\n"; 
		$head     .= "X-Mailer: PHPMail Tool\r\n"; 
		$head     .= "Reply-To: ".$sender_name."\r\n"; 
		$head     .= "Mime-Version: 1.0\r\n"; 
		$head     .= "Content-Type:multipart/mixed;"; 
		$head     .= "boundary=\"----------".$un."\"\n\n"; 
		$zag       = "------------".$un."\nContent-Type:text/html;\n"; 
		$zag      .= "Content-Transfer-Encoding: 8bit\n\n".$body."\n\n"; 
		$zag      .= "------------".$un."\n"; 
		$zag      .= "Content-Type: application/octet-stream;"; 
		$zag      .= "name=\"".basename($filename_str)."\"\n"; 
		$zag      .= "Content-Transfer-Encoding:base64\n"; 
		$zag      .= "Content-Disposition:attachment;"; 
		$zag      .= "filename=\"".basename($filename_str)."\"\n\n"; 
		$zag      .= chunk_split(base64_encode(fread($f, filesize($filename))))."\n";
		
		$result = mail($mail, $subject, $zag, $head);
		return $result; 
	}
	
	function crypt_string($str, $encrypt = true) {
		if ($encrypt) {
			$crypt_str = base64_encode($str);
			$crypt_str = urlencode($crypt_str);
			return $crypt_str;
		} else {
			$str = urldecode($str);
			$crypt_str = base64_decode($str);
			return $crypt_str;
		}
		return 0;
	}

	function drawImage($source, $destination, $quality) {
		if ($source) {
			$copyright_image = './images/vodbig.png';
			
			$img_size = getimagesize($source);
			$img_size2 = getimagesize($copyright_image);
			$src = imagecreatefromjpeg($source);
			$src2 = imagecreatefrompng($copyright_image);
			$img = imagecreatetruecolor($img_size[0], $img_size[1]);
			$x = intval($img_size[0]) - intval($img_size2[0]);
			$y = intval($img_size[1]) - intval($img_size2[1]); 
			
			imagecopyresampled($img, $src, 0, 0, 0, 0, $img_size[0], $img_size[1], imagesx($src),imagesy($src));
			imagecopyresampled($img, $src2, $x, $y, 0, 0, $img_size[0], $img_size[1], imagesx($src),imagesy($src));  
	  		imageJPEG($img, $destination, $quality); 
		} 
		return $destination;
	}

	function getPages($page_id) {
		global $page_array;
		
		$result = db_query("SELECT id FROM pages WHERE parent_id = ".$page_id." AND lang_id = ".LANG_ID." AND visible = 1 AND deleted = 0");
		if (db_num_rows($result) > 0) {
			while ($row = db_fetch_array($result)) {
				$page_array[] .= $row['id'];
				
				getPages($row['id']);
			}
		}
	}

	function cleanDir($dir) {
        $files = glob($dir."/*");
        $c = count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {      
                if (file_exists($file)) {
                    unlink($file);
                }   
            }
        }
    }
?>
