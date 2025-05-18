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
class hr_model extends model{

	/**
	* @desc   获取工具箱列表
	* @param  $whereData:查询条件
	* @param  $data:自定义处理数组 
	*/
	public function getList($whereData,$data=array()) {        
       
		$select  =   $data['field'] ? $data['field'] : '*';     
      
		$List    =   $this -> select_all('toolbox_doc',$whereData,$select);
		
		return $List;
	}
	
	/**
	* @desc   获取工具箱详情
	*/
	public function getInfo($id,$data	=	array()){
		
		if(!empty($id)){
			
			$select   =   $data['field'] ? $data['field'] : '*';	
			
			$Info	  =	 $this -> select_once('toolbox_doc', array('id'=>intval($id)), $select);
		}
		return $Info;
	
	}
	
	/**
	* @desc   添加文档
	*/
	public function addHrInfo($data){
		
		$PostData = array(
			
			'name'		=>	$data['name'],
			
			'cid'		=>	$data['cid'],
			
			'is_show'	=>	$data['is_show'],
			
			'url'		=>	$data['url'],
			
			'add_time'	=>	time(),
		
		);
		
		if ($data && is_array($data)){
			
			$nid	=	$this->insert_into('toolbox_doc', $PostData);
		}
		
		return $nid;
	
	}
	/**
	* @desc   更新文档
	*/
	public function upHrInfo($whereData, $data = array()){
		
		$PostData	=	$data['post'];
		
		if ($PostData && is_array($PostData)){
			 $nid  	=  $this -> update_once('toolbox_doc', $PostData, $whereData);
		}
		return $nid;
	}
	/**
	* @desc   删除文档数据
	*/
	public function delHr($delId){
		
		if(empty($delId)){
           
			return	array(
              
			  'errcode' => 8,
               
			  'msg' 	=> '请选择要删除的数据！',                
            );
        
		}else{
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			
			}else{
				
				$return['layertype']	=	0;
			}
			
			$row	=	$this->getList(array('id'=>array('in',$delId)));
			
			$rowlist	=	$row['list'];
			
			if(is_array($rowlist)){
				
				foreach($rowlist as $v){
					
					@unlink("..".$v['url']);
				
				}
			
			}

			 
			$delid=$this -> delete_all('toolbox_doc',array('id' => array('in',$delId)),'');
			
			if($delid){
				
				$return['msg']		=	'文档';
				
				$return['errcode']	=	$delid ? '9' :'8';
				
				$return['msg']		=	$delid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
			}
			
		}
		
		return	$return;
		
	}
	
	/**
	* @desc   获取工具箱类别列表
	*/
	public function getClassList($where = array(), $field = '*'){
	    
	    $hrclass  =   $this -> select_all('toolbox_class', $where, $field);
	    if(!empty($hrclass)){
	    	foreach($hrclass as $k=>$v){
	    		if($v['pic']){
	    			$hrclass[$k]['pic']	=	checkpic($v['pic']);
	    		}
	    	}
	    }
	    
	    return $hrclass;
	}
	
	/**
	* @desc   获取工具箱类别详情
	*/
	function getClassInfo($where = array(), $data	=	array()){
		
		$select    		 	=   $data['field'] ? $data['field'] : '*';	
			
		$Info	=	 $this -> select_once('toolbox_class',$where, $select);
		if($Info['pic']){
			$Info['pic']	=	checkpic($Info['pic']);
		}
		return $Info;
	}
	/**
	* @desc   添加工具箱类别
	*/
	public function addClassInfo($data = array()){
		
		$PostData = array(
			
			'name'		=>	$data['name'],
			
			'content'	=>	$data['content'],
			
			'pic'		=>	$data['pic'],
		
		);
		
		if ($data && is_array($data)){
			
			$nid	=	$this->insert_into('toolbox_class', $PostData);
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   修改工具箱类别
	*/
	public function upClassInfo($whereData, $data = array()){
		
		if(!empty($whereData)) {
			
			$PostData=array(
				
				'name'		=>	$data['name'],
					
				'content'	=>	$data['content'],
				
				
			
			);
			if($data['pic']){

				$PostData['pic']	=	$data['pic'];

			}
			
			if ($data && is_array($data)){
	           
			   $nid  =  $this -> update_once('toolbox_class', $PostData, array('id'=>$whereData['id']));
	        
			}
			
			return $nid;
		
		}
	
	}
	
	/**
	* @desc   删除工具箱类别
	*/
	public function delHrClass($delId){
		
		if(empty($delId)){
           
			return	array(
              
			  'errcode' => 8,
               
			  'msg' 	=> '请选择要删除的数据！',                
            );
        
		}else{
			
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			
			}else{
				
				$return['layertype']	=	0;
			}
			
			$result	=	$this -> delete_all('toolbox_class',array('id' => array('in',$delId)),'');
			
			if($result){

				$this -> delete_all('toolbox_doc',array('cid' => array('in',$delId)),'');
			}
			if($result){
				
				$return['msg']		=	'HR类别';
				
				$return['errcode']	=	$result ? '9' :'8';
				
				$return['msg']		=	$result ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
			}
		
		}
	
		return	$return;
		
	}

}
?>