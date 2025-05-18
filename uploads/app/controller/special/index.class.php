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
class index_controller extends special_controller{
	function index_action(){

		$this->seo("spe_index");
		$this->spetpl('index');
	}
	function show_action(){
		$specialM	=	$this->MODEL('special');
		$info		=	$specialM->getSpecialOne(array("id"=>(int)$_GET['id'],"display"=>1));

		$this->yunset("info",$info);

		if($this->uid && $this->usertype=='2'){
			$isapply	=	$specialM->getSpecialComOne(array("uid"=>$this->uid,"sid"=>(int)$_GET['id']));

			$applysuccess	=	$specialM->getSpecialComOne(array("uid"=>$this->uid,"sid"=>(int)$_GET['id']));
			$this->yunset("applysuccess",$applysuccess);
			$this->yunset("isapply",$isapply);
		}
		//设置用户类型
        if($this->uid &&  $this->usertype &&  $this->usertype != 2){
            $typename               =   array('1' => '个人账户');
            $this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
        }
        $this -> yunset('typename',$typename);

		$this->data	=	array('spename'=>$info['title']);
		$this->seo("spe_show");

		$tpl		=	@explode('.',$info['tpl']);

		$this->spetpl($tpl[0]);
	}
	function apply_action(){
		$data		=	array(
			'id'		=>	(int)$_POST['id'],
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
		);
		$specialM	=	$this->MODEL('special');
		$return		=	$specialM->addSpecialComInfo($data);
		if($return['url']){
			$this->layer_msg($return['msg'],$return['errcode'],0,$return['url']);
		}else{
			$this->layer_msg($return['msg'],$return['errcode'],0);
		}
	}
}
?>