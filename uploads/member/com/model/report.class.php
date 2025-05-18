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
class report_controller extends company
{

    function index_action()
    {
        if ($_POST['eid']) {
            $reportM    =   $this->MODEL('report');

            $row        =   $reportM -> getReportOne(array(
                'p_uid'     =>  $this->uid,
                'eid'       =>  $_POST['eid'],
                'orderby'   =>  'inputtime,desc'
            ));

            if (is_array($row) && ! $row['result']) {
                echo 2;
                die();
            }
            
            $eid        =   intval($_POST['eid']);

            $reason     =   $_POST['reason'];

            $crm        =   $this->MODEL('admin')->getAdminUser(array('uid' => $eid));

            $rData      =   array(
                
                'did'       =>  $this->config['did'],
                'p_uid'     =>  $this->uid,
                'eid'       =>  intval($_POST['eid']),
                'usertype'  =>  $this->usertype,
                'inputtime' =>  time(),
                'username'  =>  $this->username,
                'r_name'    =>  $crm['name'],
                'r_reason'  =>  $reason,
                'type'      =>  2      //  顾问
            );

            $new_id     =   $reportM -> addCrmReport($rData);

            if ($new_id) {
                
                $this ->MODEL('log') ->addMemberLog($this->uid, $this->usertype, '投诉招聘顾问', 23, 1);
                echo '1';
            } else {
                
                echo '0';
            }
        }
    }

    function show_action()
    {
        $reportM        =   $this->MODEL('report');

        $urlarr         =   array();
        
        $urlarr['c']    =   'report';
        $urlarr['act']  =   'show';
        $urlarr['page'] =   '{{page}}';
        
        $where          =   array();
        $where['p_uid'] =   $this->uid;
        $where['type']  =   2;

        $pageurl        =   Url('member', $urlarr);
        $pageM          =   $this -> MODEL('page');
        $pages          =   $pageM->pageList('report', $where, $pageurl, $_GET['page'], $this->config['sy_listnum']);

        if ($pages['total'] > 0) {
            
            if ($_GET['order']) {
            
                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'inputtime';
            }
            
            $where['limit']         =   $pages['limit'];

            $List                   =   $reportM -> getReportList($where);

            $this->yunset('rows', $List['list']);
        }

        $this->public_action();

        $this->com_tpl('report');
    }

    function del_action()
    {
        $reportM    =   $this->MODEL('report');

        if ($_GET['id']) {

            $del    =   $_GET['id'];

            if ($del) {
                
                $return     =   $reportM->delReport(array('id' => $del,'uid'=>$this->uid));
                $this->layer_msg($return['msg'], $return['errcode'], 0, $_SERVER['HTTP_REFERER']);
            } else {

                $this->layer_msg('请选择您要删除的信息！', 8, 0, $_SERVER['HTTP_REFERER']);
            }
        }
    }
}
?>