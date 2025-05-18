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
class warning_controller extends adminCommon
{
    
    function index_action()
    {
        $ConfigM    =   $this->MODEL('config');
        
        if ($_GET['date'] && $_GET['time'] < 1) {
            
            $times  =   @explode('~', $_GET['date']);
            
            $where['ctime'][]   =   array('>=', strtotime($times[0] . " 00:00:00"));
            $where['ctime'][]   =   array('<=', strtotime($times[1] . " 23:59:59"));
            $urlarr['date']     =   $_GET['date'];
        }
        
        $urlarr['page'] =   '{{page}}';
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM -> pageList('warning', $where, $pageurl, $_GET['page']);
        
        if ($pages['total'] > 0) {
            
            $where['orderby']   =   'id';
            $where['limit']     =   $pages['limit'];
            $list               =   $ConfigM -> getWarningList($where);
        }
        
        $this->yunset('list', $list);
        $this->yuntpl(array('admin/admin_warning'));
    }
    
    /**
     * 预警配置
     */
    function config_action()
    {
        $ConfigM    =   $this->MODEL('config');
        
        if ($_POST['config']) {
        
            unset($_POST['config']);
            
            foreach ($_POST as $key => $v) {
            
                $config =   $ConfigM -> getNum(array('name' => $key));
                
                if ($config == false) {
                    
                    $ConfigM -> addInfo(array( 'name' => $key, 'config' => $v ));
                } else {
                    
                    $ConfigM -> upInfo(array( 'name' => $key ), array( 'config' => $v));
                }
            }
            
            $this->web_config();
            
            $this -> ACT_layer_msg('预警配置修改成功！', 9, 1, 2, 1);
        }
        $this->yuntpl(array('admin/admin_warning_config'));
    }

    function del_action()
    {
        $this -> check_token();
        $ConfigM    =   $this->MODEL('config');
        
        if ($_GET['del']) {
        
            $return =   $ConfigM->delWarning($_GET['del']);
            
            $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }
}
?>