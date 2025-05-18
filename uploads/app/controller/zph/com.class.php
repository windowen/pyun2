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
class com_controller extends zph_controller
{

    function index_action()
    {
        $this -> Zphpublic_action();

        $id     =   intval($_GET['id']);

        $zphM   =   $this -> MODEL('zph');
        
        $row    =   $zphM -> getInfo(array('id' => $id), array('pic' => 1));
        
        if (empty($row)) {
            $this -> ACT_msg(url("zph"), '没有找到该招聘会！');
        }
        
        $this -> yunset('row', $row);

        $where              =   array();
        $where['zid']       =   $id;
        $where['status']    =   1;
        $where['orderby']   =   array('sort,desc','ctime,asc');

        $urlarr             =   array();
        $urlarr['c']        =   $_GET['c'];
        $urlarr['id']       =   $id;
        $urlarr['page']     =   '{{page}}';
        
        $pageurl            =   Url('zph', $urlarr, '1');

        $pageM              =   $this -> MODEL('page');
        $pages              =   $pageM -> pageList('zhaopinhui_com', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            
            $where['limit']     =   $pages['limit'];

            $List               =   $zphM -> getZphCompanyList($where);
			
            $this -> yunset('rows', $List);
        }
		
        $data['zph_title']      =   $row['title'];
        $data['zph_desc']       =   $this->GET_content_desc($row['body']);
        $this -> data           =   $data;
        $this -> seo('zph_com');
        $this -> zph_tpl('zphcom');
    }
}
?>