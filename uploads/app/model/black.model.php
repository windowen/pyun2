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
class black_model extends model{	
	/**
     * @desc 引用log类，添加用户日志
     *
     * @param   int     $uid
     * @param   int     $usertype
     * @param   string  $content
     * @param   string  $opera
     * @param   string  $type
     * @return  boolean
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '')
    {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        
        return $LogM->addMemberLog($uid, $usertype, $content, $opera = '', $type = '');
    }
	 /**
     * @desc 获取黑名单详细信息
     */
	function getBlackInfo($where,$data	=	array()){

		$field			=    $data['field'] ? $data['field'] : '*';		
		$info		=	 $this -> select_once('blacklist',$where, $field);
		return $info;
	
	}
	// 职位黑名单
    function getBlackList($Where = array(),$data=array()) {
        
        $select =	$data['field'] ? $data['field'] : '*';
        
        $List	=	$this->select_all('blacklist', $Where,$select);
        
        return $List;
        
    }
	// 职位黑名单数目
    function getBlackNum($Where = array()) {
        return $this->select_num('blacklist', $Where);
    }
	/**
     * @desc 个人面试通知里的屏蔽企业
     */
	public function addBlacklist($data = array()){
		if($data['type']=='yqms'){//个人面试通知里的屏蔽企业
			
			$id			=	$data['id'];
			$uid		=	intval($data['uid']);
			$usertype	=	intval($data['usertype']);

			include_once ('job.model.php');
			$jobM	=   new job_model($this->db, $this->def);
			$info	=	$jobM -> getYqmsInfo(array('id'=>$id),array('field'=>'fid,fname'));
			
			$arr	=	array(
				'p_uid'		=>	$info['fid'],
				'inputtime'	=>	time(),
				'c_uid'		=>	$uid,
				'usertype'	=>	1,
				'com_name'	=>	$info['fname']
			);
			$haves	=	$this -> getBlackInfo(array('c_uid'=>$uid,'p_uid'=>$info['fid'],'usertype'=>1));
			if(is_array($haves)){
				
				return array('msg'=>'该用户已在您黑名单中！','errcode'=>8);
			}else{
				$nid=$this -> insert_into("blacklist",$arr);
				
				$jobM -> delYqms('',array('where'=>array('uid'=>$uid,'fid'=>$info['fid'])));
				
				if($nid){
					
					$this -> addMemberLog($data['uid'],$data['usertype'],"屏蔽公司 <".$info['fname']."> ，并删除邀请信息",26,3);
					
					return array('msg'=>'操作成功！','errcode'=>9);
				}else{
					return array('msg'=>'操作失败！','errcode'=>8);
				}
			}
		}elseif($data['cuid']){//个人隐私里屏蔽企业
			$cuid	=	$data['cuid'];//获取企业uid
			if(!empty($cuid)){
				if(is_array($cuid)){
					
					$ids    				=	$cuid;
				}else{
					
					$ids   	 				=   @explode(',', $cuid);
				}
				
				$id             =   pylode(',', $ids);
				
				require_once ('company.model.php');
				$companyM	=	new company_model($this->db, $this->def);
				
				$company	=	$companyM->getList(array('uid'=>array('in',$id)),array('field'=>'`uid`,`name`'));
				
				$cdata		=	array();
				foreach($company['list'] as $v){
					$cdata	=	array(
						'p_uid'		=>	$v['uid'],
						'c_uid'		=>	$data['uid'],
						'inputtime'	=>	time(),
						'usertype'	=>	1,
						'com_name'	=>	$v['name']
						
					);
					$this->insert_into("blacklist",$cdata);
				}
				return array('msg'=>'操作成功！','errcode'=>9,'layertype'=>1);
			}else{
				return array('msg'=>'请选择要屏蔽的公司！','errcode'=>8,'layertype'=>1);
			}
		}
	}
	/**
	 * 删除黑名单
	 * @param string $id    格式：单个，如1 ; 批量，如1,2,3
	 * @param array $data
	 */
	 function delBlackList($id = null , $data = array()){
	    
	    if(!empty($id)|| !empty($data['where'])){
	        $where      =   array();

			if(!empty($id)){
				if(is_array($id)){
	            
					$ids    				=	$id;
					
					$return['layertype']	=	1;
				}else{
					
					$ids   	 				=   @explode(',', $id);
					$return['layertype']	=	0;
				}
				
				$id             =   pylode(',', $ids);
				$where['id']    =   array('in', $id);
			}
	        
	        if ($data['where']) {
                
                $where          =   array_merge($where, $data['where']);
                
            }
			$return['id']	=	$this -> delete_all('blacklist', $where, '');
			
			if($data['uid']){
				if($data['type']=='all'){
					$this -> addMemberLog($data['uid'],$data['usertype'],"清空公司黑名单信息",26,3);
				}else{
					$this -> addMemberLog($data['uid'],$data['usertype'],"删除公司黑名单信息",26,3);
				}
			}
	        $return['errcode']	=	$return['id'] ? '9' :'8';
	        $return['msg']		=	$return['id'] ? '删除成功！' : '删除失败！';
		}elseif($data['where']){
			
			$where	=	$data['where'];
			
			$nid	=	$this -> delete_all('blacklist',$where,'');
			
			return	$nid;
	    }else{
	        $return['msg']			=	'请选择您要删除的数据！';
	        $return['errcode']		=	8;
			$return['layertype']	=	0;
	    }
	    
	    return	$return;
	}
	
	/**
     * @desc 
     */
	public function addBlackone($data = array()){
		
		if(!empty($data)){
			
			$nid	=	$this->insert_into("blacklist",$data);
			
		}
		
		return	$nid;
		
	}
}
?>