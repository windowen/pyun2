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
class index_controller extends adminCommon{
    
    /**
     * @desc后台首页
     */
    function index_action($set = ''){
    
        global $db_config;
        
        $this -> yunset('db_config', $db_config);
        
        $_POST  =  $this -> post_trim($_POST);
        
        if (! empty($_POST['username']) && ! empty($_POST['password'])) {
            
            if (strpos($this -> config['code_web'], '后台登录') !== false) {
                
                if ($this -> config['code_kind'] > 2) {
                    
					$check	=	verifytoken($this->config);
                    // 极验验证
                    if ($check['code'] != '200') {
                        $this -> ACT_layer_msg($check['msg'], 8, 'index.php');
                    } else {
                        $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
                    }
                    
                } else {
                    
                    if (md5(strtolower($_POST['authcode'])) === $_SESSION['authcode']) {
                        
                        unset($_SESSION['authcode']);
                        
                        $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
                        
                    } else {
                        
                        unset($_SESSION['authcode']);
                        
                        $this -> ACT_layer_msg('验证码错误！', 8, 'index.php');
                    }
                    
                }
                
            } else {
                
                $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
            }
            
        }
        
		

        $tpname  =  $this -> admin_get_user_shell($_SESSION['auid'], $_SESSION['ashell']) ? 'index' : 'login';
        
        if ($tpname == 'login') {
            
            $this->yuntpl(array('admin/' . $tpname));
            
            die;
        }
         
        // 权限配置
        
        $adminM    =  $this -> MODEL('admin');
        
        $power     =  $adminM -> getPower(array('uid'=>$_SESSION['auid']));
        
        $this -> yunset('power', $power['power']);
		
		$this -> yunset('indexurl', 'index.php?m=admin_right');
        
        $nav_user  =  array(
            'name'        =>  $power['name'],
            'group_name'  =>  $power['group_name']
        );
        
        //查询最近登录时间
        $logM  =  $this->MODEL('log');

        $adminLog  =  $logM -> getAdminLog(array('uid' => $_SESSION['auid'],'content' => '登录成功','orderby' => 'ctime'));
        
        // 导航配置
        $navM      =  $this -> MODEL('navigation');

		$navList   =  $navM -> getAdminNavList(array('display'=>array('<>',1),'id'=>array('in', pylode(',', $power['power'])),'orderby'=>'sort,ASC'),array('utype'=>'power'));


        $setarr    =  array(
            'one_menu'        =>  $navList['one_menu'],
            'two_menu'        =>  $navList['two_menu'],
            'navigation'      =>  $navList['navigation'],
            'menu'            =>  $navList['menu'],
            'nav_user'        =>  $nav_user,
            'admin_lasttime'  =>  $adminLog['ctime'] ? $adminLog['ctime'] : time()
        );
        
        $this -> yunset($setarr);
        
        // 获取默认页面
        if (is_array($navList['navigation'])) {
            
            foreach ($navList['navigation'] as $v) {
                
                $navigation_url_id[]  =  $v['id'];
            }
            
            $navigation_url = $this -> GET_web_default($navigation_url_id, $power['power']);

            
        }
        
        $this -> yunset('navigation_url', $navigation_url);
        
        if ($set == '') {
            
            $this->yuntpl(array('admin/' . $tpname));
            
        }
    }
    /**
     * @desc 首页-管理员退出
     */
    function logout_action(){
        
        $this -> adminlogout();
        
        $this -> layer_msg('您已成功退出！', 9, 0, 'index.php');
    }
    
    /**
     *@desc 首页-清除缓存
     */
    function del_cache_action(){
        
        $cache   =  $this->del_dir('../data/templates_c', 1);
        
        $cache   =  $this->del_dir('../data/cache', 1);
        
        /* 生成四位JS，css识别码 */
        $configM  =  $this->MODEL('config');
        
        $configM -> cacheCode();
        
        $this -> web_config();
        
        if ($cache == true) {
            
            $this -> layer_msg('更新成功！', 9, 0, 'index.php');
            
        } else {
            
            $this -> layer_msg('更新失败,请检查是否有权限！', 8, 0, 'index.php');
        }
    }
    /**
     * 首页-后台地图
     */
    function map_action(){
        
        $this -> index_action(1);
        
        $this -> yuntpl(array('admin/admin_map'));
    }
    /**
     * 获取页面标题
     */
    function topmenu_action(){
        
        $id  =  (int) $_GET['id'];
        // 解决ie9 $.get $.post 回调函数的返回值为undefine
        header('Content-Type: text/html; charset=UTF-8');
        
        if ($id == '1000') {
            echo '管理首页';
        } else {
            $arr  =  $this -> GET_web_check($id);
            
            $n    =  explode('-', $arr);
            
            unset($n[count($n) - 1]);
            
            echo implode('-', $n);
        }
    }
    /**
     * 首页-设置快捷导航
     */
    function shortcut_menu_action(){
        
        $tpname = $this -> admin_get_user_shell($_SESSION['auid'], $_SESSION['ashell']);
        
        if ($_POST['chk_value'] && is_array($tpname)) {
            
            $navM    =  $this -> MODEL('navigation');
            
            $navM -> upAdminNav(array('menu'=>1),array('menu'=>2));
            
            $navM -> upAdminNav(array('menu'=>2),array('id'=>array('in',@implode(',', $_POST['chk_value']))));
            
            echo 1;
            
            die;
        } else {
			
			$this->ACT_layer_msg("无权操作！",8,$this->config['sy_weburl'],2,1);
            
        }
    }
    /**
     * 首页-查询相关统计数据
     */
    function msgNum_action(){
        
        $MsgNum  =  $this -> MODEL('msgNum');
        echo $MsgNum->msgNum();
    }

    function wxbind_action(){
        $WxM=$this->MODEL('weixin');
        $qrcode = $WxM->applyWxQrcode($_COOKIE['wxadminbind'],'wxadminbind');
        if(!$qrcode){
            echo 0;
        }else{
            echo $qrcode;
        }
    }
    function getwxbindstatus_action(){
        if($_SESSION['auid']){
            $adminM  =  $this -> MODEL('admin');
            $uadmin = $adminM ->  getAdminUser(array('uid'=>$_SESSION['auid']));
            if($uadmin['wxid']!=''){
                echo 1;
            }else{
                echo 0;
            }
        }
    }
    function delAdminWxBind_action(){
        if($_SESSION['auid']){
            $adminM  =  $this -> MODEL('admin');
            $uadmin = $adminM ->  getAdminUser(array('uid'=>$_SESSION['auid']));
            if($uadmin['wxid']!=''){
                $adminM ->  upInfo(array('wxid'=>''),array('uid'=>$_SESSION['auid']));
            }
            echo 1;
        }
    }
    
}
?>