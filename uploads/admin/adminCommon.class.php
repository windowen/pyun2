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

/**
 * phpyun后台公共代码： admin（pc后台）
 */
class adminCommon extends common{

    
    function admin(){
        
        $this -> get_admin_user_shell(); // 权限
                                          
        // 凡是有POST参数过来的都验证token信息
        if ($this -> config['sy_iscsrf'] != '2') {
            
            if (! $_SESSION['pytoken']) {
                
                $_SESSION['pytoken']  =  substr(md5(uniqid() . $_SESSION['auid'] . $_SESSION['ausername'] . $_SESSION['ashell']), 8, 12);
            }
            if ($_POST) {
                if ($_POST['pytoken'] != $_SESSION['pytoken']) {
                    
                    $this -> ACT_layer_msg('来源地址非法！', 8, $this->config['sy_weburl']);
                }
                unset($_POST['pytoken']);
            }
            $this -> yunset('pytoken', $_SESSION['pytoken']);
        }
    }

    function get_admin_user_shell(){

		$shellMsg = '暂无操作权限';

        // cache要判断C ajax不需要判断
        if ($_SESSION['auid'] && $_SESSION['ashell']) {
            
			//验证登录信息
            $row  =  $this -> admin_get_user_shell($_SESSION['auid'], $_SESSION['ashell']);
            
            if (!$row) {
                
                $this -> adminlogout();
                
                $this -> ShellMsg('登录超时，请刷新后重新登录！');exit();
            }
            
			

            if ($_GET['m'] == '' || $_GET['m'] == 'index' || $_GET['m'] == 'admin_nav') {
                
                $_GET['m'] = 'admin_right';
            }
            if ($this->config['did'] && $_GET['a']) {
                
                $_GET['c'] = $_GET['a'];
            }
            $c   =  $_GET['c'];
            $m   =  $_GET['m'];
            if ($_GET['m'] != 'admin_right') {
			
				//优先验证4级权限池（各类子模块权限）
				
				$moduleNav = $this -> get_shell_module($row['m_id'],$m,$c);
			
				if($moduleNav['error'] == '2'){//子权限验证未通过
					
					$this -> ShellMsg($shellMsg);exit();

				}elseif($moduleNav['error'] == '1'){//子权限验证通过 直接放行 OR 继续父级权限的认证
					
					//return true;
					
				}
                $url      =  'index.php?m=' . $m;
                
				
                $c_array  =  array(
                    'cache',
                    'markcom',
                    'markuser',
                    'advertise',
                    'zhaopinhui',
                    'admin_user',
                    'wx',
                );
                
                $navM    =  $this -> MODEL('navigation');
                
                if ($c && $c != 'savagroup' && in_array($m, $c_array)) {
                    
                    if ($m == 'admin_user' && $c == 'pass') {
                        
                        $c  =  'myuser';
                    }elseif ($m == 'wx' && $c == 'Getpubtool') {
                        
                        $c  =  'pubtool';
                    }
                    $url  .=  '&c=' . $c;
                    
                    $info  =  $navM -> getAdminNav(array('url'=>$url));
                    
                    if (empty($info)) {
                        
                        $url  =  'index.php?m=' . $m;
                    }
                }
                
                $nav  =  $this -> get_shell($row['m_id'], $url);
                if (! $nav) {
					
                    // $this->adminlogout();
                     $this -> ShellMsg($shellMsg);exit();
                }
                
                if (is_numeric($this->config['did'])) {
                    
                    if ($m == 'admin_user') {
                        
                        $where['url']   =  'index.php?m=admin_user&c=myuser';
                        $where['dids']  =  1;
                        
                    } else {
                        
                        $where['url']   =  $url;
                        $where['dids']  =  1;
                    }
                    $info  =  $navM -> getAdminNav($where);
                } else {
                    $info  =  $navM -> getAdminNav(array('url'=>$url));
                }
                if (! $info) {
                    $this -> ShellMsg($shellMsg);exit();
                }
            }
        } else {
            if ($_GET['m'] != '') {
                $this -> adminlogout();
                $this -> ShellMsg('登录超时，请刷新后重新登录！');exit();
            }
        }
    }

    // 用户退出
    function adminlogout(){
        unset($_SESSION['authcode']);
        unset($_SESSION['auid']);
        unset($_SESSION['ausername']);
        unset($_SESSION['ashell']);
        unset($_SESSION['md']);
        unset($_SESSION['tooken']);
        unset($_SESSION['xsstooken']);
       
    }
	
	function ShellMsg($msg = '您暂无操作权限！'){
		
		//三种提醒方式 JS/AJAX：自定义错误状态 表单POST：ACT_MSG GET：普通提示
		header('HTTP/1.1 777 Not Authorization'); 

		if($_POST){
			$this->ACT_layer_msg( $msg ,8);
		}else{
			echo $msg;
		}
		
		exit();
	}
    /**
     *@desc  后台用户登录
     */
    function admin_get_user_login($username, $password, $url = 'index.php'){
        
        global $config;
        
        $username  =  str_replace(' ', '', $username);
        
        $adminM    =  $this -> MODEL('admin');
        
        if ($config['sy_web_site'] == '1') {
            
            $where['username']        =  $username;
            $where['PHPYUNBTWSTART']  =  '';
            $where['did']             =  $config['did'];
            $where['isdid']           =  array('=',1,'OR');
            $where['PHPYUNBTWEND']    =  '';
            $where['status']          =  1;
            
            $user  =  $adminM -> getAdminUser($where, array());
        } else {
            
            $user  =  $adminM -> getAdminUser(array('username' => $username,'status' => 1), array());
        }
        
        $verify  =  $adminM -> verifyPass($password, $user['password']);
        
        if ($verify) {
            
            $_SESSION['auid']       =  $user['uid'];
            $_SESSION['ausername']  =  $user['username'];
            $_SESSION['xsstooken']  =  sha1($config['sy_safekey']);
            $_SESSION['ashell']     =  md5($user['username'] . $user['password'] . $this -> md);
            
            $logM      =  $this -> MODEL('log');
            
            $adminLog  =  $logM -> getAdminLog(array('uid' => $_SESSION['auid'],'content' => '登录成功','orderby' => 'ctime'));
            
            $time      =  time();
            
            if ($adminLog) {
                
                $this -> cookie -> setCookie('lasttime', $adminLog['ctime'], $time + 80000);
            } else {
                
                $this -> cookie -> setCookie('lasttime', $time, $time + 80000);
            }
            
            $this -> cookie -> setCookie('ashell', md5($user['username'] . $user['password'] . $this -> md), $time + 80000);
            
            $adminM -> upAdminUser(array('lasttime' => $time), array('uid' => $user['uid']));
           
            $logM -> addAdminLog('登录成功', '', '', '');
            
            $this -> ACT_layer_msg('登录成功！', 9, $url);
        } else {
            
            $this -> ACT_layer_msg('密码或用户错误！', 8, $url);
        }
    }


    /**
     * 管理员用户登录权限验证
     */
    function admin_get_user_shell($uid, $shell){
        
        global $config;
        
        if (! preg_match("/^\d*$/", $uid)) {
            return false;
        }
        
        $adminM    =  $this -> MODEL('admin');
        
        if ($config['sy_web_site'] == '1') {
            
            $where['uid']             =  $uid;
            $where['PHPYUNBTWSTART']  =  '';
            $where['did']             =  $config['did'];
            $where['isdid']           =  array('=',1,'OR');
            $where['PHPYUNBTWEND']    =  '';
            
            $user  =  $adminM -> getAdminUser($where);
        } else {
            
            $user  =  $adminM -> getAdminUser(array('uid' => $uid));
        }
        
        $ps = !empty($user) ? $shell == md5($user['username'] . $user['password'] . $this->md) : FALSE;
        
        return $ps ? $user : NULL;
    }

    /**
     * 检查访问地址
     */
    function check_token(){
        if ($this->config['sy_iscsrf'] != '2') {
            
            if ($_SESSION['pytoken'] != $_GET['pytoken'] || ! $_SESSION['pytoken']) {
                
                unset($_SESSION['pytoken']);
                
                $this -> ACT_layer_msg('来源地址非法！', 8, 'index.php');
                
                exit();
            }
        }
    }

    /**
     * 后台顶部导航条默认页面
     * @param $id     
     * @param $power
     */
    function GET_web_default($id, $power){
        
        if ($this->config['sy_web_site'] == '1' && $this->config['did']) {
            
            $where['dids'] = 1;
        }
        
        $navM  =  $this -> MODEL('navigation');
        
        $web   =  $navM -> getAdminNavList(array('keyid'=>array('in',pylode(',', $id)),'orderby'=>'sort,asc'));
        
        if (is_array($web['list'])) {
            
            foreach ($web['list'] as $v) {
                
                if (@in_array($v['id'], $power)) {
                    
                    $arr[]           =  $v['id'];
                    
                    $arr2[$v['id']]  =  $v['keyid'];
                }
            }
            
            $where['keyid']    =  array('in',pylode(',', $arr));
            $where['orderby']  =  'sort,asc';
            
            $webaa  =  $navM -> getAdminNavList($where);
            
            if (is_array($webaa)) {
                
                foreach ($webaa['list'] as $va) {
                     
                    if (@in_array($va['id'], $power)) {
                        
                        $value[$arr2[$va['keyid']]]  =  $va['url'];
                    }
                }
            }
        }
        
        return $value;
    }
	/**
     * 管理员组操作模块权限认证
     * @param string $mid  管理员类型id
     * @param string $web  要检测的链接url
     * @param string $type
     * @return boolean
     */
    function get_shell_module($mid, $m,$c){
        
        
        
		$navM    =  $this -> MODEL('navigation');
        //首先检测当前链接是否在操作模块权限池中
		$nav     =  $navM -> getAdminNav(array('url'=>'index.php?m='.$m.'&c='.$c,'level'=>4));
			
		//不在权限池就无需验证
		if(!empty($nav)){
			$adminM  =  $this->MODEL('admin');
			$row     =  $adminM -> getPower(array('id'=>$mid));
			//判断当前管理员类型有权限、且该类型下有用户
			if ($row['power'] && $row['name']){
				
				
		
				if(in_array($nav['id'], $row['power'])){

					$return['error'] = 1;
					$return['power'] = $row;
				}else{	
					$return['error'] = 2;
				}
			} else {
				$return['error'] = 2;
			}
		}else{

			$return['error'] = 3;
			
		}
	
        
		
        
        return $return;
    }
    /**
     * 管理员组导航菜单权限检测
     * @param string $mid  管理员类型id
     * @param string $web  要检测的链接url
     * @param string $type
     * @return boolean
     */
    function get_shell($mid, $web, $type = ''){
        // $web=str_replace('&','&amp;',$web);
        
        $adminM  =  $this->MODEL('admin');
        
        $row     =  $adminM -> getPower(array('id'=>$mid),1);
		
        //判断当前管理员类型有权限、且该类型下有用户
        if ($row['power'] && $row['group_name']){
            
            $navM    =  $this -> MODEL('navigation');
            
            $nav     =  $navM -> getAdminNav(array('url'=>$web));
           
            $return  =  @in_array($nav['id'], $row['power']) ? true : false;
        } else {
            $return  =  false;
        }
        return $return;
    }
    /**
     * 获取页面名称
     * @param string $id
     * @return string
     */
    function GET_web_check($id){
        
        $navM  =  $this -> MODEL('navigation');
        
        $nav   =  $navM -> getAdminNav(array('id'=>$id));
        
        if (is_array($nav)) {
            
            $value  .=  $this -> GET_web_check($nav['keyid']);
            
            $value  .=  $nav['name'] . ' - ';
        }
        return $value;
    }
}
?>