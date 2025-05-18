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
class reward_model extends model{
	
	function getList($whereData,$data=array()){
		$ListNew		=	array();
		
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
		$List			=	$this->select_all("reward",$whereData,$data['field']);
		
		if(!empty( $List )){
			
			foreach($List as $key=>$val){
				$nids[] 		= 	$val['nid'];
				$tnids[]	 	= 	$val['tnid'];
			}
			
			$classWhere['id']	=	array('>',0);
			$class				=	$this->select_all("redeem_class",$classWhere);
			
			$classname='';
			foreach($List as $k=>$v){
        		foreach($class as $val){
        			if($v['nid']==$val['id']){
        				$classname			=	$val['name'];
        			}
					
					if($v['tnid']==$val['id']){
						$classname			=	$List[$k]['classname'].'-'.$val['name'];
					}
					
					$List[$k]['classname']	=	$classname;
        		}
        	}
			
			$ListNew['list']	=	$List;
			
		}
		
		return $ListNew;
	}
	
	function addInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('reward',$setData);
			
		}

		return $nid;
	}
	
	function getInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('reward',$whereData,$data['field']);
		}

		return $List;
	}
	
	function upInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('reward',$data,$whereData);
			
		}

		return $nid;
	
	}
	
	function delReward($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
 
		$result			=	$this	->	delete_all('reward',$whereData,$limit);
		
		return	$result;
		
	}
	
}
?>