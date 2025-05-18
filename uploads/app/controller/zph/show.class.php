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
class show_controller extends zph_controller
{

    function index_action()
    {
        $this   ->  Zphpublic_action();
        $id     =   intval($_GET['id']);
        
        $zphM   =   $this->MODEL('zph');
        $row    =   $zphM->getInfo(array('id' => $id), array('banner' => 1));
        
        if (empty($row)) {
            $this -> ACT_msg(url('zph'), '没有找到该招聘会！');
        }
        
        $this->yunset('Info', $row);

        $rows   =   $zphM -> getZphPicList(array('zid' => $id));
        $this->yunset('Image_info', $rows);

        if($this->uid &&  $this->usertype &&  $this->usertype != 2){
            $typename       =   array('1' => '个人账户');
            $this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
        }
        $this -> yunset('typename',$typename);
        $data['zph_title']  =   $row['title'];
        $data['zph_desc']   =   $this -> GET_content_desc($row['body']); 
        $this -> data       =   $data;

        $this -> seo('zph_show');
        $this -> zph_tpl('zphshow');
    }
}
?>