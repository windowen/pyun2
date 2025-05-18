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
class admin_tiny_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		$cacheM   	=   $this ->  MODEL('cache');
		$cache		=	$cacheM->GetCache(array('user'));
		foreach($cache['userdata']['user_word'] as $k=>$v){
			
		  $ltar[$v]	=	$cache['userclass_name'][$v];
		}
		
		$search_list[]	=	array("param"=>"sex","name"=>'用户性别',"value"=>$cache['user_sex']);
		$search_list[]	=	array("param"=>"exp","name"=>'工作年限',"value"=>$ltar);
		$search_list[]	=	array("param"=>"status","name"=>'审核状态',"value"=>array("1"=>"已审核","2"=>"未审核"));
		$search_list[]	=	array("param"=>"time","name"=>'发布时间',"value"=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
		
		$this->yunset("search_list",$search_list);
	}
  
	function index_action(){
		
		$this->set_search();
    
		$tinyM   =   $this ->  MODEL('tiny');

		if($_GET['keyword']){
		  
			$keytype  =   intval($_GET['type']);
			$keyword  =   trim($_GET['keyword']);
			
			if($keytype       ==  1){
			  
			   $where['username'] = array('like',$keyword);
			}elseif($keytype  ==  2){
				
				$where['job'] = array('like',$keyword);
			}elseif($keytype  ==  3){
			  
				$where['mobile'] = array('like',$keyword);
			}elseif($keytype  ==  4){
			  
				$where['qq'] = array('like',$keyword);
			}
			$urlarr['keytype']	=  $keytype;
			$urlarr['keyword']	=  $keyword;
			
		} 

		if($_GET['sex']){
			
			$where['sex']   =   intval($_GET['sex']);
			$urlarr['sex']  =   intval($_GET['sex']);
        
		}
		if($_GET['time']){
      
			if($_GET['time']=='1'){
        
				$where['time']    =  array('>=',strtotime('today'));
			}else{
        
				$where['ctime']   =  array('>=',strtotime('-'.intval($_GET['time']).' day'));
			}
			$urlarr['time']=$_GET['time'];
		}
		if($_GET['exp']){
			
			$where['exp']   =   intval($_GET['exp']);
			$urlarr['sex']  =   intval($_GET['exp']);

		}
		if($_GET['status']){
			
			$where['status']  	=  intval($_GET['status']) == 2 ? 0 : intval($_GET['status']);
			$urlarr['status']   =  intval($_GET['status']);
      
		}
		
		$urlarr['page'] = '{{page}}';
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('resume_tiny',$where,$pageurl,$_GET['page']);
		
		if($pages['total']  > 0){
		  
			if($_GET['order']){
			 
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];
			 
			}else{
			  
				$where['orderby']	=	array('status,asc','id,desc');
			}
			$where['limit']   		=   $pages['limit'];
			
			$List   			=   $tinyM -> getResumeTinyList($where);
			
			$this -> yunset(array('rows'=>$List['list']));
		}
    
		$cacheM		=	$this -> MODEL('cache');
    
		$domain		=	$cacheM	-> GetCache('domain');
	    
		$this -> yunset('Dname', $domain['Dname']);
		
		$this->yuntpl(array('admin/admin_tiny'));
	}
	//普工简历
	function show_action(){
	
		$tinyM   	=   $this ->  MODEL('tiny');
		if($_GET['id']){
			$id   	=   intval($_GET['id']);
			$return	=	$tinyM ->  getResumeTinyInfo(array('id'=>$id));
			
			$this->yunset('rows',$return);
		}
		
		$this->yuntpl(array('admin/admin_tiny_show'));
	}
	function status_action(){//审核
    
		$tinyM		=   $this ->   MODEL('tiny');
		$status		=	intval($_POST['status']);
		$id			=	@explode(',',$_POST['allid']);
		
		$nid		=	$tinyM  -> setResumeTinyStatus($id,array('status'=>$status));
		if($nid){
			echo $status;die;
		}
	}
	function ajax_action(){
    
		$tinyM    =   $this -> MODEL('tiny');
		$id       =   intval($_GET['id']);
		$info     =   $tinyM  -> getResumeTinyInfo(array('id'=>$id));
		echo json_encode($info);
	}
	
	function del_action(){
		
		$this->check_token();

		$tinyM  = 	$this ->MODEL('tiny');
		$id		=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
		
		$return	=	$tinyM -> delResumeTiny($id);
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	
	function edit_action(){
		
		if($_GET['id']){
			$tinyM  	= 	$this ->MODEL('tiny');
			$id   		=   intval($_GET['id']);
			$return 	=   $tinyM -> getResumeTinyInfo(array('id'=>$id),array('cache'=>'1'));
			
			$this->yunset('row',$return);
			$cacheM		=	$this -> MODEL('cache');
			$CacheM		=	$this->MODEL('cache');
			$CacheList	=	$CacheM->GetCache(array('city','user'));
			$this->yunset($CacheList);
		}
		$this->yuntpl(array('admin/admin_tiny_add'));
	}
	function save_action(){
		$tinyM	= 	$this ->  MODEL('tiny');
		
		$post	=   array(
			'username' 		=>  $_POST['username'],
			'sex' 			=>  $_POST['sex'],
			'exp' 			=>  $_POST['exp'],
			'job' 			=>  $_POST['job'],
			'mobile' 		=>  $_POST['mobile'],
			'provinceid' 	=>  $_POST['provinceid'],
			'cityid' 		=>  $_POST['cityid'],
			'three_cityid'	=>  $_POST['three_cityid'],
			'production' 	=>  $_POST['production'],
			'password'		=>	$_POST['password'],
			'status' 		=>  1
		);
		$data=array(
			'id'		=>	(int)$_POST['id'],
			'post'		=>	$post,
			'type'=>'admin'
		);
		$return  = $tinyM  ->  addResumeTinyInfo($data,'admin');
		
		if($return['errcode']==9){
			$this->ACT_layer_msg($return['msg'],$return['errcode'],'index.php?m=admin_tiny');
		}else{
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
		}
	}
	
	function set_action(){
    
		$this->yuntpl(array('admin/admin_tinyset'));
	}
	
	function tinyset_action(){
      
		$configM    =   $this->   MODEL("config");
		
		foreach($_POST as $key=>$v){
			
			$where['name']	=  $key;
			$config 		= 	$configM -> getNum($where);
			
			if($config==false){

				$data  		=  array(
					'name'    =>  $key,
					'config'  =>  $v,
				);
				$msg   		=   $configM -> addInfo($data);
			}else{

				$where['name']  =   $key;
				
				$data   		=   array(
					'config'	=>  $v,
				);
				$msg   			=   $configM -> upInfo($where,$data);
			}
			if(!$msg){

				$this->web_config();
				$this->ACT_layer_msg('操作失败！',8);
			}
		}
		$this->web_config();
		$this->ACT_layer_msg('操作成功！',9);
	}

	function tinyNum_action(){
		$MsgNum = $this->MODEL("msgNum");
		echo $MsgNum->tinyNum();
	}
  
      	/**
	 * @desc  普工  分站设置
	 */
	function checksitedid_action(){
	    
	    $id		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('参数不全请重试！', 8);
	    }
	    
	    $ids		 =	@explode(',',$_POST['uid']);
	    $id 		 =	pylode(',',$ids);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('请正确选择需分配用户！', 8);
	    }
	    
	    $siteM       =  $this->MODEL('site');
	    
	    $didData	 =    array('did' => $did);
	   
	    $siteM -> updDid(array('resume_tiny'),array('id'=>array('in', $id)),$didData);
	    
	    $this->ACT_layer_msg('普工简历(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER'],2,1);
	    
	}
}
?>