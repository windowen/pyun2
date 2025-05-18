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
class productnews_controller extends adminCommon{

    function set_search()
    {
        $search_list[]=array("param"=>"status","name"=>'审核状态',"value"=>array("1"=>"已审核","3"=>"未审核","2"=>"未通过"));
        $search_list[]=array("param"=>"time","name"=>'发布时间',"value"=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $this->yunset("search_list",$search_list);
    }

    function index_action()
    {
        $this->set_search();
        
        $companyM = $this->MODEL('company');
        
        if ($_GET['time']) {
            
            if ($_GET['time'] == '1') {
                
                $where['ctime'] = array(
                    '>=',
                    strtotime('today')
                );
            } else {
                $where['ctime'] = array(
                    '>=',
                    strtotime('-' . intval($_GET['time']) . ' day')
                );
            }
            $urlarr['time'] = $_GET['time'];
        }
        
        if ($_GET['status']) {
            
            $status = intval($_GET['status']);
            
            $where['status'] = $status == 3 ? 0 : $status;
            
            $urlarr['status'] = $status;
        }
        
        if ($_GET['keyword']) {
            
            $keytype = intval($_GET['type']);
            
            $keyword = trim($_GET['keyword']);
            
            if ($keytype == 1) {
                
                $cwhere['name'] = array(
                    'like',
                    $keyword
                );
                
                $ctList = $companyM->getList($cwhere, array(
                    'field' => 'uid'
                ));
                
                $comapant = $ctList['list'];
                
                foreach ($comapant as $v) {
                    
                    $comid[] = $v['uid'];
                }
                
                $where['uid'] = array(
                    'in',
                    pylode(',', $comid)
                );
            } elseif ($keytype == 2) {
                
                $where['title'] = array(
                    'like',
                    $keyword
                );
            }
            
            $urlarr['keytype'] = $keytype;
            
            $urlarr['keyword'] = $keyword;
        }
        
        $urlarr['page'] = "{{page}}";
		
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        
        $pageM = $this->MODEL('page');
        
        $pages = $pageM->pageList('company_product', $where, $pageurl, $_GET['page']);
        
        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_GET['order']) {
                
                $where['orderby'] = $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order'] = $_GET['order'];
                $urlarr['t'] = $_GET['t'];
            } else {
                
                $where['orderby'] = array(
                    'status,asc',
                    'id,desc'
                );
            }
        }
        
        $where['limit'] = $pages['limit'];
        
        $rows = $companyM->getCompanyProductList($where, array(
            'utype' => 'admin',
            'cache' => '1'
        ));
        
        $this->yunset("rows", $rows);
        
        $this->yuntpl(array('admin/admin_comproduct'));
    }

    function prostatusbody_action()
    {
        $CompanyM = $this->MODEL('company');
        
        $id = intval($_GET['id']);
        
        $info = $CompanyM->getComProductInfo(array(
            'id' => $id
        ), array(
            'field' => 'statusbody'
        ));
        
        echo $info['statusbody'];
        die();
    }

    function prostatus_action()
    {
        if (empty($_POST['status'])){
            
            $this->ACT_layer_msg("请选择审核状态！", 8, $_SERVER['HTTP_REFERER']);
        }
        
        $CompanyM = $this->MODEL('company');
        
        $sysmsgM = $this->MODEL('sysmsg');
        
        $status = intval($_POST['status']);
        
        $data['status'] = $status;
        
        $data['statusbody'] = $_POST['statusbody'];
        
        $id = $_POST['id'];
        
        if ($id) {
            $nid = $CompanyM->upCompanyProductStatus($id, $data);
            
            $where['id'] = array('in',$id);
            
            $CpList = $CompanyM->getCompanyProductList($where, array(
                'field' => 'uid,title'
            ));
            
            /* 消息前缀 */
            $tagName = '产品';
            
            foreach ($CpList as $v) {
                
                $uids[] = $v['uid'];
                
                /* 处理审核信息 */
                if ($data['status'] == 2) {
                    
                    $statusInfo = $tagName . $v['name'] . '审核未通过 , ';
                    
                    if ($data['statusbody']) {
                        
                        $statusInfo .= '原因：' . $data['statusbody'];
                    }
                    
                    $msg[$v['uid']][] = $statusInfo;
                } elseif ($data['status'] == 1) {
                    
                    $msg[$v['uid']][] = $tagName . $v['name'] . '已审核通过';
                }
            }
            
            $this->MODEL('log')->addAdminLog("企业产品(ID:" . $id . ")审核成功");
            
            $sysmsgM->addInfo(array(
                'uid' => $uids,
                'usertype' => 2,
                'content' => $msg
            ));
            
            $nid ? $this->ACT_layer_msg("产品审核(ID:" . $id . ")设置成功！", 9, $_SERVER['HTTP_REFERER'], 2, 1) : $this->ACT_layer_msg("设置失败！", 8, $_SERVER['HTTP_REFERER']);
        } else {
            
            $this->ACT_layer_msg("非法操作！", 8, $_SERVER['HTTP_REFERER']);
        }
    }

    function prodel_action()
    {
        $CompanyM = $this->Model('company');
        
        $delID = is_array($_POST['del']) ? $_POST['del'] : $_GET['id'];
        
        $return = $CompanyM->delCompanyProduct($delID);
        
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }
	
    function comnews_action()
    {
        $this->set_search();
        
        $companyM = $this->MODEL('company');
        
        if ($_GET['time']) {
            
            if ($_GET['time'] == '1') {
                
                $where['ctime'] = array(
                    '>=',
                    strtotime('today')
                );
            } else {
                $where['ctime'] = array(
                    '>=',
                    strtotime('-' . intval($_GET['time']) . ' day')
                );
            }
            $urlarr['time'] = $_GET['time'];
        }
        
        if ($_GET['status']) {
            
            $status = intval($_GET['status']);
            
            $where['status'] = $status == 3 ? 0 : $status;
            
            $urlarr['status'] = $status;
        }
        
        if ($_GET['keyword']) {
            
            $keytype = intval($_GET['type']);
            
            $keyword = trim($_GET['keyword']);
            
            if ($keytype == 1) {
                
                $cwhere['name'] = array(
                    'like',
                    $keyword
                );
                
                $ctList = $companyM->getList($cwhere, array(
                    'field' => 'uid'
                ));
                
                $comapant = $ctList['list'];
                
                foreach ($comapant as $v) {
                    
                    $comid[] = $v['uid'];
                }
                
                $where['uid'] = array(
                    'in',
                    pylode(',', $comid)
                );
            } elseif ($keytype == 2) {
                
                $where['title'] = array(
                    'like',
                    $keyword
                );
            }
            $urlarr['type'] = $_GET['type'];
            $urlarr['keyword'] = $_GET['keyword'];
        }
        
        $urlarr['page'] = "{{page}}";
		
		$urlarr['c'] 	= "comnews";
        
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        
        $pageM = $this->MODEL('page');
        
        $pages = $pageM->pageList('company_news', $where, $pageurl, $_GET['page']);
        
        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_GET['order']) {
                
                $where['orderby'] = $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order'] = $_GET['order'];
                $urlarr['t'] = $_GET['t'];
            } else {
                
                $where['orderby'] = array(
                    'status,asc',
                    'id,desc'
                );
            }
        }
        
        $where['limit'] = $pages['limit'];
        
        $rows = $companyM->getCompanyNewsList($where, array(
            'utype' => 'admin',
            'cache' => '1'
        ));
        
        $this->yunset("rows", $rows);
        
        $this->yuntpl(array('admin/admin_comnews'));
    }

    function newsstatusbody_action()
    {
        $CompanyM = $this->MODEL('company');
        
        $id = intval($_GET['id']);
        
        $info = $CompanyM->getCompanyNewsLockInfo($id, array(
            'field' => 'statusbody'
        ));
        
        echo $info['statusbody'];
        die();
    }

    function newsstatus_action()
    {
        if (empty($_POST['status'])){
            
            $this->ACT_layer_msg("请选择审核状态！", 8, $_SERVER['HTTP_REFERER']);
        }
        
        $CompanyM = $this->MODEL('company');
        
        $sysmsgM = $this->MODEL('sysmsg');
        
        $status = intval($_POST['status']);
        
        $data['status'] = $status;
        
        $data['statusbody'] = $_POST['statusbody'];
        
        $allid = @explode(',', $_POST['pid']);
        
        if ($allid) {
            
            $nid = $CompanyM->upCompanyNewsStatus($allid, $data);
            
            $where['id'] = array(
                'in',
                pylode(',', $allid)
            );
            ;
            
            $Cnews = $CompanyM->getCompanyNewsList($where, array(
                'field' => 'uid,title'
            ));
            /* 消息前缀 */
            $tagName = '企业新闻';
            
            foreach ($Cnews as $v) {
                
                $uids[] = $v['uid'];
                
                /* 处理审核信息 */
                if ($data['status'] == 2) {
                    
                    $statusInfo = $tagName . ':' . $v['title'] . '审核未通过';
                    
                    if ($data['statusbody']) {
                        
                        $statusInfo .= ' , 原因：' . $data['statusbody'];
                    }
                    
                    $msg[$v['uid']][] = $statusInfo;
                } elseif ($data['status'] == 1) {
                    
                    $msg[$v['uid']][] = $tagName . ':' . $v['title'] . '已审核通过';
                }
            }
            // 发送系统通知
            
            $sysmsgM->addInfo(array(
                'uid' => $uids,
                'usertype' => 2,
                'content' => $msg
            ));
            
            $nid ? $this->ACT_layer_msg("企业新闻审核(ID:" . $allid . ")设置成功！", 9, $_SERVER['HTTP_REFERER'], 2, 1) : $this->ACT_layer_msg("设置失败！", 8, $_SERVER['HTTP_REFERER']);
        } else {
            
            $this->ACT_layer_msg("非法操作！", 8, $_SERVER['HTTP_REFERER']);
        }
    }

    function newsdel_action()
    {
        $CompanyM = $this->Model('company');
        
        $delID = is_array($_POST['del']) ? $_POST['del'] : $_GET['id'];
        
        $return = $CompanyM->delCompanyNews($delID, array(
            'uid' => $this->uid,
            'usertype' => $this->usertype
        ));
        
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }
}
?>