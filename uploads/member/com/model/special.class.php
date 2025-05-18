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
class special_controller extends company{
	//招聘会
	function index_action(){
		$this->company_satic();
		$this->public_action();
		$specialM	=	$this->MODEL('special');
		$urlarr["c"]	=	"special";
		$urlarr["page"]	=	"{{page}}";
		$pageurl		=	Url('member',$urlarr);
		$where['uid']	=	$this->uid;
		
		$pageM		=	$this  -> MODEL('page');
		$pages		=	$pageM -> pageList('special_com',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
		
		if($pages['total'] > 0){
			if($_GET['order'])
			{
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']		=	$_GET['order'];
				$urlarr['t']			=	$_GET['t'];
			}else{
				$where['orderby']		=	'id';
			}
			$where['limit']	=	$pages['limit'];
			
			$List	=	$specialM -> getSpecialComList($where);
			
			$this->yunset("rows" , $List['list']);
		}
		$this->com_tpl("special");
	}
	function del_action(){
		$logM		=	$this -> MODEL('log');
		
		$specialM	=	$this -> MODEL('special');
		
		$delid		=	$specialM -> delSpecialCom(array('id'=>(int)$_GET['id'],'uid'=>$this->uid)," ");
		
		if($delid){
			
			$logM -> addMemberLog($this->uid,$this->usertype,"删除专题报名",14,3);//会员日志
			
			$this->layer_msg('删除成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>