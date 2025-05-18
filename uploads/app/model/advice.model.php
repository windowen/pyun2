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
class advice_model extends model{
	
	/**
	 * 获取意见反馈列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getList($whereData,$data=array()){
		
		$List		=	$this -> select_all('advice_question',$whereData);

		return	$List;
	}
	/**
	 * 获取单条意见反馈
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	function getInfo($whereData,$data=array()){
	    
	    $field  =	empty($data['field']) ? '*' : $data['field'];
	    if (!empty($whereData)) {
	        
	        $List  =  $this -> select_once('advice_question',$whereData, $field);
	        return $List;
	    }
	}
	public function addInfo($data=array()){
		session_start();
		if($data['infotype']==''){
			return array('msg'=>'请选择意见类型','errcode'=>8);
		}elseif($data['username']==''){
			return array('msg'=>'请填写联系人姓名','errcode'=>8);
		}elseif($data['mobile']==''){
			return array('msg'=>'请填写联系手机','errcode'=>8);
		}elseif($data['content']==''){
			return array('msg'=>'请填写反馈内容','errcode'=>8);
		}elseif($data['authcode']==''){
			return array('msg'=>'请填写验证码','errcode'=>8);
		}elseif(md5(strtolower($data['authcode']))!=$_SESSION['authcode']){
			unset($_SESSION['authcode']);
			return array('msg'=>'验证码错误','errcode'=>8);
		}else{
			$arr		=	array(
					'username'	=>	$data['username'],
					'ctime'		=>	time(),
					'infotype'	=>	$data['infotype'],
					'content'	=>	$data['content'],
					'mobile'	=>	$data['mobile']
			);
			$nid		=	$this -> insert_into("advice_question",$arr);
			
			if($data['utype']=='pc'){
				$url	=	Url('advice');
			}
			if($data['utype']=='wap'){
				$url	=	Url('wap',array('c'=>'advice'));
			}
			if($nid){
				return array('msg'=>'提交成功，感谢你的反馈！','errcode'=>9,'url'=>$url);
			}else{
				return array('msg'=>'提交失败，请重新填写！','errcode'=>8,'url'=>$url);
			}
		}
	}
	/**
	 * 删除意见反馈
	 * $whereData 	查询条件
	 */
	public function delInfo($delId)
	{

	    $return['layertype']	=	0;
		
		if($delId){
		
			if(is_array($delId)){
				$delId	=	pylode(',', $delId);

				$return['layertype']	=	1;
			}
		 
			$return['id']		=	$this->delete_all('advice_question',array('id'=>array('in',$delId)),"");
			
	        $return['msg']		=	'意见反馈(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
	    }else{
	        $return['msg']		=	'请选择您要删除的意见反馈';
	        $return['errcode']	=	'8';
	    }
	    return $return;
	}
	
	public function statusInfo($data=array() , $whereData=array()){
		if(!empty($data)){
			$nid      					=	$this->update_once('advice_question',$data,$whereData);
			
	        $return['msg']				=	'意见反馈(ID:'.$whereData['id'].')';
			$return['errcode']			=	$nid ? '9' :'8';
			$return['msg']				=	$nid ? $return['msg'].'处理成功！' : $return['msg'].'处理失败！';
	    }else{
	        $return['msg']      		=	'请选择您要处理的意见反馈';
	        $return['errcode']  		=	'8';
	    }
	    return $return;
	}
}
?>