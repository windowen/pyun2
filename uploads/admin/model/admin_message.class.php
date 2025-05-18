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
class admin_message_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		$search_list[]=array("param"=>"infotype","name"=>'意见类型',"value"=>array("1"=>"建议","2"=>"意见","3"=>"求助","4"=>"投诉"));
		
		$ad_time=array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$status_arr=array('1'=>'未处理','2'=>'已处理');
		
		$search_list[]=array("param"=>"end","name"=>'意见时间',"value"=>$ad_time);
		
		$search_list[]=array("param"=>"status","name"=>'意见时间',"value"=>$status_arr);
		
		$this->yunset("search_list",$search_list);
	}
	function index_action(){
		$this->set_search();
		
		$adviceM=$this->MODEL('advice');
		
		if(trim($_GET['keyword'])){
			
			if($_GET["type"]==1){
				
				$where['username']	=	array('like',trim($_GET['keyword']));
			}else{
				
				$where['content']	=	array('like',trim($_GET['keyword']));
			}
			$urlarr['type']			=	$_GET['type'];
			
			$urlarr['keyword']		=	$_GET['keyword'];
		}
		if($_GET['end']){
			if($_GET['end']=='1'){
				
				$where['ctime']		=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				
				$where['ctime']		=	array('>=',strtotime('-'.$_GET['end'].'day'));
			}
			$urlarr['end']			=	$_GET['end'];
		}
		if($_GET['infotype']){
			
			$where['infotype']		=	$_GET['infotype'];
			
			$urlarr['infotype']		=	$_GET['infotype'];
		}
		if($_GET['status']){
			$where['status']		=	$_GET['status'];
			
			$urlarr['status']		=	$_GET['status'];
		}
		$urlarr['page']	=	"{{page}}";
		
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('advice_question',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		
		if($pages['total'] > 0){
			
			if($_GET['order'])
			{
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				
				$urlarr['order']	=	$_GET['order'];
				
				$urlarr['t']		=	$_GET['t'];
			}else{
				
				$where['orderby']	=	'ctime,desc';
			}
			
			$where['limit']	=	$pages['limit'];
		
			$List		=	$adviceM -> getList($where);
			
		}
		
        $this->yunset('rows',$List);
		
		$this->yunset("get_type", $_GET);
		
		$this->yuntpl(array('admin/admin_message'));
	}
	function del_action(){
	    if($_GET['del']){
			$this->check_token();
			
	    	$del=$_GET['del'];
			
	    	if($del){
				
	    		$return	=	$this -> MODEL('advice') -> delInfo($del);
				
				$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
			}else{
				$this->layer_msg('非法操作！',8);
			}
	    }
	}
	function content_action(){
		$con=$this -> MODEL('advice') -> getInfo(array('id'=>intval($_GET['id'])));
		
		if($con['infotype']==1){
			$con['type']='建议';
		}elseif($con['infotype']==2){
			$con['type']='意见';
		}elseif($con['infotype']==3){
			$con['type']='求助';
		}elseif($con['infotype']==4){
			$con['type']='投诉';
		}
		$con['name']=$con['username'];
		$con['ctime']=date('Y-m-d H:i:s',$con['ctime']);
		echo json_encode($con);die;
	}
	
	function handlecontent_action(){
	    $adviceM	=	$this -> MODEL('advice');
		$row		=	$adviceM -> getInfo(array('id'=>$_GET['id']));
		echo trim($row['handlecontent']);die;
	}
	
	function status_action(){
	    $adviceM	=	$this -> MODEL('advice');
	    
	    $post       =  array(
	        'status'     =>  intval($_POST['status']),
	        'handlecontent'  =>  trim($_POST['handlecontent'])
	    );
	    
	    $return     =  $adviceM -> statusInfo($post,array('id'=>$_POST['mid']));
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	
}