<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class map_controller extends company
{
	function index_action(){
		$companyM	=	$this->MODEL('company');
		
		$row		=	$companyM->getInfo($this->uid,'',array('field'=>'`x`,`y`,`address`,`provinceid`,`cityid`,`three_cityid`'));

		$this->yunset("row",$row);
		$this->public_action();
		$this->company_satic();
		$this->yunset($this->MODEL('cache')->GetCache(array('city')));
		$this->com_tpl('map');
	}
	
	function save_action(){
		$companyM	=	$this->MODEL('company');
		
		$data		=	array(
			'xvalue'	=>	$_POST['xvalue'],
			'yvalue'	=>	$_POST['yvalue']
		);
		//容错机制，在未生成企业身份时提前营业执照认证会出错
		if(!$this -> comInfo['info']['uid']){
			$userinfoM    =   $this->MODEL("userinfo");
			$userinfoM -> activUser($this->uid,2);
		}
		$return		=	$companyM->setMap($this->uid,$data);
		
		if ($_POST['type']=='info'){
			
			if($return['id']){
				echo 1;die;
			}else{
				echo 0;die;
			}
			
		}else{
			
			$this->ACT_layer_msg($return['msg'],$return['cod'],$return['url']);
			
		}
	}
}
?>