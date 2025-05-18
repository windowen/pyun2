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
class regset_controller extends adminCommon
{

    function index_action()
    {
       
        $this->yunset("config", $this->config);
        
        $this->yuntpl(array('admin/admin_regset'));
    }

    // 保存
    function save_action()
    {
        if ($_POST['config']) {
            
            $data = array(
                
                'reg_moblie'          => $_POST['reg_moblie'],
                
                'reg_email'           => $_POST['reg_email'],
                
                'reg_user'            => $_POST['reg_user'],
                
                'reg_passconfirm'     => $_POST['reg_passconfirm'],
                
                'reg_user_stop'       => $_POST['reg_user_stop'],
                
                'reg_real_name_check' => $_POST['reg_real_name_check'],
                
                'sy_regname'          => $_POST['sy_regname'],
                
                'sy_def_email'        => $_POST['sy_def_email'],
                
                'sy_web_mobile'       => $_POST['sy_web_mobile'],
                
                'sy_reg_interval'     => $_POST['sy_reg_interval'],
                
                'sy_reg_invite'       => $_POST['sy_reg_invite']
            
            );
            $configM = $this->MODEL('config');
            
            $configM->setConfig($data);
            
            $this->web_config();
            
            $this->layer_msg("注册设置成功！", 9, 1);
        }
    }
}
?>