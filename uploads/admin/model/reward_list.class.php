<?php
/** $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class reward_list_controller extends adminCommon{
	
	function index_action(){
		$redeemM	=	$this->MODEL('redeem');
		if($_GET['status']){
            if($_GET['status']	==	'-1'){
            	$where['status']	=	'0';
            }else{
				$where['status']	=	$_GET['status'];
            }
			$urlarr['status']		=	$_GET['status'];
		}
		
		if($_GET['change']){
			if($_GET['change']	==	'1'){
				$where['ctime']		=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['ctime']		=	array('>=',strtotime('-'.$_GET['change'].'day'));
			}
			$urlarr['change']		=	$_GET['change'];
		}
		
		if(trim($_GET['keyword'])){
			if($_GET['type']=='1'){
				$where['name']		=	array('like',trim($_GET['keyword']));
			}elseif($_GET['type']=='2'){
				$where['username']	=	array('like',trim($_GET['keyword']));
			}
			$urlarr['type']			=	$_GET['type'];
			$urlarr['keyword']		=	trim($_GET['keyword']);
		}
		
		
		
		
		if($_GET['order']=="asc"){
			$this->yunset("order","desc");
		}else{
			$this->yunset("order","asc");
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c']; 
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    //limit order 只有在列表查询时才需要
			
			if($_GET['order']){
				if($_GET['order']=="desc"){
					$where['orderby']	=	$_GET['t'].",desc";
				}else{
					$where['orderby']	=	$_GET['t'].",asc";
				}
			}else{
				$where['orderby']	=	array('status,asc','id,desc');
			}
		    $where['limit']			=	$pages['limit'];
			$urlarr['order']		=	$_GET['order'];
			$urlarr['t']			=	$_GET['t'];
			//获取列表
			$List					=	$redeemM -> getChangeList($where);
			
			$this->yunset("rows",$List['list']);
		}
		
		$changetime		=	array('1'=>'一天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		$search_list[]	=	array("param"=>"change","name"=>'兑换时间',"value"=>array("1"=>"今天","3"=>"最近三天","7"=>"最近七天","15"=>"最近半月","30"=>"最近一个月"));
		$search_list[]	=	array("param"=>"status","name"=>'审核状态',"value"=>array("-1"=>"未审核","1"=>"已审核","2"=>"未通过"));
		$this->yunset("search_list",$search_list);
        $this->yunset("change",$changetime);
		$this->yuntpl(array('admin/reward_list'));
		
	} 
	function statusbody_action(){
		$redeemM		=	$this->MODEL('redeem');
		$where['id']	=	(int)$_GET['id'];
		$data['field']	=	'`statusbody`';
		$userinfo 		= 	$redeemM->getChangeInfo($where,$data);
		echo $userinfo['statusbody'];die;
	}
	function status_action(){ 
	
		$redeemM	=	$this->MODEL('redeem');
		
		if(intval($_POST['id'])){
			
			$change	=	$redeemM->getChangeInfo(array('id'=>intval($_POST['id'])),array('field'=>'`gid`,`num`,`status`,`uid`,`integral`,`name`,`usertype`'));
			$reward	=	$redeemM->getInfo(array('id'=>$change['gid']),array('field'=>'`id`,`stock`,`name`'));
			
			if($_POST['status']>0&&$change['status']=='0'){
				if($_POST['status']=='1'){
					if(trim($_POST['express'])&&trim($_POST['expnum'])){
						$value['express']	=	trim($_POST['express']);
						$value['expnum']	=	trim($_POST['expnum']);
					}
				}else{
					$stock			=	$reward['stock']+$change['num'];
					if($stock<0){
						$stock='0';
					}
					$orderM		=	$this->MODEL("companyorder");
					$ordernum	=	$orderM->getCompanyPayNum(array('com_id'=>$change['uid'],'pay_remark'=>'未通过积分兑换'));
					if(!$ordernum){
						$IntegralM		=	$this->MODEL("integral");
						$IntegralM->company_invtal($change['uid'],$change['usertype'],$change['integral'],true,"未通过积分兑换",true,2,'integral',24);//积分操作记录
					}
					
					$upReData['num']	=	array('-',$change['num']);
					$upReData['stock']	=	$stock;
					$upReWhere['id']	=	$change['gid'];
					$redeemM->upInfo($upReData,$upReWhere);
					
					$value['express']	=	'';
					$value['expnum']	=	'';
				}
			}
			
			/* 消息前缀 */		
			$tagName  				=	'您兑换的商品';
			
			/* 处理审核信息 */
			if ($_POST['status'] == 2){
				
				$statusInfo  =  '您兑换的商品:<a href="rewardtpl,'.$reward['id'].'">'.$reward['name'].'</a>,审核未通过';
				
				if($_POST['statusbody']){
					
					$statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
					
				}
				
				$msg[$change['uid']]  =  $statusInfo;
				
			}elseif($_POST['status'] == 1){
				
				$msg[$change['uid']]  =  '您兑换的商品:<a href="rewardtpl,'.$reward['id'].'">'.$reward['name'].'</a>,已审核通过';
				
			}
			
			if(!empty($msg)){
				$uids[]		=	$change['uid'];
				
				//发送系统通知
				
				$sysmsgM	=	$this->MODEL('sysmsg');
				
				$sysmsgM -> addInfo(array('uid'=>$uids,'usertype' => $change['usertype'],'content'=>$msg));

			}
			
			$value['status']		=	$_POST['status'];
			$value['linktel']		=	$_POST['linktel'];
			$value['linkman']		=	$_POST['linkman'];
			$value['statusbody']	=	$_POST['statusbody'];
			$where['id']			=	intval($_POST['id']);
			$id						=	$redeemM->upChangeInfo($where,$value);	
 			$id?$this->ACT_layer_msg("兑换记录审核(ID:".$aid.")设置成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("设置失败！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function statuss_action(){
		$redeemM		=	$this->MODEL('redeem');
		
		$where['id']	=	array('in',$_POST['allid']);
		$data['field']	=	'`id`,`status`';
		
		$change			=	$redeemM->getChangeList($where,$data);
		foreach($change['list'] as $val){
			if($val['status']!=2){
				$redeemM->upChangeInfo(array('id'=>$val['id']),array('status'=>$_POST['status']));
			}
		}
		$this->MODEL('log')->addAdminLog("批量审核(ID:".$_POST['allid'].")审核成功");
		echo $_POST['status'];die;
	}
	function del_action(){
		$IntegralM	=	$this->MODEL('integral');
		$redeemM	=	$this->MODEL('redeem');
		$rewardM	=	$this->MODEL("reward");
		if($_GET['del']){ 
			$this->check_token();
			
			if(is_array($_GET['del'])){
				$where['id']	=	array('in',pylode(',',$_GET['del']));
				$data			=	array('type'=>'all');
				$layer_type		=	1;
				$delid			=	pylode(',',$_GET['del']);
			}else{
				$this->check_token();
				$where['id']	=	(int)$_GET['del'];
				$data			=	array('type'=>'one');
				$layer_type		=	0;
				$delid			=	(int)$_GET['del'];
			}
		    
			$rowsWhere['id']	=	$where['id'];
			$rowsData['field']	=	'`uid`,`gid`,`num`,`integral`,`usertype`,`status`';
			$rowss				=	$redeemM->getChangeList($rowsWhere,$rowsData['field']);
			$rowss				=	$rowss['list'];
			if($rowss&&is_array($rowss)){
				foreach($rowss as $val){
					if($val['status']==0){
						$IntegralM->company_invtal($val['uid'],$val['usertype'],$val['integral'],true,"取消兑换",true,2,'integral',24);
						$upReData['stock']	=	array('+',$val['num']);
						$upReData['num']	=	array('-',$val['num']);
						$upRewhere['id']	=	$val['gid'];
						$redeemM->upInfo($upReData,$upRewhere);
					}
				}
			}
			$del	=	$redeemM->delChange($where,$data);
			$delid?$this->layer_msg('兑换记录(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('请选择要删除的内容！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>