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
class yqmb_controller extends company
{

    public function index_action()
    {
        
        // 企业账号通用信息
        $this   ->  public_action();
        $this   ->  company_satic();
       
        $yqmbM          =   $this -> MODEL('yqmb');
  
        $where          =   array('uid' => $this -> uid);
        
        $urlarr         =   array('c' => 'yqmb', 'page' => '{{page}}');

        $pageurl        =   Url('member', $urlarr);

        $pageM          =   $this -> MODEL('page');
        
        $pages          =   $pageM -> pageList('yqmb', $where, $pageurl, $_GET['page']);
        
        
        if ($pages['total'] > 0) {
            
            $where['orderby']   =   'id,desc';
            $where['limit']     =   $pages['limit'];
            $rows               =   $yqmbM -> getList($where);
        }
       
        $this -> yunset('totalNum', $pages['total']);
        $this -> yunset('rows', $rows);
 
        $this -> com_tpl('yqmb');
    }

  
    public function editSave_action()
    {
        $_POST  =   $this -> post_trim($_POST);
        $yqmbM  =   $this->MODEL('yqmb');

        $where  =   array();

        if($_POST['yid']){
            
            $where['id']=   $_POST['yid'];
            
        }

        $data = array(
            'uid'       =>  $this->uid
        );
        $setdata = array(
            'name'      => $_POST['name'],
            'linkman'   => $_POST['linkman'],
            'linktel'   => $_POST['linktel'],
            'content'   => $_POST['content'],
            'intertime' => $_POST['intertime'],
            'address'   => $_POST['address'],
        );
        $return         =   $yqmbM->addInfo($setdata,$data,$where);

        // 返回值
        if ($return['errcode'] == 9) {
            
            $this->ACT_layer_msg($return['msg'], 9,$_SERVER['HTTP_REFERER']);
        } else {
            
            $this->ACT_layer_msg($return['msg'], 8);
        }
    }

   
    public function del_action()
    {
        $_POST  =   $this -> post_trim($_POST);

        $yqmbM  =   $this -> MODEL('yqmb');

        $res    =   $yqmbM->delYqmb($_POST['id'],array('uid'=>$this->uid));

        echo json_encode($res);
        
        die();
    }

    
    
}
?>