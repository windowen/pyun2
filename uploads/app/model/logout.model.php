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
class logout_model extends model{

    /**
	 * @desc   注销账号申请列表
	 * @param  $whereData:查询条件
	 */
	public function getList($whereData , $data = array('utype'=>'')) 
	{  
		
		$List  =  $this->select_all('member_logout',$whereData);
		
		return $List;
	}
	
	public function getInfo($where = array())
	{
	    $row  =  $this->select_once('member_logout',$where);
	    return $row;
	}
	/**
	 * 注销账号申请
	 */
	public function apply($data = array(),$p = array())
	{
	    $return  =  array();
	    // 验证密码
	    $user  =  $this->select_once('member',array('uid'=>$data['uid']),'`uid`,`usertype`,`password`,`salt`,`username`,`moblie`');
	    
	    $res   =  passCheck($p['password'],$user['salt'],$user['password']);
	    
	    if (!$res){
	        $return  =  array(
	            'msg'      =>  '密码错误',
	            'errcode'  =>  8
	        );
	    }else{
	       
	        $data['ctime']     =  time();
	        $data['status']    =  1;
	        $data['username']  =  $user['username'];
	        $data['tel']       =  $user['moblie'];
	        
	        $nid  =  $this->insert_into('member_logout',$data);
	        
	        require_once ('log.model.php');
	        $logM  =  new log_model($this->db, $this->def);
	        $logM->addMemberLog($user['uid'], $user['usertype'], '申请注销账号', 31, 1);
	        
	        $msg  =  '注销账号申请';
	        
	        $return['msg']      =  $nid ? $msg.'成功' : $msg.'失败 ';
	        $return['errcode']  =  $nid ? 9 : 8;
	    }
	    return $return;
	}
	/**
	 * 注销账号申请处理
	 */
	public function status($where)
	{
	    $return  =  array();
	    if (!empty($where)){
	        
	        $row  =  $this->select_once('member_logout',$where);
	        
	        if (!empty($row)){
	            
	            $nid  =  $this->update_once('member_logout',array('status'=>2), $where);
	            
	            if ($nid){
	                
	                $member  =  $this->select_once('member',array('uid'=>$row['uid']),'`uid`,`username`,`moblie`,`email`');
	                
	                if (!empty($member)){
	                    
	                    // 用户名/手机号/邮箱，前后加字符串，地方绑定信息清空
	                    $str  =  'out';
	                    $arr  =  array(
	                        'username'     =>  $this->createNoncestr(),
	                        'moblie'       =>  $str.'_'.$member['moblie'].'_'.$str,
	                        'email'        =>  $str.'_'.$member['email'].'_'.$str,
	                        'status'       =>  2,
	                        'pwuid'        =>  0,
	                        'pw_repeat'    =>  0,
	                        'qqid'         =>  '',
	                        'qqunionid'    =>  '',
	                        'sinaid'       =>  '',
	                        'wxid'         =>  '',
	                        'wxopenid'     =>  '',
	                        'unionid'      =>  '',
	                        'wxname'       =>  '',
	                        'wxbindtime'   =>  ''
	                    );
	                    $this->update_once('member',$arr, array('uid'=>$row['uid']));
	                    // 发送短信、邮件通知
	                    $sendData  =  array(
	                        'email'      =>  $member['email'],
	                        'mobile'     =>  $member['moblie'],
	                        'uid'        =>  $member['uid'],
	                        'username'   =>  $member['username'],
	                        'type'       =>  'logouted'
	                    );
	                    
	                    require_once ('notice.model.php');
	                    $noticeM   =  new notice_model($this->db, $this->def);
	                    $noticeM -> sendEmailType($sendData);
						$sendData['port']	=	'5';
	                    $noticeM -> sendSMSType($sendData);
	                }
	                
	                $return['msg']      =  '注销账号申请(ID：'.$row['id'].')处理成功';
	                $return['errcode']  =  9;
	                
	            }else{
	                
	                $return['msg']      =  '注销账号申请处理失败 ';
	                $return['errcode']  =  8;
	            }
	        }else{
	            
	            $return['msg']      =  '没有该注销申请';
	            $return['errcode']  =  8;
	        }
	    }else{
	        
	        $return['msg']      =  '请选择需要处理的申请';
	        $return['errcode']  =  8;
	    }
	    return $return;
	}
	/**
	 * 注销账号申请删除
	 */
	public function del($delId = null)
	{
	    if (empty($delId)) {
	        
	        $return  =  array('errcode' => 8, 'msg' => '请选择要删除的数据！');
	    } else {
	        
	        if (is_array($delId)) {
	            
	            $delId                =   pylode(',', $delId);
	            
	            $return['layertype']  =   1;
	        } else {
	            
	            $return['layertype']  =   0;
	        }
			 
	        $return['id']       =   $this -> delete_all('member_logout', array('id' => array('in', $delId)), '');
	        
	        $return['errcode']  =   $return['id'] ? 9 : 8;
	        
	        $msg                =   '注销账号申请（ID：'.$delId.'）';
	        
	        $return['msg']      =   $return['id'] ? $msg.'删除成功！' : '删除失败！';
	    }
	    return $return;
	}
	//作用：产生16位随机字符串
	private function createNoncestr($length = 16) 
	{
	    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
	    $str = '';
	    for ($i = 0; $i < $length; $i++) {
	        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	}
}
?>