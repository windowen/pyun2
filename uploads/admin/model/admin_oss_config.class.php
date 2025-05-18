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
class admin_oss_config_controller extends adminCommon{

    /**
     * 工具-数据-OSS设置
     */
    function index_action(){
        
        @include(APP_PATH.'data/api/aliyun_oss/oss_data.php');
        
        $this -> yunset('oss_data',$oss_data);
        
        $this->config['sy_ossurl']  =  'https://'.$oss_data['userdomain'];
        
        $this -> yunset('config', $this->config);
        
        $this -> yuntpl(array('admin/admin_oss_config'));
    }

    /**
     * OSS保存
     */
    function save_action(){
        
        if($_POST['oss_config']){
            
            if ($_POST['sy_oss']){
                $sy_oss     =  1;
                $sy_ossurl  =  $_POST['sy_ossurl'];
            }else{
                //oss关闭时，将地址清空
                $sy_oss     =  2;
                $sy_ossurl  =  '';
            }
            
            $config  =  array(
                'sy_oss'     =>  $sy_oss,
                'sy_ossurl'  =>  $sy_ossurl
            );
            $configM  =  $this->MODEL('config');
            
            $configM -> setConfig($config);
            
            $this -> web_config();
            
            $oss_data  =  array(
                'access_id'   =>  $_POST['access_id'],
                'access_key'  =>  $_POST['access_key'],
                'endpoint'    =>  $_POST['endpoint'],
                'bucket'      =>  $_POST['bucket'],
                'userdomain'  =>  str_replace('https://', '', $_POST['sy_ossurl'])
            );
            
            made_web(APP_PATH.'data/api/aliyun_oss/oss_data.php',ArrayToString($oss_data),'oss_data');
            
            $this->ACT_layer_msg('OSS配置设置成功',9,$_SERVER['HTTP_REFERER'],2,1);
        }
    }

}
?>