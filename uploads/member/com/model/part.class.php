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
class part_controller extends company
{

    function index_action()
    {
        $partM  =   $this->MODEL('part');

        include PLUS_PATH . 'part.cache.php';

        $uid    =   $this -> uid;

        $where['uid']   =   $uid;

        if ($_GET['keyword']) {

            $keyword            =   trim($_GET['keyword']);

            $where['name']      =   array( 'like', $keyword);

            $urlarr['keyword']  =   $keyword;
        }
        
        $pageurl        =   Url('member', $urlarr);

        $pageM          =   $this->MODEL('page');

        $pages          =   $pageM->pageList('partjob', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            $where['orderby']   =   'lastupdate,desc';
            $where['limit'] =   $pages['limit'];
            
            $rows           =   $partM -> getList($where);
            
            $this->yunset('rows', $rows);
        }
         
        $this -> public_action();
        $this -> company_satic();
        $this -> com_tpl('partlist');
    }

    function del_action()
    {
        $partM  =   $this->MODEL('part');

        $delID  =   is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];

        $delRes =   $partM->delPart($delID,array('uid' => $this->uid, 'usertype' => $this->usertype));

        $this -> layer_msg($delRes['msg'], $delRes['errcode'], $delRes['layertype'], $_SERVER['HTTP_REFERER']);
    }

    function opera_action()
    {
        $this->part();
    }

    // 刷新兼职职位
    function refresh_part_action()
    {
        if ($_POST) {

            $comtcM             =   $this->MODEL('comtc');

            $_POST['uid']       =   $this->uid;
            $_POST['usertype']  =   $this->usertype;

            $return =   $comtcM->refresh_part($_POST);

            if ($return['status'] == 1) { // 兼职刷新成功

                echo json_encode(array(
                    'error' => 1,
                    'msg'   => $return['msg']
                ));
            } else if ($return['status'] == 2) {

                echo json_encode(array(
                    'error'     =>  2,
                    'pro'       =>  $return['pro'],
                    'online'    =>  $return['online'],
                    'integral'  =>  $return['integral'],
                    'jifen'     =>  $return['jifen'],
                    'price'     =>  $return['price']
                ));
            } else { // 职位刷新失败

                echo json_encode(array(
                    'error' => 3,
                    'msg'   => $return['msg'],
                    'url'   => $return['url']
                ));
            }
        } else {

            echo json_encode(array(
                'error' => 3,
                'msg' => '参数错误，请重试！'
            ));
        }
    }
}
?>