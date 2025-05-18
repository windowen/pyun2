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
class link_model extends model{
	function get_cache(){
		include(LIB_PATH."cache.class.php");
		$cacheM		= 	new cache(PLUS_PATH,$this);
		$makecache	=	$cacheM->link_cache("link.cache.php");
	}
	/**
	* @desc   获取友情链接列表
	* @param  $whereData:查询条件
	* @param  $data:自定义处理数组 
	*/
	public function getList($whereData,$data=array()) {        
       
		$select  =   $data['field'] ? $data['field'] : '*';     
      
		$List    =   $this -> select_all('admin_link',$whereData,$select);

		if(!empty($List)){
			foreach($List as $k => $v){
				if($v['pic']){
					$List[$k]['pic']	=	checkpic($v['pic']);
				}
			}
		}
		
	   return $List;  
    
	}
	/**
	* @desc   获取工具箱详情
	*/
	public function getInfo($where=array(),$data	=	array()){
		
		$select   =   $data['field'] ? $data['field'] : '*';	
			
		$Info	  =	 $this -> select_once('admin_link',$where, $select);
		
		if($Info['pic']){
			$Info['pic_n']	=	checkpic($Info['pic']);
		}
		
		return $Info;
	}
	/**
	* @desc   审核友情链接
	*/
	 function setLinkStatus($id,$data=array())
    {
        if($id){
			
			$return['id']	=	$this->update_once("admin_link",array('link_state'=>$data['status']),array('id'=>$id));
			
			if($return['id']){
				
				$this->get_cache();
				
				$return['msg']		=	'友情链接审核成功！';
				$return['errcode']	=	9;
			}else{
				$return['msg']		=	'友情链接审核失败！';
				$return['errcode']	=	8;
			}
		}else{
			$return['msg']		=	'请选择审核数据！';
			$return['errcode']	=	8;
		}
		return $return;
    }
    function addInfo($data=array())
    {
		$id		=	$data['id'];
		$post	=	$data['post'];
		if($data['utype']=='index'){
			session_start();
			if(md5(strtolower($data['authcode'])) != $_SESSION['authcode'] || empty($_SESSION['authcode'])){
				unset($_SESSION['authcode']);
				$return['msg']		=	'验证码不正确！';
				$return['errcode']	=	8;
				return	$return;
			}
			
		}
		if($data['utype']=='admin'){
			if(preg_match("/[^\d-., ]/",$post['link_sorting'])){
				$return['msg']		=	'请正确填写，排序是数字！';
				$return['errcode']	=	8;
			}
		}
		if($post['sorting']==""){
			$post['sorting']	=	"0";
		}
		if($post['phototype']==""){
			$post['phototype']	=	"0";
		}
		if($return['msg']==''){
			if($id){
				$return['id']		=	$this -> update_once("admin_link",$post,array('id'=>$id));
				$msg	=	'修改';
			}else{
				
				$post['link_time']	=	time();
				$return['id']		=	$this -> insert_into("admin_link",$post);
				$msg	=	'添加';
			}
			if($return['id']){
				$this->get_cache();
				if($data['utype']=='index'){
					$return['msg']	=	'请等待管理员审核！';
				}
				if($data['utype']=='admin'){
					
					$return['msg']	=	'友情链接(ID:'.$return['id'].')'.$msg.'成功！';
				}
				$return['errcode']	=	9;
			}else{
				$return['msg']		=	$msg.'失败！';
				$return['errcode']	=	8;
			}
		}

		return	$return;
    }
	/**
	* @desc   删除友情链接
	*/
	public function delInfo($id,$data=array()){
		
		if(empty($id)){
           
			return	array(
              
				'errcode' 	=> 	8,
				'msg' 		=> 	'请选择要删除的数据！',
				'layertype'	=>	0
            );
        
		}else{
			
			if(is_array($id)){
				
				$ids	=	pylode(',',$id);
				$return['layertype']	=	1;
			
			}else{
				$ids	=	$id;
				$return['layertype']	=	0;
			}
			 
			
			$return['id']	=	$this -> delete_all('admin_link',array('id' => array('in',$ids)),'');
			$this->get_cache();
			$return['msg']		=	'友情链接(ID:'.$ids.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}
		return	$return;
	}
	public function setLinkSite($data=array()){
		
		if($data['uid']){
			$ids	=	@explode(',',$data['uid']);
			$id 	= 	pylode(',',$ids);
			if($id){
				require_once ('site.model.php');
				$siteM 		= 	new site_model($this->db, $this->def);
				$Table 		= 	array('admin_link');
				$siteM->updDid($Table,array('id'=>array('in',$id)),array('did'=>$data['did']));
				
				$this->get_cache();
				
				$return['msg']		=	"友情链接(ID:".$data['uid'].")分配站点成功！";
				$return['errcode']	=	9;
			}else{
				$return['msg']		=	'请正确选择需分配用户！';
				$return['errcode']	=	8;
			}
		}else{
			$return['msg']			=	'参数不全请重试！';
			$return['errcode']		=	8;
		}
		return $return;
	}
	/**
	* @desc 友情链接数目
	*/
	function getLinkNum($where = array()){
		return $this->select_num('admin_link', $where);
	}
}
?>