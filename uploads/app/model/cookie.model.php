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
 * cookie操作类
 */
class cookie_model extends model{
  private function _getCookieDomain(){
    global $pageType;
    if($this->config['sy_web_site'] == '1' || ((isset($pageType) && $pageType=='wap' && strpos($this->config['sy_wapdomain'],'/wap')===false && $this->config['sy_wapdomain']))){
		
        if($this->config['sy_web_site'] == '1' && $this->config['sy_onedomain']!=""){
            $weburl  =  get_domain($this->config['sy_onedomain']);
        }elseif($this->config['sy_web_site'] == '1' && $this->config['sy_indexdomain']!=""){
            $weburl  =  get_domain($this->config['sy_indexdomain']);
        }else{
            $weburl  =  get_domain($this->config['sy_weburl']);
        }
    }else{
        $weburl = '';
    }
    return $weburl;
  }

  //设置cookie
  //原common addcookie
	public function setcookie($name,$value,$time=0){
    $weburl = $this->_getCookieDomain();
    if(is_array($value)){
      foreach ($value as $k => $v) {
        SetCookie($name . '[' . $k . ']', $v, $time,'/', $weburl);
      }
    }else{
      SetCookie($name,$value,$time,"/",$weburl);
    }
  }
  
  public function add_cookie($uid,$username,$salt,$email,$pass,$type,$expire="1",$userdid=''){
	if($expire){
		$expire_date=$expire*86400;
	}else{
		$expire_date=86400;
	}
		
	if($this->config['did']&&$userdid==''){
		$userdid=$this->config['did'];
	}

    $this->setcookie("uid",$uid,time() + $expire_date);
    $this->setcookie("shell",md5($username.$pass.$salt), time() + $expire_date);
    $this->setcookie("usertype",$type,time()+$expire_date);
    $this->setcookie("userdid",$userdid,time()+$expire_date);
  }
    
  public function unset_cookie(){

	 
    $this->setcookie("uid","",time() - 604800,"/");
    $this->setcookie("shell", "", time() - 604800,"/");
    $this->setcookie("usertype","",time() - 604800,"/");
    $this->setcookie("userdid","",time() - 604800,"/");

    $this->setcookie("lookjob","",time() - 604800, "/");
    $this->setcookie("lookresume","",time() - 604800, "/");
    $this->setcookie("favjob","",time() - 604800, "/");
    $this->setcookie("talentpool","",time() - 604800, "/");
    $this->setcookie("useridjob","",time() - 604800, "/");
    $this->setcookie("exprefresh","",time() - 604800, "/");
    $this->setcookie("jobrefresh","",time() - 604800, "/");
    $this->setcookie("support","",time() - 604800, "/");
    
		if(is_weixin()){
			$this->setcookie("wxout",'1',time()+604800,"/");
		}
  }
}
