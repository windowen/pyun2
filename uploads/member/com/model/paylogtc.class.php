<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class paylogtc_controller extends company{
	function index_action(){
		$this	->	public_action();
		$statis	=	$this	->	company_satic();
		if($statis['rating']){
			$ratingM	=	$this	->	MODEL('rating');
			$rating		=	$ratingM	->	getInfo(array('id'=>$statis['rating']));
		}
		$comM	=	$this	->	MODEL('company');
		$com	=	$comM	->	getInfo($this->uid);
		if($statis['days']){
			
			$this	->	yunset("days",$statis['days']);
			
		}		
		
		$comorderM		=	$this		->	MODEL('companyorder');
		$allprice		=	$comorderM	->	getCompanyPaySumPrice(array('com_id'=>$this->uid,'usertype' => 2,'type'=>'1','order_price'=>array('<','0')));
		
		$statis['zhjf']	=	number_format($statis['integral']);
  		$this	->	yunset("integral",number_format(str_replace("-","", $allprice)));
		$this	->	yunset("com",$com);
		$this	->	yunset("statis",$statis);
		$this	->	yunset("rating",$rating);
		$this	->	com_tpl('paylogtc');
	}
}
?>