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
 
global $db_config,$db;

$count = $db->select_num("company_job","`autotime`>='".strtotime(date('Y-m-d'))."'");
$size = 1000; 

$num = ceil($count/$size); 
for($i=0;$i<$num;$i++){
	$offset = $i * $size;
	$autoList = $db->select_all("company_job","`autotime`>='".strtotime(date('Y-m-d'))."' limit {$offset},{$size} ","`id`");

	$jobId = array();
	$SqlCase = 'lastupdate = CASE id ';
	foreach($autoList as $key=>$value){
		$jobId[] = $value['id'];
		$LastTime = strtotime('-'.rand(1,59).' minutes', time());

		$SqlCase .= sprintf("WHEN %d THEN %d ", $value['id'], $LastTime); 

	}
	$SqlCase .= 'END';

	$nid = $db->update_all("company_job",$SqlCase,"`id` IN (".@implode(',',$jobId).")");
}

?>