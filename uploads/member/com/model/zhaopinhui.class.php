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
class zhaopinhui_controller extends company{
	//招聘会
	function index_action(){
		$this->company_satic();
		$this->public_action();
		$zphM	=	$this->MODEL('zph');
		$urlarr["c"]	=	"zhaopinhui";
		$urlarr["page"]	=	"{{page}}";
		$pageurl		=	Url('member',$urlarr);
		$uid            =   $this -> uid;
		$where['uid']	=	$uid;
		
		$pageM	=	$this  -> MODEL('page');
		$pages	=	$pageM -> pageList('zhaopinhui_com',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
		
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
			
			$List	=	$zphM -> getZphCompanyList($where);
			
			$this->yunset("rows" , $List);
		}
	 
		$this->com_tpl("zhaopinhui");
	}
	function del_action(){
		$IntegralM	=	$this -> MODEL('integral');
		
		$zphM		=	$this -> MODEL('zph');
		
		$logM		=	$this -> MODEL('log');
		
		$row		=	$zphM -> getZphComInfo(array('id'=>(int)$_GET['id'],'uid'=>$this->uid),array('field'=>"`price`,`status`"));

		$delid		=	$zphM -> delZphCom($_GET['id'],array('uid'=>$this->uid));
		if($delid){
			if($row['status']==0 && $row['price']>0){
				$IntegralM -> company_invtal($this->uid,$this -> usertype, $row['price'],true,"退出招聘会",true,2,'integral');//积分操作记录
			}
			$logM -> addMemberLog($this->uid,$this->usertype,"退出招聘会",14,3);//会员日志
			
			$this -> layer_msg('退出成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this -> layer_msg('退出失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>