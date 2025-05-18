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
class privacy_controller extends user{
	//隐私设置
	function index_action(){ 
		$blackM						=	$this->MODEL('black');
		
		$where['c_uid']				=	$this->uid;
		$where['usertype']			=	'1';
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('blacklist',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			$where['orderby']	=	'id,desc';
			
		    $where['limit']		=	$pages['limit'];
			
			$List				=	$blackM->getBlackList($where);
			
			$this->yunset("rows",$List);
		}
		$this->public_action();
		$this->user_tpl('privacy');
	}
	function up_action(){
		if(intval($_GET['status'])){
			$resumeM	=	$this->MODEL('resume');
			$logM		=	$this->MODEL('log');
			
			$resumeM->upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('status'=>intval($_GET['status'])))); 
			$resumeM->upInfo(array('uid'=>$this->uid),array('eData'=>array('status'=>intval($_GET['status'])))); 
			if(intval($_GET['status'])==2){
				$stext	=	'隐藏';
			}else if(intval($_GET['status'])==1){
				$stext	=	'公开';
			}
			$logM->addMemberLog($this->uid,$this->usertype,"设置简历为".$stext,2,2);
			
			$this->layer_msg('简历设置成功！',9,0);
		}
	}
	function del_action(){
		if($_GET['id']){
			$blackM		=	$this->MODEL('black');
			$id			=	(int)$_GET['id'];
			
			$return		=	$blackM->delBlackList($id,array('uid'=>$this->uid,'usertype'=>$this->usertype,'where'=>array('c_uid'=>$this->uid)));
			
			$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],"index.php?c=privacy");
 		}
	}
	function delall_action(){
		$blackM		=	$this->MODEL('black');
		
		$return		=	$blackM->delBlackList('',array('uid'=>$this->uid,'usertype'=>$this->usertype,'where'=>array('c_uid'=>$this->uid),'type'=>'all'));
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],"index.php?c=privacy");
	}
	function searchcom_action(){
		$blackM			=	$this->MODEL('black');
		$companyM		=	$this->MODEL('company');
		
		$blacklist		=	$blackM->getBlackList(array('c_uid'=>$this->uid),array('field'=>'`p_uid`'));
		if($blacklist && is_array($blacklist)){
			$uids			=	array();
			foreach($blacklist as $v){
				
				if($v['p_uid'] && !in_array($v['p_uid'],$uids)){
					
					$uids[]	=	$v['p_uid'];
				}
			}
			$where['uid']	=	array('notin',pylode(',',$uids));
		}
		$where['name']		=	array('like',$this->stringfilter(trim($_POST['name'])));
		
		$company			=	$companyM->getList($where,array('field'=>'`uid`,`name`'));
		$company			=	$company['list'];
		
		$html	=	"";
		if($company && is_array($company)){
			
			foreach($company as $val){
				
				$html	.=	"<li class=\"cur\"><input class=\"re-company\" type=\"checkbox\" value=\"".$val['uid']."\" name=\"buid[]\"><a href=\"".Url('company',array('c'=>'show',"id"=>$val['uid']))."\" target=\"_blank\">".$val['name']."</a></li>";
			} 
		}else{
			$html		=	"<li class=\"cur\">暂无符合条件企业</li>";
		}
		echo $html;die;
	}
	function save_action(){
		
		$blackM		=  $this->MODEL('black');
		$data		=	array(
			'cuid'		=>	$_POST['buid'],
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
		);
		$return		=  $blackM -> addBlacklist($data);
		
		$this -> layer_msg($return['msg'],$return['errcode'],1,"index.php?c=privacy");
	}
}
?>