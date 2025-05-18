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
class comcert_controller extends adminCommon
{

    function set_search()
    {
        
        $search_list    =   array();
        
        $search_list[]  =   array(
            
            'param'     =>  'status',
            'name'      =>  '审核状态',
            'value'     =>  array(
                
                '3'     =>  '未审核',
                '1'     =>  '已审核',
                '2'     =>  '未通过'
                
            )
        );

        $search_list[]  =   array(
            
            'param'     =>  'end',
            'name'      =>  '申请时间',
            'value'     =>  array(
                
                '1'     =>  '今天',
                '3'     =>  '最近三天',
                '7'     =>  '最近七天',
                '15'    =>  '最近半月',
                '30'    =>  '最近一个月'
            )
        );

        $this->yunset('search_list', $search_list);
    }

    /**
     * @desc 后台  - 企业 认证审核列表
     */
    function index_action() {
        
        $this->set_search();

        $companyM       =   $this->MODEL('company');
        
        $where          =   array();

        $where['type']  =   3;

        if ($_GET['status']) {

            $status             =   intval($_GET['status']);

            $where['status']    =   $status == 3 ? 0 : $status;

            $urlarr['status']   =   $status;
        }

        if ($_GET['keyword']) {

            $keyword            =   trim($_GET['keyword']);

            $cwhere['name']     =   array('like', $keyword);

            $ctList             =   $companyM -> getList($cwhere, array('field' => 'uid'));

            $comapant           =   $ctList['list'];
            
            $comids             =   array();
            
            foreach ($comapant as $v) {

                $comids[]       =   $v['uid'];
                
            }

            $where['uid']       =   array('in',pylode(',', $comids));

            $urlarr['keyword']  =   $keyword;
        }

        if ($_GET['end']) {

            if ($_GET['end'] == '1') {

                $where['ctime'] =   array('>=', strtotime('today'));
                
            } else {

                $where['ctime'] =   array('>=', strtotime('-'.intval($_GET['end']).' day'));
            }
            
            $urlarr['end']      =   $_GET['end'];
        }

        $urlarr['page'] = '{{page}}';

        $pageurl = Url($_GET['m'], $urlarr, 'admin');

        // 提取分页
        $pageM = $this->MODEL('page');

        $pages = $pageM->pageList('company_cert', $where, $pageurl, $_GET['page']);

        // 分页数大于0的情况下 执行列表查询

        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
                
            } else {

                $where['orderby']   =   array('status,asc','id,desc');
                
            }
            
            $where['limit']         =   $pages['limit'];
            
            $rows   =   $companyM -> getCertList($where, array('utype'=>'comcert'));
            
            $this   ->  yunset('rows', $rows);
        }

        $this->yuntpl(array('admin/admin_com_cert'));
        
    }
    

    function sbody_action()
    {
        $CompanyM = $this->MODEL('company');

        $where['type'] = 3;

        $where['uid'] = intval($_POST['uid']);

        $info = $CompanyM->getCertInfo($where, array('field' => 'statusbody'));

        echo $info['statusbody'];die;
    }

    /**
     * @desc 后台     -   企业认证审核
     */
    function status_action(){

        // 引用compayorder中MODEL类文件
        $companyorder   =   $this->MODEL('companyorder');

        $companyM       =   $this->MODEL('company');
        if ($_POST['noyyzz'] == '1') {
            
            $this->ACT_layer_msg('该企业尚未上传营业执照，无法审核！', 8, $_SERVER['HTTP_REFERER']);
        }

        if ($_POST['status'] == '') {
            
            $this->ACT_layer_msg('请选审核状态！', 8, $_SERVER['HTTP_REFERER']);
        }
        
        

        if ($_POST['uid']) {

            $uid        =   $_POST['uid'];
          
            $status     =   intval($_POST['status']);

            if ($status != 1) {

                $yyzz_status = 2;
                
            } else {

                $yyzz_status = 1;
                
                // 如果是“审核通过”，判断之前是否有过“审核通过的记录”，没有则增加营业执照审核通过的积分（只有第一次审核通过才加积分）
                 $comids     =   @explode(',', $uid);
				
                if (count($comids)>1) {
                    
                   
                    $paywhere['com_id'] = array('in', pylode(',', $comids));

                    $paywhere['pay_remark'] = '认证营业执照';
                    
                    $companypay = $companyorder->getPayList($paywhere, array('field' => 'com_id'));

                    foreach ($companypay as $k => $v) {
                        
                        if (in_array($v, $uid)) {

                            unset($uid[$k]);
                        }
                    }
                    foreach ($uid as $v) {

                        $this->MODEL('integral')->invtalCheck($v,2,'integral_comcert', '认证营业执照');
                    }
                    
                } elseif ($uid != '') {

                    $paywhere['com_id'] = $uid;

                    $paywhere['pay_remark'] = '认证营业执照';
                    $num = $companyorder->getCompanyPayNum($paywhere);
                    
                    if ($num < 1) {
                        $this->MODEL('integral')->invtalCheck($uid,2, 'integral_comcert', '认证营业执照');
                    }
                }
            }
            
            $companyData = array('yyzz_status' => $yyzz_status);
			$uids       =   @explode(',', $_POST['uid']);
            $companyM   ->  upInfo($uids, '', $companyData);

            $companycertData = array(

                'status' => $_POST['status'],

                'statusbody' => $_POST['statusbody']
            );
 
            $id = $companyM -> upCertInfo(array('type'=>'3','uid' => array('in', pylode(',', $uid))), $companycertData,array('utype'=>'admin'));
                
  
            // 职位免审核开启，管理勾选同步审核职位，未审核职位同步审核成功
            if ($this->config['com_free_status'] == '1' && $_POST['job_status']) {
                 
                $jobM   =   $this -> MODEL('job');
                $jobM   ->  upInfo(array('state'=>'1','r_status'=>1), array('state'=>'0', 'uid'=>array('in',pylode(',', $uid))));
            }
                
            $ComA       =   $companyM->getList(array('uid'=>array('in',pylode(',', $uid))), array('field' => 'uid,name,linkmail'));
            
            $company    =   $ComA['list'];
 
            if ($this->config['sy_email_set'] == '1') {
                
                if (is_array($company)) {
                
                    $notice = $this->MODEL('notice');
                    
                    foreach ($company as $v) {
                        if ($this->config['sy_email_comcert'] == '1' && $_POST['status'] > 0) {
                            if ($_POST['status'] == '1') {
                                $certinfo = '营业执照审核通过！';
                            } else {
                                $certinfo = '营业执照审核未通过！';
                            }
                            $notice->sendEmailType(array(
                                'email' => $v['linkmail'],
                                'certinfo' => $certinfo,
                                'comname' => $v['name'],
                                'uid' => $v['uid'],
                                'name' => $v['name'],
                                'type' => 'comcert'
                            ));
                        }
                    }
                }
            }
			
			/* 消息前缀 */		
			foreach($company as $v){
				
				 $uids[]  =  $v['uid'];
							
				/* 处理审核信息 */
				if ($_POST['status'] == 2){
					
					$statusInfo  =  '很遗憾 , 贵公司营业执照未能通过审核';
					
					if($_POST['statusbody']){
						
						$statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
						
					}
					
					$msg[$v['uid']]  =  $statusInfo;
					
				}elseif($_POST['status'] == 1){
					
					$msg[$v['uid']]  =  '贵公司营业执照审核通过，招聘人才更轻松！';
					
				}
			}
			//发送系统通知
			$sysmsgM	=	$this->MODEL('sysmsg');
			
			$sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));
			
            if ($id) {

                $this->ACT_layer_msg('企业认证审核(UID:' . $uid . ')设置成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
            } else {

                $this->ACT_layer_msg('设置失败！', 8, $_SERVER['HTTP_REFERER']);
            }
        } else {

            $this->ACT_layer_msg('非法操作！', 8, $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * @desc 删除企业认证
     */
    function del_action(){
        
        $companyM       =   $this->MODEL('company');

        if (is_array($_POST['del'])) {

            $linkid     =   $_POST['del'];
            
        } else {

            $this   ->  check_token();

            $linkid     =   $_GET['uid'];
        }
        
        $companyM   ->  upInfo($linkid, '', array('yyzz_status' => 0));
        
        $err    =   $companyM -> delCert($linkid, array('type'=>'3'));
        
        $this   ->  layer_msg($err['msg'], $err['errcode'], $err['layertype'], $_SERVER['HTTP_REFERER']);
         
    }

    function comCertNum_action()
    {
        $MsgNum = $this->MODEL('msgNum');

        echo $MsgNum->comCertNum();
    }
}

?>