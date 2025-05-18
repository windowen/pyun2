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
class once_controller extends common{
	function index_action(){
		if($this->config['sy_once_web']=="2"){
			$data['msg']	=	'很抱歉！该模块已关闭！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
		}
		
		$CacheM		=	$this->MODEL('cache');
        $CacheArr	=	$CacheM->GetCache(array('city'));
		$this->yunset($CacheArr);
		
		foreach($_GET as $k=>$v){
			if($k!=""){
				$searchurl[]	=	$k."=".$v;
			}
		}
		$searchurl=@implode("&",$searchurl);
		$this->yunset("searchurl",$searchurl);
		
		$onceM		=	$this->MODEL('once');
		
		$ip			=	fun_ip_get();
		$start_time	=	strtotime(date('Y-m-d 00:00:00')); //开始时间
		$mess		=	$onceM->getOnceNum(array('login_ip'=>$ip,'ctime'=>array('>',$start_time)));

		if($this->config['sy_once'] > 0){
			$num	=	$this->config['sy_once']-$mess;
		}else{
			$num	=	1;
		}
		$this->yunset("num",$num);
		$adtime	=	array("0"=>"不限","7"=>"一周以内","15"=>"半个月","30"=>"一个月","60"=>"两个月","180"=>"半年","365"=>"一年");
		$this->yunset("adtime",$adtime);
		$this->yunset('backurl', Url('wap'));
		$this->get_moblie();
		$this->seo("once");
		$this->yunset("topplaceholder","请输入招聘关键字,如：服务员...");
		$this->yunset("headertitle","店铺招聘");
		$this->yuntpl(array('wap/once'));
	}
	
	function add_action(){ 
		if($this->config['sy_once_web']=="2"){
			$data['msg']	=	'很抱歉！该模块已关闭！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
		}
 		$ip		=	fun_ip_get();
		$this->yunset("ip",$ip);
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
		$onceM	=	$this->MODEL('once');
		if((int)$_GET['id']){
			$id		=	(int)$_GET['id'];
			$row	=	$onceM->getOnceInfo(array('id'=>$id));
			if(!empty($row)){
			//检测当前密码是否对应
				session_start();
				
				if($row['password'] == $_SESSION['oncepass']){
					$this->yunset('row',$row);
				}else{
					
					header("Location:".Url('wap',array('c'=>'once','a'=>'show','id'=>$id)));
					exit();
				}
			}
		}else{
			if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!="" && $_COOKIE['fast']){
				//未付款订单
				$companyorderM	=	$this->MODEL('companyorder');
				$orderNum 		= 	$companyorderM->getCompanyOrderNum(array('order_state'=>1,'type'=>25,'fast'=>$_COOKIE['fast']));
				$this->yunset("num",$orderNum);
			}
		}
		
		if($_POST['submit']){
			$authcode	=	md5(strtolower($_POST['authcode']));
			
			if($_POST['edate']){
				$edate		=	strtotime("+".(int)$_POST['edate']." days");
			}
					
			$post		=   array(
				'title' 		=>  $_POST['title'],
				'companyname'	=>  $_POST['companyname'],
				'linkman'		=>	$_POST['linkman'],
				'phone' 		=>  $_POST['phone'],
				'provinceid' 	=>  $_POST['provinceid'],
				'cityid' 		=>  $_POST['cityid'],
				'three_cityid'	=>  $_POST['three_cityid'],
				'address' 		=>  $_POST['address'],
				'require' 		=>  $_POST['require'],
				'base'			=>	$_POST['preview'],
				'edate'			=>	$edate,
				'edate1'		=>	$_POST['edate'],
				'salary'		=>	$_POST['salary'],
				'password'		=>	$_POST['password'],
				'status' 		=>  $this->config['com_fast_status'],
				'ctime'			=>	time(),
				'did'			=>	$this->userdid ? $this->userdid : $this->config['did'],
				'login_ip'		=>	fun_ip_get()
			);
			$data		=	array(
				'id'				=>	(int)$_POST['id'],
				'post'				=>	$post,
				'authcode'			=>	$authcode,
				
				'verify_token'		=>	$_POST['verify_token'],
				'fast'				=>	$_COOKIE['fast'],
				'utype'				=>	'wap'
			);
			
			$return  	= 	$onceM  ->  addOnceInfo($data);
			
			echo json_encode($return);die;
		}
		
		$this->get_moblie();
		
		$this->yunset("headertitle","店铺招聘");
		$this->yunset("title","添加店铺招聘");
		$this->yuntpl(array('wap/once_add'));
	}
	function show_action(){
		if($this->config['sy_once_web']=="2"){
			$data['msg']	=	'很抱歉！该模块已关闭！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
		}
		
        $onceM	=	$this->MODEL('once');
		$id		=	(int)$_GET['id'];
		$onceM->upOnce(array('hits'=>array('+',1)),array('id'=>$id));
		
		$row	=	$onceM->getOnceInfo(array('id'=>$id));
		
		if($row['status']<1  && !$_GET['pay']){
			
			$data['msg']	=	'店铺正在审核！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
			
		}elseif($row['pay']=='1' && !$_GET['pay']){
			
			$data['msg']	=	'店铺招聘付费中！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
			
		}
		$this->yunset("row",$row);
		
		$data['once_job']	=	$row['title'];
		$data['once_name']	=	$row['companyname'];
		$description		=	$row['require_n'];
		$data['once_desc']	=	$this->GET_content_desc($description);
		$this->data			=	$data;
		$this->seo('once_show');
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
		$this->get_moblie();
		$this->yunset("headertitle","店铺招聘");
		$this->yuntpl(array('wap/once_show'));
	}
	
	function pay_action(){
		if($this->config['sy_once_web']=="2"){
			$data['msg']	=	'很抱歉！该模块已关闭！';
			$data['url']	=	'index.php';
			$this->yunset("layer",$data);
		}
		$onceM	=	$this->MODEL('once');
		$row	=	$onceM->getOnceInfo(array('id'=>(int)$_GET[id]));
		if($_GET['id']){
			
			if(!$row){
				$data['msg']	=	'店铺信息不存在！';
				$data['url']	=	Url('wap',array('c'=>'once'));
				$this->yunset("layer",$data);
			}
		}
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']	=	'1';
		}
		if($paytype){
			$this->yunset("paytype",$paytype);
		}
		
		$data['once_job']	=	$row['title'];
		$data['once_name']	=	$row['companyname'];
		$description		=	$row['require_n'];
		$data['once_desc']	=	$this->GET_content_desc($description);
		$this->data			=	$data;
     	$this->seo('once_show');
		
		$this->get_moblie();
		
		$this->yunset("headertitle","店铺招聘");
		$this->yuntpl(array('wap/once_pay'));
	}
	
	function getOrder_action(){
		
		if($_POST){
			$onceM	=	$this->MODEL('once');
			$data	=	array(
				'id'			=>	$_POST['id'],
				'did'			=>	$this->userdid,
				'pay_type'		=>	$_POST['paytype'],
				'once_price'	=>	$_POST['once_price'],
			);
			$return	=	$onceM->payOnce($data);
			if($return['id']){
				
				if($_POST['paytype']=='alipay'){
					
					
					$dingdan	=	$return['orderid'];
					$price		=	$this->config['once_pay_price'];
					$url		=	$this->config['sy_weburl'].'/api/wapalipay/alipayto_fast.php?dingdan='.$dingdan.'&dingdanname='.$dingdan.'&alimoney='.$price;
					
				}
				header('Location: '.$url);exit();
			}else{
				
				$data['msg']	=	'提交失败！！';
				$data['url']	=	Url('wap',array('c'=>'once'));
				$this->yunset("layer",$data);
			}
			
		}
	}
	
	function paylog_action(){
		$companyorderM	=	$this->MODEL('companyorder');
		$rows 			= 	$companyorderM->getList(array('order_state'=>1,'type'=>25,'fast'=>$_COOKIE['fast']));
		
		$this->yunset("rows",$rows);
			
		$this->yunset("headertitle","待付款店铺");
		$this->seo('once');
		$this->yuntpl(array('wap/once_paylog'));
	}
	
	function delpaylog_action(){
		$orderM	=	$this->MODEL('companyorder');
		$return	=	$orderM->del((int)$_GET['id'],array('utype'=>'once'));
		if($return['errcode']==9){
			$return['msg']='取消订单成功！';
		}else{
			$return['msg']='取消订单失败！';
		}
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	function ajax_action(){
		$onceM		=	$this->MODEL('once');
		$data		=	array(
			'code'		=>	$_POST['checkcode'],
			'id'		=>	(int)$_POST['id'],
			'password'	=>	$_POST['password'],
			'type'		=>	$_POST['operation_type'],
			'utype'		=>	'wap'
			
		);
		$return		=	$onceM -> setOncePassword($data);
		
		echo json_encode($return);die;
	}
}
?>