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
class job_controller extends user{
	//职位管理
	function index_action(){
		$this -> public_action();
		$this -> member_satic();
		$JobM    					=   $this -> MODEL('job');
		$statisM    				=   $this -> MODEL('statis');
		
		$where['uid']				=  $this -> uid;
		if($_GET['browse']){
			
			$where['is_browse']		=	$_GET['browse'];
			$urlarr['browse']		=	$_GET['browse'];
		}
		if($_GET['datetime']){
			if($_GET['datetime']=='1'){
				$where['datetime']	=	array('>',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['datetime']	=	array('>',strtotime('-'.intval($_GET['datetime']).' day'));
			}
			$urlarr['datetime']		=	$_GET['datetime'];
		}
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('userid_job',$where,$pageurl,$_GET['page']);

      
		
        if($pages['total'] > 0){
          $where['orderby']		=	'id';
          $where['limit']			=	$pages['limit'];
        
          $list   	 =   $JobM -> getSqJobList($where,array('uid'=>$this->uid,'usertype'=>$this->usertype));
        }
       
		
        
        $StateList		=	array('1'=>'未查看','2'=>'已查看','3'=>'等待通知','4'=>'条件不符','5'=>'无法联系');
        $this->yunset("StateList",$StateList);
        
        $search_list	=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
        $this->yunset("search_list",$search_list);
        
        $num=$JobM -> getSqJobNum(array('uid'=>$this->uid));
		
        $statisM -> upInfo(array('sq_jobnum'=>$num),array('uid'=>$this->uid,'usertype'=>$this->usertype));
       
        $this->yunset("total",$pages['total']);
        $this->yunset("rows",$list);
        $this->yunset("js_def",3);
        $this->user_tpl('job');
	}
	function del_action(){		
  	$JobM   =   $this -> MODEL('job');
		if($_GET['del']||$_GET['id']){
			if(is_array($_GET['del'])){
				$id =   $_GET['del'];
			}else{
				$id =   intval($_GET['id']);
			}
			$arr    =   $JobM -> delSqJob($id,array('utype'=>'user','uid'=>$this->uid,'usertype'=>$this->usertype));
			$this ->  layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}
	 
	function qs_action(){
    $JobM   	=   $this -> MODEL('job');
		if($_POST['id']){
			
			$id=(int)$_POST['id'];
			
			$return		=   $JobM -> qxSqJob(array('id'=>$id,'body'=>$_POST['body'],'uid'=>$this->uid,'usertype'=>$this->usertype));
			
			$this ->  ACT_layer_msg($return['msg'], $return['errcode'],$_SERVER['HTTP_REFERER']);
		}
	}
}
?>