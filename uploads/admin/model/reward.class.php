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
class reward_controller extends adminCommon{
	function index_action(){
		$redeemM	=	$this->MODEL("redeem");
	
		if(trim($_GET['keyword'])){
			if($_GET['ctype']=='2'){
				$where['integral']	=	intval($_GET['keyword']);
			}else{
				$where['name']		=	array('like', trim($_GET['keyword']));
			}
			$urlarr['keyword']		=	trim($_GET['keyword']);
		}
		
		if($_GET['nid']){
			$where['nid']			=	$_GET['nid'];
			$urlarr['nid']			=	$_GET['nid'];
		}
		
		if($_GET['status']){
			if($_GET['status']=='2'){
				$where['status']	=	0;
			}else{
				$where['status']	=	$_GET['status'];
			}
			$urlarr['status']		=	$_GET['status'];
		}
		
		if($_GET['rec']){
			if($_GET['rec']=='2'){
				$where['rec']		=	0;
			}else{
				$where['rec']		=	$_GET['rec'];
			}
			$urlarr['rec']			=	$_GET['rec'];
		}
		
		if($_GET['hot']){
			if($_GET['hot']=='2'){
				$where['hot']		=	'0';
			}else{
				$where['hot']		=	$_GET['hot'];
			}
			$urlarr['hot']			=	$_GET['hot'];
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c']; 
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('reward',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    //limit order 只有在列表查询时才需要
			
			if($_GET['order']){
				$where['orderby']	=	$_GET['t'].",".$_GET['order'];
			}else{
				$where['orderby']	=	'id,desc';
			}
		    $where['limit']			=	$pages['limit'];
			$urlarr['order']		=	$_GET['order'];
			$urlarr['t']			=	$_GET['t'];
			//获取列表
			$List					=	$redeemM -> getList($where);
			$this->yunset("rows",$List);
		}
		
		//提取类别
		$carr			=	array();
		$acWhere['id']	=	array('>','0');
		$classAll		=	$redeemM->GetRewardClass($acWhere);
        foreach($classAll['list'] as $val){
            $carr[$val['id']]	=	$val['name'];
        }
		
		$search_list[]	=	array("param"=>"status","name"=>'状态',"value"=>array("1"=>"上架","2"=>"下架"));
		$search_list[]	=	array("param"=>"nid","name"=>'类别',"value"=>$carr);
		$search_list[]	=	array("param"=>"rec","name"=>'推荐',"value"=>array("1"=>"是","2"=>"否"));
		$search_list[]	=	array("param"=>"hot","name"=>'热门',"value"=>array("1"=>"是","2"=>"否"));
		$this->yunset("search_list",$search_list);
        $this->yunset("get_type",$_GET);
		$this->yuntpl(array('admin/admin_reward'));
	}
	
	function add_action(){
		
		$redeemM	=	$this->MODEL("redeem");
		
		if($_GET['id']){
			$info	=	$redeemM->getInfo(array('id'=>(int)$_GET['id']));
			$this	->	yunset("info",$info);
		}
		
		$cWhere['id']	=	array('>','0');
		$class			=	$redeemM->GetRewardClass($cWhere);
		$this->yunset("class",$class['list']);
		
		$this->yunset($this->MODEL('cache')->GetCache(array('redeem')));
		$this->yuntpl(array('admin/admin_reward_add'));
	}
	
	function save_action(){
		$redeemM	=	$this->MODEL("redeem");
	    $info		=	$redeemM->getInfo(array('id'=>(int)$_POST['id']));
		
		//处理数据
		if($_FILES['file']['tmp_name']){
	 		$upArr    =  array(
				'file'  	=>  $_FILES['file'],
				'dir'   	=>  'reward',
				
			);
			//缩率图参数

			$uploadM  =  $this->MODEL('upload');

			$pic      =  $uploadM->newUpload($upArr);
			
			if (!empty($pic['msg'])){

				$this->ACT_layer_msg($pic['msg'],8);

			}elseif (!empty($pic['picurl'])){

				$pictures 	=  	$pic['picurl'];
			}
	 	}
	 	if(isset($pictures)){
	 		$value['pic']		=	$pictures;
	 	}
		$value['name']			=	$_POST['name'];
	    $value['nid']			=	$_POST['nid'];
		$value['tnid']			=	$_POST['tnid'];
		$value['integral']		=	$_POST['integral'];
		$value['restriction']	=	$_POST['restriction'];
		$value['stock']			=	$_POST['stock'];
		$value['sort']			=	$_POST['sort'];
		$value['content']		=	str_replace("&amp;","&",$_POST['content']);
		$value['status']		=	$_POST['status'];
		$value['sdate']			=	time();
		$value['hot']			=	'0';
		
	    if($_POST['id']){
			$nbid	=	$redeemM->upInfo($value,array('id'=>(int)$_POST['id']));
	        isset($nbid)?$this->ACT_layer_msg("商品(ID:".$_POST['id'].")更新成功！",9,"index.php?m=reward",2,1):$this->ACT_layer_msg("更新失败！",8,"index.php?m=reward");
	    }else{
	        $nbid	=	$redeemM->addInfo($value);
	        isset($nbid)?$this->ACT_layer_msg("商品(ID:".$nbid.")添加成功！",9,"index.php?m=reward",2,1):$this->ACT_layer_msg("添加失败！",8,"index.php?m=reward");
	    }
	}
	
	function status_action(){
		$redeemM	=	$this->MODEL("redeem");
		$id			=	$redeemM->upInfo(array('status'=>$_GET['rec']),array('id'=>(int)$_GET['id']));
		$this->MODEL('log')->addAdminLog("商品(ID:".$_GET['id'].")状态设置成功！");
		echo $id?1:0;die;
	}

	function rec_action(){
		$redeemM	=	$this->MODEL("redeem");
		$id			=	$redeemM->upInfo(array('rec'=>$_GET['rec']),array('id'=>(int)$_GET['id']));
		$this->MODEL('log')->addAdminLog("商品(ID:".$_GET['id'].")状态设置成功！");
		echo $id?1:0;die;
	}

    function hot_action(){
		$redeemM	=	$this->MODEL("redeem");
		$id			=	$redeemM->upInfo(array('hot'=>$_GET['rec']),array('id'=>(int)$_GET['id']));
		$this->MODEL('log')->addAdminLog("商品(ID:".$_GET['id'].")状态设置成功！");
		echo $id?1:0;die;
	}

	function del_action(){
		$redeemM	=	$this->MODEL("redeem");
		if(is_array($_GET['del'])){
			$where['id']	=	array('in',pylode(',',$_GET['del']));
			$del			=	$redeemM->delReward($where,array('type'=>'all'));
			$layer_type		=	1;
			$delid			=	pylode(',',$_GET['del']);
		}else{
			$this->check_token();
			$where['id']	=	(int)$_GET['del'];
			$del			=	$redeemM->delReward($where,array('type'=>'one'));
			$layer_type		=	0;
			$delid			=	(int)$_GET['del'];
		}
		
		if(!$delid){
			$this->layer_msg('请选择要删除的内容！',8);
		}
		
		$del?$this->layer_msg('商品(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
		
	}	
	function set_action(){
		$this -> yuntpl(array('admin/admin_rewordset'));
	}
	function saveset_action(){
		 if($_POST['submit']){
	        $this -> web_config();        
	        $this -> ACT_layer_msg('商品配置设置成功！',9,$_SERVER['HTTP_REFERER'],2,1);
	    }
	}
}
?>