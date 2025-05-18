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
class templates_model extends model{
	
	/*
	 * 获取配置列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('templates',$whereData);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	/*
	* 获取配置详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	

	function getInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('templates',$whereData,$data['field']);
		}

		return $List;
	
	}

	/*
	* 创建配置
	* $setData 	自定义处理数组
	*
	*/
	

	function addInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('templates',$setData);
			
		}

		return $nid;
	
	}

	/*
	* 更新配置
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/

	function upInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('templates',$data,$whereData);
			
		}

		return $nid;
	
	}
	/*
	* 查询数量
	* $whereData 	查询条件
	*
	*/

	function getNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('templates',$whereData);
			
		}

		return $num;
	
	}
	
}
?>