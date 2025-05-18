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
class yqmb_model extends model{
	
	function getInfo($whereData=array(),$data=array()){
		
		if(!empty($whereData)){

			$field		=	$data['field'] ? $data['field'] : '*';
		
			$info		=	$this -> select_once('yqmb',$whereData,$field);

			if($info['intertime']){
				$info['intertime'] = date('Y-m-d H:i:s',$info['intertime']);
			}
			
			return	$info;
		}
	}
	function getList($whereData=array(),$data=array()){
		
		if(!empty($whereData)){

			$field		=	$data['field'] ? $data['field'] : '*';
		
			$List		=	$this -> select_all('yqmb',$whereData,$field);
			foreach($List as $k=>$v){
				if($v['intertime']){
					$List[$k]['intertime'] = date('Y-m-d H:i:s',$v['intertime']);
				}
			}
			if($data['utype']=='admin'){
				$List	=	$this->moreListData($List);
			}
			return	$List;
		}
	}
	function moreListData($list=array()){
		if(!empty($list)){
			$uids = array();
			foreach($list as $k=>$v){
				if($v['uid']){
					$uids[] = $v['uid'];
				}
			}

			if(!empty($uids)){
				$comWhere['uid'] = array('in',pylode(',',$uids));
				$companys	=	$this -> select_all('company',$comWhere,'`uid`,`name`');
			}
			$comname = array();
			foreach($companys as $ck=>$cv){
				$comname[$cv['uid']] = $cv['name'];
			}
			if(!empty($comname)){
				foreach($list as $lk=>$lv){
					$list[$lk]['comname'] = $comname[$lv['uid']];
				}
			}
		}
		return $list;
	}
	function getNum($whereData=array(),$data=array()){

		if(!empty($whereData)){

			$num		=	$this -> select_num('yqmb',$whereData);
		
			return	$num;
		}
		
	}
	/*
	* 添加邀请模板
	* $setData 	自定义处理数组
	* $whereData 	查询条件
	*
	*/

	function addInfo($setData = array(),$data=array(),$whereData=array()){

		$return = array();

		if(!empty($setData)){

			if($data['uid']){

				$com = $this -> select_once('company',array('uid'=>$data['uid']),'`uid`');
				
				
				if(!empty($com)){

					$mbnum	=	$this -> select_num('yqmb',array('uid'=>$com['uid']));


					if(empty($whereData) && $mbnum >= $this->config['com_yqmb_num']){

						$return['error'] 	= 4;
						$return['msg'] 		= '最多只能设置'.$this->config['com_yqmb_num'].'个面试模板';

					}else{
						$intertime	=	strtotime($setData['intertime']);
						if (empty($setData['linkman'])) {
						    $return['msg']		=	'联系人不能为空！';
						}
						elseif (empty($intertime)) {
						    $return['msg']		=	'面试时间不能为空！';
						}
						elseif ($intertime < time()) {
						    $return['msg']		=	'面试时间不能小于当前时间！';
						}
						elseif (empty($setData['linktel'])) {
						    $return['msg']		=	'联系方式不能为空！';
						}
						elseif(!CheckMoblie($setData['linktel']) && !CheckTell($setData['linktel'])){
							$return['msg']		=	'手机格式错误';
						}
				        elseif (empty($setData['address'])) {
				            $return['msg']		=	'面试地址不能为空！';
				        }else{
				        	
				        	$setData['name'] = $setData['name'] ? $setData['name'] : $setData['linkman'].'邀请面试模板';

				        	$dataV	=	array(
								'uid'			=>	$com['uid'],
								'name'			=>	$setData['name'],
								'content'		=>	$setData['content'],
								'address'		=>	$setData['address'],
								'linkman'		=>	$setData['linkman'],
								'linktel'		=>	$setData['linktel'],
								'intertime'		=>	$intertime,
								'did'			=>	$setData['did'],
								'addtime'		=>	time(),
							);

							

							if(!empty($whereData)){
								
								$nid	=	  $this -> update_once('yqmb',$dataV, $whereData);
								
								$return['msg']	=	'更新';
							}else{
								$nid	=	$this -> insert_into('yqmb',$dataV);
								$return['msg']	=	'添加';
							}

							if($nid){
								$return['error'] 	= 1;
								$return['msg'] 		.= '成功';
							}else{
								$return['error'] 	= 2;
								$return['msg'] 		.= '失败';
							}
				        }
					}
					
				}else{
					$return['error'] 	= 2;
					$return['msg'] 		= '数据异常，请重试';
				}

			}
		}else{

			$return['error'] 	= 2;
			$return['msg'] 		= '数据异常，请重试';

		}

		$return['errcode']	=	$nid ? '9' :'8';

		return $return;
	
	}
	
	public function delYqmb($delId,$data=array()){
	    
		if(!empty($delId)){
			
			$return['layertype']	=	0;
			
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			}
		}
		if($data['uid']){
			$delWhere	=	array('id' => array('in',$delId),'uid'=>$data['uid']);
		}else{
			$delWhere	=	array('id' => array('in',$delId));
		}
		$return['id']		=	  $this -> delete_all('yqmb',$delWhere,'');
		
		$return['errcode']	=	$return['id'] ? '9' :'8';
		
		$return['msg']		=	$return['id'] ? '删除成功！' :'删除失败！';
		
		return	$return;
	}
	
	
}
?>