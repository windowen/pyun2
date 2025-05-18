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
class special_controller extends common{
	function index_action(){
		$this->yunset("headertitle","专题招聘");
		$this->seo("spe_index");
		$this->yuntpl(array('wap/spe_index'));
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

		$this->data	=	array('spename'=>$info['title']);
		$this->seo("spe_show");

		$this->yunset("headertitle","专题详情页");

		$this->yuntpl(array('wap/spe_show'));
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