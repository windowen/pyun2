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
class resume_controller extends common	
{
	
	public $userInfo	=	array();

    function __construct($tpl, $db, $def = '', $model = 'index', $m = '')
    {
        $this->common($tpl, $db, $def, $model, $m);
        
        if ($this->usertype == '2' || $this->usertype == '3') {
	
			$userInfoM	=	$this->MODEL('userinfo');

			$this->userInfo	=	$userInfoM -> getUserInfo(array('uid' => $this->uid), array('usertype' => $this->usertype));

			$this->yunset('userInfo', $this->userInfo);
        }
    }

}
?>