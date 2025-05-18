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
class navmap_model extends model{
	
	/**
	 * 获取网站地图列表
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function getNavMapList($whereData=array(),$data=array()){
		$field	=   $data['field'] ? $data['field'] : '*';
		$list  	=  	$this -> select_all('navmap',$whereData,$field);
		return	$list;
	}
	/**
	 * 获取网站地图详细信息
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	public function getNavMap($whereData=array(),$data=array('field'=>'*')){
		$info  =  $this -> select_once('navmap',$whereData,$data['field']);
		return	$info;
	}
	/**
	 * 添加网站地图
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function addNavMap($addData=array(),$data=array()){
		$return  =  $this -> insert_into('navmap',$addData);
		return	$return;
	}
	/**
	 * 更新网站地图
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function upNavMap($whereData=array(),$addData=array()){
		$return  =  $this -> update_once('navmap',$addData,$whereData);
		return	$return;
	}
	/**
	 * 删除网站地图
	 */
	public function delNavMap($delId){
		if(empty($delId)){
			return array('msg'=>'请选择您要删除的信息！','errcode'=>8);
		}else{
			if(is_array($delId)){
				$delId	=	pylode(',',$delId);
				$return['layertype']	=	1;
			}else{
				$return['layertype']	=	0;
			}
			 
			$nid	=	$this->delete_all('navmap',array('id'=>array('in',$delId),'nid'=>array('in',$delId,'OR')),'');	
			if($nid){
				$return['msg']		=	'网站地图';
				$return['errcode']	=	$nid?'9':'8';
				$return['msg']		.=	$nid?'删除成功！':'删除失败！';
			}
		}	
		return	$return;
	}
}
?>