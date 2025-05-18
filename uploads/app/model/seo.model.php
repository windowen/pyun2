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
class seo_model extends model{
	
	/**
	 * 获取seo列表
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function getSeoList($whereData=array(),$data=array()){
		$field	=   $data['field'] ? $data['field'] : '*';
		$list  	=  	$this -> select_all('seo',$whereData,$field);
		return	$list;
	}
	/**
	 * 获取seo详细信息
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	public function getSeoInfo($whereData=array(),$data=array('field'=>'*')){
		$info  =  $this -> select_once('seo',$whereData,$data['field']);
		return	$info;
	}
	/**
	 * 添加seo
	 * $data		自定义
	 */
	public function addSeo($addData=array(),$data=array()){
		$return  =  $this -> insert_into('seo',$addData);
		return	$return;
	}
	/**
	 * 更新seo
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	public function upSeo($whereData=array(),$addData=array()){
		$return  =  $this -> update_once('seo',$addData,$whereData);
		return	$return;
	}
	/**
	 * 删除seo
	 * 删除条件
	 */
	public function delSeo($whereData=array(),$data=array()){
		$result	=  $this -> delete_all('seo',$whereData,'');		
		return	$result;
	}
}
?>