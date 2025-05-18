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
class cron_controller extends adminCommon{
	function public_act(){
		//$arrweek[]="不选";
		$arrweek[]="周一";
		$arrweek[]="周二";
		$arrweek[]="周三";
		$arrweek[]="周四";
		$arrweek[]="周五";
		$arrweek[]="周六";
		$arrweek[]="周日";
		//$montharr[]="不选";
		for($i=1;$i<=31;$i++){
			$montharr[]=$i."日";
		}
		//$hourarr[]="不选";
		for($i=0;$i<=23;$i++){
			$hourarr[]=$i."时";
		}
		$this->yunset("hourarr",$hourarr);
		$this->yunset("montharr",$montharr);
		$this->yunset("arrweek",$arrweek);
	}
	function index_action(){
		
		$rows	=	$this->MODEL('cron')->getList();
		
		$this->yunset("rows",$rows);
		
		$this->yuntpl(array('admin/admin_cron_list'));
	}
	function add_action(){
		$this->public_act();
		
		if($_GET["id"]){
			
			$row	=	$this->MODEL('cron')->getInfo(array('id'=>$_GET["id"]));
			
			$this->yunset("row",$row);
		}
		$this->yuntpl(array('admin/admin_cron_add'));
	}
	function save_action(){

		//添加
		if($_POST['msgconfig']){
			$CronM	=	$this->MODEL('cron');
			
			$return	=	$CronM->upaddInfo($_POST);
			
			if($return['errcode']=='9'){
				
				$this->croncache();
				
				$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=cron");
				
			}else{
				
				$this->ACT_layer_msg($return['msg'],$return['errcode']);
				
			}
		}
	}
	
	function del_action(){
		
		$this->check_token();
		
		if($_GET["id"]){
			
			$CronM	=	$this->MODEL('cron');
			
			$return	=	$CronM->delInfo($_GET["id"]);
			
			$this->croncache();
			
			$this->layer_msg($return['msg'],$return['errcode'],0,"index.php?m=cron");
		}
	}
	
	//生成计划任务缓存文件
	function croncache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$cacheclass->cron_cache("cron.cache.php");
	}

	function run_action(){
		if($_GET['id'])
		{
			include PLUS_PATH.'cron.cache.php';
			$CronM=$this->MODEL('cron');
			$CronM->excron($cron,$_GET['id']);
		}

		$this->layer_msg('计划任务(ID:'.$_GET["id"].')执行成功！',9,0,"index.php?m=cron");
	}


}

?>