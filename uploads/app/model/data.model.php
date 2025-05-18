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
class data_model extends model{
	
	/*
	 * 获取数据调用列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('outside',$whereData);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	/*
	* 获取数据调用详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	

	function getInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('outside',$whereData,$data['field']);
		}

		return $List;
	
	}

	/*
	* 创建数据调用
	* $postData		自定义处理数组
	*
	*/
	

	function addInfo($postData){

		if(!empty($postData)){
			$data	=	array(
				'name'		=>	$postData['name'],
				'type'		=>	$postData['type'],
				'byorder'	=>	$postData['byorder'],
				'code'		=>	$postData['code'],
				'num'		=>	$postData['num'],
				'titlelen'	=>	$postData['titlelen'],
				'infolen'	=>	$postData['infolen'],
				'edittime'	=>	$postData['edittime'],
				'urltype'	=>	$postData['urltype'],
				'timetype'	=>	$postData['timetype'],
				'where'		=>	$postData['where']
			);
			
			$return['id']		=	$this -> insert_into('outside',$data);
			
            $return['msg']		=	'数据调用(ID:'.$return['id'].')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
            
            return $return;
		}
	
	}

	/*
	* 更新数据调用
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/

	function upInfo($postData = array(),$whereData){

		if(!empty($whereData)){
			
			$data	=	array(
				'name'		=>	$postData['name'],
				'type'		=>	$postData['type'],
				'byorder'	=>	$postData['byorder'],
				'code'		=>	$postData['code'],
				'num'		=>	$postData['num'],
				'titlelen'	=>	$postData['titlelen'],
				'infolen'	=>	$postData['infolen'],
				'edittime'	=>	$postData['edittime'],
				'urltype'	=>	$postData['urltype'],
				'timetype'	=>	$postData['timetype'],
				'where'		=>	$postData['where']
			);
			$return['id']		=	$this -> update_once('outside',$data,$whereData);
			
            $return['msg']		=	'数据调用(ID:'.$return['id'].')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'修改成功！' : $return['msg'].'修改成功！';
            
            return $return;
			
		}
	
	}

	/*
	* 删除数据调用
	* $whereData 	查询条件
	*
	*/
	function delData($delId)
	{
	
		if($delId)
		{
			if(is_array($delId))
			{
				$delId	=	pylode(',',$delId);

				$return['layertype']	=	1;
			}else{
				$return['layertype']	=	0;
			}
			$return['id']		=	$this -> delete_all('outside',array('id' => array('in',$delId)),'');
			
			$return['msg']		=	'数据调用(ID:'.$delId.')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
			
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}else{
			$return['msg']		=	'请选择您要删除的数据调用！';
			$return['errcode']	=	8;
		}

		return	$return;
	}
	
	
}