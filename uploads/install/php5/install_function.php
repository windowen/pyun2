<?php
/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
function show_view($content) {
	global $_K;
	show_header();
	
	echo $content;
	show_footer();
	exit();
}
function fun_url_ck() {
    if (preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) {
        header("Location: index.php");
        exit ();
    }
}
function check_dir_iswritable($dir_path){
    $is_writale=1;
    $file_hd=@fopen($dir_path.'/test.txt','w');
    if(!$file_hd){
		@fclose($file_hd);
		@unlink($dir_path.'/test.txt');
		$is_writale=0;
		return $is_writale;
    }
    @fclose($file_hd);
    @unlink($dir_path.'/test.txt');
    return $is_writale;
}
function show_msg($message,$url_forward) {
	global $_K;
	if($url_forward) {
		$_K['extrahead'] = '<meta http-equiv="refresh" content="1; url='.$url_forward.'">';
		$message = "<a href=\"$url_forward\">$message(跳转中...)</a>";
	}
	show_header();
	echo "<table>
	<tr><td>$message</td></tr>
	</table>";
	show_footer();
	exit();
}
function show_header() {
	global $_K, $_SC;
	$ver=KEKE_VERSION;
	$ver_time = KEKE_RELEASE;
	$nowarr = array($_GET['step'] => ' class="current"');
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>PHPYun.人才系统安装程序'.VERSION.'</title>
<link href="images/css.css" rel="stylesheet" type="text/css" />
<script src="../js/jquery-1.8.0.min.js"/></script>
<script src="js/form_and_validation.js"/></script>
</head>
<body>
<div class="centent">
<div class="wrap">
    <div class="head">
	<h1 class="logo_install">安装</h1>
	<div class="version">人才系统安装程序'.VERSION.'</div>
</div>';
}
function show_footer() {
	echo '<div class="footer">
		Powered by <a href="http://www.phpyun.com" style="color:blue;">PHPYun.人才系统</a> '.VERSION.' &copy;'.YEAR.'
	</div>
</div>
</div>
</body>
</html>';
}
function check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre) {
	if(!function_exists('mysql_connect')) {
		show_msg($lang['advice_mysql_connect'], 0);
	}
	if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
		$errno = mysql_errno();
		$error = mysql_error();
		if($errno == 1045) {
			show_msg($lang['database_errno_1045'], 0);
		} elseif($errno == 2003) {
			show_msg($lang['database_errno_2003'], 0);
		} else {
			show_msg($lang['database_connect_error'], 0);
		}
	} else {
		if($query = @mysql_query("SHOW TABLES FROM $dbname")) {
			while($row = mysql_fetch_row($query)) {
				if(preg_match("/^$tablepre/", $row[0])) {
					return false;
				}
			}
		}
	}
	return true;
}
function dirfile_check(&$dirfile_items) {
	foreach($dirfile_items as $key => $item) {
		$item_path = $item['path'];
		if($item['type'] == 'dir') {
			if(!dir_writeable(S_ROOT.$item_path)) {
				if(is_dir(S_ROOT.$item_path)) {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nodir';
				}
			} else {
				$dirfile_items[$key]['status'] = 1;
				$dirfile_items[$key]['current'] = '+r+w';
			}
		} else {
			if(file_exists(S_ROOT.$item_path)) {
				if(is_writable(S_ROOT.$item_path)) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				}
			} else {
				if(dir_writeable(dirname(S_ROOT.$item_path))) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nofile';
				}
			}
		}
	}
}
function env_check(&$env_items) {
	foreach($env_items as $key => $item) {
		if($key == 'php') {
			$env_items[$key]['current'] = PHP_VERSION;
		} elseif($key == 'attachmentupload') {
			$env_items[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
		} elseif($key == 'gdversion') {
			$tmp = function_exists('gd_info') ? gd_info() : array();
			$env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
			unset($tmp);
		} elseif($key == 'diskspace') {
			if(function_exists('disk_free_space')) {
				$env_items[$key]['current'] = floor(disk_free_space(S_ROOT) / (1024*1024)).'M';
			} else {
				$env_items[$key]['current'] = 'unknow';
			}
		} elseif(isset($item['c'])) {
			$env_items[$key]['current'] = constant($item['c']);
		}
		$env_items[$key]['status'] = 1;
		if($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
			$env_items[$key]['status'] = 0;
		}
	}
}
function function_check(&$func_items) {
	global $lang;
	foreach($func_items as $item) {
		function_exists($item) or show_msg($lang["advice_".$item], 0);
	}
}
if(!function_exists('file_put_contents')) {
	function file_put_contents($filename, $s) {
		$fp = @fopen($filename, 'w');
		@fwrite($fp, $s);
		@fclose($fp);
		return TRUE;
	}
}
function dir_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}
function dir_clear($dir) {
	global $lang;
	showjsmessage($lang['clear_dir'].' '.str_replace(S_ROOT, '', $dir));
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}
function runquery($sql) {
	global $lang, $tablepre, $db;

	if(!isset($sql) || empty($sql)) return;
    $orig_tablepre = "keke_";
	$sql = str_replace("\r", "\n", str_replace(' '.$orig_tablepre, ' '.$tablepre, $sql));
	$sql = str_replace("\r", "\n", str_replace(' `'.$orig_tablepre, ' `'.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				$db->query(createtable($query));
			} else {
				$db->query($query);
			}

		}
	}
}
function createtable($sql) {

	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=".DBCHARSET : " TYPE=$type");
}
function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
function show_error($error_no, $error_msg = 'ok', $success = 1, $quit = TRUE) {

	if(KEKE_OFF) {
		$error_code = $success ? 0 : constant(strtoupper($error_no));
		$error_msg = empty($error_msg) ? $error_no : $error_msg;
		$error_msg = str_replace('"', '\"', $error_msg);
		$str = "<root>\n";
		$str .= "\t<error errorCode=\"$error_code\" errorMessage=\"$error_msg\" />\n";
		$str .= "</root>";
		echo $str;
		exit;
	} else {
		show_header();
		global $step;$_K;

		$title = keke_lang($error_no);
		$comment = keke_lang($error_no.'_comment', false);
		$errormsg = '';

		if($error_msg) {
			if(!empty($error_msg)) {
				foreach ((array)$error_msg as $k => $v) {
					if(is_numeric($k)) {
						$comment .= "<li><em class=\"red\">".keke_lang($v)."</em></li>";
					}
				}
			}
		}

		if($step) {
			echo "<div class=\"desc\"><b>$title</b><ul>$comment</ul>";
		} else {
			echo "</div><div class=\"main\" style=\"margin-top: 20px;\"><b>$title</b><ul style=\"line-height: 200%; margin-left: 30px;\">$comment</ul>";
		}

		if($quit) {
			echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="red">'.keke_lang('error_quit_msg').'</span><br /><br /><br />';
		}

		echo '<center><input type="button" onclick="history.back()" value="'.keke_lang('click_to_back').'" /><center><br /><br /><br />';

		echo '</div>';

		$quit && show_footer();
	}
}
function show_env_result(&$env_items, &$dirfile_items, &$func_items) {
	$env_str = $file_str = $dir_str = $func_str = '';
	$error_code = 0;

	foreach($env_items as $key => $item) {
		if($key == 'php' && (strcmp($item['current'], $item['r']) < 0 || $item['r']<5.4)) {
			
			$status = 0;
			$error_code = ENV_ERROR;
			$item['r'] = "<font color='red'>".$item['r']."（版本过低）</font>";

		}
		$status = 1;
		if($item['r'] != 'notset') {
			if(intval($item['current']) && intval($item['r'])) {
				if(intval($item['current']) < intval($item['r'])) {
					$status = 0;
					$error_code = ENV_ERROR;
				}
			} else {
				if(strcmp($item['current'], $item['r']) < 0) {
					$status = 0;
					$error_code = ENV_ERROR;
				}
			}
		}
		if(KEKE_OFF) {
			$env_str .= "\t\t<runCondition name=\"$key\" status=\"$status\" Require=\"$item[r]\" Best=\"$item[b]\" Current=\"$item[current]\"/>\n";
		} else {
			$env_str .= "<tr>\n";
			$env_str .= "<td>".keke_lang($key)."</td>\n";
			$env_str .= "<td class=\"padleft\">".keke_lang($item['r'])."</td>\n";
			$env_str .= "<td class=\"padleft\">".keke_lang($item['b'])."</td>\n";
			$env_str .= "</tr>\n";
		}
	}
	foreach($dirfile_items as $key => $item) {
		$tagname = $item['type'] == 'file' ? 'File' : 'Dir';
		$variable = $item['type'].'_str';

		if(KEKE_OFF) {
			if($item['status'] == 0) {
				$error_code = ENV_ERROR;
			}
			$$variable .= "\t\t\t<File name=\"$item[path]\" status=\"$item[status]\" requirePermisson=\"+r+w\" currentPermisson=\"$item[current]\" />\n";
		} else {
			$$variable .= "<tr>\n";
			$$variable .= "<td>$item[path]</td>";
            if(!is_dir($item[path])){
                $$variable .= "<td class=\"nw pdleft1\">".keke_lang('nodir')."</td>\n";
                $error_code=2;
            }else{
                if(check_dir_iswritable($item[path])){
                    $$variable .= "<td class=\"w pdleft1\">".keke_lang('writeable')."</td>\n";
                }else{
                    $$variable .= "<td class=\"nw pdleft1\">".keke_lang('unwriteable')."</td>\n";
                    $error_code=2;
                }
            }
			$$variable .= "<td class=\"w pdleft1\">".keke_lang('writeable')."</td>\n";
			$$variable .= "</tr>\n";
		}
	}

	if(KEKE_OFF) {
		$str = "<root>\n";
		$str .= "\t<runConditions>\n";
		$str .= $env_str;
		$str .= "\t</runConditions>\n";
		$str .= "\t<FileDirs>\n";
		$str .= "\t\t<Dirs>\n";
		$str .= $dir_str;
		$str .= "\t\t</Dirs>\n";
		$str .= "\t\t<Files>\n";
		$str .= $file_str;
		$str .= "\t\t</Files>\n";
		$str .= "\t</FileDirs>\n";
		$str .= "\t<error errorCode=\"$error_code\" errorMessage=\"\" />\n";
		$str .= "</root>";
		echo $str;
		exit;

	} else {
		show_header();
		echo '<div class="step"><ul><li class="current"><em><strong>1</strong></em>检测环境</li><li><em>2</em>创建数据</li><li><em>3</em>完成安装</li></ul><div class="server">';

		echo '<table width="100%"><tbody><tr><td class="td1"width="161px">环境检测</td><td class="td1" width="161px">当前服务器</td><td class="td1" width="161px">PHPYUN. 最佳</td></tr>';
        echo $env_str;
        echo '</tbody></table>';


		echo '<table width="100%"><tbody><tr><td class="td1">目录、文件权限检查</td><td class="td1" width="25%">当前状态</td><td class="td1" width="25%">所需状态</td></tr>';
		echo $file_str;
		echo $dir_str;
		echo '</tbody></table>';


		echo '<table width="100%"><tbody><tr><td class="td1">函数名称</td><td class="td1" width="25%">检查结果</td><td class="td1" width="25%">建议</td></tr>';

		foreach($func_items as $item) {
			$status = function_exists($item);
			$func_str .= "<tr>\n";
			$func_str .= "<td>$item()</td>\n";
			if($status) {
				$func_str .= "<td class=\"w pdleft1\">".keke_lang('supportted')."</td>\n";
				$func_str .= "<td class=\"padleft\">".keke_lang('supportted')."</td>\n";
			} else {
				$error_code = ENV_ERROR;
				$func_str .= "<td class=\"nw pdleft1\">".keke_lang('unsupportted')."</td>\n";
				$func_str .= "<td><font color=\"red\">".keke_lang('advice_'.$item)."</font></td>\n";
			}
		}
		echo $func_str;
		echo '</tbody></table>';
		echo '</div></div><div class="bottom">';
        echo '<input type="button" value="重新检测" onclick="location.href=\'index.php?step=checkset\'" class="submit">';
        if($error_code==2){
            echo '<input type="button" value="检查出错，安装无法继续" onclick="window.close();" class="submit_az">';
		}else{
            echo '<input type="button" value="下一步" onclick="location.href=\'index.php?step=sql\'" class="submit">';
		}
        echo '</div>';
		show_footer();
	}
}
function show_sql_result(){
	show_header();
	echo '
<div class="step">
    <ul>
        <li class="on">
            <em>
                1
            </em>
            检测环境
        </li>
        <li class="current">
            <em>
                2
            </em>
            创建数据
        </li>
        <li class="on">
            <em>
                3
            </em>
            完成安装
        </li>
    </ul>
    <div class="server_1" style="overflow:auto;" id="kays">
    </div>
</div>
<div class="bottom">
    <input type="submit" value="正在安装..." name="submit" style="border:none; background:url(images/an.jpg) no-repeat;width:95px;height:25px;color:#fff;font-weight:bold;">
</div>';
    show_footer();

}
function show_tips($tip, $title = '', $comment = '', $style = 1) {
	global $lang;
	$title = empty($title) ? keke_lang($tip) : $title;
	$comment = empty($comment) ? keke_lang($tip.'_comment', FALSE) : $comment;
	if($style) {
		echo "<div class=\"desc\"><b>$title</b>";
	} else {
		echo "</div><div class=\"main\" style=\"margin-top: -123px;\">$title<div class=\"desc1 marginbot\"><ul>";
	}
	$comment && print('<br>'.$comment);
	echo "</div>";
}
function keke_lang($lang_key, $force = true) {
	return isset($GLOBALS['lang'][$lang_key]) ? $GLOBALS['lang'][$lang_key] : ($force ? $lang_key : '');
}