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
class admin_userchange_controller extends adminCommon{	 
	private function set_search(){
		$search_list[]  =  array('param'=>'sqtype','name'=>'申请方式','value'=>array('1'=>'用户申请','2'=>'管理员转换'));
		$search_list[]  =  array('param'=>'status','name'=>'审核状态','value'=>array('3'=>'待确认','1'=>'已同意','2'=>'已拒绝'));
		$search_list[]  =  array('param'=>'presusertype','name'=>'当前会员','value'=>array('1'=>'个人会员','2'=>'企业会员'));
		$search_list[]  =  array('param'=>'applyusertype','name'=>'申请会员','value'=>array('1'=>'个人会员','2'=>'企业会员'));
		$search_list[]  =  array('param'=>'applytime','name'=>'申请时间','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
		$this->yunset('search_list',$search_list);
	}
	function index_action(){
		$this->set_search();
		if(trim($_GET['keyword'])){
			if($_GET['type']==1){
				
				$where['name']		=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==2){
				$where['uid']			=	array('like',trim($_GET['keyword']));
			}
			$urlarr['keyword']			=	$_GET['keyword'];
			
			$urlarr['type']				=	$_GET['type'];
		}
		if($_GET['sqtype']){
			$sqtype      				=   intval($_GET['sqtype']);
            $where['type']     			=   $sqtype;
            $urlarr['status']        	=   $sqtype;
		}
		if ($_GET['status']) {
            $status      		=   		intval($_GET['status']);
            $where['status']     		=   $status == 3 ? 0 : $status;
            $urlarr['status']        	=   $status;
		}
		if($_GET['id']){
			$id							=	intval($_GET['id']);
			$where['id']				=	$id;
			$urlarr['id']				=	$id;
		}
		if($_GET['presusertype']){
			$presusertype				=	intval($_GET['presusertype']);
			$where['pres_usertype']		=	$presusertype;
			$urlarr['pres_usertype']	=	$presusertype;
		}
		if($_GET['applyusertype']){
			$applyusertype				=	intval($_GET['applyusertype']);
			$where['apply_usertype']	=	$applyusertype;
			$urlarr['apply_usertype']	=	$applyusertype;
		}
		$userinfoM	=	$this->MODEL('userinfo');
		$urlarr['page']	=	'{{page}}';
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('user_change',$where,$pageurl,$_GET['page']);
		if($pages['total'] > 0){
			if($_GET['order']){
                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
            }else{
                $where['orderby']		=	array('status,asc','apply_time,desc');
            }
			$where['limit']	=  	$pages['limit'];
			$List			=	$userinfoM -> getUserChangeList($where,array('field'=>'`id`,`uid`,`name`,`pres_usertype`,`apply_usertype`,`apply_time`,`lastupdate`,`status`,`applybody`,`did`,`statusbody`,`type`'));
			$this -> yunset('rows',$List);
		}
		$cacheM	=	$this -> MODEL('cache');
		$domain	=	$cacheM	-> GetCache('domain');
		$this -> yunset('Dname', $domain['Dname']);
		$this->yuntpl(array('admin/admin_userchange'));
	}
	/**
	 * 会员身份切换列表:获取拒绝、审核未通过原因
	 */
	function lockinfo_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $member     =  $userinfoM -> getUserChangeInfo(array('id'=> $_GET['id']),array('field'=>'statusbody'));
	    
	    echo trim($member['statusbody']);die;
	}
	/**
	 * 申请列表（页面统计数量）
	 */
	function memNum_action(){
		$MsgNum	=	$this->MODEL('msgNum');
		echo $MsgNum->memchangeNum();
	}
	function status_action(){
		$userinfoM			=			$this->MODEL('userinfo');
		$id					=			$_POST['id'];
		$status				=			intval($_POST['status']);

		if(!$id){
			$this->ACT_layer_msg('请选择要审核的用户',8);
		}
		if($status==""){
		    $this->ACT_layer_msg('请选择审核状态',8);
		}
		if($status==2){
			$state			=			1;
		}else{
			$state			=			0;
		}

		$data				=			array(
			'status'		=>			$status,
			'statusbody'	=>			$_POST['statusbody'],
			'lastupdate'	=>			time(),
			'state'			=>			$state
		);

		$id_arr 			= 			$id ? @explode(',',$id) : array();
			
		if(!empty($id_arr)){

			$return				=			$userinfoM->upAllUserChange($id_arr,$data,'5');

		}else{
			$moblie				=			$_POST['moblie'];
			//查询手机号码：判断当前用户是哪个类型呢？
			$uid				=			$_POST['uid'];
		    
	    	$return				=			$userinfoM->upUserChange($id,$uid,$moblie,$data,'5');
		}
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}
	/**
	 * 切换身份: 删除
	 */
	function del_action(){
		$userinfoM			=			$this->MODEL('userinfo');
	    if ($_GET['del']){
	        $this -> check_token();
	        $id   =  intval($_GET['del']);
	    }elseif ($_POST['del']){
	        $id   =  $_POST['del'];
		}
	    $return   =  $userinfoM -> delUserChange($id);
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}

}
?>