<?php
/*
 * 分城市调用
 * ----------------------------------------------------------------------------
 *
 *
 * ============================================================================
*/
function smarty_function_fz_list($paramer,&$smarty){
	global $db,$db_config;
	include(PLUS_PATH."domain_cache.php");
	include(PLUS_PATH."config.php");
	$domainarr=array();
	foreach($site_domain as $k=>$v){
		if($v['fz_type']=='1'){
			if($v['three_cityid']>0){
				$cityid=$v['three_cityid'];
			}elseif($v['cityid']>0){
				$cityid=$v['cityid'];
			}else{
				$cityid=$v['province'];
			}
			
			if($v['mode']=='2'){
				$indexdir[$cityid] = $v['indexdir'];
				$domainarr[$cityid]=$config['sy_weburl'].'/'.$v['indexdir'].'/';
			}else{
				$domainarr[$cityid]='http://'.$v['host'];
			}
			
			$city_id[]=$cityid;
		}
	}
	$city_ids=implode(",",$city_id);
	$sitecity=$db->select_all("city_class","`id` in(".$city_ids.")","`id`,`name`,`letter`");
	$city_ABC = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	foreach($city_ABC as $key=>$val){
		foreach($sitecity as $v){
			if($val==$v['letter']){
				$v['indexdir']=$indexdir[$v['id']];
				$v['url']=$domainarr[$v['id']];
				$site[$val][]= $v;
			}
		}
	}
	$smarty->assign("$paramer[fz_list]",$site);
}
?>