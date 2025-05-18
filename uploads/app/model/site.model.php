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
class site_model extends model{
	/**
	 * 修改分站的id
	 * $tableData		修改的表名数组
	 * $whereData       修改条件数据
     * $upData          修改的数据
	 */
    public function updDid($tableData = array(), $whereData = array(), $upData = array()){
		if(empty($tableData)){
			return false;
		}
		foreach($tableData as $tableV){		
			$this->update_once($tableV, $upData, $whereData);
		}
		return true;
	}
	/*
	 * 获取分站列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData,$data=array()){
		
		$ListNew	=	array();
		
		$field		=	$data['field'] ? $data['field'] : '*';
		
		$List		=	$this -> select_all('domain',$whereData,$field);
		
		return	$List;
		
	}
	
	/*
	* 获取分站详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	

	function getInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('domain',$whereData,$data['field']);
			
			if($List){
				
				$List['weblogo']  = checkpic($List['weblogo']);
				
			}
		}

		return $List;
	
	}
	
	/*
	* 创建分站
	* $setData 	自定义处理数组
	* $whereData 	查询条件
	*
	*/

	function addInfo($setData = array(),$whereData = array()){

		if(!empty($setData)){
			$data=array(
				'title'			=>	$setData['title'],
				'mode'			=>	$setData['mode'],
				'domain'		=>	$setData['domain'],
				'indexdir'		=>	$setData['indexdir'],
				'province'		=>	$setData['provinceid'],
				'cityid'		=>	$setData['cityid'],
				'three_cityid'	=>	$setData['three_cityid'],
				'type'			=>	$setData['type'],
				'tpl'			=>	$setData['tpl'],
				'hy'			=>	$setData['hy'],
				'fz_type'		=>	$setData['fz_type'],
				'style'			=>	$setData['style'],
				'webtitle'		=>	$setData['webtitle'],
				'webkeyword'	=>	$setData['webkeyword'],
				'webmeta'		=>	$setData['webmeta']
			);
			if(isset($setData['weblogo'])){
				$data['weblogo']	=	$setData['weblogo'];
			}
		}
		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('domain',$data,$whereData);
			
		}else{
			
			$nid	=	$this -> insert_into('domain',$data);
			
		}

		return $nid;
	
	}
	/*
	* 更新数据
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/

	function upInfo($data = array(),$whereData){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('domain',$data,$whereData);
			
		}

		return $nid;
	
	}
	/*
	* 删除分站
	* $delId 	条件id
	* $data 	自定义处理数组 isclean是否是清除数据
	*/
	function delDomain($delId){
		
		if($delId)
		{
			if(is_array($delId))
			{
				$delId	=	pylode(',',$delId);

				$return['layertype']	=	1;
			}else{
				$return['layertype']	=	0;
			}

			$return['id']		=	$this -> delete_all("domain",array('id' => array('in',$delId)),"");
			
			$return['msg']		=	'分站(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}else{
			$return['msg']		=	'请选择要删除的分站！';
			$return['errcode']	=	8;
		}

		return	$return;
	}
	
	/*
	* 会员列表分配分站
	* $data 	自定义处理数组
	*/
	function memberSiteDid($data=array()){
		if(!empty($data)){
			if(empty($data['uid'])){
				
				return array('msg'=>'参数不全请重试！','status'=>8);
				
			}
			
			$uids	=	@explode(',',$data['uid']);
			
			$uid	=	pylode(',',$uids);
			
			if(empty($uid)){
				
				return array('msg'=>'请正确选择需分配用户！','status'=>8);
				
			}
			$didData	=	array('did' => intval($data['did']));
			
			$minfo		=	$this->select_all('member',array('uid'=>array('in',$uid)),'`uid`,`usertype`');
			
			if(is_array($minfo)&&$minfo){
				foreach($minfo as $v){
					if($v['usertype']==1){
						$rids[]		=	$v['uid'];
					}
					if($v['usertype']==2){
						$comids[]	=	$v['uid'];
					}
					
				}
			}
			if(is_array($rids)&&$rids) {
				
				$Table	=	array(
					'member', 'company_cert', 'company_order',
					
					'look_job','member_statis',
					
					'resume','resume_expect','userid_job'
				);
			}
			if(is_array($comids)&&$comids) {
				
				$Table	=	array(
					'member', 'company_cert', 'company_order',
					
					'company','company_statis','company_job','company_news',
					
					'company_product','partjob','hotjob','user_change'
				);
				$this -> updDid(array('userid_msg'), array('fid' => array('in', $uid)), $didData);
			}
			
			if(is_array($rids)||is_array($comids)) {
				
				$this -> updDid(array('report'), array('p_uid' => array('in', $uid)), $didData);
				
			}
			if(is_array($comids)) {
				
				$this -> updDid(array('down_resume'), array('comid' => array('in', $uid)), $didData);
				
				$this -> updDid(array('look_resume', 'userid_job'), array('com_id'=>array('in', $uid)), $didData);
				
			}
			
			$this -> updDid(array('company_pay'),array('com_id'=>array('in', $uid)),$didData);
			
			$this -> updDid($Table,array('uid'=>array('in', $uid)),$didData);
			
			$return['msg']		=	'会员(ID:'.$data['uid'].')分配站点成功！';
			
			$return['errcode']	=	9;
		}
		return $return;
	}
}
?>