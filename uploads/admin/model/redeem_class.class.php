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
class redeem_class_controller extends adminCommon{
	function index_action(){
		$redeemM			=	$this->MODEL('redeem');
		$where['keyid']		=	'0';
		$where['orderby']	=	'sort,asc';
		$position			=	$redeemM->GetRewardClass($where);
		$this->yunset("position",$position['list']);
		$this->yuntpl(array('admin/redeem_class'));
	}
	
	function save_action(){
		$redeemM			=	$this->MODEL('redeem');
	    $_POST				=	$this->post_trim($_POST);	
	    $position			=	explode('-',$_POST['name']);
		foreach ($position as $val){
			if($val){
				$name[]=$val;
			}
		}
		$where['name']		=	array('in',@implode(',', $name));
		$redeem_class		=	$redeemM->GetRewardClass($where);
		
		if(empty($redeem_class['list'])){
    		$sort=explode('-', $_POST['sort']);
			
            foreach ($name as $key=>$val){
				
                if($_POST['ctype']=='1'){//一级分类
                    $value['name']	=	$val;
                }else{
					$value['name']	=	$val;
					$value['keyid']	=	intval($_POST['nid']);
                }
				
				$add	=	$redeemM->addRedeemClassInfo($value);
            }
			$this->cache_action();
			$add		?	$msg	=	2	:	$msg	=	3;
			$this->MODEL('log')->addAdminLog("商品类别(ID:".$add.")添加成功");
		}else{
			$msg	=	1;
		}
		echo $msg;die;
	}
	
	function up_action(){
		$redeemM					=	$this->MODEL('redeem');
		if((int)$_GET['id']){
			$oneWhere['id']			=	(int)$_GET['id'];
			$onejob					=	$redeemM->getRedeemClassInfo($oneWhere);
			
			$twoWhere['keyid']		=	(int)$_GET['id'];
			$twoWhere['orderby']	=	'sort,asc';
			$twojob					=	$redeemM->GetRewardClass($twoWhere);
			
			$this->yunset("onejob",$onejob);
			$this->yunset("twojob",$twojob['list']);
			$this->yunset("id",(int)$_GET['id']);
		}
		$pWhere['keyid']			=	'0';
		$position					=	$redeemM->GetRewardClass($pWhere);
		$this->yunset("position",$position['list']);
		$this->yuntpl(array('admin/redeem_class'));
	}
	
	function upp_action(){
		$redeemM	=	$this->MODEL('redeem');
		if($_POST['update']){
			if(!empty($_POST['position'])){
				$value['name']	=	$_POST['position'];
				$value['sort']	=	$_POST['sort'];
				$where['id']	=	$_POST['id'];
				$up				=	$redeemM->upRedeemClassInfo($where,$value);
				$this->cache_action();
				$up?$this->ACT_layer_msg("商品类别(ID:".$_POST['id'].")更新成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("更新失败，请销后再试！",8,$_SERVER['HTTP_REFERER']);
			}else{
				$this->ACT_layer_msg("请正确填写你要更新的分类！",8,$_SERVER['HTTP_REFERER']);
			}
		}
		$this->yuntpl(array('admin/redeem_class'));
	}
	
	function del_action(){
		$redeemM	=	$this->MODEL('redeem');
		if(is_array($_POST['del'])){
			
			$where['id']	=	array('in',pylode(',',$_POST['del']));
			
			$where['keyid']	=	array('in',pylode(',',$_POST['del']),'OR');
			
			$del			=	$redeemM->delRedeemClass($where,array('type'=>'all'));
			
			$layer_type		=	1;
			
			$delid			=	pylode(',',$_POST['del']);
			
		}else{
			
			$this->check_token();
			
			$where['id']	=	(int)$_GET['delid'];
			
			$where['keyid']	=	array('=',(int)$_GET['delid'],'OR');
			
			$del			=	$redeemM->delRedeemClass($where,array('type'=>'one'));
			
			$layer_type		=	0;
			
			$delid			=	(int)$_GET['delid'];
		}
		
		if(!$delid){
			
			$this->layer_msg('请选择要删除的内容！',8);
		
		}
		$this->cache_action();
		isset($del)?$this->layer_msg('商品类别删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
		
		
	}
	
	function ajax_action(){
		$redeemM	=	$this->MODEL('redeem');
		if($_POST['sort']){
			$sValue['sort']	=	$_POST['sort'];
			$sWhere['id']	=	$_POST['id'];
			$up				=	$redeemM->upRedeemClassInfo($sWhere,$sValue);
			$this->MODEL('log')->addAdminLog("商品类别(ID:".$_POST['id'].")排序修改成功");
		}
		
		if($_POST['name']){
			$nValue['name']	=	$_POST['name'];
			$nWhere['id']	=	$_POST['id'];
			$up				=	$redeemM->upRedeemClassInfo($nWhere,$nValue);
			$this->MODEL('log')->addAdminLog("商品类别(ID:".$_POST['id'].")名称修改成功");
		}
		$this->cache_action();
		echo '1';die;
	}
	
	function cache_action()	{
		include(LIB_PATH."cache.class.php");
		$cacheclass	= 	new cache(PLUS_PATH,$this->obj);
		$makecache	=	$cacheclass->redeem_cache("redeem.cache.php");
	}
}

?>