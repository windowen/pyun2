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
class admin_partjob_controller extends adminCommon{
    
    function set_search(){
        
        $cacheM             =       $this -> MODEL('cache');
        
        $cache              =       $cacheM -> GetCache(array('part'));
        
        $partdata           =       $cache[partdata];
        
        $partclass_name     =       $cache[partclass_name];
        
        foreach ($partdata['part_billing_cycle'] as $k => $v) {
            $billing_cycle[$v] = $partclass_name[$v];
        }
        
        $states     =   array( "1" => "已审核",  "4" => "未审核",  "3" => "未通过", "2" => "已过期","5" => "已锁定");
        $updates    =   array( "1" => "今天", "3" => "最近三天", "7" => "最近七天", "15" => "最近半月", "30" => "最近一个月"  );
        $edates     =   array( "1" => "已到期", "3" => "最近三天", "7" => "最近七天", "15" => "最近半月", "30" => "最近一个月" );
        
        $search_list = array();
        
        $search_list[] = array(
            "param" => "state",
            "name" => '审核状态',
            "value" => $states
        );
        
        $search_list[] = array(
            "param" => "lastupdate",
            "name" => '更新时间',
            "value" => $updates
        );
        
        $search_list[] = array(
            "param" => "edate",
            "name" => '结束日期',
            "value" => $edates
        );
        
        $search_list[] = array(
            "param" => "billing_cycle",
            "name" => '结算周期',
            "value" => $billing_cycle
        );
        
        $this->yunset("search_list",$search_list);
    }
    
    function index_action(){
        
        $comM      =   $this -> MODEL('company');
        
        $partM     =   $this -> MODEL('part');
        
        $this->set_search();
        
        if($_GET['uid']){
            
            $where['uid']    =   intval($_GET['uid']);
            
            $urlarr['uid']   =   intval($_GET['uid']);
            
            $ccom            =   $comM -> getInfo($where['uid'],array('field'=>'`name`')) ;
            
            $this    ->  yunset('ccname',$ccom['name']);
        }
        
        if($_GET['state']){
            
            $state    =   intval($_GET['state']);
            
            if($state == 1){
                
                $where['state']   =   '1';
                
                $where['PHPYUNBTWSTART_A']    =   '';
                
                $where['edate'][]             =   array('>', time(),'OR');
                $where['edate'][]             =   array('=', '','OR');
                
                $where['PHPYUNBTWEND_A']      =   '';
             
                
            }elseif($state == 2){
                
                $where['PHPYUNBTWSTART_A']    =   '';
                
                $where['edate'][]             =   array('<', time(),'AND');
                $where['edate'][]             =   array('>', '0','AND');
                
                $where['PHPYUNBTWEND_A']    =   '';
             
                
            }elseif($_GET['state']=="3"){
                
                $where['state']   =   $state;
                 
                
            }elseif($_GET['state']=="4"){
                $where['state']   =   '0';  
            }elseif($_GET['state']=='5'){
               $where['r_status']		=	  2;
            }
            
            $urlarr['state']      =   $state;
        }
        
        if($_GET['lastupdate']){
            
            
            if(intval($_GET['lastupdate']) == 1){
                
                $where['lastupdate']  =   array('>', strtotime(date('Y-m-d 00:00:00')));
                
            }else{
                
                $where['lastupdate']  =   array('>', strtotime('-'.intval($_GET['lastupdate']).' day'));
                
            }
            
            $urlarr['lastupdate']     =   $_GET['lastupdate'];
        }
        
        if($_GET['edate']){
            if(intval($_GET['edate']) == 1){
                
                $where['edate'][]     =   array('<', time(),'AND');
                $where['edate'][]     =   array('>', '0','AND');
                
            }else{
                
                $where['edate'][]     =   array('<', strtotime('+'.intval($_GET['edate']).' day'),'AND');
                $where['edate'][]     =   array('>', time(),'AND');
            }
            
            $urlarr['edate']          =   $_GET['edate'];
        }
        
        if ($_GET['billing_cycle']){
            
            $where['billing_cycle']   =   intval($_GET['billing_cycle']);
            
            $urlarr['billing_cycle']  =   intval($_GET['billing_cycle']);
        }
        
        $typeSkr      =   intval($_GET['type']);
        $keywordSkr   =   trim($_GET['keyword']);
        
        if(!empty($keywordSkr)){
            
            if($typeSkr == 1){
                
                $where['com_name']  =   array('like', $keywordSkr);
                
            }else if ($typeSkr == 2){
                
                $where['name']      =   array('like', $keywordSkr);
                
            }
            
            $urlarr['keyword']   =   $keywordSkr;
            $urlarr['type']      =   $typeSkr;
        }
        
        $urlarr['page']	=	'{{page}}';
        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('partjob',$where,$pageurl,$_GET['page']);
        
        if($pages['total'] > 0){
            
            if($_GET['order']){
                
                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
                
            }else{
                
                $where['orderby']		=	array('state,asc','id,desc');
                
            }
            
            $where['limit']				=	$pages['limit'];
            
            $partList     =   $partM -> getList($where);
            $this -> yunset(array('rows'=>$partList));
            
        }
        
        $this->yuntpl(array('admin/admin_partjob'));
    }
    
    /**
     * @desc 职位详情及修改
     */
    
    function show_action(){
        
        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM -> GetCache(array('city','part'));
        $this -> yunset($cache);
        
        $partM      =   $this->MODEL('part');
        
        $companyM	=	$this->MODEL('company');
        if($_GET['id']){
            
            $List   =   $partM -> getInfo(array('id' => intval($_GET['id'])));
            
            $show   =   $List['info'];
            $show['workcishu']	=	count($show['worktime_n']);
            $this->yunset('show',$show);
            
            $uid            =       $show['uid'];
            $company		=		$companyM->getInfo($uid,array('field'=>'`uid`,r_status'));

            $this->yunset('company',$company);

            $this->yunset("today",date("Y-m-d"));
            
        }
        
        if($_POST['update']){
            
            if($_POST['timetype']){
                $_POST['edate']     =   "";
            }else{
                $_POST['edate']     =   strtotime($_POST['edate']);
            }
            
            $data                 =   $_POST;
            if($_POST['r_status']==1){
                $data['state']        =   '1';
            }else{
                $data['state']        =   '0';
            }
           
            
            $arr  =   $partM -> upPartInfo($data);
            
            $this ->  ACT_layer_msg($arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER']);
        }
        
        $this->yuntpl(array('admin/admin_partjob_show'));
    }
    
    /**
     * @desc 删除兼职
     */
    function del_action(){
        
        $this->check_token();
        
        $partM    =   $this -> MODEL('part');
        
        if(is_array($_GET['del'])){
            
            $id           =   $_GET['del'];
            
        }else{
            
            $id           =   intval($_GET['del']);
            
        }
        
        $arr  =   $partM -> delPart($id, array('utype' => 'admin'));
        
        $this ->  layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);
        
    }
    
    /**
     * @desc 兼职审核，查询审核原因提取
     */
    function lockinfo_action(){
        
        $partM     =   $this -> MODEL('part');
        
        $pInfo     =   $partM -> getInfo(array('id' => intval($_POST['id'])),array('field'=>'`statusbody`'));
        
        echo  $pInfo['info']['statusbody']; die;
    }
    
    /**
     * @desc 兼职推荐
     */
    function recommend_action(){
        
        $id     =   trim($_POST['pid']);
        
        $data   =   array(
            
            'rec'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['days'])
            
        );
        
        $partM     =   $this -> MODEL('part');
        
        $arr       =   $partM -> addRecPart($id, $data);
        
        $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);
    }
    
    
    /**
     * @desc 兼职延期
     */
    function ctime_action(){
        
        $partM  =   $this -> MODEL('part');
        
        $id     =   trim($_POST['jobid']);
        
        $arr    =   $partM -> addPartTime($id, array('days'=>intval($_POST['days'])));
        
        $this   ->  Act_layer_msg($arr['msg'], $arr['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
        
    }
    
    /**
     * @desc 兼职职位审核
     */
    function status_action()
    {
        $partM          =   $this->MODEL('part');
        
        $statusData     =   array(
            
            'state'         =>  intval($_POST['status']),
            'statusbody'    =>  trim($_POST['statusbody'])
        );
        
        $return         =   $partM->statusPartJob($_POST['pid'], $statusData);
        $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
    }
    
    function partjobstatus_action()
    {
        if ($_POST) {
            
            $id         =   intval($_POST['cid']);
            $uid        =   intval($_POST['cuid']);
            $status     =   intval($_POST['r_status']);
            $statusbody =   trim($_POST['statusbody']);
            
            $partM      =   $this->MODEL('part');
            
            $post   =   array(
                
                'uid'           =>  $uid,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );
            
            $return     =   $partM -> status($id, $post);
            $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }
    
    /**
     * @desc 刷新兼职
     */
    function refresh_action(){
        
        $partM          =   $this -> MODEL('part');
        
        $data['ids']    =	$_POST['ids'];
        
        $partM  ->  refreshPartJob($data);
        
    }
    
    /**
     * @desc 获取兼职职位数据数目
     */
    function partNum_action(){
        $MsgNum=$this->MODEL('msgNum');
        echo $MsgNum->partNum();
    }
}
?>