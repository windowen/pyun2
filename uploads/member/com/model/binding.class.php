<?php
/* *
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class binding_controller extends company{
    
    function index_action(){
        
        $this   ->  company_satic();
        $this   ->  public_action();

        $company	=	$this -> comInfo['info'];
		$this -> yunset("company", $company);
        
        $uid        =   intval($this->uid);
        
        $UserinfoM  =   $this -> MODEL('userinfo');
        $member     =   $UserinfoM -> getInfo(array('uid'=> $uid), array('setname'=>'1'));
        $this -> yunset("member", $member);
		
        $comM		=   $this -> MODEL('company');
        $cert       =   $comM -> getCertInfo(array('uid'=>$uid, 'type'=>'3'));
        $this ->  yunset("cert",$cert);
        
        $this ->  com_tpl("binding");
    }
    
    /**
     * @desc 企业会员中心绑定手机
     */
    function save_action(){
        
        $CookieM   =   $this->MODEL('cookie');
        $CookieM   ->  SetCookie('delay', '', time() - 60);
        
        $ComapnyM  =   $this->MODEL('company');
        
        $data      =   array(
            'uid'		=>	$this->uid,
            'usertype'	=>	$this->usertype,
            'moblie'	=>	$_POST['moblie']
        );
		if(!$this -> comInfo['uid']){
			$userinfoM    =   $this->MODEL("userinfo");
			$userinfoM -> activUser($this->uid,2);
		}
        $errCode   =   $ComapnyM -> upCertInfo(array('uid'=>$this->uid, 'check2'=>$_POST['code']), array('status'=>'1'), $data);

        echo $errCode; die;
    }
    
    /**
     * @desc 企业会员中心，营业执照认证
     */
    function savecert_action(){
        
        $ComapnyM  =   $this->MODEL('company');

        $CookieM   =   $this->MODEL('cookie');
        $CookieM   ->  SetCookie('delay', '', time() - 60);
        
        $uid       =   intval($this->uid);
        $usertype  =   intval($this->usertype);
        
        $row       =   $ComapnyM -> getCertInfo(array('uid'=>$uid, 'type' => '3'));
        
        if($this ->comInfo['r_status']==0){
            $status=   $this->comInfo['r_status'];
        }else{
            $status=   $this->config['com_cert_status'] == '1' ? 0 : 1;
        }
        /* 更新营业执照参数整理 */
        $upData    =   array(
            'status'    =>  $status,
            'file'      =>  $_FILES['file'],
            'ctime'     =>  time()
        );
        
        if (!empty($row) && is_array($row) && $row['ctime']) {
            
            $err   =   $ComapnyM -> upCertInfo(array('id'=>intval($row['id']), 'uid' => $uid), $upData, array('yyzz' => '1', 'usertype' => $usertype, 'com_name'=>trim($_POST['company_name']),'type'=>'3'));
            
        }else{
            
            /* 新增营业执照认证参数整理，包含自定义查询参数  */
            $postData  =   array(
                
                'uid'      =>  $uid,
                'type'     =>  '3',
                'step'     =>  '1',
                'file'     =>  $_FILES['file'],
                'did'      =>  $this->userdid,
                'usertype' =>  $usertype,
                'com_name' =>  trim($_POST['company_name'])
            );
            
            $postData  =   array_merge($postData, $upData);
			//容错机制，在未生成企业身份时提前营业执照认证会出错
            if(!$this -> comInfo['info']['uid']){
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($uid,2);
			}
            $err       =   $ComapnyM -> addCertInfo($postData);
        }
        
        
        $this->ACT_layer_msg($err['msg'],$err['errcode'],$_SERVER['HTTP_REFERER']);
        
    }
    /**
     * @desc 解除绑定   
     */
    function del_action(){
        
        $companyM	=	$this->MODEL('company');
        
        $return 	=	$companyM->delBd($this->uid,array('type'=>$_GET['type'],'usertype'=>$this->usertype));
        
        $this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
    }
}
?>