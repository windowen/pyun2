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
class partapply_controller extends company
{

    function index_action()
    {   
        
        $partM              =   $this->MODEL('part');
        
        $comid              =   $this->uid;

        $where['comid']     =   $comid;

        if ($_GET['jobid']) {

            $jobid          =   intval($_GET['jobid']);

            $where['jobid'] =   $jobid;

            $urlarr['jobid']=   $jobid;
        }
        
        

        if ($_GET['status']) {

            $status             =   intval($_GET['status']);

            $where['status']    =   $status;

            $urlarr['status']   =   $status;
        }
        
        $urlarr['c']        =   $_GET['c'];

        $urlarr['page']     =   '{{page}}';

        $pageurl            =   Url('member', $urlarr);

        $pageM              =   $this -> MODEL('page');

        $pages              =   $pageM -> pageList('part_apply', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            
            $where['orderby']   =   array('ctime,desc');
            
            $where['limit']     =   $pages['limit'];
            
            $rows               =   $partM -> getPartSqList($where); 
        }
        
        $partJob                =   $partM -> getList(array('uid' => $comid), array('field' => '`id`,`name`'));
        
        $this -> yunset('JobList', $partJob);
         
        if($_GET['jobid'] && $_GET['status']==''){
          $jobid          =   intval($_GET['jobid']);
          
          $partwhere['id'] =   $jobid;
		  $partwhere['uid'] =   $this -> uid;
          
          $partInfo       =   $partM->getInfo($partwhere,array('field'=>'`name`'));
          $partJob        =   $partInfo['info'];
          $this -> yunset('jobname', $partJob['name']);
        } 
         

            
        
        
        $this -> yunset('total', $pages['total']);
        
        $this -> yunset('rows', $rows);
        
        // 未查看 已查看 已联系
        $this -> yunset(array( 'StateList' => array( array( 'id' => 1, 'name' => '未查看' ), array( 'id' => 2, 'name' => '已查看' ), array( 'id' => 3, 'name' => '已联系' ) ) ));
        
        $this -> public_action();
        
        $this -> company_satic();


        $this -> com_tpl('partapply');
    }

    function status_action()
    { // 设置状态
        $partM = $this->MODEL('part');

        $id = intval($_POST['id']);

        $status = intval($_POST['status']);

        $where['id'] = $id;

        $where['comid'] = $this->uid;

        $data = array(

            'status' => $status
        );

        $partM->upPartSq($where, $data);
    }

    function del_action()
    {
        $partM = $this->MODEL('part');

        $logM = $this->Model('log');

        $delID = is_array($_POST['delid']) ? $_POST['delid'] : $_GET['del'];

        $logM->addMemberLog($this->uid, $this->usertype, "删除兼职报名", 6, 3);

        $delRes = $partM->delPartApply($delID, array(
            'uid' => $this->uid,
            'usertype' => $this->usertype
        ));

        $this->layer_msg($delRes['msg'], $delRes['errcode'], $delRes['layertype'], $_SERVER['HTTP_REFERER']);
    }
}
?>