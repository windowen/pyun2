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
class usercert_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
	    
		$search[]  =  array("param"=>"status","name"=>'审核状态',"value"=>array("1"=>"已审核","2"=>"未审核","3"=>"未通过"));
		
		$lo_time   =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$search[]  =  array("param"=>"time","name"=>'发布时间',"value"=>$lo_time);
		
		$this->yunset("search_list",$search);
	}
	/**
	 * 会员-个人-个人认证审核:列表
	 */
	function index_action(){
	    
		$this -> set_search();
		
		$where['idcard_pic']             =  array('<>','');
		
		if($_GET['status']){
		    
		    $status                      =   intval($_GET['status']);
		    
		    if ($status == 1){
		        
		        $where['idcard_status']  =  1;
		        
		    }elseif ($status == 2){
		        
		        $where['idcard_status']  =  0;
		        
		    }elseif ($status == 3){
		        
		        $where['idcard_status']  =  2;
		    }
		    $urlarr['status']            =  $status;
		}
		if ($_GET['keyword']){
		    
		    $keyword                     =  trim($_GET['keyword']);
		    
		    $where['name']           	 =  array('like', $keyword);
		    
		    $urlarr['keyword']           =  $keyword;
		}
		if($_GET['time']){
		    
		    $cert_time                   =  intval($_GET['time']);
		    
		    if($cert_time == 1){
		        
		        $where['cert_time']      =	 array('>=',strtotime('today'));
		        
		    }else{
		        
		        $where['cert_time']      =	 array('>',strtotime('-'.$cert_time.' day'));
		    }
		    
		    $urlarr['time']	      =	 $cert_time;
		}
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('resume',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
		        
		        $where['orderby']	    =	$_GET['t'].','.$_GET['order'];
		        $urlarr['order']		=	$_GET['order'];
		        $urlarr['t']			=	$_GET['t'];
		    }else{
		        $where['orderby']		=	array('idcard_status,ASC','cert_time,DESC');
		    }
		    $where['limit']				=	$pages['limit'];
		    
		    $resumeM  =  $this->MODEL('resume');
		    
		    $List     =  $resumeM -> getResumeList($where,array('utype'=>'admin'));
		    
		    
		    $this -> yunset('rows',$List);
		}
		
		$this->yuntpl(array('admin/admin_user_cert'));
	}
	/**
	 * 会员-个人-个人认证审核: 审核
	 */
	function status_action(){
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $post  =  array(
	        'idcard_status'  =>  intval($_POST['status']),
	        'statusbody'     =>  trim($_POST['statusbody'])
	    );
	    
	    $return  =  $resumeM -> statusCert($_POST['uid'],array('post'=>$post));
	    
        if($_POST['type']==1){
            echo json_encode($return);die;
        }else{
            $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,$return['layertype']);
        }
	}
	/**
	 * 会员-个人-个人认证审核: 查询审核原因
	 */
	function sbody_action(){
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $resume   =  $resumeM -> getResumeInfo(array('uid'=>intval($_POST['uid'])),array('field'=>'statusbody'));
	    
	    echo trim($resume['statusbody']);die;
	}
	/**
	 * 会员-个人-个人认证审核: 删除个人认证
	 */
	function del_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $uid   =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $uid   =  $_POST['del'];
	    }
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> delResumeCert($uid);
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员-个人-个人认证审核: 数据统计查询
	 */
	function idCardNum_action(){
	    
		$MsgNum   =  $this -> MODEL('msgNum');
		
		echo $MsgNum -> idCardNum();
	}
}

?>