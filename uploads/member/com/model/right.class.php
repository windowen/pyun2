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
class right_controller extends company{
	function index_action(){
		$this	->	company_satic();
		$this	->	public_action();
		
		$ratingM	=	$this	->	MODEL('rating');
		$whereData	=	array(
			'category'	=>	'1',
			'service_price'	=>array('>','0'),
			'display'=>'1',
			'orderby'=>array('type,asc','sort,desc')
		);
		if($this->config['com_vip_type']==2 || $this->config['com_vip_type']==0){
			/*套餐模式*/
			$whereData['type']	=	'1';
			$rows	=	$ratingM	->	getList($whereData);

			$this->yunset("rows",$rows);
			$this->com_tpl('member_right');
		}elseif($this->config['com_vip_type']==1){
			/*时间模式*/
			$whereData['type']	=	'2';
			$times	=	$ratingM	->	getList($whereData);
			
			$this->yunset("times",$times);
			$this->com_tpl('member_time');
		}
		
	}
	
	function time_action(){
		$this	->	company_satic();
		$this	->	public_action();
		
		$ratingM	=	$this	->	MODEL('rating');
		$whereData	=	array(
			'category'		=>	'1',
			'service_price'	=>	array('>','0'),
			'display'		=>	'1',
			'orderby'		=>	array('type,asc','sort,desc')
		);
		if($this->config['com_vip_type']==2){
			/*套餐模式*/
			$whereData['type']	=	'1';
			$rows	=	$ratingM	->	getList($whereData);
			$this->yunset("rows",$rows);
			$this->com_tpl('member_right');
		}elseif($this->config['com_vip_type']==1 || $this->config['com_vip_type']==0){
			/*时间模式*/
			$whereData['type']	=	'2';
			$times	=	$ratingM	->	getList($whereData);
			
			$this	->	yunset("times",$times);
			$this	->	com_tpl('member_time');
		}
	}
	
	function added_action(){
		$statis		=	$this	->company_satic();
		if($statis['rating_type'] == 2){
		    $this->ACT_msg("index.php?c=right", "时间会员无需购买增值服务！");
		}
        $this		->	public_action();
		$id			=	intval($_GET['id']);

		$ratingM	=	$this	->	MODEL('rating');	
		$rows		=	$ratingM	->	getComServiceList(array('display'=>'1','orderby'=>'sort,desc'));

		if(empty($id)){
			//$row	=	$ratingM	->	getComServiceInfo(array('display'=>'1','orderby'=>'sort,desc'),array('field'=>'id'));
			$id		=	$rows[0]['id'];
		}
		$info		=	$ratingM	->	getComSerDetailList(array('type'=>$id,'orderby'=>'sort,desc'));

		if ($statis){
			$discount	=	$ratingM	->	getInfo(array('id'=>$statis['rating']));
			$this		->	yunset("discount",$discount);
		}
		$this	->	yunset("info",$info);
		$this	->	yunset("rows",$rows);
		$this	->	com_tpl('added');
	}
}
?>
