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
class admin_loginlog_controller extends adminCommon{
  function  set_search(){
    $ad_time						=			array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		$search_list[]					=			array("param"=>"end","name"=>'操作时间',"value"=>$ad_time);
		$this->yunset("search_list",$search_list);
  }
	function index_action(){

    $this -> set_search();
		$logM							=			$this->MODEL('log');
		$memberM						=			$this->MODEL('userinfo');

		if($_GET['utype']){
			$utype						=			$_GET['utype'];
			$where['usertype'] 			= 			trim($_GET['utype']);
			$urlarr['utype']			=			$_GET['utype'];
		}else{
			$utype						=			1;
			$where['usertype'] 			= 			1;
			$urlarr['utype']			=			$_GET['utype'];
		}
		if(intval($_GET['uid'])){
			$where['uid'] 				= 			intval($_GET['uid']);
			$urlarr['uid']				=			$_GET['uid'];
		}
		if($_GET['end']){
			if($_GET['end']=='1'){
				$where['ctime'][] 		= 			array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['ctime'][] 		= 			array('>=',strtotime('-'.(int)$_GET['end'].'day'));
			}
			$urlarr['end']				=			$_GET['end'];
		}
		if(trim($_GET['keyword'])){
			if($_GET['type']==1){
			    $member					=			$memberM->getList(array('username'=>array('like',trim($_GET['keyword']))),array('field'=>'`uid`,`username`'));
				foreach($member as $v){
					$uid[]				=			$v['uid'];
				}
				$where['uid']			=			array('in',pylode(",",$uid));
			}else{
				$where['content']		=			array('like',trim($_GET['keyword']));
			}
			$urlarr['keyword']			=			$_GET['keyword'];
			$urlarr['type']				=			$_GET['type'];
		}
		if($_GET['time']){
			$time						=			@explode('~',$_GET['time']);
			$where['ctime'][] 			= 			array('>=',strtotime($time[0]."00:00:00"));
			$where['ctime'][] 			= 			array('<=',strtotime($time[1]."23:59:59"));
			$urlarr['time']				=			$_GET['time'];
		}


		//内容搜索
		if(trim($_GET['content'])){
			$content = $_GET['content'];
			$where['content'] = array('like',trim($_GET['content']));
			$urlarr['content'] = $_GET['content'];
		}

	    $urlarr['page']	 				=			'{{page}}';
	    $pageurl		 				=			Url($_GET['m'],$urlarr,'admin');
	    $pageM			 				=			$this  -> MODEL('page');
	    $pages			 				=			$pageM -> pageList('login_log',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){

			if($_GET['order']){

	            $where['orderby']		=			$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=			$_GET['order'];
	            $urlarr['t']			=			$_GET['t'];

	        }else{

	            $where['orderby']		=			array('id,desc');

	        }
	        $where['limit']		   		=  			$pages['limit'];

	        $List  						=  			$logM -> getLoginlogList($where,array('utype'=>'admin'));

	        $this -> yunset(array('rows'=>$List));
	    }
		$this->yuntpl(array('admin/admin_loginlog'));
	}


	function dellog_action(){
		$this->check_token();
		$logM = $this->MODEL('log');

		if($_GET['del']=='allcom'){
			$where['usertype']			=			'2';

			$logM->delLoginlog('',$where);

			$this->layer_msg('已清空企业日志！',9,0,$_SERVER['HTTP_REFERER']);
	    }elseif($_GET['del']=='alluser'){
			$where['usertype']			=			'1';

			$logM->delLoginlog('',$where);

			$this->layer_msg('已清空个人日志！',9,0,$_SERVER['HTTP_REFERER']);
	    }elseif($_GET['del']){
	    	$del=$_GET['del'];

	    	if($del){
	    		if(is_array($del)){
					$layer_type=1;
					$where['id'] = array('in',pylode(',',$del));
					$logM->delLoginlog('',$where);

		    	}else{

		    		$logM->delLoginlog('',array('id' => $del));

		    		$layer_type=0;

		    	}
				$this->layer_msg('登录日志(ID:'.$del.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']);
	    	}else{
				$this->layer_msg('请选择您要删除的信息！',8,0,$_SERVER['HTTP_REFERER']);
	    	}
	    }
	}
}
?>

