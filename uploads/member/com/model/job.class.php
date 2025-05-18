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
class job_controller extends company
{

    function index_action()
    {
        $this -> public_action();
        $this -> company_satic();
        
        $jobM       =   $this->MODEL('job');
        
        $StatisM    =   $this->MODEL('statis');
        $suid       =   $this->spid ? $this->spid : $this->uid;
        $statis     =   $StatisM -> getInfo($suid, array('usertype' => '2'));
        $this->yunset('statis', $statis);
        
        $urlarr     =   array('c' => 'job', 'page' => '{{page}}');

        $uid        =   $this->uid;
		
        $where['uid']	=   $uid;
        // 关键字刷选
        if ($_GET['keyword']) {
            
            $where['name']      =   array('like', trim($_GET['keyword']));
            $urlarr['keyword']  =   $_GET['keyword'];
        }
        // 职位状态 0:待审核职位 1:招聘中的职位 3:未通过职位 4:已下架职位 5:所有职位
        if($_GET['type']==2){
            $urlarr['type']        =   $_GET['type'];
        }
        if ($_GET['w'] == 4) {
            
            $where['status']    =   '1';
            $urlarr['w']        =   $_GET['w'];
        } elseif ($_GET["w"] == 1) {

            $where['status']    =   '0';
            $where['state']     =   '1';
            $urlarr['w']        =   $_GET['w'];
        } elseif ($_GET["w"] == 5) {

            $urlarr['w']        =   $_GET['w'];
        } else {
            
            $where['state']     =   $_GET['w'];
            $urlarr['w']        =   $_GET['w'];
        }
        
        $pageurl    =   Url('member', $urlarr);

        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('company_job', $where, $pageurl, $_GET['page'], $this->config['sy_listnum']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {
                
                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {
                
                $where['orderby']   =   array('lastupdate,desc','id,desc');
            }
            
            $where['limit']         =   $pages['limit'];
            $List                   =   $jobM -> getList($where, array('sqjobnum' => 'yes'));
			
            $this->yunset('rows', $List['list']);
        }

        // 统计各状态职位数量
        // 职位状态 0:待审核职位 1:招聘中的职位 3:未通过职位 4:已下架职位 5:所有职位
		
		$swhere['com_id']	=	$uid;
        $audit  =   $jobM->getList($swhere, array('field' => "`status`,`state`"));
    
        $w0 = $w1 = $w2 = $w3 = $w4 = $w5 = 0;

        if (is_array($audit['list'])) {
            foreach ($audit['list'] as $value) {

                if ($value['state'] == '0') {
                    $w0 += 1;
                }
                if ($value['status'] == '0' && $value['state'] == '1') {
                    $w1 += 1;
                }

                if ($value['state'] == '3') {
                    $w3 += 1;
                }
                if ($value['status'] == '1') {
                    $w4 += 1;
                }
                $w5 += 1;
            }
        }
        
        $this->yunset(array(
            'w0' => $w0,
            'w1' => $w1,
            'w3' => $w3,
            'w4' => $w4,
            'w5' => $w5
        ));
        $this -> yunset('audit', $w0);

        if (intval($_GET['w']) == 1) {
            $type     =     $_GET['type'];
            $this -> yunset('type', $type);
            $this->com_tpl('joblist');
        } else {
            
            $this->com_tpl('job');
        }
    }

    function opera_action()
    {
        $this->job();
    }

    // 刷新职位方法，套餐处理集合到comtc.model.php类里面
    function refresh_job_action()
    {
        if ($_POST) {

            $comtcM	            = 	$this->MODEL('comtc');
			
			$_POST['spid']		=	$this->spid;
			$_POST['uid']		=	$this->uid;
			$_POST['usertype']	=	$this->usertype;
			
            $return	= 	$comtcM->refresh_job($_POST);

            if ($return['status'] == 1) {// 职位刷新成功
                
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
            } else {// 职位刷新失败
                
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

    /**
     * 职位推广套餐 查询
     */
    function jobPromote_action()
    {
        $uid    =   $this->uid;
        $type   =   intval($_POST['type']);
        //预留职位自动刷新
        if ($type != 5){
            
            $jobM   =   $this->MODEL('job');
            $return =   $jobM->jobPromote($uid, array('type' => $type, 'spid' => $this->spid));
        }else{
            
            $return = array('status'=>2);
        }

        echo json_encode($return);
        die();
    }

    function setJobPromote_action()
    {
        if ($_POST) {

            $jobM   =   $this->MODEL('job');

            $_POST['uid']       =   $this->uid;
            $_POST['spid']      =   $this->spid;
            $_POST['username']  =   $this->username;
            $_POST['usertype']  =   $this->usertype;
 
            $return = $jobM->setJobPromote(intval($_POST['jobid']), $_POST);

            echo json_encode($return);
            die();
        }
    }

}
?>