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
class tpl_model extends model{
	
	/*
	 * 获取商家模板列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getComtplList($whereData=array(),$data=array('field'=>'*')){
		$list	=	$this	->	select_all('company_tpl',$whereData,$data['field']);
		return	$list;
	}
	/*
	 * 获取单个导航
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getComtpl($whereData=array(),$data=array('field'=>'*')){
		$one	=	$this	->	select_once('company_tpl',$whereData,$data['field']);
		return	$one;
	}
	/*
	 * 添加企业模板
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function addComtpl($addData = array())
    {
        if (! empty($addData)) {

            $return             =   array();

            $return['id']       =   $this->insert_into('company_tpl', $addData);

            $return['msg']      =   '企业模板(ID:' . $return['id'] . ')';

            $return['errcode']  =   $return['id'] ? '9' : '8';

            $return['msg']      =   $return['id'] ? $return['msg'] . '添加成功！' : $return['msg'] . '添加失败！';

            return $return;
        }
    }
	/*
	 * 更新企业模板
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function upComtpl($addData=array(),$whereData=array()){
		if(!empty($addData)){
					
			$return['id']		=	$this	->	update_once('company_tpl',$addData,$whereData);
		
			$return['msg']		=	'企业模板(ID:'.$delId.')';
			
			$return['errcode']	=	$return['id'] ? '9' :'8';
			
			$return['msg']		=	$return['id'] ? $return['msg'].'更新成功！' : $return['msg'].'更新失败！';
			
			return	$return;
		}
	}
	/*
	 * 删除企业模板
	 * $whereData 	查询条件
	 */
	function delComtpl($delId){
		$return['layertype']	=	0;
		
		if(!empty($delId))
		{
			if(is_array($delId))
			{
				$delId					=	pylode(',', $delId);

				$return['layertype']	=	1;
			}

			$return['id']		=	$this	->	delete_all('company_tpl',array('id'=>array('in',$delId)),'');
		
			$return['msg']		=	'企业模板(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}
		return	$return;
	}
	/*
	 * 添加导航
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function addNav($addData=array(),$data=array()){
		$return	=	$this	->	insert_into('navigation',$addData);
		return	$return;
	}
	/*
	 * 更新导航
	 * $whereData 	查询条件
	 * $data		自定义
	 */
	function upNav($addData=array(),$whereData=array()){
		$return	=	$this	->	update_once('navigation',$addData,$whereData);
		return	$return;
	}
	/*
	 * 删除导航
	 * $id 			导航id，可以是数组 
	 * $data		自定义
	 */
	function delNav($whereData=array(),$data=array()){

		$result  =  $this -> delete_all('navigation',$where,'');
		
		return	$result;
	}
	/*
	 * 获取简历模板列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getResumetplList($whereData=array(),$data=array('field'=>'*')){
		
		$list  =  $this -> select_all('resumetpl',$whereData,$data['field']);

		if(!empty($list)){

			foreach($list as $k=>$v){

				if($v['pic']){

					$list[$k]['pic']	=	checkpic($v['pic']);
					
				}
			}
		}
		
		return	$list;
	}
	/*
	 * 获取简历模板
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getResumetpl($whereData=array(),$data=array('field'=>'*')){
		
		$list	=	$this	->	select_once('resumetpl',$whereData,$data['field']);

		if($list['pic']){

			$list['pic_n']	=	checkpic($list['pic']);
			
		}
		
		return	$list;
	}
	/*
	 * 添加简历模板
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function addResumetpl($addData=array()){
		if(!empty($addData)){
			
			$return['id']		=	$this	->	insert_into('resumetpl',$addData);
		
			$return['msg']		=	'模板(ID:'.$return['id'].')';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
			
			return	$return;
		}
	}
	/*
	 * 更新简历模板
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function upResumetpl($addData=array(),$whereData=array()){
		if(!empty($addData)){
			
			$return['id']		=	$this	->	update_once('resumetpl',$addData,$whereData);
			
			$return['msg']		=	'模板(ID:'.$return['id'].')';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'更新成功！' : $return['msg'].'更新失败！';
		
			return	$return;
		}
	}
	/*
	 * 删除简历模板
	 * $whereData 	查询条件
	 */
	function delResumetpl($delId){
		$return['layertype']	=	0;
		
		if(!empty($delId))
		{
			if(is_array($delId))
			{
				$delId					=	pylode(',', $delId);

				$return['layertype']	=	1;
			}
			 
			$return['id']		=	$this	->	delete_all('resumetpl',array('id'=>array('in',$delId)),'');
		
			$return['msg']		=	'模板(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}
		return	$return;
	}
	/*
	 * 个人购买简历模板
	 * $data		自定义
	 */
	function payResumetpl($data=array()){
		if(!empty($data)){
			require_once ('statis.model.php');
			$statisM    =   new statis_model($this->db, $this->def);
			
			require_once ('integral.model.php');
			$integralM    =   new integral_model($this->db, $this->def);
			
			$id		=intval($data['id']);
			
			$statis	=	$statisM->getInfo($this->uid,array('usertype'=>1,'field'=>'`tpl`,`paytpls`,`integral`'));
			$info	=	$this -> getResumetpl(array('id'=>$id),array('field'=>'`price`'));
			
			$paytpls=array();
			if($statis['paytpls']){
				$paytpls	=	@explode(',',$statis['paytpls']); 
				
				if(in_array($id,$paytpls)){
					return array('msg'=>'请勿重复购买！','errcode'=>8);
				}
			}
			if($info['price']>$statis['integral']){
				return array('msg'=>$this->config['integral_pricename'].'不足，请先充值！','errcode'=>8);
			}else{ 
				$nid	=	$integralM->company_invtal($data['uid'],1,$info['price'],false,"购买简历模板",true,2,'integral',15);
				
				if($nid){
					$paytpls[]=$id;
					
					$statisM->upInfo(array('paytpls'=>pylode(',',$paytpls)),array('uid'=>$data['uid'],'usertype'=>1));
					return array('msg'=>'购买成功！','errcode'=>9);
				}else{
					return array('msg'=>'购买失败！','errcode'=>8);
				}
			}
		}
	}
	/*
	 * 个人使用简历模板
	 * $addData		自定义
	 */
	function setResumetpl($data=array()){
		if(!empty($data)){
			require_once ('statis.model.php');
			$statisM		=   new statis_model($this->db, $this->def);
			
			require_once ('integral.model.php');
			$integralM		=   new integral_model($this->db, $this->def);
			
			$id				=intval($data['id']);
			
			$statis			=	$statisM->getInfo($data['uid'],array('usertype'=>1,'field'=>'`tpl`,`paytpls`,`integral`'));
			$info			=	$this -> getResumetpl(array('id'=>$id),array('field'=>'`price`'));
			
			$paytpls		=	array();
			if($statis['paytpls']){
				$paytpls	=	@explode(',',$statis['paytpls']);  
			}
			if(in_array($id,$paytpls)==false && $id>0){
				return array('msg'=>'请先购买！','errcode'=>8);
			}
			$statisM -> upInfo(array('tpl'=>$id),array('uid'=>$data['uid'],'usertype'=>1));
			return array('msg'=>'操作成功！','errcode'=>9);
		}
	}
	/*
	 * 获取首页模板主题列表
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getTplindexList($whereData=array(),$data=array('field'=>'*')){
		
		$list	=	$this	->	select_all('tplindex',$whereData,$data['field']);
		if(!empty($list)){
			foreach ($list as $key => $value) {
				if($value['pic']){
					$list[$key]['pic']	=	checkpic($value['pic']);
				}
			}
		}
		return	$list;
	}
	/*
	 * 获取首页模板主题
	 * $whereData 	查询条件
	 * $data		自定义查询字段 field:查询字段，默认为*
	 */
	function getTplindex($whereData=array(),$data=array('field'=>'*')){
		
		$list	=	$this	->	select_once('tplindex',$whereData,$data['field']);
		if($list['pic']){
			$list['pic']	=	checkpic($list['pic']);
		}
		return	$list;
	}
	/*
	 * 添加首页模板主题
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function addTplindex($addData=array()){
		if(!empty($addData)){
			
			$return['id']	=	$this	->	insert_into('tplindex',$addData);
			
			$return['msg']		=	'主题模板(ID:'.$return['id'].')';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
			
			return	$return;
		}
	}
	/*
	 * 更新首页模板主题
	 * $whereData 	查询条件
	 * $addData		自定义
	 */
	function upTplindex($addData=array(),$whereData=array()){
		if(!empty($addData)){
			
			$return['id']	=	$this	->	update_once('tplindex',$addData,$whereData);
			
			$return['msg']		=	'主题模板(ID:'.$return['id'].')';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';
            
            $return['msg']		=	$return['id'] ? $return['msg'].'更新成功！' : $return['msg'].'更新失败！';
			
			return	$return;
		}
	}
	/*
	 * 删除首页模板主题
	 * $whereData 	查询条件
	 */
	function delTplindex($delId){
		$return['layertype']	=	0;
		
		if(!empty($delId))
		{
			if(is_array($delId))
			{
				$delId					=	pylode(',', $delId);

				$return['layertype']	=	1;
			}
			 
			$return['id']		=	$this	->	delete_all('tplindex',array('id'=>array('in',$delId)),'');
		
			$return['msg']		=	'主题模板(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}
		return	$return;
	}
	
	
	
}
?>