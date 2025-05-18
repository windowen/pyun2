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
class admin_msg_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		
		$lo_time   =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$search[]  =  array('param'=>'zx','name'=>'咨询时间','value'=>$lo_time);
		
		$f_time    =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$search[]  =  array('param'=>'hf','name'=>'回复时间','value'=>$f_time);
		
		$this -> yunset('search_list',$search);
	}
	/**
	 * 会员-个人-求职咨询: 列表
	 */
	function index_action(){
	    
		$this -> set_search();
		
		if($_GET['keyword']){
		    
		    $keytype  =   intval($_GET['type']);
		    
		    $keyword  =   trim($_GET['keyword']);
		    
		    if ($keytype == 1){
		        
		        $where['username']    =  array('like',$keyword);
		        
		    }elseif ($keytype == 2){
		        
		        $where['job_name']    =  array('like',$keyword);
		        
		    }elseif ($keytype == 3){
		        
		        $where['com_name']    =  array('like',$keyword);
		        
		    }elseif ($keytype == 4){
		        
		        $where['content']     =  array('like',$keyword);
		        
		    }elseif ($keytype == 5){
		        
		        $where['reply']       =  array('like',$keyword);
		    }
		    $urlarr['keytype']	      =  $keytype;
		    
		    $urlarr['keyword']	      =  $keyword;
		}
		if($_GET['zx']){
		    
		    $zx                       =  intval($_GET['zx']);
		    
		    if($zx == 1){
		        
		        $where['datetime']	  =  array('>=',strtotime('today'));
		        
		    }else{
		        
		        $where['datetime']	  =  array('>=',strtotime('-'.$zx.' day'));
		    }
		    
		    $urlarr['zx']             =  $zx;
		}
		if($_GET['hf']){
		    
		    $hf  =  intval($_GET['hf']);
		    
		    if($hf == 1){
		        
		        $where['reply_time']  =  array('>=',strtotime('today'));
		        
		    }else{
		        
		        $where['reply_time']  =  array('>=',strtotime('-'.$hf.' day'));
		    }
		    
		    $urlarr['hf']             =  $hf;
		}
		if($_GET['job']){
		    
		    $where['type']            =  intval($_GET['job']);
		    
		    $urlarr['job']            =  $_GET['job'];
		}
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('msg',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
		        
		        $where['orderby']	    =	'id,'.$_GET['order'];
		        
		        $urlarr['order']		=	$_GET['order'];
		    }else{
		        $where['orderby']		=	'id,desc';
		    }
		    $where['limit']				=	$pages['limit'];
		    
		    $msgM  =  $this->MODEL('msg');
		    
		    $List  =  $msgM -> getList($where);
		    
		    $this -> yunset('mes_list',$List['list']);
		}
		$this->yuntpl(array('admin/admin_msg'));
	}
	/**
	 * 会员-个人-求职咨询: 删除求职咨询
	 */
	function del_action(){
	    if ($_GET['id']){
	        
	        $this -> check_token();
	        
	        $id   =  intval($_GET['id']);
	        
	    }elseif ($_POST['del']){
	        
	        $id   =  $_POST['del'];
	    }
	    $msgM     =  $this->MODEL('msg');
	    
	    $return   =  $msgM -> delInfo($id);
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员-个人-求职咨询: 求职咨询预览
	 */
	function msgshow_action(){
		if($_POST['id']){
		    
		    $id      =  intval($_POST['id']);
		    
		    $msgM    =  $this->MODEL('msg');
		    
		    $return  =  $msgM -> getInfo(array('id'=>$id));
		    
			echo json_encode($return);die;
		}
	}
}
?>