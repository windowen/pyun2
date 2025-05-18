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
class index_controller extends common{
	function index_action(){
     if($this->config['province']){
			$_GET['provinceid'] 	= 	$this->config['province'];
		}
		if($this->config['cityid']){
		    $_GET['cityid'] 		= 	$this->config['cityid'];
		}
		if($this->config['three_cityid']){
		    $_GET['three_cityid'] 	= 	$this->config['three_cityid'];
		}
		if($_GET['city']){//城市匹配
			$city					=	explode("_",$_GET['city']);
			$_GET['provinceid']		=	$city[0];
			$_GET['cityid']			=	$city[1];
			$_GET['three_cityid']	=	$city[2];
		}
		
		$FinderParams	=	array('city','provinceid','cityid','three_cityid','exp','sex','add_time','keyword');
		foreach($_GET as $k=>$v){
			if(in_array($k,$FinderParams) && $v!="" && $v!="0"){ 
				$finder[$k]	=	$v; 
			}
		}
		
		unset($finder['city']);
		
		$this->yunset('finder',$finder);
		
		session_start(); 
		
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('user','city'));
		$this->yunset($CacheList);
		
		if($this->config['sy_tiny_web']=="2"){
			header("location:".Url('error'));
		}
		
		if($_GET['keyword']=='请输入普工简历的关键字'){
			$_GET['keyword']	=	'';
		}
		
		$tinyM		=	$this->MODEL('tiny');
		$ip			=	fun_ip_get();
		$this->yunset("ip",$ip);
		
		$s_time		=	strtotime(date('Y-m-d 00:00:00')); //今天开始时间
		$m_tiny		=	$tinyM->getResumeTinyNum(array('login_ip'=>$ip,'time'=>array('>',$s_time)));
		
		if($this->config['sy_tiny']>0){
			$num	=	$this->config['sy_tiny']-$m_tiny;
		}else{
			$num	=	1;
		}

        $this->yunset("num",$num);
		
		$add_time	=	array("0"=>"不限","7"=>"一周以内","15"=>"半个月","30"=>"一个月","60"=>"两个月","180"=>"半年","365"=>"一年");
		$this->yunset("add_time",$add_time); 
		
		$this->seo("tiny");
		$this->yun_tpl(array('index'));
	}

	function ajax_action(){
		$tinyM		=	$this->MODEL('tiny');
		$data		=	array(
			'code'		=>	$_POST['code'],
			'id'		=>	(int)$_POST['tid'],
			'password'	=>	$_POST['pw'],
			'type'		=>	$_POST['type'],
			'utype'		=>	'pc'
			
		);
		$return		=	$tinyM->setResumeTinyPassword($data);
		echo json_encode($return);die;
	}
	function show_action(){
		$tinyM		=	$this->MODEL('tiny');
			
		if(isset($_GET['id'])){
			$id			=	(int)$_GET['id'];
		
			$tinyM->upResumeTiny(array('hits'=>array('+',1)),array('id'=>$id));
			
			$t_info		=	$tinyM->getResumeTinyInfo(array('id'=>$id));
		}

		if($t_info['id']){

			$this->yunset('t_info',$t_info);
			
			$ip 		= 	fun_ip_get();
			$this->yunset('ip',$ip);
			
			$s_time		=	strtotime(date('Y-m-d 00:00:00')); //今天开始时间
			$m_tiny		=	$tinyM->getResumeTinyNum(array('login_ip'=>$ip,'time'=>array('>',$s_time)));
			if($this->config['sy_tiny']>0){
				$num	=	$this->config['sy_tiny']-$m_tiny;
			}else{
				$num	=	1;
			}
			$this->yunset("num",$num);
			
			$data['tiny_username']	=	$t_info['username'];
			$data['tiny_job']		=	$t_info['job'];
			$data['tiny_desc']		=	$t_info['production'];
			$this->data				=	$data;
			$this->seo('tiny_cont');
			
 			$this->yun_tpl(array('show'));
		}else{
			$this->ACT_msg($this->config['sy_weburl'],"没有找到该简历！");
		}
		
	} 
	function add_action(){
		
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('user','city'));
		$this->yunset($CacheList);

		$tinyM	=	$this->MODEL('tiny');
		
        $info	=	$tinyM->getResumeTinyInfo(array('id'=>intval($_GET['id'])),array('cache'=>1));
		
		if(!empty($info)){
			//检测当前密码是否对应
			session_start();
			
			if($info['password'] == $_SESSION['tinypass']){
				$this->yunset('info',$info);
				$this->yunset($info['cache']);
			}else{
				
				header("Location:".Url('tiny',array('c'=>'show','id'=>(int)$_GET['id'])));
				exit();
			}
		}
		
		$this->seo("tiny");
		$this->yun_tpl(array('add'));
	}
	function save_action(){
		$tinyM		= 	$this ->  MODEL('tiny');
		$authcode	=	md5(strtolower($_POST['authcode']));

		$post		=   array(
			'username' 		=>  $_POST['username'],
			'sex' 			=>  $_POST['sex'],
			'exp' 			=>  $_POST['exp'],
			'job' 			=>  $_POST['job'],
			'mobile' 		=>  $_POST['mobile'],
			'production' 	=>  $_POST['production'],
			'password'		=>	$_POST['password'],
			'provinceid' 	=>  $_POST['provinceid'],
			'cityid' 		=>  $_POST['cityid'],
			'three_cityid'	=>  $_POST['three_cityid'],
			'status' 		=>  $this->config['user_wjl'],
			'login_ip'		=>	fun_ip_get(),
			'time'			=>	time(),
			'lastupdate'	=>	time(),
			'did'			=>	$this->userdid,
		);
		$data		=	array(
			'id'				=>	(int)$_POST['id'],
			'post'				=>	$post,
			'authcode'			=>	$authcode,
			
			'verify_token'		=>	$_POST['verify_token'],
		);
					
		$return  	= 	$tinyM  ->  addResumeTinyInfo($data);

		if($return['url']){
			$this->ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
		}else{
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
		}
	}
}
?>
