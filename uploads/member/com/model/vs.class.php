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
class vs_controller extends company
{

    function index_action()
    {
        $this->public_action();

        $this->company_satic();
 
        $this->com_tpl('vs');
    }

    function save_action()
    {
        $UserinfoM  =   $this->MODEL('userinfo');

        if ($_POST['submit']) {

            $data   =   array(
                
                'uid'           =>  $this->uid,
                'usertype'      =>  $this->usertype,
                'oldpassword'   =>  $_POST['oldpassword'],
                'password'      =>  $_POST['password'],
                'repassword'    =>  $_POST['repassword']
                
            );
            
            $info   =   $UserinfoM -> getInfo(array('uid'=> $this->uid), array('field'=>'username'));
      
            if (is_array($info)) {

                if ($this->config['sy_uc_type'] == "uc_center" && $info['name_repeat'] != "1") {

                    $this->obj->uc_open();

                    $ucresult = uc_user_edit($info['username'], $_POST['oldpassword'], $_POST['password'], "", "1");
                
                    if ($ucresult == - 1) {

                        $this->ACT_layer_msg("原始密码错误！", 8, "index.php?c=vs");
                        
                    }
                    
                } else {

                    $err    =   $UserinfoM -> savePassword($data);
                    
                    
                }

                
                
               

                if($err['errcode'] == '8'){
                     
                    $this -> ACT_layer_msg($err['msg'], $err['errcode'], "index.php?c=vs");
                    
                }else{
                    $this -> cookie -> unset_cookie();
                    $this -> ACT_layer_msg($err['msg'], $err['errcode'], $this->config['sy_weburl'] . "/index.php?m=login");
                    
                }
            }
        }
    }
}
?>