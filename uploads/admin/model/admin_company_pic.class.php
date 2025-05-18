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
class admin_company_pic_controller extends adminCommon
{

    function set_search()
    {
        $search_list[]  = array(
            'param'     => 'status',
            'name'      => '审核状态',
            'value'     => array(
                '0' => '已审核',
                '1' => '未审核'
            )
        );

        $this->yunset('search_list', $search_list);
    }

    /**
     * @desc 后台企业 -   图片管理    -   企业LOGO
     */
    function index_action()
    {
        $this           ->  set_search();

        $CompanyM       =   $this->MODEL('company');
        
        $where          =   array();

        $where['logo']  =   array('<>','');

 
        $keytype = intval($_GET['type']);

        $keyword = trim($_GET['keyword']);
        
        if (!empty($keyword)) {
            
            if ($keytype == 2) {
                
                $where['uid']   =   array('like', $keyword);
                
            } else {
                
                $where['name']  =   array('like', $keyword);
                
            }
            
            $urlarr['keytype']  =   $keytype;
            
            $urlarr['keyword']  =   $keyword;
        }

        if (isset($_GET['status'])) {

            $status                 =   intval($_GET['status']);

            $where['logo_status']   =   $status;

            $urlarr['status']       =   $status;
        }

        $urlarr['page'] =   '{{page}}';

        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        // 提取分页
        $pageM          =   $this   ->  MODEL('page');
        
        $pages          =   $pageM  ->  pageList('company', $where, $pageurl, $_GET['page'], '15');
		
        if ($pages['total'] > 0) {
            
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
                
            } else {

                $where['orderby']   =   array('logo_status,desc','uid,desc');
				
				
                
            }
            
        }

        $where['limit']             =   $pages['limit'];

        $ListNew                    =   $CompanyM -> getList($where,array('field'=>'uid,name,logo,logo_status',array('logo'=>'2')));
        
        $this   ->  yunset(array('rows' => $ListNew['list']));

        $this   ->  yuntpl(array('admin/admin_company_pic'));
    }

    /**
     * @desc 后台企业 -   图片管理    -   企业环境
     */
    function show_action()
    {
        $this           ->  set_search();

        $companyM       =   $this->MODEL('company');

        $where          =   array();
        
        $keytype        =   intval($_GET['type']);
        
        $keyword        =   trim($_GET['keyword']);
        
        if (!empty($keyword)) {
        
            if ($keytype == 2) {
                
                $where['uid']       =   array('like', $keyword);
                
            } elseif ($_GET['type'] == '3') {
                
                $where['title']     =   array('like', $keyword);
                
            } else {
                
                $ListNew            =   $companyM -> getList(array('name'=> array('like', $keyword)), array('field' => 'uid'));
                
                $com                =   $ListNew['list'];
                
                foreach ($com as $v) {
                    
                    $uid[]          =   $v['uid'];
                    
                }
                
                $where['uid']       =   array('in', pylode(',', $uid));
            }
            
            $urlarr['type']         =   $keytype;
            
            $urlarr['keyword']      =   $keyword;
            
        }
        
        if (isset($_GET['status'])) {

            $status                 =   intval($_GET['status']);

            $where['status']        =   $status;

            $urlarr['status']       =   $status;
        }

        $urlarr['c']                =   'show';

        $urlarr['page']             =   '{{page}}';

        $pageurl                    =   Url($_GET['m'], $urlarr, 'admin');

        $pageM                      =   $this -> MODEL('page');

        $pages                      =   $pageM -> pageList('company_show', $where, $pageurl, $_GET['page'], '15');

        if ($pages['total'] > 0) {
            
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
                
            } else {

                $where['orderby']   =   array('status,desc','id,desc');
                
            }
        }

        $where['limit']             =   $pages['limit'];

        $rows                       =   $companyM -> getCompanyShowList($where);

        $this -> yunset('rows', $rows);

        $this->yuntpl(array('admin/admin_company_picshow'));
    }

    /**
     * @desc 后台企业 -   图片管理    -   企业横幅
     */
    function banner_action()
    {
        $this           ->  set_search();

        $companyM       =   $this->MODEL('company');
        
        $where          =   array();
        
        $keytype        =   intval($_GET['type']);
        
        $keyword        =   trim($_GET['keyword']);
        
        if (!empty($keyword)) {
            
            if ($keytype == 2) {
                
                $where['uid']   =   array('like', $keyword);
                
            } else {
                
                $ListNew        =   $companyM->getList(array('name'=>array('like', $keyword)), array('field' => 'uid'));
                
                $com            =   $ListNew['list'];
                
                foreach ($com  as  $v) {
                    
                    $uid[]      =   $v['uid'];
                    
                }
                
                $where['uid']   =   array('in', pylode(',', $uid));
            }
            
            $urlarr['keytype']  =   $keytype;
            
            $urlarr['keyword']  =   $keyword;
            
        }

        if (isset($_GET['status'])) {

            $status                 =   intval($_GET['status']);

            $where['status']        =   $status;

            $urlarr['status']       =   $status;
        }

        $urlarr['c']            =   'banner';

        $urlarr['page']         =   '{{page}}';

        $pageurl                =   Url($_GET['m'], $urlarr, 'admin');

        $pageM                  =   $this -> MODEL('page');

        $pages                  =   $pageM -> pageList('banner', $where, $pageurl, $_GET['page'], '15');

        if ($pages['total'] > 0) {

            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
                
            } else {

                $where['orderby']   =   array('status,desc','id,desc');
                
            }
        }

        $where['limit']             =   $pages['limit'];

        $rows                       =   $companyM -> getBannerList($where);

        $this->yunset('rows', $rows);

        $this->yuntpl(array('admin/admin_company_picbanner'));
        
    }
    
    /**
     * @desc 企业LOGO审核
     */
    function status_action()
    {
        $companyM  =  $this->MODEL('company');
        
        $post  	   =  array(
            'logo_status'    	=>  intval($_POST['status']),
            'logo_statusbody'  	=>  $_POST['statusbody']
        );
        
        $return   =  $companyM -> statusLogo($_POST['uid'], array('post'=>$post));
        
        $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'],2,1);
    }
    
    /**
     * @desc 企业LOGO审核说明
     */
    function getStatusBody_action()
    {
        $companyM   =   $this -> MODEL('company');
        
        $company    =   $companyM -> getInfo(intval($_GET['uid']), array('field' => 'logo_statusbody'));
        
        echo trim($company['logo_statusbody']);die();
        
    }
    /**
     * @desc 企业环境审核说明
     */
    function getShowStatusBody_action()
    {
        $companyM   =   $this -> MODEL('company');
        
        $comshow    =   $companyM -> getCompanyShowInfo(intval($_GET['id']), array('field' => 'statusbody'));
        
        echo trim($comshow['statusbody']);die();
        
    }
    /**
     * @desc 企业横幅审核说明
     */
    function getBannerStatusBody_action()
    {
        $companyM   =   $this -> MODEL('company');
        
        $company    =   $companyM -> getBannerInfo(intval($_GET['id']), array('field' => 'statusbody'));
        
        echo trim($company['statusbody']);die();
        
    }
    /**
     * @desc 企业环境审核
     */
    function showStatus_action(){

        $CompanyM           =           $this->MODEL('company');
        
        $status             =           intval($_POST['status']);
        
        $statusbody         =           trim($_POST['statusbody']);

        $post               =           array(

            'status'        =>          $status,

            'statusbody'    =>          $statusbody

        );
       

        $return             =           $CompanyM->statusShow($_POST['sid'],array('post'=>$post));

        $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'],2,1);
    }
    /**
     * @desc 企业环境横幅
     */
    function bannerStatus_action(){

        $CompanyM           =       $this->MODEL('company');
        
        $status             =       intval($_POST['status']);
        
        $statusbody         =       trim($_POST['statusbody']);
        
        $post               =       array(

            'status'        =>      $status,
            
            'statusbody'    =>      $statusbody
        
        );

        $return             =           $CompanyM->statusBanner($_POST['sid'],array('post'=>$post));

        $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'],2,1);

    }

    /**
     * @desc 后台  企业 修改 图片
     */
    function uploadsave_action()
    {
        
        $CompanyM   =   $this->MODEL('company');

        $_POST      =   $this->post_trim($_POST);

        $id         =   $_POST['id'];

        $UploadM    =   $this->MODEL('upload');
        
        if ($_POST['update']) {

            switch ($_POST['type']) {
                case 'logo':
                    $dir    =   'company';
                    break;
                
                case 'show':
                    $dir    =   'show';
                    break;

                case 'banner':
                    $dir    =   'company';
                    break;    
            }

            if($_FILES['file']['tmp_name']){
              $upArr    =  array(
                'file'  =>  $_FILES['file'],
                'dir'   =>  $dir
              );

              $uploadM  =  $this->MODEL('upload');

              $pic      =  $uploadM->newUpload($upArr);
              
              if (!empty($pic['msg'])){

                $this->ACT_layer_msg($pic['msg'],8);

              }elseif (!empty($pic['picurl'])){

                $pictures   =   $pic['picurl'];
              }
            }

            if ($_POST['type'] == 'logo') {
                
                if(isset($pictures)){

                    $data['logo']   =   $pictures;

                    $nbid   =   $CompanyM -> upInfo($id, '', $data);

                    $this   ->  automsg('管理员操作：修改企业logo', $id);
                }
                
                isset($nbid) ? $this->ACT_layer_msg('企业logo(ID:' . $id . ')修改成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1) : $this->ACT_layer_msg('修改失败！', 8, $_SERVER['HTTP_REFERER']);
                
            }
            
            
            if ($_POST['type'] == 'show') {

                $row    =   $CompanyM -> getCompanyShowInfo($id, array('field' => 'picurl,title,uid'));

                $data = array(

                    'sort' => $_POST['sort'],

                    'title' => $_POST['title'],

                    'ctime' => time()
                );

                if(isset($pictures)){

                    $data['picurl']   =   $pictures;

                }

                $nbid       =   $CompanyM -> upCompanyShow($id, $data);

 
                $this->automsg('管理员：修改企业环境(ID:' . $id . ')', $row['uid']);

                isset($nbid) ? $this->ACT_layer_msg('企业环境(ID:' . $id . ')修改成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1) : $this->ACT_layer_msg('修改失败！', 8, $_SERVER['HTTP_REFERER']);
                
                
            }
            
            
            if ($_POST['type'] == 'banner') {

                $row    =   $CompanyM -> getBannerInfo($id, array('field' => 'pic,uid'));
 
                if(isset($pictures)){

                    $data['pic']   =   $pictures;

                    $nbid       =   $CompanyM -> upBanner($id, $data);

                    $this->automsg('管理员修改企业横幅', $row['uid']);

                }

                isset($nbid) ? $this->ACT_layer_msg('企业横幅(ID:' . $id . ')修改成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1) : $this->ACT_layer_msg('修改失败！', 8, $_SERVER['HTTP_REFERER']);
            }
        }
    }

    /**
     * @desc 后台 - 企业图片管理 - 删除
     */
    function del_action()
    {
         
        $CompanyM   =   $this->MODEL('company');

        if ($_GET['delid']) {

            $this   ->  check_token();
            
            $id     =   intval($_GET['delid']);
            

            if ($_GET['type'] == 'logo') {  
 
                $delid  =   $CompanyM -> upInfo($id, '' , array('logo'=>''));
 
                $this   ->  automsg('管理员 ：删除企业logo', $id);

                $delid ? $this->layer_msg('企业logo(ID:' . $_GET['delid'] . ')删除成功！', 9, 0, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 0, $_SERVER['HTTP_REFERER']);
            }
            
            if ($_GET['type'] == 'show') {  

                $row    =   $CompanyM -> getCompanyShowInfo($id,array('field'=>'uid,picurl'));

                $delid  =   $CompanyM -> delCompanyShow($id,array('utype'=>'admin'));

                $this   ->  automsg('管理员：删除企业环境(ID:' . $id . ')', $row['uid']);

                $delid ? $this->layer_msg('企业环境(ID:' . $id . ')删除成功！', 9, 0, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 0, $_SERVER['HTTP_REFERER']);
            }
            
            
            if ($_GET['type'] == 'banner') { // 删除企业横幅

                $row    =   $CompanyM->getBannerInfo($id,array('field'=>'uid,pic'));
 
                $delid  =   $CompanyM -> delBanner($id);

                $this   ->  automsg('管理员：删除企业横幅', $row['uid']);

                $delid ? $this->layer_msg('企业横幅(ID:' . $id . ')删除成功！', 9, 0, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 0, $_SERVER['HTTP_REFERER']);
            }
            
        }else if (is_array($_GET['del'])) {

            $this   ->  check_token();

            if ($_GET['type'] == 'logo') { // 删除logo

                $row    =   $CompanyM -> getList(array('uid'=>array('in',pylode(',', $_GET['del'])), 'logo'=>array('<>','')), array('field' => 'uid,logo'));

                $delid  =   $CompanyM   ->  upInfo($_GET['del'], '', array('logo'=>''));

                if ($row) {

                    foreach ($row as $v) {

                        $this->automsg('管理员操作：删除企业logo', $v['uid']);
                    }
                }
            
                $delid ? $this->layer_msg('企业logo(ID:' . $_GET['del'] . ')删除成功！', 9, 1, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 1, $_SERVER['HTTP_REFERER']);
            
            }
            
            if ($_GET['type'] == 'show') {  

                $ids    =   $_GET['del'];

                $row    =   $CompanyM->getCompanyShowList(array('id'=>array('in',pylode(',', $ids)), 'picurl'=>array('<>', '')), array('field' => 'picurl,id,uid'));

                $delid  =   $CompanyM -> delCompanyShow($ids,array('utype'=>'admin'));

                if ($row) {
                    
                    foreach ($row as $v) {

                        $this->automsg('管理员操作：删除企业环境(ID:' . $v['id'] . ')', $v['uid']);
                        
                    }
                    
                }
                
                $delid ? $this->layer_msg('企业环境(ID:' . $ids . ')删除成功！', 9, 1, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 1, $_SERVER['HTTP_REFERER']);
            }
            if ($_GET['type'] == 'banner') { // 删除企业横幅

                $ids    =   $_GET['del'];

                $row    =   $CompanyM -> getBannerList(array('id'=>pylode(',', $ids),'pic'=>array('<>','')), array('field' => 'pic,uid'));

                $delid  =   $CompanyM -> delBanner($ids);

                if ($row) {

                    foreach ($row as $v) {

                        $this->automsg('管理员操作：删除企业横幅', $v['uid']);
                    }
                }
                $delid ? $this->layer_msg('企业横幅(ID:' . $ids . ')删除成功！', 9, 1, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, 1, $_SERVER['HTTP_REFERER']);
            }
            
        } else {
            
            $this->layer_msg('请选择您要删除的图片！', 8, 1);
        }
    }
}
?>