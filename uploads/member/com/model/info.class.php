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
class info_controller extends company{
	function index_action(){
		$companyM	=	$this->MODEL('company');
		
		//获取1-基本信息、 2-手机认证、 3-证书认证 
		$row		=	$companyM->getInfo($this->uid,array('info'=>'1', 'edit'=>'1','logo'=>'1','utype'=>'user'));
		if(!$row['uid']){

			//获取注册信息
			$userinfoM	=   $this->MODEL("userinfo");
			$row		=	$userinfoM -> getInfo(array('uid'=>$this -> uid),array('field'=>'`moblie` as linktel,`email` as linkmail,`moblie_status`,`email_status`'));
			
		}
		$this->yunset("row",$row);
		$this->yunset("tel",$row['tel']);
		
		$this->yunset("cert",$row['cert']);
		
		$this->public_action();
		$this->company_satic();
		$this->yunset($this->MODEL('cache')->GetCache(array('com','city','job','hy')));
 
		$this->com_tpl('info');
	}
	
	function save_action(){
		
		$companyM  =  $this->MODEL('company');
		
		$company    =   $this -> comInfo['info'];
		 
		if($company){
			$rstaus     =   $company['r_status'];
		}else{
			$rstaus		=	$this->config['com_status'];
		}


		$mData     =  array(
		    'moblie'        =>  $_POST['linktel'],
		    'email'         =>  $_POST['linkmail']
		);
		
		$setData   =  array(
			'name' 			=> 	$_POST['name'], 
			'shortname' 	=> 	$_POST['shortname'], 
			'hy' 			=> 	$_POST['hy'], 
			'pr' 			=> 	$_POST['pr'], 
			'mun' 			=> 	$_POST['mun'], 
			'provinceid' 	=>	$_POST['provinceid'], 
			'cityid' 		=> 	$_POST['cityid'],
			'three_cityid' 	=> 	$_POST['three_cityid'], 
		    'address' 		=>	$_POST['address'],
		    'x' 		    =>	$_POST['x'],
		    'y' 		    =>	$_POST['y'],
		    'linkman'		=> 	$_POST['linkman'], 
			'linktel'		=>	$_POST['linktel'], 
			'linkphone' 	=> 	$_POST['linkphone'], 
			'linkmail' 		=> 	$_POST['linkmail'], 
			'sdate' 		=> 	$_POST['sdate'], 
			'moneytype' 	=> 	$_POST['moneytype'], 
			'money' 		=>	$_POST['money'], 
			'linkjob' 		=> 	$_POST['linkjob'], 
			'linkqq' 		=> 	$_POST['linkqq'], 
			'website' 		=> 	$_POST['website'], 
			'busstops' 		=> 	$_POST['busstops'], 
			'infostatus' 	=> 	$_POST['infostatus'], 
			'welfare' 		=> 	$_POST['welfare'], 
			'r_status' 		=> 	$rstaus,
			'rating'		=>	$company['rating'],
			'lastupdate'	=>  time(),
			'content'		=> 	str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content'])
		);

		if(!$this -> comInfo['info']['uid']){
			$userinfoM    =   $this->MODEL("userinfo");
			$userinfoM -> activUser($this -> uid,2);
		}
		 
		$return   =   $companyM -> setCompany(array('uid' => $this -> uid), array('mData' => $mData, 'comData' => $setData, 'utype' => 'user'));  
		 
		echo json_encode($return);die;
	}
	
	/**
	 * @desc 查询企业名称和手机号码是否使用
	 */
	function ajaxCheck_action(){
	    if($_POST){
	        
	        $comM      =   $this->MODEL('company');
	        
	        $typeStr   =   trim($_POST['typeStr']);
	        $checkStr  =   trim($_POST['checkStr']);
	        
	        $return    =   $comM -> getCheckUsed($this->uid, array('typeStr' => $typeStr, 'checkStr' => $checkStr));
	        
	        echo json_encode($return);die;
	    }
	}
}
?>