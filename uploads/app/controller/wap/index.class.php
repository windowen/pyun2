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
class index_controller extends common{
	function index_action(){
		
		if(!$this->config['did'] && $this->config['sy_gotocity']=='1' && !$_COOKIE['gotocity']){
			go_to_city($this->config);
		}
		if($this->uid && $this->usertype=='1'){

			$resumeM    =  $this->MODEL('resume');
			
			$expect    =  $resumeM->getExpect(array('uid'=>$this->uid,'defaults'=>1),array('field'=>'id,status'));

			if(!empty($expect)){
				$user_resume	= $resumeM->getUserResumeInfo(array('uid'=>$this->uid,'eid'=>$expect['id']),array('field'=>'`skill`,`work`,`project`,`edu`,`training`'));
				$resume_yhnum = 0;
				foreach($user_resume as $rk=>$rv){
					if($rv==0){
						$resume_yhnum++;
					}
				}
				
				$this->yunset('resume_yhnum',$resume_yhnum);
			}
			$this->yunset('expect',$expect);
		}

		$this->get_moblie();
		if($this->config["did"]){
			$this->seo("index",$this->config['sy_webtitle'],$this->config['sy_webkeyword'],$this->config['sy_webmeta']);
		}else{
			$this->seo('index');
		}
			$this->yunset('indexnav',1);
		$this->yuntpl(array('wap/index'));
		
	}

	function loginout_action(){
		$this->cookie->unset_cookie();
		$this->wapheader('');
	}
	// 关于我们
	function about_action()
	{
	    $descM    =   $this->MODEL('description');
	    $content  =   $descM -> getDes(array('name'=>'关于我们'),array('field'=>'content'));
	    $this->yunset('content',$content);
	    $this->yunset('headertitle','关于我们');
	    $this->yunset('title','关于我们');
	    $this->yuntpl(array('wap/about'));
	}
    // 联系我们
	function contact_action()
	{
	    $descM    =   $this->MODEL('description');
	    $content  =   $descM -> getDes(array('name'=>'联系我们'),array('field'=>'content'));
	    $this->yunset('content',$content);
	    $this->yunset('headertitle','联系我们');
	    $this->yunset('title','联系我们');
	    $this->yuntpl(array('wap/about'));
	}
	
	// 隐私政策
	function privacy_action()
	{
	    $descM    =   $this->MODEL('description');
	    $content  =   $descM -> getDes(array('name'=>'隐私政策'),array('field'=>'content'));
	    $this->yunset('content',$content);
	    $this->yunset('headertitle','隐私政策');
	    $this->yunset('title','隐私政策');
	    $this->yuntpl(array('wap/about'));
	}
	// 用户协议
	function protocol_action()
	{
	    $descM    =   $this->MODEL('description');
	    $content  =   $descM -> getDes(array('name'=>'注册协议'),array('field'=>'content'));
	    $this->yunset('content',$content);
	    $this->yunset('headertitle','服务协议');
	    $this->yunset('title','服务协议');
	    $this->yuntpl(array('wap/about'));
	}
	
}
?>