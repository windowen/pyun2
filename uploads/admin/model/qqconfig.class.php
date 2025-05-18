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
class qqconfig_controller extends adminCommon{
	function index_action()
	{
		$this->yunset("config",$this->config);
		$this->yuntpl(array('admin/admin_qq_config'));
	}

	//保存
	function save_action()
	{
 		if($_POST['config']){
			unset($_POST['config']);
			
			$this -> MODEL('config') -> setConfig($_POST);
			
			$this->web_config();
			
			$this->ACT_layer_msg("快捷登录参数配置修改成功！",9,1,2,1);
		}
	}
}

?>