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
class services_controller extends common{
	function index_action(){
		$this->get_moblie();
		$M=$this->MODEL('article');
		$row=$M->GetDescriptionOnce(array('id'=>'5'),array('field'=>'content'));
		$this->yunset("row",$row);
		$this->yunset("headertitle","服务协议"); 
		$this->yuntpl(array('wap/services'));
	}	
}
?>