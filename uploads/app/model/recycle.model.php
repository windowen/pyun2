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
class recycle_model extends model{
	
	/*
	 * 获取回收站列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData,$data=array()){

		$ListNew	=	array();
		$List		=	$this -> select_all('recycle',$whereData);
		return	$List;
	}
	
	/*
	* 获取回收站详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
 	function getInfo($whereData, $data = array()){
		
		if(!empty($whereData)){

			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$Info	=	$this -> select_once('recycle', $whereData, $data['field']);
		}

		return $Info;
	
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

	 

	/**
	 *	@desc	恢复数据,单表恢复
	 *	@param	array $where
	 */
	function recoverTb($where = array()){
	
		$return	=	array(
			'errcode'	=>	8,
			'msg'		=>	''
		);

		if(!empty($where)){

			
			
			$id	=	$where['id'];

			if(is_array($id)){
				
				$ids	=	$id;
			}else{
				
				$ids	=	@explode(',', $id);
			}
			

			$recycleList	=	$this -> getList(array('id' => array('in', pylode(',', $ids))));
			
			
			if(!empty($recycleList)){
				 
				foreach($recycleList as $v){
					
					$body	=	unserialize($v['body']);
					 
					$this	->	insert_into($v['tablename'],$body);
					$this	->	delRecycle(array('id' => $v['id']));
				}
				
				$return['errcode']	=	9;
				$return['msg']		=	'数据恢复成功！';

			}else{
				  
				$return['msg']		=	'请选择有效数据进行恢复！';
			}
		}else{

			$return['msg']			=	'参数错误！';
		}

		return $return;
	}

	/**
	 *	@desc	恢复数据，关联恢复，通过删除插入的总记录进行查询恢复
	 *	@param	array $where
	 */
	function recoverTbs($where = array()){
	
		$return	=	array(
			'errcode'	=>	8,
			'msg'		=>	''
		);

		if(!empty($where)){
			
			$id	=	$where['id'];
			
			if(is_array($id)){
				
				$ids	=	$id;
			}else{
				
				$ids	=	@explode(',', $id);
			}
			

			$rLists		=	$this -> getList(array('id' => array('in', pylode(',', $ids))));
			
			if(!empty($rLists)){
				
				$rcyIds	=	array();	

				foreach($rLists as $v){
					$rcyIds	=	array_merge($rcyIds, @explode(',', $v['rids']));
				}
				
				$rcyList	=	$this -> getList(array('id' => array('in', pylode(',', $rcyIds))));		
				 
				foreach($rcyList as $v){
					
					$body	=	unserialize($v['body']);
					 
					$this	->	insert_into($v['tablename'],$body);
					$this	->	delRecycle(array('id' => $v['id']));
				}

				$this	->	delRecycle(array('id' => array('in', pylode(',', $ids))));
				$return['errcode']	=	9;
				$return['msg']		=	'数据恢复成功！';

			}else{
				  
				$return['msg']		=	'请选择有效数据进行恢复！';
			}
		}else{

			$return['msg']			=	'参数错误！';
		}

		return $return;
	}

	/*
	* 删除数据调用
	* $whereData 	查询条件 istime	是否根据时间删除
	*
	*/
 	function delRecycle($whereData)
	{
		if($whereData)
		{
			$return['id']		=	$this -> delete_all('recycle', $whereData, '', '', '1');
			
			$return['msg']		=	'回收站数据(ID:'.$delId.')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
			
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}else{
			$return['msg']		=	'请选择您要删除的信息！';
			$return['errcode']	=	8;
		}

		return	$return;
	}
	
	
}