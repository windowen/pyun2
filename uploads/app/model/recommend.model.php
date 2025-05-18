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
class recommend_model extends model{
	
	/*
	 * 获取recommend	列表
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	 
	function getList($whereData, $data=array()){
		$ListNew			=	array();
		$List				=	$this -> select_all('recommend', $whereData);
		return	$List;
	}
	
	/*
	* 获取recommend		详情
	* $whereData		查询条件
	* $data				自定义处理数组
	*
	*/
	function getInfo($whereData, $data = array()){		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];		
			$Info			=	$this -> select_once('recommend', $whereData, $data['field']);
		}

		return $Info;	
	}


	/*
	* 添加recommend		详情
	* $data				自定义处理数组
	*/
	function addRecommendInfo($data = array()){
		return $this -> insert_into('recommend', $data);
	}

	/*
	* 获取recommend		数量
	* $whereData		查询条件
	*
	*/
    function getRecommendNum($Where = array()) {
        return $this -> select_num('recommend', $Where);
    }

	/*
	* 创建回收站
	* $postData		自定义处理数组
	*
	*/

	function addInfo($data){

		if(!empty($data)){
			
			$return['id']		=	$this -> insert_into('outside',$data);
			
            $return['msg']		=	'回收站(ID:'.$return['id'].')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
            
            return $return;
		}
	
	}

	/*
	* 恢复回收站
	* $postData		自定义处理数组
	*
	*/

	function setInfo($table,$postData){

		if(!empty($postData)){
			
			$return['id']		=	$this -> insert_into($table,$postData);
			
            $return['msg']		=	$table.'(ID:'.$return['id'].')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'恢复成功！' : $return['msg'].'恢复失败！';
            
            return $return;
		}
	
	}

	 
	
}
?>