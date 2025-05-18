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
class atn_controller extends user{
	function index_action(){
		$this->public_action();
		$atnM						=	$this->MODEL('atn');
		
		$where['uid']				=	$this->uid;
		$where['sc_usertype']		=	'2';
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('atn',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			$where['orderby']	=	'id,desc';
			
		    $where['limit']		=	$pages['limit'];
			
			$List				=	$atnM->getatnList($where,array('utype'=>'company'));
			
			$this->yunset("rows",$List);
		}
 		$this->user_tpl('atn');
	}
	function del_action(){
		$atnM		=	$this->MODEL('atn');
		$return		=	$atnM->delAtnAll((int)$_GET['id'],array('cuid'=>intval($_GET['uid']),'uid'=>$this->uid,'usertype'=>$this->usertype,'sc_usertype'=>2));

		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],"index.php?c=atn");
	}
}
?>